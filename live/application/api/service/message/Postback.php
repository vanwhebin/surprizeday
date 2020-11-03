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
use app\api\model\ActivityUser as ActivityUserModel;
use app\api\model\Email as EmailModel;
use app\api\model\Message;
use app\api\model\Message as MessageModel;
use app\api\service\message\freebie\CoffeeMaker;
use app\api\service\message\freebie\Hair;
use app\api\service\message\freebie\Humidifier;
use app\api\service\message\freebie\IndoorGrill;
use app\api\service\message\freebie\SmoothieBlender;
use app\lib\enum\ActivityEnum;
use app\lib\enum\UserEnum;

class Postback
{
    const POSTBACK_AGREE  = 'TALK_TO_ME';
    const POSTBACK_GIVEAWAY  = 'SHOW_ME_GIVEAWAY';
    const POSTBACK_FREEBIE = 'SHOW_ME_FREEBIE';
    const POSTBACK_SUBSCRIBE = 'CONFIRM_SUBSCRIBE';

    const TEAMUP_POSTBACK_AGREE_REPLY = "You're in! Wanna increase your chance to win? Get 2 bonus entries when you refer a friend to enter. Share now👇";
    const DEFAULT_POSTBACK_AGREE_REPLY = "Thank you! You'll be notified once the winner has been drawn.";


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
    public static function handlePostback($user, $payload)
    {
        $payload = json_decode($payload['payload'], true);
        if ($payload['status'] === self::POSTBACK_AGREE) {
            // 用户确认信息
            return self::afterPostBackConfirm($user, $payload);

        } else if ($payload['status'] === self::POSTBACK_FREEBIE) {
            // 用户尝试freebie
            return self::afterPostbackFreebie($user, $payload);

        } else if ($payload['status'] === self::POSTBACK_SUBSCRIBE) {
            // 用户接受消息
            return self::afterPostbackSubscribe($user, $payload);

        } else if ($payload['status'] === self::POSTBACK_GIVEAWAY) {
            // 用户尝试giveaway
            return self::afterPostbackGiveaway($user, $payload);
        }

        return false;
    }


    /**
     * 处理postback下的giveaway情况
     * @param $user
     * @param $payload
     * @return bool
     * @throws \Exception
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */

    public static function afterPostbackGiveaway($user, $payload)
    {
        if ($payload['type'] === ActivityEnum::USER_ACTIVITY_RECOMMEND['LATEST']) {
            return self::afterPostbackGiveawayLatest($user);
        }
        return true;
    }


    /**
     * 随机推送给用户最新未参与的活动
     * @param $user
     * @return bool
     * @throws \Exception
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function afterPostbackGiveawayLatest($user)
    {
        $msgTitle = sprintf("Hey %s, here are the latest giveaways you can enter: ", ucfirst(explode(' ',$user->name)[0]));
        $recommend = ActivityModel::getRecommendations($user->id);
        if ($recommend) {
            $msg = (new EmailModel())->handleRecomEmailStr($msgTitle, $recommend);
        } else {
            $msg = "We post giveaways on our Facebook page every day, we will get you updated. Thanks.";
        }
        return Message::sendTextMessage($user->message_id, $msg);
    }



    /**
     * 处理用户取消订阅的回调
     * @param $user
     * @param $payload
     * @return bool
     * @throws \Exception
     * @throws \think\Exception
     */
    public static function afterPostbackSubscribe($user, $payload)
    {
        if ($payload['type'] === UserEnum::UNSUBSCRIBE) {
            $title = "You have successfully unsubscribed. You could also subscribe at anytime.";
            $btnPayloadInfo = ['status' => Postback::POSTBACK_SUBSCRIBE, 'type' => UserEnum::SUBSCRIBE];
            $btnPayload = [
                'template_type' => "button",
                'text' =>  $title,
                'buttons'  => [
                    [
                        'type'   => "postback",
                        'title'  => "Subscribe",
                        'payload'=> json_encode($btnPayloadInfo)
                    ]
                ]
            ];
            // 用户取消订阅  confirm
            $user->confirm = UserEnum::UNSUBSCRIBE;
            $user->save();
            return MessageModel::sendButtonMessage($user->message_id, $btnPayload);
        } else {
            $title = "We are honored to have your back! Tab below to enter our latest giveaway.";
            $btnPayloadInfo = ['status' => Postback::POSTBACK_GIVEAWAY, 'type'=> ActivityEnum::USER_ACTIVITY_RECOMMEND['LATEST']];
            $btnPayload = [
                'template_type' => "button",
                'text' =>  $title,
                'buttons'  => [
                    [
                        'type'   => "postback",
                        'title'  => "Get Started",
                        'payload'=> json_encode($btnPayloadInfo)
                    ]
                ]
            ];
            // 用户取消订阅  confirm
            $user->confirm = UserEnum::SUBSCRIBE;
            $user->save();
            return MessageModel::sendButtonMessage($user->message_id, $btnPayload);
        }
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
     * 处理freebie的各种情况
     * @param $user
     * @param $payload
     * @return bool
     * @throws \Exception
     * @throws \think\Exception
     */
    public static function afterPostbackFreebie($user, $payload)
    {
        $lowerStr = strtolower($payload['product']);
        switch ($lowerStr) {
            case Hair::PRODUCT:
                return Hair::freebieHair($user, $payload);
                break;
            case Humidifier::PRODUCT:
                return Humidifier::freebieHumidifier($user, $payload);
                break;
            case CoffeeMaker::PRODUCT:
                return CoffeeMaker::freebieCoffeeMaker($user, $payload);
                break;
            case IndoorGrill::PRODUCT:
                return IndoorGrill::freebieGrillOffer($user, $payload);
                break;
            case SmoothieBlender::PRODUCT:
                return SmoothieBlender::freebieSmoothieBlender($user, $payload);
                break;
            default:
                return true;
        }

    }
}