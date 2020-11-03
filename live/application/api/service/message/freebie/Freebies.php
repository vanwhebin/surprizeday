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
        $askPayload = [
            'template_type' => "button",
            'text' =>  "What product would you like?",
            'buttons'  => [
                [
                    'type'   => "postback",
                    'title'  => "Coffee Maker",
                    'payload'=> json_encode([
                        'status' => Postback::POSTBACK_FREEBIE,
                        'product' => CoffeeMaker::PRODUCT,
                        'freebie_id' => 25,

                    ])
                ],
                [
                    'type'   => "postback",
                    'title'  => "Smoothie Blender",
                    'payload'=> json_encode([
                        'status' => Postback::POSTBACK_FREEBIE,
                        'product' => SmoothieBlender::PRODUCT,
                        'freebie_id' => 26,

                    ])
                ],
            ]
        ];
        MessageModel::sendImageMessage($senderID, "https://www.surprizeday.com/media/20191220/542fe4bf7c68715328f41fabd2f452a8.png");
        MessageModel::sendImageMessage($senderID, "https://www.surprizeday.com/media/20191220/e4bc9351f8130ce827e35113ffe488cd.png");
        return MessageModel::sendButtonMessage($senderID, $askPayload);
    }
}