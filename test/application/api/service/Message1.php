<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/6/17
 * Time: 17:37
 */

namespace app\api\service;

use app\api\job\Messenger as MessengerJob;
use app\api\model\Activity as ActivityModel;
use app\api\model\ActivityUser as ActivityUserModel;
use app\api\model\Log;
use app\api\model\Message as MessageModel;
use app\api\model\PrizeMore as PrizeMoreModel;
use app\api\model\User as UserModel;
use think\facade\Request;

class Message1
{
    /**
     * @param $senderID
     * @param $content
     * @return bool
     * @throws \Exception
     * @throws \think\Exception
     */
    public static function sendActivityNotification($senderID, $content)
    {
        $message = [
            'attachment' => [
                'type' => 'template',
                'payload' => [
                    "template_type"=>"generic",
                    'elements'  => [$content],
                ],
            ],
        ];

        return MessageModel::callSendAPI($senderID, $message);
    }

    /**
     * @param $event
     * @return bool
     * @throws \Exception
     * @throws \think\Exception
     */
    public static function handleMessage($event)
    {
        Log::create([
            'topic' => "handleMessage",
            'log' => json_encode(['event' => $event, 'param' => Request::post(), 'time' =>date('Y-m-d H:i:s')])
        ]);
        $senderID = $event['sender']['id'];
        $message = $event['message'];
        $messageText = isset($message['text']) ? $message['text'] : '';
        $messageAttachments = isset($message['attachments'])?$message['attachments']:"";
        if ($messageText) {
            $lowerStr = strtolower(str_replace([' ', '"', "'"], ['', '', ''], $messageText));
            switch ($lowerStr) {
                case 'stop':
                case 'unsubscribe':
                    return MessageModel::handleSubscribeMessage($senderID, 0);
                    break;
                case 'confirm':
                case 'entered':
                case 'enter':
                case 'done':
                    return MessageModel::handleSubscribeMessage($senderID, 1);
                    break;
                // case 'freebies':
                case 'hair':
                    return self::handleReviewCallMessage($senderID);
                    break;
                default:
                    return true;
            }
        } else if ($messageAttachments) {
            Log::create([
                'topic' => "freebies feedback",
                "log"   => json_encode(['message_id' => $senderID, 'attachment'=> $messageAttachments]),
            ]);

            return true;
        }
        return true;
    }


    /**
     *  * 向用户发送备选产品
     * @param $senderID
     * @return bool
     * @throws \Exception
     * @throws \think\Exception
     */
    public static function handleReviewCallMessage($senderID)
    {
        MessageModel::sendTextMessage($senderID, 'Limited to one flat iron per household. Get full rebate from us after reviewing the product. ');
        MessageModel::sendImageMessage($senderID,'https://images-na.ssl-images-amazon.com/images/I/61dUkdMcj%2BL._SL1500_.jpg');
        MessageModel::sendImageMessage($senderID,'https://images-na.ssl-images-amazon.com/images/I/61tvyUZEJeL._SL1485_.jpg');
        MessageModel::sendImageMessage($senderID,'https://www.surprizeday.com/media/20191119/fac3dd29d0deff8fea725ee0aace2d90.jpg');

        $payload= [
            'template_type' => "button",
            'text' =>  "Tap below to claim offer. You'll need an active Amazon account and Paypal to receive rebate.",
            'buttons'  => [
                [
                    'type'   => "postback",
                    'title'  => "Claim now",
                    'payload'=> json_encode(['status' => MessageModel::POSTBACK_REBATE])
                ]
            ]
        ];
        return MessageModel::sendButtonMessage($senderID,$payload);
    }



    /**
     * 用户同意推送消息
     * @param $event
     * @return bool
     * @throws \Exception
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function handleOptin($event)
    {
        Log::create([
            'log' => json_encode([
                'request'=> [
                    'event' => $event,
                ],
            ]),
            'topic' => 'optIn',
        ]);
        $senderID = $event['sender']['id'];
        $activityID = $event['optin']['ref'];
        $newUserNull = $user = UserModel::getUserByMsgID($senderID);
        if (!$user) {
            $user = self::handleRetrieveUserID($event);
        }
        // $userTag = explode('_', $event['optin']['ref']);
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
     * @return bool
     * @throws \Exception
     * @throws \think\Exception
     */
    public static function handleMessengerUserInfo($event)
    {
        // 通过用户optin获取用户messenger信息，创建用户帐号
        $senderID = $event['sender']['id'];
        $userTag = explode('_', $event['optin']['ref']);
        $msgInfoUrl = sprintf(config('fb.RETRIEVE_MESSENGER_INFO_API'), $senderID, config('fb.PAGE_ACCESS_TOKEN'));
        $res = curlHttp($msgInfoUrl, [], 'GET',  ['Content-Type: application/json'], false);

        $userMsgInfo = json_decode($res, true);
        if (!array_key_exists('error', $userMsgInfo)) {
            $user = UserModel::getUserByMsgID($userMsgInfo['id']);
            if (!$user) {
                $newUser = UserModel::create([
                    'message_id'    => $userMsgInfo['id'],
                    'name'          => $userMsgInfo['first_name']. ' '. $userMsgInfo['last_name'],
                    'avatar'        => $userMsgInfo['profile_pic'],
                    'tag'           => $userTag[0],
                ]);

                // 增加一条参与活动记录和，推送通知记录
                Activity::connected($newUser->id, $userTag[1], $senderID);
            } else {
                $user->tag = $userTag[0];
                $user->save();
                Activity::connected($user->id, $userTag[1], $senderID);
            }
        } else {
            Log::create([
                'log' => $res,
                'topic' => 'retrieve msg_info error',
            ]);
        }
        return true;
    }

