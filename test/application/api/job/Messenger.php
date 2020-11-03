<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/7/29
 * Time: 15:22
 */

namespace app\api\job;

use app\api\model\Activity as ActivityModel;
use app\api\model\ActivityUser as ActivityUserModel;
use app\api\model\Log;
use app\api\model\Log as LogModel;
use app\api\model\Message as MessageModel;
use app\api\model\User as UserModel;
use app\api\model\Winner as WinnerModel;
use app\api\service\message\Postback;
use app\api\service\UserToken;
use app\lib\email\SwiftMailer;
use app\lib\redis\RedisClient;
use think\Exception;
use think\facade\Hook;
use think\Queue;
use think\queue\Job;

class Messenger
{
    const CONFIRM_MSG_HANDLER = 'app\api\job\Messenger@confirmMsg';
    const TIME_UP_MSG_HANDLER = 'app\api\job\Messenger@timeUpMsg';
    const TEAM_UP_MSG_HANDLER = 'app\api\job\Messenger@teamUpMsg';

    const QUEUE_NAME = 'debugMessengerQueue';

    const FIRST_DELAY_NOTIFICATION = (30 * 60);   // 确认消息30分钟延迟发送任务
    const LAST_DELAY_NOTIFICATION = (23 * 60 * 60); // 最后提醒23.5小时之后
    const TEAMUP_MSG_DELAY = 1 ; // 两秒之后

    const DEBUG_FIRST_DELAY_NOTIFICATION = 15;   // 调试状态下延迟30秒发送任务
    const DEBUG_LAST_DELAY_NOTIFICATION =  2 * 15; // 调试状态下延迟60秒发送任务
    const DEBUG_WINNER_DELAY_NOTIFICATION = 30; // 调试状态下延迟30秒发送任务

    public $realDelayQueueName = 'queues:'.self::QUEUE_NAME.':delayed';
    public $realDelayQueueNameIndex;

    public $winnerEmailSubject = "Result of";

    public function __construct()
    {
        $this->realDelayQueueNameIndex = explode('@',self::CONFIRM_MSG_HANDLER)[1];
    }

    /**
     * 队列任务失败写入日志，发送给管理员
     * @param $data
     * @return bool
     */
    public function failed($data)
    {
        // 任务达到最大执行次数之后，失败了
        LogModel::create([
            "log" => var_export($data, true),
            "topic" => __CLASS__.'@'.self::QUEUE_NAME."@queue_failed",
        ]);
        return sendMsg2Manager(json_encode([$data]));
    }

    /**
     * 通知参与活动的用户开奖信息, 30分钟和23小时
     * 给用户发送活动开奖任务方法
     * @param Job $job
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function timeUpMsg(Job $job, $data)
    {
        $curActivity = ActivityModel::where(['id' => $data['activityID']])
            ->field(['start_time', 'slug', 'title', 'id'])->find();
        $delay = $this->_checkDelayed($curActivity);
        if ($delay) {
            $job->release($delay);
            // return true;
        } else {
            $isJobStillNeedToBeDone = $this->_checkWinnerMsg($data);
            if (!$isJobStillNeedToBeDone) {
                $job->delete();
                return true;
            }

            try{
                $isJobDone = MessageModel::sendNotifyMessage($data['messageID'], $data['info']);
                if (!empty($data['email']) && !checkSubscribeStatus($data['email'])) {
                    $this->_sendWinnerEmail($data['email'], $data['info']);
                }
            }catch (Exception $e){
                return true;
            }
            if ($isJobDone) {
                // 如果任务执行成功， 删除任务
                $job->delete();
                return true;
            } else {
                if ($job->attempts() > 3) {
                    //通过这个方法可以检查这个任务已经重试了几次了
                    $job->delete();
                    return true;
                }
            }
        }
    }

    /**
     * 发送邮件
     * @param $recipient
     * @param $activityInfo
     * @return \Mailgun\Model\Message\SendResponse
     */
    protected function _sendWinnerEmail($recipient, $activityInfo)
    {
        $subject = $this->winnerEmailSubject . " {$activityInfo['seo_title']} ". date('Y-m-d');
        $content = $this->_handleEmailContent($activityInfo);
        try{
            return sendMailGunEmail($recipient, $subject, $content);
        }catch(Exception $e){
            Log::create([
                'topic' => 'winner email',
                'log'   => json_encode(['err_msg' => $e->getMessage(), 'email' => $recipient, 'content' => $content])
            ]);
        }
    }


    protected function _handleEmailContent($activityInfo)
    {
        $content = "Hello,".PHP_EOL.PHP_EOL. "<br><br>The winner has been drawn for {$activityInfo['seo_title']}<br><br>".PHP_EOL;
        $content .= " <a href='". $activityInfo['url'] ."'>Check Winner</a><br><br>".PHP_EOL.PHP_EOL;
        $content .= ("Have a wonderful day!<br>".PHP_EOL."Cheers,<br>".PHP_EOL."Surprize Team<br><br>");

        return $content;
    }


