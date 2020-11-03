<?php

namespace app\api\job;
use app\api\model\Activity;
use app\api\model\Activity as ActivityModel;
use app\api\model\Email as EmailModel;
use app\api\model\Log;
use app\api\model\Message;
use app\api\model\User;
use app\lib\email\SwiftMailer;
use app\lib\redis\QueueHelper;
use think\Exception;
use think\Queue;
use think\queue\Job;


/**
 * 处理24小时内推送新活动提醒
 * Class RouteMsg
 * @package app\index\job
 */
class RouteNewMsg {

    public $jobHandlerClassName  = 'app\api\job\RouteNewMsg';

    public $jobQueueName = "RouteNewMsg";

    public $realDelayQueueName;

    public $delayTime = (60 * 60 * 24);

    public $msgTitle = "Hey %s, here are the latest giveaways you can enter: ";

    public $emailSubject = "Check our latest giveaways";

    public function __construct()
    {
        // 调试状态下，使用10秒进行处理
        $this->delayTime = !config('app_debug') ? $this->delayTime : 30;
        $this->realDelayQueueName = 'queues:'. $this->jobQueueName .':delayed';
    }

    /**
     * fire方法是消息队列默认调用的方法
     * @param Job $job
     * @param $data
     * @throws Exception
     * @throws \Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function fire(Job $job,$data)
    {
        // 有些消息在到达消费者时,可能已经不再需要执行了
        $isJobStillNeedToBeDone = $this->check($data, false);
        if(!$isJobStillNeedToBeDone){
            $job->delete();
            return;
        }

        $isJobDone = $this->execute($data);
        if ($isJobDone) {
            // 如果任务执行成功， 则循环执行24小时后再次执行
            $job->release($this->delayTime);
        }else{
            // 发送失败，用户已经无法发送msgr消息
            $job->delete();
        }
    }

    /**
     * 有些消息在到达消费者时,可能已经不再需要执行了
     * @param array|mixed    $data     发布任务时自定义的数据
     * @param $beforeInsert  boolean   是否在插入之前检查
     * @return bool          任务执行的结果
     */
    private function check($data, $beforeInsert){
        // 推送给用户的消息不能是用户已参与的
        // 如果用户已经取消订阅，则取消推送
        $return = false;
        $confirm = $data['confirm'];
        if (!$beforeInsert) {
            $confirm =  User::where(['id' => $data['userID']])->value('confirm');
        }
        if (!$confirm) {
            return $return;
        }

        if (empty($data['messageID']) && empty($data['email'])) {
            return $return;
        }

        if($data['messageID']){
            // 检查队列中是否已经存在对应的任务
            $queueHelper = new QueueHelper($this->realDelayQueueName);
            $pattern = '*'.$data['messageID'].'*';
            $return = !$queueHelper->checkRedisQueue($data, $pattern);

            Log::create([
                'topic' => "routeNewMsg",
                "log" => json_encode([$return, $data, $pattern])
            ]);
        }

        return $return;

    }


    /**
     * 该方法用于接收任务执行失败的通知，你可以发送邮件给相应的负责人员
     * @param $jobData  string|array|...      //发布任务时传递的 jobData 数据
     */
    public function failed($jobData){
        Log::create([
            'topic' => 'RouteTask@'.__CLASS__,
            'log'   => "Warning: Job failed after max retries. job data is :".var_export($jobData, true)."\n"
        ]);
    }

    /**
     * 执行对用户进行新活动提醒 执行推送任务
     * @param $data
     * @return bool
     * @throws \Exception
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function execute($data)
    {
        $otherRecommendMsg = $this->_handleRecommendation($data);
        if (!$otherRecommendMsg) {
            // 没有推送内容，无需发送
            return true;
        }

        if (!empty($data['messageID'])){
            $this->sendRouteMsg($data, $otherRecommendMsg);
        }

        if (!empty($data['email']) && !checkSubscribeStatus($data['email'])) {
            $this->sendRouteEmail($data, $otherRecommendMsg);
        }

        return true;
    }

    /**
     * 处理其他推荐
     * @param $data  array 队列中的数据
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function _handleRecommendation($data)
    {
        $msgTitle = sprintf($this->msgTitle, $data['userName']);
        $recommend = ActivityModel::getRecommendations($data['userID']);
        if ($recommend) {
            return (new EmailModel())->handleRecomEmailStr($msgTitle, $recommend);
        }
        return false;
    }


    /**
     * 使用msgr进行日常活动推送
     * @param $data
     * @param $msg
     * @return bool
     * @throws \Exception
     * @throws \think\Exception
     */
    public function sendRouteMsg($data, $msg)
    {
        // 使用msgr发送日常提醒
        return Message::sendTextMessage($data['messageID'], $msg);
    }


    /**
     * 使用邮件发送日常提醒
     * @param $data
     * @param $msg
     * @return bool|\Mailgun\Model\Message\SendResponse
     */
    private function sendRouteEmail($data, $msg)
    {
        $subject = $this->emailSubject . ' '. date('Y-m-d');
        try{
            return sendMailGunEmail($data['email'], $subject, $msg);
        }catch(Exception $e){
            Log::create([
                'topic' => 'winner email',
                'log'   => json_encode(['err_msg' => $e->getMessage(), 'email' => $data['email'], 'content' => $msg])
            ]);
            return false;
        }
    }

    /**
     * 插入前做好检查
     * @param $data
     * @param $beforeInsert
     * @return bool
     */
    public function createRouteMsg($data, $beforeInsert)
    {
        if (!$this->check($data, $beforeInsert)) {
            return false;
        }

        return Queue::later($this->delayTime, $this->jobHandlerClassName, $data, $this->jobQueueName);
    }


}