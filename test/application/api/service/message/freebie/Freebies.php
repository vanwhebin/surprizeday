<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/11/21
 * Time: 11:14
 */

namespace app\api\service\message\freebie;


use app\api\model\Message as MessageModel;
use app\api\service\message\Postback;

class Freebies
{
    const PRODUCT = 'all';
    /**
     * freebie 关键词触发
     * @param $senderID
     * @return bool
     * @throws \Exception
     * @throws \think\Exception
     */
    public static function getFreebies($senderID)
    {
        $str = "Get full rebate from us for testing and reviewing the product. You'll need an active Amazon account and Paypal to receive rebate.";
        $askPayload = [
            'template_type' => "button",
            'text' =>  "Which product would you like?",
            'buttons'  => [
                [
                    'type'   => "postback",
                    'title'  => "Styling iron",
                    'payload'=> json_encode([
                        'status' => Postback::POSTBACK_FREEBIE,
                        'product' => Hair::PRODUCT,
                        'step' => self::PRODUCT,

                    ])
                ],
                [
                    'type'   => "postback",
                    'title'  => "Humidifier & Diffuser",
                    'payload'=> json_encode([
                        'status' => Postback::POSTBACK_FREEBIE,
                        'product' => Humidifier::PRODUCT,
                        'step' => self::PRODUCT,
                    ])
                ]
            ]
        ];
        MessageModel::sendTextMessage($senderID, $str);
        MessageModel::sendImageMessage($senderID, "https://www.surprizeday.com/media/20191119/fac3dd29d0deff8fea725ee0aace2d90.jpg");
        MessageModel::sendImageMessage($senderID, "https://www.surprizeday.com/media/20191120/d2925b5ffee88942f19026c5bf233790.jpg");
        return MessageModel::sendButtonMessage($senderID, $askPayload);
    }
}