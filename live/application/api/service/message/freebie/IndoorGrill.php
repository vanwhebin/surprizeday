<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/12/23
 * Time: 19:13
 */

namespace app\api\service\message\freebie;

use app\api\model\Message as MessageModel;

class IndoorGrill
{

    const PRODUCT = "indoor_grill";

    /**
     * @param $user
     * @param $payload
     * @return bool
     * @throws \Exception
     * @throws \think\Exception
     */
    public static function freebieGrill($user, $payload)
    {
        return self::freebieGrillOffer($user);
    }

    /**
     * 20191223特别促销
     * @param $user
     * @param $payload
     * @return bool
     * @throws \Exception
     * @throws \think\Exception
     */
    public static function freebieGrillOffer($user, $payload)
    {
        $msg = 'Congrats, you got the offer! Get this indoor grill on Amazon for only $9.99 by apply 10% coupon on page and get $53 rebate via PayPal after your review goes live on Amazon!';
        $offerPayload = [
            'template_type' => "button",
            'text' =>  $msg,
            'buttons'  => [
                [
                    "type" => 'web_url',
                    "title" => 'Shop now',
                    "url" => config('domain').'/freebies?ref=3cf8719535de3634b1d0821c0e57958d',
                ],
            ]
        ];
        MessageModel::sendButtonMessage($user->message_id, $offerPayload);
        $tip = "Limited to 1 per household. Please send us your order number after purchase.";
        return MessageModel::sendTextMessage($user->message_id, $tip);
    }

}