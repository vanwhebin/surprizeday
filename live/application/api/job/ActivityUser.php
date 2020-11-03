<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/8/7
 * Time: 14:31
 */
namespace app\api\job;

use app\api\model\ActivityTeam as ActivityTeamModel;
use app\api\model\ActivityTeamUser as ActivityTeamUserModel;
use app\api\model\Log as LogModel;
use app\api\model\Message as MessageModel;
use app\api\model\User as UserModel;
use app\api\service\Token;
use think\Exception;
use think\facade\Hook;
use think\Queue;
use think\queue\Job;

class ActivityUser
{
    const TEAMUP_HANDLER = 'app\api\job\ActivityUser@teamup';

    // const QUEUE_NAME = 'activityUserQueue';
    const QUEUE_NAME = 'messengerQueue';
    const MAX_ENTRY = 200;
    const INC_ENTRY = 2;   // 新增两个entry

    const FIRST_DELAY_SHARE = (3);   // 确认消息3秒延迟发送任务

    const DEBUG_FIRST_DELAY_SHARE = 1;   // 调试状态下延迟1秒发送任务

    /**
     * 队列任务失败写入日志，发送给管理员
     * @param $data
     * @return bool
     */
    public function failed($data)
    {
        // 任务达到最大执行次数之后，失败了
        LogModel::create([
            "log"   =>  var_export($data, true),
            "topic" =>  __CLASS__.'@'.self::QUEUE_NAME."@queue_failed",
        ]);
        return sendMsg2Manager(json_encode($data));
    }

    /**
     * * 执行处理用户组队
     * @param $teamMaster
     * @param $data
     * @return bool
     * @throws Exception
     * @throws \Exception
     */
    public function handleTeamUp($teamMaster, $data)
    {
        ActivityTeamUserModel::create([
            'activity_user_id' => $data['activityUserID'],
            'team_id' => $teamMaster->id,
            'user_id' => $data['curUser']['id'],
        ]);
        $teamMaster->setInc('cur_entry',self::INC_ENTRY);
        // 在activity_team_user表中新增记录

        $referUser = UserModel::get($teamMaster->user_id);
        $name = explode(" ", $data['curUser']['name'])[0];
        $feedbackContent = sprintf(config('fb.TEAM_UP_FEEDBACK'), $name);
        MessageModel::sendTextMessage($referUser->message_id, $feedbackContent);
        return true;
    }


    /**
     * 用户组队操作
     * @param Job $job
     * @param $data
     * @return bool
     * @throws \Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */

    public function teamup(Job $job, $data)
    {
        $teamMaster = ActivityTeamModel::where([
            'activity_id' => $data['activityID'],
            'user_id' => $data['referUserID'],
        ])->find();
        if (!$teamMaster) {
            $teamMaster = ActivityTeamModel::create([
                'activity_id' => $data['activityID'],
                'user_id' => $data['referUserID'],
                'max_entry' => self::MAX_ENTRY,
                'cur_entry' => 1,
            ]);
        }
        $teamMasterArr = $teamMaster->toArray();
        LogModel::create([
            "log" => json_encode([$teamMasterArr, $data]),
            "topic" => "teamMaster",
        ]);

        $exceedMaxCountOrTeamUser = $this->_checkBeforeFire($teamMasterArr,  $data['curUser']['id']);
        if ($exceedMaxCountOrTeamUser) {
            $job->delete();
            return true;
        }

        try{
            $isJobDone = $this->handleTeamUp($teamMaster, $data);
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

    // 执行前检查
    protected function _checkBeforeFire($teamMasterArr, $curUserID)
    {
        // TODO 检查是否接收推送
        return $this->_checkMaxCountTeamUser($teamMasterArr, $curUserID);
    }

    /**
     * 需要判断组队的人数是否大于最大限制人数
     * 记录是否已经存在
     * @param $teamMasterArr array ActivityTeam表记录对象数组
     * @param $curUserID integer user表记录ID，表示当前用户
     * @return bool
     */
    protected function _checkMaxCountTeamUser($teamMasterArr, $curUserID)
    {
        if ($teamMasterArr['max_entry'] && ($teamMasterArr['cur_entry'] > $teamMasterArr['max_entry'])) {
            return $teamMasterArr['max_entry'];
        } else {
            $teamUser = ActivityTeamUserModel::getOne($teamMasterArr['id'], $curUserID);
            return $teamUser;
        }
    }


    /**
     * 处理用户来源推荐的情况
     * @param $activityID integer 当前活动ID
     * @param $curUser  object 当前用户的用户对象
     * @param $activityUser  object 当前活动参与者的活动用户记录对象
     * @param $refer array 当前用户所refer进来的用户信息
     * @return bool|mixed
     */
    public static function handleChanel($activityID, $curUser, $activityUser, $refer)
    {
        $userIDArr = json_decode(Token::getVarByToken($refer['refer_user']), true);
        if (empty($userIDArr) || empty($userIDArr['uid']) || $userIDArr['uid'] == $curUser->id){
            return false;
            // throw new InvalidParamException(['msg' => "Your are in! but invalid share link."]);
        }
        if (!$refer) {
            return true;
        } else {
            //  记录
            if ($refer['refer_type'] === 'share') {
                // 写入组队队列
                $data['referUserID'] = $userIDArr['uid'];
                $data['activityUserID'] = $activityUser->id;
                $data['curUser'] = $curUser->toArray();
                $data['activityID'] = $activityID;
                LogModel::create([
                    "log" => json_encode([$data, $userIDArr, $activityID, $refer, $activityUser->id, $curUser]),
                    "topic" =>"test teamup"
                ]);

                return Queue::later(1,self::TEAMUP_HANDLER, $data, self::QUEUE_NAME);
            }
            // TODO 其他的形式处理 等待扩展可能性
            return true;
        }
    }

    
}