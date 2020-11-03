<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/10/30
 * Time: 19:09
 */

namespace app\api\service;

use app\api\model\Log;
use app\api\model\Message as MessageModel;


class Review
{
    const FREEBIES = [
        "christmas-tree" => [
            "subTitle" => "Get 100% refund",
            "title"   => "You’ve successfully reserved a free Christmas tree!",
            "img"   => 'https://www.surprizeday.com/media/20191011/aa0c4efef4a6b20defd4714a6bab73db.jpg',
        ],
        "garbage-bin" => [
            "subTitle" => "Get 100% refund",
            "title"   => "You’ve successfully reserved a free Stainless Steel Bin Combo!",
            "img"   => 'https://www.surprizeday.com/media/20191011/cb41c5894e497d5797579918fb09949b.jpg',
        ],
        "torch-light" => [
            "subTitle" => "Get 100% refund",
            "title"   => "You’ve successfully reserved a free Solar LED Torch Light!",
            "img"   => 'https://www.surprizeday.com/media/20191011/e03c675f2b649798267d788595a2edf7.jpg',
        ],
        "toy-storage-organizer" => [
            "subTitle" => "Get 100% refund",
            "title"   => "You’ve successfully reserved a free Solar LED Torch Light!",
            "img"   => 'https://www.surprizeday.com/media/20191011/e03c675f2b649798267d788595a2edf7.jpg',
        ],
        "set-of-2-kids-bench" => [
            "subTitle" => "Get 100% refund",
            "title"   => "You’ve successfully reserved a free Solar LED Torch Light!",
            "img"   => 'https://www.surprizeday.com/media/20191011/e03c675f2b649798267d788595a2edf7.jpg',
        ],
        "pets-dog" => [
            "subTitle" => "Get 100% refund",
            "title"   => "You’ve successfully reserved a free Solar LED Torch Light!",
            "img"   => 'https://www.surprizeday.com/media/20191101/255ae5c1eb74c9ad22d715d201fcb671.png',
        ],
        "pets-cat" => [
            "subTitle" => "Get 100% refund",
            "title"   => "You’ve successfully reserved a free Solar LED Torch Light!",
            "img"   => 'https://www.surprizeday.com/media/20191101/efb75875975284fda09926e143f52fa1.png',
        ],
        "pets-storage-container" => [
            "subTitle" => "Get 100% refund",
            "title"   => "You’ve successfully reserved a free Solar LED Torch Light!",
            "img"   => 'https://www.surprizeday.com/media/20191101/15f0e1676197b572968c5675f568acf5.png',
        ],
        "pets-bunny-house" => [
            "subTitle" => "Get 100% refund",
            "title"   => "You’ve successfully reserved a free Solar LED Torch Light!",
            "img"   => 'https://www.surprizeday.com/media/20191101/2b09f1a2609f29a1c3f9163bf26ed692.png',
        ],
        "pets-chicken-house" => [
            "subTitle" => "Get 100% refund",
            "title"   => "You’ve successfully reserved a free Solar LED Torch Light!",
            "img"   => 'https://www.surprizeday.com/media/20191101/94a2e5ee6cb416076e2eb80c3b9bd136.png',
        ],
        "pets-house" => [
            "subTitle" => "Get 100% refund",
            "title"   => "You’ve successfully reserved a free Solar LED Torch Light!",
            "img"   => 'https://www.surprizeday.com/media/20191101/0c696a0b5fe741a3b3b5bfa29c349618.png',
        ],
    ];

    const REVIEW_AUTO_POINTS = <<<EOD
Instructions:
1. Order on Amazon and apply 25% promo code: {code}

2. Upon receiving the product, write an unbiased review on Amazon with description and your impression of the product. 

3. Send us the screenshot to your review and get 100% full refund via Paypal.

Please send us the order number after your purchase.
EOD;

    /**
     * 处理需要进行review的postback行为
     * @param $user
     * @param $payload
     * @return bool
     * @throws \Exception
     * @throws \think\Exception
     */
    public static function handleReview($user, $payload)
    {
        $freebiesArr = self::FREEBIES;
        $freebie = !empty($freebiesArr[$payload['type']]) ? $freebiesArr[$payload['type']] : [];
        if ($payload['type'] === "christmas-tree" || $payload['type'] === "garbage-bin" || $payload['type'] === "torch-light") {
            //return self::handleInstruction($user, $payload, $freebie);
        } else if ($payload['type'] === "toy-storage-organizer" || $payload['type'] === "set-of-2-kids-bench") {
            //return self::handleRollIn($user, $payload, $freebie);
        } else if($payload['type'] === "pets"){

            return CallForReview::handlePets($user->message_id);

        }else if ($payload['type'] === "pets-dog-cat" || $payload['type'] === "bunny-chicken-duck"){

            if ($payload['type'] === "pets-dog-cat" ) {
                return CallForReview::handlePets2ndQuery($user, $freebiesArr, 1);
            } else {
                return CallForReview::handlePets2ndQuery($user, $freebiesArr, 2);
            }

        } else if ($payload['type'] === 'pets-dog' || $payload['type'] === 'pets-cat') {
            return self::handleInstruction($user, $payload, $freebie);
        } else if ($payload['type'] === 'pets-chicken' || $payload['type'] === 'pets-bunny') {
            return self::handleInstruction($user, $payload, $freebie);
        } else {
            return true;
        }
    }


