<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/11/16
 * Time: 17:56
 */

namespace app\api\service\message;
use app\api\model\Media;
use app\api\model\Message as MessageModel;
use app\api\model\RebateUser;
use app\api\model\User as UserModel;
use app\api\service\CallForReview;

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
        $messageQuickReply = isset($message['quick_reply']) ? json_decode($message['quick_reply'], true): [];

        if ($messageText) {
            if ($messageQuickReply) {
                return self::handleQuickReply($senderID, $messageQuickReply, $user);
            } else {
                return self::handleText($senderID, $messageText, $user);
            }
        } else if ($messageAttachments) {
            return self::handleAttachment($senderID, $messageText, $user);
        }
        return true;
    }

    public static function handleQuickReply($senderID, $messageQuickReply, $user)
    {

        // TODO 审核名字 发送要求用户提供亚马逊profile的链接
        return MessageModel::sendTextMessage($senderID, "quick reply: you like {$messageQuickReply['payload']['ref']} film ");
    }


    /**
     * 处理发送过来的媒体信息
     * @param $senderID
     * @param $messageAttachments
     * @param $user
     * @return bool
     */
    public static function handleAttachment($senderID, $messageAttachments, $user)
    {
        if ($messageAttachments['type'] === 'image') {
            $imageSize = intval((strlen(file_get_contents($messageAttachments['payload']['url'])) / 1024));
            if ($imageSize > 50) {
                $fileName = $senderID.'-'.date('Y-m-d');
                $image = downloadImage($messageAttachments['payload']['url'], './media/rebate/', $fileName);
                if ($image['error'] === 0) {
                    RebateUser::create([
                        'screenshot' => str_replace('./media/','', $image['save_path']),
                        'message_id' => $senderID,
                        'user_id' => $user->id,
                    ]);
                }
            }
        }

        return true;
    }

    /**
     * 处理文字信息
     * @param $senderID
     * @param $messageText
     * @param $user
     * @return bool
     * @throws \Exception
     * @throws \app\lib\exception\TokenException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function handleText($senderID, $messageText, $user)
    {
        $lowerStr = strtolower(str_replace([' ', '"', "'"], ['', '', ''], $messageText));
        switch ($lowerStr) {
            case 'stop':
            case 'unsubscribe':
                return MessageModel::handleSubscribeMessage($senderID, 0);
                break;
            case 'confirm':
            case 'entered':
            case 'enter':
            case 'done':
                return MessageModel::handleSubscribeMessage($senderID, 1);
                break;
            case 'freebies':
                return Referral::handleShortLink($senderID, 271);
                break;
            case 'toys':
            case 'toy':
            case 'pets':
            case 'pet':
                return self::handleReviewCallMessage($senderID, $lowerStr);
                break;
            default:
                return true;
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