<?php

namespace app\api\model;

use app\api\service\UserToken;
use app\lib\exception\InvalidParamException;
use Laravolt\Avatar\Avatar;
use LinCmsTp5\admin\exception\user\UserException;
use think\Exception;
use app\lib\enum\UserEnum;

class User extends BaseModel
{
    protected $hidden = ['delete_time'];


    public function activity()
    {
        return $this->hasMany('ActivityUser', 'user_id', 'id');
    }


    public static function getUserByUserID($userID)
    {
        return self::where('userid', '=', $userID)->find();
    }


    public static function getUserByMsgID($msgID)
    {
        return self::where('message_id', '=',  $msgID)->find();
    }

    public static function getUserByEmail($email)
    {
        return self::where('email', '=', $email)->find();
    }

    public static function subscribeCheck($userID)
    {
        return self::where(['id' => $userID])->column('confirm');
    }

    public static function newUser($data)
    {
        $userInfo = [
            'userid' => !empty($data['userID']) ? $data['userID'] : time(),
            'name' => !empty($data['name']) ? $data['name'] : "",
            'password' => !empty($data['password']) ? md5($data['password']) : '',
            '$userInfo' => !empty($data['nickname']) ? $data['nickname'] : "",
            'email' => !empty($data['email']) ? $data['email'] : "",
            'avatar' => !empty($data['avatar']) ? $data['avatar'] : self::createAvatar($data),
        ];
        if (!empty($data['message_id'])) {
            $userInfo['message_id'] = $data['message_id'];
        }

        $user = self::create($userInfo);
        return $user;
    }


    public static function createAvatar($data)
    {
        $name = !empty($data['name']) ? $data['name'] : (!empty($data['nickname'])? $data['nickname'] : substr(uniqid(), 0,2));
        $config = config('avatar.');
        $avatar = new Avatar($config);
        $filePath = 'avatar/'.md5(slugify($name)).'.png';
        $avatar->create($name)->save('./media/'.$filePath, $quality = 100);
        return config('img_prefix').$filePath;

    }

    /**
     * 通过某一个app下的用户ID获取所有APP下的信息
     * @param $senderID
     * @return mixed
     * @throws Exception
     * @throws \Exception
     */
    public static function retrievePSIDUserInfo($senderID)
    {
        // 获取用户的message_id更新对应的数据信息
        $appsecretProof= hash_hmac('sha256', config('fb.PAGE_ACCESS_TOKEN'), config('fb.APP_SECRET'));
        $retrieveUrl = sprintf(config('fb.RETRIEVE_ID_API'), $senderID);

        $params = [
            'app' => config('fb.APP_ID'),
            'access_token' => config('fb.PAGE_ACCESS_TOKEN'),
            'appsecret_proof' => $appsecretProof,
        ];

        $res = curlHttp($retrieveUrl, $params, 'GET',  ['Content-Type: application/json'], false);

        return $res = json_decode($res, true);
    }

    /**
     * 在messenger中获取messenger用户profile
     * @param $senderID
     * @return mixed
     * @throws Exception
     * @throws \Exception
     */
    public static function retrieveMsgUserInfoByMessageID($senderID)
    {
        $retrieveUrl = sprintf(config('fb.RETRIEVE_ID_API'), $senderID);
        $params = [
            'app' => config('fb.APP_ID'),
            'access_token' => config('fb.PAGE_ACCESS_TOKEN'),
        ];
        $res = curlHttp($retrieveUrl, $params, 'GET',  ['Content-Type: application/json'], false);
        return json_decode($res, true);
    }





}
