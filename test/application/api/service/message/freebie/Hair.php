<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/11/20
 * Time: 12:13
 */

namespace app\api\service\message\freebie;


use app\api\model\Message as MessageModel;
use app\api\service\message\Postback;

class Hair
{
    const PRODUCT = 'hair_iron';
    /**
     * 选项
     *  * 向用户发送备选产品
     * @param $user
     * @param $payload
     * @return bool
     * @throws \Exception
     * @throws \think\Exception
     */
    public static function freebieHair($user, $payload)
    {
        if (!empty($payload['step']) && $payload['step'] === Freebies::PRODUCT) {
            // 通过freebie列表进入
            return self::allHairFreebie($user);
        } else {
            return self::hairProductList($user);
        }
    }


    /**
     * 用户通过freebie关键词菜单选中查看当前所有freebie
     * @param $user
     * @return bool
     * @throws \Exception
     * @throws \think\Exception
     */
    public static function allHairFreebie($user)
    {
        MessageModel::sendTextMessage($user->message_id,'Woohoo you got the offer!');
        MessageModel::sendImageMessage($user->message_id,'https://images-na.ssl-images-amazon.com/images/I/61dUkdMcj%2BL._SL1500_.jpg');
        MessageModel::sendImageMessage($user->message_id,'https://images-na.ssl-images-amazon.com/images/I/61tvyUZEJeL._SL1485_.jpg');
        MessageModel::sendImageMessage($user->message_id,'https://www.surprizeday.com/media/20191119/fac3dd29d0deff8fea725ee0aace2d90.jpg');

        $rebatePayload = [
            'template_type' => "button",
            'text' =>  "Follow steps below to order and get full refund:".PHP_EOL."
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
     * 关键词触发后，用户选择claim now
     * @param $user
     * @return bool
     * @throws \Exception
     * @throws \think\Exception
     */
    public static function hairProductList($user)
    {

        MessageModel::sendTextMessage($user->message_id,'Woohoo you got the offer!');

        $rebatePayload = [
            'template_type' => "button",
            'text' =>  "Follow steps below to order and get full refund:".PHP_EOL."
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
     * 使用关键词触发的回复，第一次发送给用户
     * @param $senderID
     * @return bool
     * @throws \Exception
     * @throws \think\Exception
     */
    public static function keywordHairFreebie($senderID)
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
                    'payload'=> json_encode(['status' => Postback::POSTBACK_FREEBIE, 'product' => self::PRODUCT])
                ]
            ]
        ];
        return MessageModel::sendButtonMessage($senderID,$payload);
    }
}