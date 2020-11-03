<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/12/20
 * Time: 18:14
 */

namespace app\api\service\message\freebie;

use app\api\model\Freebies;
use app\api\model\Message as MessageModel;

class CoffeeMaker
{
    const PRODUCT = 'coffee_maker';
    /**
     * 选项
     *  * 向用户发送备选产品
     * @param $user
     * @param $payload
     * @return bool
     * @throws \Exception
     * @throws \think\Exception
     */
    public static function freebieCoffeeMaker($user, $payload)
    {
        $str = "Thank you, offer expires within 12 hours. Please tap link below and apply 10% coupon on page. After purchase, please send us your order number immediately. Get full rebate via PP after review goes live!";
        $product = Freebies::find(['id' => $payload['freebie_id']]);

        $url = config('domain').'/freebies?' . http_build_query(['ref' => $product->landing_code]);

        $payload = [
            'template_type' => "button",
            'text' =>  $str,
            "buttons" => [
                [
                    "type" => 'web_url',
                    "url" => $url,
                    "title" => 'Shop now',
                ],
            ]
        ];


        return MessageModel::sendButtonMessage($user->message_id, $payload);
    }

}