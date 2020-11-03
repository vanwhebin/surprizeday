<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/11/20
 * Time: 19:00
 */

namespace app\api\service\message\freebie;
use app\api\model\Message as MessageModel;
// use app\api\model\Message;
use app\api\service\message\Postback;

class Humidifier
{
    const PRODUCT = "humidifier";

    /**
     * @param $user
     * @param $payload
     * @return bool
     * @throws \Exception
     * @throws \think\Exception
     */
    public static function freebieHumidifier($user, $payload)
    {

        if (!empty($payload['step']) && $payload['step'] === Freebies::PRODUCT) {
            // 通过freebie列表进入
            return self::allHumidifierFreebie($user);
        } else {
            return self::freebieHumidifierOffer($user);
        }



    }


    /**
     * 20191223特别促销
     * @param $user
     * @return bool
     * @throws \Exception
     * @throws \think\Exception
     */
    public static function freebieHumidifierOffer($user)
    {
        $msg = 'Congrats, you got the offer! Get this humidifier and diffuser for only $12.99 by applying 30% coupon on page and get $28 rebate via PayPal after your review goes live on Amazon!';
        $offerPayload = [
            'template_type' => "button",
            'text' =>  $msg,
            'buttons'  => [
                [
                    "type" => 'web_url',
                    "title" => 'Shop now',
                    "url" => config('domain').'/freebies?ref=0270de0ff95c81145dc96900b112ffa2',
                ],
            ]
        ];
        MessageModel::sendButtonMessage($user->message_id, $offerPayload);
        $tip = "Limited to 1 per household. Please send us your order number after purchase.";
        return MessageModel::sendTextMessage($user->message_id, $tip);
    }


    /**
     *  用户通过freebie关键词菜单选中查看当前所有freebie
     * @param $user
     * @return bool
     * @throws \Exception
     * @throws \think\Exception
     */
    public static function allHumidifierFreebie($user)
    {
        MessageModel::sendTextMessage($user->message_id,'Woohoo you got the offer!');
        // MessageModel::sendImageMessage($user->message_id,'https://www.surprizeday.com/media/20191120/d2925b5ffee88942f19026c5bf233790.jpg');
        MessageModel::sendImageMessage($user->message_id,'https://www.surprizeday.com/media/20191120/eec0f86ac072e79990e920e22975eed2.jpg');
        $rebatePayload = [
            'template_type' => "button",
            'text' =>  "Follow steps below to order and get full refund:".PHP_EOL."
1. Order on Amazon and apply coupon on page (if available), then send us the order number.".PHP_EOL."
2.Test the humidifier for a few days before writing an honest review on Amazon. ".PHP_EOL."
3. Send us your review screenshot/link and get 100% rebate from us via Paypal!",
            'buttons'  => [
                /*[
                    'type'   =>  "web_url",
                    'title'  => "Humidifier Model 1",
                    'url'=> config('domain').'/freebies?ref=021264595b1356dbf6599a7277e5eb4f',
                ],*/
                [
                    "type" => 'web_url',
                    "title" => 'Get it',
                    "url" => config('domain').'/freebies?ref=74aeeea67f49d7261a1504bd91e402a6',
                ],
            ]
        ];
        return MessageModel::sendButtonMessage($user->message_id, $rebatePayload);
    }


    /**
     * 用户使用关键词触发
     * @param $senderID
     * @return bool
     * @throws \Exception
     * @throws \think\Exception
     */
    public static function keywordHumidifier($senderID)
    {
        MessageModel::sendTextMessage($senderID, 'Limited to one flat iron per household. Get full rebate from us after reviewing the product. ');
        // MessageModel::sendImageMessage($senderID,'https://www.surprizeday.com/media/20191120/d2925b5ffee88942f19026c5bf233790.jpg');
        MessageModel::sendImageMessage($senderID,'https://www.surprizeday.com/media/20191120/eec0f86ac072e79990e920e22975eed2.jpg');

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



    /**
     * 关键词触发后，用户选择claim now
     * @param $user
     * @return bool
     * @throws \Exception
     * @throws \think\Exception
     */
    public static function humidifierProductList($user)
    {
        MessageModel::sendTextMessage($user->message_id,'Woohoo you got the offer!');
        // MessageModel::sendImageMessage($user->message_id, 'https://www.surprizeday.com/media/20191120/d2925b5ffee88942f19026c5bf233790.jpg');
        MessageModel::sendImageMessage($user->message_id, 'https://www.surprizeday.com/media/20191120/eec0f86ac072e79990e920e22975eed2.jpg');
        $rebatePayload = [
            'template_type' => "button",
            'text' =>  "Follow steps below to order and get full refund:".PHP_EOL."
1. Order on Amazon and apply coupon on page (if available), then send us the order number.".PHP_EOL."
2.Test the humidifier for a few days before writing an honest review on Amazon. ".PHP_EOL."
3. Send us your review screenshot/link and get 100% rebate from us via Paypal!",
            'buttons'  => [
                // [
                //     'type'   =>  "web_url",
                //     'title'  => "Humidifier Model 1",
                //     'url'=> config('domain').'/freebies?ref=021264595b1356dbf6599a7277e5eb4f',
                // ],
                [
                    "type" => 'web_url',
                    "title" => 'Claim now',
                    "url" => config('domain').'/freebies?ref=74aeeea67f49d7261a1504bd91e402a6',
                ],
            ]
        ];
        return MessageModel::sendButtonMessage($user->message_id, $rebatePayload);
    }
}