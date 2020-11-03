<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/11/16
 * Time: 18:03
 */

namespace app\api\service\message;


use app\api\model\Activity as ActivityModel;
use app\api\model\Message as MessageModel;
use app\api\service\Activity as ActivityService;
use app\api\model\User as UserModel;
use app\lib\enum\ActivityEnum;

class Referral
{

    /**
     * @param $user
     * @param $messaging
     * @return bool
     * @throws \Exception
     * @throws \app\lib\exception\TokenException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function handleReferral($user, $messaging)
    {
        $refer = $messaging['referral'];
        if ($refer['type'] === 'SHORTLINK') {
            if (!$user) {
                $user = UserModel::retrieveMsgUserInfoByMessageID($messaging['sender']['id']);
                $data = [
                    'name'          => $user['first_name']. ' '. $user['last_name'],
                    'avatar'        => $user['profile_pic'],
                    'message_id'    => $user->message_id,
                ];
                $user = UserModel::newUser($data);
            }
            return self::handleShortLink($user, $refer['ref']);
        }
        // 留待其他可能性
        return true;
    }


    /**
     * 用户通过m.me短链参与活动，并给予选择反馈
     * @param $user
     * @param $ref
     * @return bool
     * @throws \Exception
     * @throws \app\lib\exception\TokenException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function handleShortLink($user, $ref)
    {
        $ref = htmlspecialchars(strip_tags($ref));
        if (!is_numeric($ref)) {
            return false;
        }
        $actID = ActivityModel::where(['private' => ActivityEnum::PUBLIC, 'status' => ActivityEnum::SHOW])
            ->findOrEmpty(intval($ref));
        if (!$actID) {
            return false;
        } else {
            ActivityService::enroll($actID, $user->message_id);

            $payload = [
                'template_type' => "button",
                'text' =>  sprintf("Hi %s, how can we help you?", $user['first_name']),
                'buttons'  => [
                    [
                        'type'   =>  "postback",
                        'title'  => "Freebies (full rebate)",
                        'payload'=> json_encode(['status'=>Postback::FIRST_REFERRAL_REBATE_CHOICE]),
                    ],
                    [
                        "type" => 'postback',
                        "url" => 'Enter Giveaways',
                        "title" => json_encode(['status'=>Postback::FIRST_REFERRAL_GIVEAWAY_CHOICE]),
                    ],
                ]
            ];
            return MessageModel::sendButtonMessage($user->message_id, $payload);
        }
    }


}