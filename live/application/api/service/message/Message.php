<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/11/16
 * Time: 17:56
 */

namespace app\api\service\message;
use app\api\model\ActivityUser;
use app\api\model\Log;
use app\api\model\Message as MessageModel;
use app\api\model\RebateUser as RebateUserModel;
use app\api\model\User as UserModel;
use app\api\service\CallForReview;
use app\api\service\message\freebie\Freebies;
use app\api\service\message\freebie\Hair;
use app\api\service\message\freebie\Humidifier;
use think\Exception;

class Message
{

    /**
     * 处理文本或者图片等媒体消息
     * @param $user
     * @param $messaging
     * @return bool
     * @throws \Exception
     * @throws \think\Exception
     */
    public static function handleMessage($user, $messaging)
    {
        $senderID = $messaging['sender']['id'];
        $message = $messaging['message'];

        if (!$user) {
            $user = UserModel::retrieveMsgUserInfoByMessageID($messaging['sender']['id']);
            $data = [
                'name'          => $user['first_name']. ' '. $user['last_name'],
                'avatar'        => $user['profile_pic'],
                'message_id'    => $user->message_id,
            ];
            $user = UserModel::newUser($data);
        }

        $messageText = isset($message['text']) ? $message['text'] : '';
        $messageAttachments = isset($message['attachments']) ? $message['attachments']: [];
        if ($messageText) {
            $lowerStr = strtolower(str_replace([' ', '"', "'"], ['', '', ''], $messageText));
            // 订单号过滤
            self::filterAmazonOrderNumber($lowerStr, $user);

            switch ($lowerStr) {
                case 'stop':
                case 'unsubscribe':
                    return MessageModel::handleSubscribeMessage($senderID);
                    break;
                case 'confirm':
                case 'entered':
                case 'enter':
                case 'done':
                    return ActivityUser::handleConfirmMessage($senderID);
                    break;
                case 'freebies':
                case 'freebie':
                    return Freebies::getFreebies($senderID);
                    break;
                case 'hair':
                    return Hair::keywordHairFreebie($senderID);
                    break;
                case 'humidifier':
                    return Humidifier::keywordHumidifier($senderID);
                    break;
                default:
                    return true;
            }
        } else if ($messageAttachments) {
            foreach($messageAttachments as $attachment){
                if ($attachment['type'] === 'image') {
                    $imageSize = intval((strlen(file_get_contents($attachment['payload']['url'])) / 1024));
                    if ($imageSize > 25) {
                        $ext = '.'.pathinfo(parse_url($attachment['payload']['url'])['path'])['extension'];
                        $fileName = $senderID.'@'.date('Y-m-d').'-'.uniqid(). $ext;

                        try{
                            $image = downloadImage($attachment['payload']['url'], './media/rebate', $fileName);
                            if ($image['error'] == 0) {
                                RebateUserModel::create([
                                    'screenshot' => str_replace('./media/','', $image['save_path']),
                                    'message_id' => $senderID,
                                    'user_id'    => $user->id,
                                    'name' => $user->name,
                                ]);
                            }
                        }catch(Exception $e){
                            Log::create([
                                'log' => json_encode([$e->getMessage(), 'param' => [$senderID, $user->id]]),
                                'topic' => "rebate_image",
                            ]);
                            return true;
                        }
                    }
                }
            }
            return true;
        }
        return true;
    }


    /**
     * 过滤收到用户发来的订单号
     * @param $text
     * @param $user
     * @throws Exception
     * @throws \Exception
     */
    public static function filterAmazonOrderNumber($text, $user)
    {
        // 对用户发送过来的信息进行订单号过滤
        $reg = "/[0-9]{3}-[0-9]{7}-[0-9]{7}/";
        $res = preg_match_all($reg, $text, $match);
        if ($res) {
            foreach($match[0] as $orderNum){
                RebateUserModel::create([
                    'user_id' => $user->id,
                    'message_id' => $user->message_id,
                    'order_num' => $orderNum,
                    'screenshot' => "",
                    'name' => $user->name,
                ]);
            }
            MessageModel::sendTextMessage($user->message_id, 'Got it! Thanks.');
        }
    }


    /**
     *  * 向用户发送备选产品
     * @param $senderID
     * @param $keyword
     * @return bool
     * @throws \Exception
     * @throws \think\Exception
     */
    public static function handleReviewCallMessage($senderID, $keyword)
    {
        if (in_array($keyword, ['toys', 'toy'])) {
            return CallForReview::handleToys($senderID);
        } else if (in_array($keyword, ['pets', 'pet'])) {
            return CallForReview::handlePets($senderID);
        }
        return true;
    }


}