    // 通过用户统一PSID获取用户的userID从而更新到user表记录message_id

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
        // 获取用户的message_id更新对应的数据信息
        $appsecretProof= hash_hmac('sha256', config('fb.PAGE_ACCESS_TOKEN'), config('fb.APP_SECRET'));
        $retrieveUrl = sprintf(config('fb.RETRIEVE_ID_API'), $senderID);

        $params = [
            'app' => config('fb.APP_ID'),
            'access_token' => config('fb.PAGE_ACCESS_TOKEN'),
            'appsecret_proof' => $appsecretProof,
        ];

        $res = curlHttp($retrieveUrl, $params, 'GET',  ['Content-Type: application/json'], false);
        Log::create([
            'log' => json_encode([
                'request'=> [
                    'senderID' => $senderID,
                    'appsecretProof' => $appsecretProof,
                    'retrieveUrl' => $retrieveUrl,
                ],
                'response' => $res,
            ]),
            'topic' => 'retrieve user_id'
        ]);

        $res = json_decode($res, true);
        if (!array_key_exists('error', $res)) {
            $appIds = $res['data'];
            $arr = [];
            foreach($appIds as $k=>$v) {
                $arr[$v['app']['id']] = $v['id'];
            }
            $appId = config('fb.APP_ID');
            // return json([$res, $arr]);
            $userFBID = $arr[$appId]; // fb的ID
            if ($userFBID) {
                // 更新用户信息
                $user = UserModel::where(['userid'=>$userFBID])->find();
                $user->message_id = $senderID;
                $user->save();
                // 增加一条推送通知记录
                Activity::connected($user->id, $activityID, $senderID);
                return $user;
            }
            return false;
        }
        return false;
    }

    public static function handleRead($senderID, $readCallback)
    {
        return true;
    }


    public static function handleDelivery($sendPSID, $delivery)
    {
        return true;
    }

    /**
     * @param $sendPSID
     * @param $postBack
     * @return bool
     * @throws \Exception
     * @throws \app\lib\exception\TokenException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function handlePostback($sendPSID, $postBack)
    {
        $payload = json_decode($postBack['payload'], true);
        $user = UserModel::getUserByMsgID($sendPSID);

        if ($payload['status'] === MessageModel::POSTBACK_AGREE) {
            // 用户确认信息
            return self::afterPostBackConfirm($user, $payload);
        } else if ($payload['status'] === MessageModel::POSTBACK_COUPON) {
            // 用户兑换优惠券
            return self::afterPostbackRedeem($user);
        } else if ($payload['status'] === MessageModel::POSTBACK_REVIEW) {
            // 用户测评
            return self::afterPostbackReview($user, $payload);
        } else if ($payload['status'] === MessageModel::POSTBACK_REBATE) {
            // 直发器的订单
            return self::afterPostbackRebate($user, $payload);
        }


        return false;
    }


    /**
     * @param $user
     * @param $payload
     * @return bool
     * @throws \Exception
     * @throws \think\Exception
     */
    public static function afterPostbackRebate($user, $payload)
    {
        $rebatePayload = [
            'template_type' => "button",
            'text' =>  "Woohoo you got the offer! Follow steps below to order and get full refund:".PHP_EOL."
1. Order on Amazon and apply coupon on page (if available), then send us the order number.".PHP_EOL."
2.Test the flat iron for a few days before writing an honest review on Amazon. ".PHP_EOL."
3. Send us your review screenshot/link and get 100% rebate from us via Paypal!",
            'buttons'  => [
                [
                    'type'   =>  "web_url",
                    'title'  => "Flat iron (black)",
                    'url'=> config('domain').'/freebies?ref=7b2971ecf92d4ff66278fed11848ade5',
                ],
                [
                    "type" => 'web_url',
                    "title" => 'Flat iron (pink)',
                    "url" => config('domain').'/freebies?ref=93479b5bd22ed4c7baf64381639a9119',
                ],
                [
                    "type" => 'web_url',
                    "url" => config('domain').'/freebies?ref=80eb66db8bafba45c8746de51fd18559',
                    "title" => 'Flat iron (black gold)',
                ],
            ]
        ];
        return MessageModel::sendButtonMessage($user->message_id, $rebatePayload);
    }


    /**
     * @param $user
     * @param $payload
     * @return bool
     * @throws \Exception
     * @throws \app\lib\exception\TokenException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function afterPostBackConfirm($user, $payload)
    {
        // 用户确认参与活动
        if (!$user->confirm) {
            $user->confirm = 1;
            $user->save();
        }
        $activityUser = ActivityUserModel::getOne($payload['activity_id'], $user->id);
        if (!$activityUser->confirm) {
            $activityUser->confirm = 1;
            $activityUser->save();
            return MessengerJob::sendWinnerMsg($payload, $user);
        } else {
            return true;
        }
    }

    /**
     * 用户点击see offer
     * @param $user
     * @return bool
     * @throws \Exception
     * @throws \think\Exception
     */

    public static function afterPostbackRedeem($user)
    {
        return self::handleReviewCallMessage($user->message_id);
    }

    /**
     * 处理用户测评留言postback
     * @param $user
     * @param $payload
     * @return bool
     * @throws \Exception
     * @throws \think\Exception
     */
    public static function afterPostbackReview($user, $payload)
    {
        return Review::handleReview($user, $payload);
    }
}