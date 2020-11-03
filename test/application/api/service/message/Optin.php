<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/11/16
 * Time: 17:55
 */

namespace app\api\service\message;

use app\api\job\Messenger as MessengerJob;
use app\api\model\Activity as ActivityModel;
use app\api\model\Log;
use app\api\model\Message as MessageModel;
use app\api\model\User as UserModel;
use app\api\service\Activity as  ActivityService;

class Optin
{

    /**
     * 用户同意推送消息
     * @param $user
     * @param $messaging
     * @return bool
     * @throws \Exception
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function handleOptin($user, $messaging)
    {
        Log::create([
            'log' => json_encode([
                'request'=> [
                    'event' => $messaging,
                ],
            ]),
            'topic' => 'optIn',
        ]);
        $senderID = $messaging['sender']['id'];
        $activityID = $messaging['optin']['ref'];
        $newUserNull = $user;
        if (!$user) {
            $user = self::handleRetrieveUserID($messaging);
        }
        $activityInfo = ActivityModel::getSimpleInfo($activityID)->toArray();
        $info['activityID'] = $activityID;
        $info['title'] = config('fb.CONFIRM_JOIN_MSG');
        $info['image_url'] = $activityInfo['thumb']['url'];
        $info['subtitle'] = $activityInfo['title'];
        $info['url'] = config('domain'). getActivityUrl($activityInfo['slug']);
        $data = [
            'info' => $info,
            'start_time' => $activityInfo['start_time'],
            'userID' => $user->id,
            'userName' => explode(" ", $user->name)[0],
            'messageID' => $user->message_id,
            'activityID' => $activityID,
            'type' => MessengerJob::FIRST_DELAY_NOTIFICATION,
        ];
        if (!$newUserNull) {
            // 写入一条30分钟后的推送信息
            (new MessengerJob())->sendConfirmMsg($data);
        }
        return MessageModel::sendConfirmMsg($senderID, $data);
    }

    /**
     * @param $event
     * @return array|bool|null|\PDOStatement|string|\think\Model
     * @throws \Exception
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function handleRetrieveUserID($event)
    {
        $senderID = $event['sender']['id'];
        $activityID = $event['optin']['ref'];
        $res = UserModel::retrievePSIDUserInfo($senderID);
        if (!array_key_exists('error', $res)) {
            $appIds = $res['data'];
            $arr = [];
            foreach($appIds as $k=>$v) {
                $arr[$v['app']['id']] = $v['id'];
            }
            $appId = config('fb.APP_ID');

            $userFBID = $arr[$appId]; // fb的ID
            if ($userFBID) {
                // 更新用户信息
                $user = UserModel::where(['userid'=>$userFBID])->find();
                $user->message_id = $senderID;
                $user->save();
                // 增加一条推送通知记录
                ActivityService::connected($user->id, $activityID, $senderID);
                return $user;
            }
            return false;
        }
        return false;
    }


}