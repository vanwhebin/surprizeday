<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/11/1
 * Time: 18:29
 */

namespace app\api\service;

use app\api\model\Log;
use app\api\model\Message as MessageModel;

class CallForReview
{
    /**
     * 推送toys类别的回复review前置信息
     * @param $senderID
     * @return bool
     * @throws \Exception
     * @throws \think\Exception
     */
    public static function handleToys($senderID)
    {
        $defineToyStorageOrganizerPayload = json_encode([
            'status' => MessageModel::POSTBACK_REVIEW,
            'type'   => "toy-storage-organizer",
        ]);
        $defineSetOf2KidsBenchPayload = json_encode([
            'status' => MessageModel::POSTBACK_REVIEW,
            'type'   => "set-of-2-kids-bench",
        ]);

        $payload = [
            'template_type' => "button",
            'text' =>  "Which product would you like to get for free? (100% rebate):",
            'buttons'  => [
                [
                    "type" => 'postback',
                    "title" => 'Toy Storage Organizer',
                    "payload" => $defineToyStorageOrganizerPayload,
                ],
                [
                    "type" => 'postback',
                    "title" => 'Set of 2 Kids Bench',
                    "payload" => $defineSetOf2KidsBenchPayload,
                ],
            ]
        ];

        return MessageModel::sendButtonMessage($senderID, $payload);
    }

    /**
     * @param $senderID
     * @return bool
     * @throws \Exception
     * @throws \think\Exception
     */
    public static function handlePets($senderID)
    {
        $defineDogAndCatPayload = json_encode([
            'status' => MessageModel::POSTBACK_REVIEW,
            'type'   => "pets-dog-cat",
        ]);

        $defineOtherPetsPayload = json_encode([
            'status' => MessageModel::POSTBACK_REVIEW,
            'type'   => "bunny-chicken-duck",
        ]);

        $payload = [
            'template_type' => "button",
            'text' =>  "What kind of pet do you own?",
            'buttons'  => [
                [
                    "type" => 'postback',
                    "title" => 'Dogs and Cats',
                    "payload" => $defineDogAndCatPayload,
                ],
                [
                    "type" => 'postback',
                    "title" => 'Bunny Chicken Duck',
                    "payload" => $defineOtherPetsPayload,
                ],
            ]
        ];

        return MessageModel::sendButtonMessage($senderID, $payload);
    }

    /**
     * @param $user
     * @param $products
     * @param int $type
     * @return bool
     * @throws \Exception
     * @throws \think\Exception
     */
    public static function handlePets2ndQuery($user, $products, $type=1)
    {
        if ($type === 1) {
            return self::_handleDogAndCatPets($user, $products);
        } else {
            return self::_handleBunnyAndDuckOtherPets($user, $products);
        }
    }

    /**
     * 处理鸡笼兔笼的产品
     * @param $user
     * @param $products
     * @return bool
     * @throws \Exception
     * @throws \think\Exception
     */
    protected static function _handleBunnyAndDuckOtherPets($user, $products)
    {
        MessageModel::sendTextMessage($user->message_id, 'Below are some free products we offer (full rebate via Paypal)');
        MessageModel::sendImageMessage($user->message_id, $products["pets-bunny-house"]['img']);
        MessageModel::sendImageMessage($user->message_id, $products["pets-chicken-house"]['img']);
        MessageModel::sendImageMessage($user->message_id, $products["pets-house"]['img']);


        $defineChickenPayload = json_encode([
            'status' => MessageModel::POSTBACK_REVIEW,
            'type'   => "pets-chicken",
        ]);
        $defineBunnyPayload = json_encode([
            'status' => MessageModel::POSTBACK_REVIEW,
            'type'   => "pets-bunny",
        ]);


        $payload = [
            'template_type' => "button",
            'text' =>  "Which one would you like to reserve? Please choose only one:",
            'buttons'  => [
                [
                    "type" => 'postback',
                    "title" => 'Rabbit Hutch',
                    "payload" => $defineBunnyPayload,
                ],
                [
                    "type" => 'postback',
                    "title" => 'Chicken Coop',
                    "payload" => $defineChickenPayload,
                ]
            ]
        ];
        return MessageModel::sendButtonMessage($user->message_id, $payload);
    }

    /**猫狗产品的推送
     * @param $user
     * @param $products
     * @return bool
     * @throws \Exception
     * @throws \think\Exception
     */
    protected static function _handleDogAndCatPets($user, $products)
    {
        MessageModel::sendTextMessage($user->message_id, 'Below are some free products we offer (full rebate via Paypal)');
        MessageModel::sendImageMessage($user->message_id, $products["pets-dog"]['img']);
        MessageModel::sendImageMessage($user->message_id, $products["pets-cat"]['img']);
        // MessageModel::sendImageMessage($user->message_id, $products["pets-storage-container"]['img']);

        $defineDogPayload = json_encode([
            'status' => MessageModel::POSTBACK_REVIEW,
            'type'   => "pets-dog",
        ]);
        $defineCatPayload = json_encode([
            'status' => MessageModel::POSTBACK_REVIEW,
            'type'   => "pets-cat",
        ]);

        $defineContainerPayload = json_encode([
            'status' => MessageModel::POSTBACK_REVIEW,
            'type'   => "pets-storage-container",
        ]);

        $payload = [
            'template_type' => "button",
            'text' =>  "Which one would you like to reserve? Please choose only one:",
            'buttons'  => [
                [
                    "type" => 'postback',
                    "title" => 'Dog Grooming Table',
                    "payload" => $defineDogPayload,
                ],
                [
                    "type" => 'postback',
                    "title" => 'Cat carrying cage',
                    "payload" => $defineCatPayload,
                ],
                // [
                //     "type" => 'postback',
                //     "title" => 'Pet Food Container',
                //     "payload" => $defineContainerPayload,
                // ],
            ]
        ];
        return MessageModel::sendButtonMessage($user->message_id, $payload);
    }

    /**
     * review请求自动回复
     * @param $senderID
     * @return bool
     * @throws \Exception
     * @throws \think\Exception
     */

    public static function handleTreeLights($senderID)
    {
        Log::create([
            'topic' => "freebies",
            "log"   => json_encode(['message_id' => $senderID]),
        ]);

        $defineChristMasTreePayload  = json_encode([
            'status' => MessageModel::POSTBACK_REVIEW,
            'type'   => "christmas-tree",
            'url'    => "https://www.amazon.com/dp/B07Y4W45VL",
            'code'   => 'CB785Z4N'
        ]);

        $defineGarbageCanPayload  = json_encode([
            'status' => MessageModel::POSTBACK_REVIEW,
            'type'   => "garbage-bin",
            'url'    => "https://www.amazon.com/dp/B07XZ9J4WB/",
            'code'   => 'FWEIV8ZZ'
        ]);

        $defineTorchLightPayload  = json_encode([
            'status' => MessageModel::POSTBACK_REVIEW,
            'type'   => 'torch-light',
            'url'    => "https://www.amazon.com/dp/B07YCGWBYF",
            'code'   => '7B7UFN72'
        ]);

        $payload = [
            'template_type' => "button",
            'text' =>  "Which product would you like to get for free? Please choose only one from below:",
            'buttons'  => [
                [
                    "type" => 'postback',
                    "title" => 'Christmas Tree',
                    "payload" => $defineChristMasTreePayload,
                ],

                [
                    "type" => 'postback',
                    "title" => 'Torch Light',
                    "payload" => $defineTorchLightPayload,
                ],
            ]
        ];

        return MessageModel::sendButtonMessage($senderID, $payload);
    }
}