    /**
     * 给用户发送活动参与确认消费方法
     * @param Job $job
     * @param $data
     * @return bool
     * @throws \Exception
     */

    public function confirmHandler(Job $job, $data)
    {
        try {
            MessageModel::sendTextMessage($data['messageID'], $data['info']['title']);
            if (array_key_exists('type', $data)) {
                // 这是确认消息30分钟延迟发送任务
                // 再插入一条23小时之后的延迟消息，因为如果用户没有点击确认，在开奖前是无法推送的
                $delayTime = config('app_debug')? self::DEBUG_LAST_DELAY_NOTIFICATION: self::LAST_DELAY_NOTIFICATION;
                $handler = self::CONFIRM_MSG_HANDLER;
                $queueName = self::QUEUE_NAME;
                unset($data['type']);
                $data['info']['title'] = sprintf(config('fb.SECOND_MSG_CONFIRM_TITLE'), $data['userName']);
                Queue::later($delayTime, $handler, $data , $queueName);
            }
            return true;
        } catch (Exception $e) {
            Hook::listen('queue_failed', ['request' => $job->getRawBody(), 'response' => $e->getMessage()]);
            return false;
        }

    }

    /**
     * 用户参与活动确认信息推送
     * @param Job $job
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function confirmMsg(Job $job, $data)
    {
        // 有些消息在到达消费者时,可能已经不再需要执行了
        $isJobStillNeedToBeDone = $this->_checkConfirmMsg($data);
        if (!$isJobStillNeedToBeDone) {
            $job->delete();
            return true;
        }

        $isJobDone = $this->confirmHandler($job, $data);

        if ($isJobDone) {
            // 如果任务执行成功， 记得删除任务
            $job->delete();
            return true;
        } else {
            if ($job->attempts() > 3) {
                //通过这个方法可以检查这个任务已经重试了几次了
                $job->delete();
                return true;
            }
        }
    }

    /**
     * 用户组队消息推送
     * @param Job $job
     * @param $data array
     * @return bool
     * @throws \Exception
     */
    public function teamupMsg(Job $job, $data)
    {
        try{
            $isJobDone = MessageModel::sendTeamUpMsg($data['messageID'], $data);
        }catch (Exception $e){
            Hook::listen('queue_failed', ['request' => $job->getRawBody(), 'response' => $e->getMessage()]);
            return true;
        }

        if ($isJobDone) {
            // 如果任务执行成功， 删除任务
            $job->delete();
            return true;
        } else {
            if ($job->attempts() > 3) {
                //通过这个方法可以检查这个任务已经重试了几次了
                $job->delete();
                return true;
            }

        }
    }

    /**
     * 发送组队信息的生产动作
     * @param $data array 发送msgr信息使用的数据
     * @param $user object 当前用户的user对象
     * @param $expireLeftTime integer 活动有效期的剩余时间
     * @return bool
     * @throws \app\lib\exception\TokenException
     */
    public static function sendTeamUpMsg($data, $user, $expireLeftTime)
    {
        $userToken = (new UserToken())->getShareToken($user->id, "facebook", $expireLeftTime);
        $info['title'] = $data['info']['seo_title'];
        $info['image_url'] = $data['info']['image_url'];
        $info['url'] = $data['info']['url']. "?". http_build_query(['srefer' => 'share','fbmsguid' => $userToken]);
        $data['info'] = $info;
        $handler = self::TEAM_UP_MSG_HANDLER;
        $queueName = self::QUEUE_NAME;
        LogModel::create([
            "log" =>json_encode($data),
            "topic" => "queue_teamup",
        ]);
        Queue::later(1, $handler, $data, $queueName);
        return true;
    }

    /**
     * 用户确认之后在开奖时间发送提示信息
     * @param $payload
     * @param $user
     * @return bool
     * @throws Exception
     * @throws \Exception
     * @throws \app\lib\exception\TokenException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function sendWinnerMsg($payload, $user)
    {
        $activityInfo = ActivityModel::getSimpleInfo($payload['activity_id'])->toArray();
        $data['userID'] = $user->id;
        $data['activityID'] = $payload['activity_id'];
        $data['messageID'] = $user->message_id;
        $data['userName'] = $user->name;
        $data['email'] = $user->email;
        $data['confirm'] = $user->confirm;
        $info['title'] = config('fb.WINNER_MSG_TITLE');
        $info['seo_title'] = $activityInfo['seo_title'];
        $info['image_url'] = $activityInfo['thumb']['url'];
        $info['subtitle'] = $activityInfo['title'];
        $info['private'] = $activityInfo['private'];
        $info['url'] = getActivityUrl($activityInfo['slug']);
        $data['info'] = $info;
        $data['handleTime'] = $activityInfo['start_time'] - time() + rand(1, 60);
        $delayTime = config('app_debug')? self::DEBUG_WINNER_DELAY_NOTIFICATION : $data['handleTime'];
        $handler = self::TIME_UP_MSG_HANDLER;
        $queueName = self::QUEUE_NAME;

        Queue::later($delayTime, $handler, $data , $queueName);
        return self::activityTypeHandler($activityInfo, $user, $data);
    }

    /**
     *  * 根据活动类型来进行后续处理
     * @param $activityArr  array 活动信息
     * @param $user object 当前用户信息
     * @param $data array  需要用到的活动信息
     * @return bool
     * @throws Exception
     * @throws \Exception
     * @throws \app\lib\exception\TokenException
     */