    /**
     * 先行收集用户的意向
     * @param $user
     * @param $payload
     * @param $freebie
     * @return bool
     * @throws \Exception
     * @throws \think\Exception
     */
    public static function handleRollIn($user, $payload, $freebie=[])
    {

        Log::create([
            'topic' => 'review call',
            'log'   => json_encode(['message_id' => $user->message_id, 'type' => $payload['type']])
        ]);
        $msg = "Thank you! We'll get back to you immediately if we have one available.";
        return MessageModel::sendTextMessage($user->message_id, $msg);
    }


    /**
     * @param $user
     * @param $payload
     * @param $freebie
     * @return bool
     * @throws \Exception
     * @throws \think\Exception
     */
    public static function handleInstruction($user, $payload, $freebie)
    {
        MessageModel::sendTextMessage($user->message_id, 'Thank you! One moment please.');
        sleep(2);

        $type["pets-dog"] = [
            'name' => 'dog grooming table',
            'title' => 'We have a dog grooming table available! (2 left, link automatically expires if sold out)',
            'img' => 'https://scontent-hkg3-1.xx.fbcdn.net/v/t1.15752-9/73375496_466221840906256_1056554331249049600_n.png?_nc_cat=110&_nc_oc=AQmOAgvaw2tzBNNKRrVLUgWLYW6RC65DquNrvW_N5B2WnUBTVGonr4ya81OKHeqK38o&_nc_ht=scontent-hkg3-1.xx&oh=3f3a2b1cfc61be10515194b3b2cf0afb&oe=5E48357F',
            'code' => '9XKYRJ6K',
            'url' => 'https://www.surprizeday.com/freebies?ref=011bcc0fa3ce6cf2dd8ef8e1a60b52f8',
        ];

        $type["pets-cat"] = [
            'name' => 'cat cage',
            'title' => 'We have a cat cage available! (2 left, link automatically expires if sold out)',
            'img' => 'https://scontent-hkg3-1.xx.fbcdn.net/v/t1.15752-9/72209749_456247058344001_6911025467902394368_n.png?_nc_cat=107&_nc_oc=AQk9WIHqM3mmZv7iLvHZEHGpi4rwdr5J4kc0bweTV58H4FGBtVE8N9PlVJoq31TnYdI&_nc_ht=scontent-hkg3-1.xx&oh=5bb7f12eaa6ce8cee24109427147e1c3&oe=5E50780A',
            'code' => 'TGJSY7PE',
            'url' => 'https://www.surprizeday.com/freebies?ref=e92191cc839590d99c7fcdfe7ea1316c',
        ];


        $type["pets-chicken"] = [
            'name' => 'chicken coop',
            'title' => 'We have a chicken coop available! (1 left, link automatically expires if sold out)',
            'img' => 'https://scontent-hkg3-1.xx.fbcdn.net/v/t1.15752-9/74906716_459271838038634_1685201633438334976_n.png?_nc_cat=104&_nc_oc=AQmSYz5VLRM8KxSDCdYQAMsZ9un-EY_ch1xGDd5VwQIVDqnBhCXFBKhhyKWnQ5F4C3o&_nc_ht=scontent-hkg3-1.xx&oh=c29c1a255a9bcd93925a498fbae08864&oe=5E64D65F',
            'code' => '4FSGWY3A',
            'url' => 'https://www.surprizeday.com/freebies?ref=9a5f40820f4a2d94f767f77951bd12fc',
        ];

        $type["pets-bunny"] = [
            'name' => 'rabbit hutch',
            'title' => 'We have a rabbit hutch available! (1 left, link automatically expires if sold out)',
            'img' => 'https://scontent-hkg3-1.xx.fbcdn.net/v/t1.15752-9/73399758_424192888297704_7537343824075423744_n.png?_nc_cat=110&_nc_oc=AQnfZ0IiE-_nTqIUtEqdxBmtnnAb8V7xSaQM2M1S1d7lfe4WFRxpemntiQUfvClihqs&_nc_ht=scontent-hkg3-1.xx&oh=7c0cbc39ce86d1a77d9e58ef2f8e9f09&oe=5E5AADF5',
            'code' => 'PZTO78FC',
            'url' => 'https://www.surprizeday.com/freebies?ref=b6540434af2d58522a56e4264ed37381',
        ];



        MessageModel::sendTextMessage($user->message_id, $type[$payload['type']]['title']);
        MessageModel::sendImageMessage($user->message_id, $type[$payload['type']]['img']);
        MessageModel::sendTextMessage($user->message_id, $type[$payload['type']]['code']);

        $btnPayload = [
            'template_type' => "button",
            'text' =>  str_replace('{code}', $type[$payload['type']]['code'],self::REVIEW_AUTO_POINTS),
            "buttons" => [
                [
                    "type" => 'web_url',
                    "url" => $type[$payload['type']]['url'],
                    "title" => 'Redeem now',
                ],
            ]
        ];

        return MessageModel::sendButtonMessage($user->message_id, $btnPayload);


    }

    /**
     * @param $user
     * @param $payload
     * @param $freebie
     * @return bool
     * @throws \Exception
     * @throws \think\Exception
     */
    public function handleGenericMessage($user, $payload, $freebie)
    {
        $msgTpl = MessageModel::$genericTpl;
        $msgPayload = $msgTpl['attachment']['payload'];
        $msgPayload['elements'] =  [
            [
                "title" => $freebie['title'],
                "image_url" => $freebie['img'],
                "subtitle" => $freebie['subTitle'],
                "default_action" => [
                    "type" => "web_url",
                    "url"  => $payload['url'],
                    "webview_height_ratio" => "tall",
                ],
                "buttons" => [
                    [
                        "type" => 'web_url',
                        "url" => $payload['url'],
                        "title" => 'Redeem now',
                    ],
                ]
            ]
        ];
        return MessageModel::sendGenericTplMessage($user->message_id, $msgPayload);
    }

}