    public static function activityTypeHandler($activityArr, $user, $data)
    {
        switch ($activityArr['type']) {
            case 1:  // 默认无任何操作
                return MessageModel::sendTextMessage($user->message_id, Postback::DEFAULT_POSTBACK_AGREE_REPLY);
            case 2:  // 组团
                MessageModel::sendTextMessage($user->message_id, Postback::TEAMUP_POSTBACK_AGREE_REPLY);
                $userToken = (new UserToken())->getShareToken($user->id, "facebook", $data['handleTime']);
                $info['title'] = $data['info']['seo_title'];
                $info['image_url'] = $data['info']['image_url'];
                $info['url'] = $data['info']['url']. "?". http_build_query(['srefer' => 'share','fbmsguid' => $userToken]);
                $data['info'] = $info;
                return MessageModel::sendTeamUpMsg($user->message_id, $data);
                // return self::sendTeamUpMsg($data, $user, $data['handleTime']);
            default:  // 默认无任何操作
                return true;
        }
    }


    /**
     * 写入一条用户确认的推送信息
     * @param $jobDataDelay
     * @return bool
     */
    public function sendConfirmMsg($jobDataDelay)
    {
        $this->_checkConfirmMsg($jobDataDelay, false);
        $jobDataDelay['info']['title'] = sprintf(config('fb.FIRST_MSG_CONFIRM_TITLE'), $jobDataDelay['userName']);
        $jobHandlerClassName = self::CONFIRM_MSG_HANDLER;
        $jobQueueName = self::QUEUE_NAME;
        $jobDelayTimeType = config('app_debug')? self::DEBUG_FIRST_DELAY_NOTIFICATION: self::FIRST_DELAY_NOTIFICATION;

        Queue::later($jobDelayTimeType, $jobHandlerClassName , $jobDataDelay , $jobQueueName);
        return true;
    }

    /**
     * 一旦用户点击确认信息，就不需要执行任务
     * @param array|mixed $data 发布任务时自定义的数据
     * @param boolean $beforeExecute 检查的时机,0表示插入队列前的检查,1表示执行队列前的检查,时候有必要执行
     * @return boolean                 任务执行的结果
     */
    protected function _checkConfirmMsg($data, $beforeExecute=true)
    {
        if ($data['start_time'] < time() || $data['messageID']) {
            return false;
        }

        $confirm = UserModel::where('id', '=', $data['userID'])->column('confirm');
        if (!$confirm) {
            return false;
        }
        // if($beforeExecute) {
        //     $activityUser = ActivityUserModel::getOne($data['activityID'], $data['userID']);
        //     return !boolval($activityUser->confirm);
        // } else {
        //     $searchPattern = '*'.$this->realDelayQueueNameIndex.'*'.$data['messageID'].'*';
        //     if(!$this->_checkRedisQueue($data, $searchPattern)){
        //         return true;
        //     } else {
        //         // 如果需要的话，可以对数据进行处理
        //         return false;
        //     }
        // }
    }


    /**
     * 检查队列中的任务信息
     * @param $data
     * @param $searchPattern
     * @return array|bool
     */
    protected function _checkRedisQueue($data, $searchPattern)
    {
        // 检查是否存在重复的确认信息
        $redis = new RedisClient();
        $redis->connect($redis->host, $redis->port);
        $redis->auth($redis->password);
        $redis->select($redis->db);
        $redis->setOption(\Redis::OPT_SCAN, \Redis::SCAN_NORETRY);
        $it = null;
        while ($arr = $redis->zScan($this->realDelayQueueName, $it, $searchPattern, 1000)) {
            if (count($arr)) {
                $redis->close();
                return $arr;
            }
        }
        $redis->close();
        return false;
    }

    /**
     * 用户已查看结果
     * @param array|mixed $data 发布任务时自定义的数据
     * @return boolean        任务执行的结果
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function _checkWinnerMsg($data)
    {
        if (!$data['confirm']) {
            // 用户取消推送
            return false;
        }

	    $winner = WinnerModel::where(['activity_id' => $data['activityID']])->find();
        if (!$winner) {
            return false;
        }
        $activityUser = ActivityUserModel::getOne($data['activityID'], $data['userID']);
        if ($activityUser->checked) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 检查当前任务是否已经延时
     * @param $data
     * @return bool|int|mixed
     */
    protected function _checkDelayed($data)
    {
        return (intval($data['start_time']) <= time()) ? false: ($data['start_time'] - time());
    }

}