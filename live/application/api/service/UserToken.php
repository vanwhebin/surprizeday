<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/5/22
 * Time: 16:16
 */

namespace app\api\service;


use app\api\model\User as UserModel;
use app\lib\exception\InvalidParamException;
use app\lib\exception\TokenException;
use app\lib\exception\WeChatException;
use think\Exception;
use think\facade\Cache;
use think\facade\Request;

class UserToken extends Token
{
    /**
     * 用户通过facebook登录获取token
     * @param $userID
     * @return string
     * @throws Exception
     * @throws TokenException
     */
    public function getToken($userID)
    {
        if (empty($userID)) {
            // 有可能因为非法code传递拿不到微信的授权
            throw new Exception('Response from FB request is invalid');
        } else {
            // 拿到userID生成token返回到前台
            return $this->grantToken($userID);
        }
    }

    /**
     * 用户通过email登录获取token
     * @param $data
     * @return string
     * @throws InvalidParamException
     * @throws TokenException
     */
    public function getEmailToken($data)
    {
        if (!array_key_exists('email', $data)) {
            throw new InvalidParamException(['msg' => 'Email is required']);
        } else {
            return $this->grantUserEmailToken($data['email']);
        }
    }


    public function processLoginFail($wxResult)
    {
        throw new WeChatException([
            'errorCode' => $wxResult['errorcode'],
            'msg'       => $wxResult['errmsg'],
        ]);
    }

    /**
     * 得到结果，根据用户实际是否存在生成Token
     * 存入缓存
     * 返回前台
     * @param $userID
     * @return string
     * @throws TokenException
     */
    public function grantToken($userID)
    {
        $data = input('post.');
        // 查看数据库是否存在
        $user = UserModel::getUserByUserID($userID);
        if ($user) {
            $uid = $user->id;
        } else {
            //　生成用户数
            $user = UserModel::newUser($data);
            $uid = $user->id;
        }

        $cacheValue = $this->prepareCache($uid, 16);
        $token = $this->saveIntoCache($cacheValue);
        return $token;
    }

    /**
     * @param $email
     * @return string
     * @throws TokenException
     */
    public function grantUserEmailToken($email)
    {
        $data = input('post.');
        // 查看数据库是否存在
        $user = UserModel::getUserByEmail($email);
        if ($user) {
            $uid = $user->id;
        } else {
            $user = UserModel::newUser($data);
            $uid = $user->id;
        }

        $cacheValue = $this->prepareCache($uid, 16);
        $token = $this->saveIntoCache($cacheValue);
        return $token;
    }


    /**
     * @param $uid
     * @param int $scope
     * @return mixed
     */
    private function prepareCache($uid, $scope=16)
    {
        $cacheValue['uid'] = $uid;
        $cacheValue['scope'] = $scope;
        return $cacheValue;
    }

    /**
     * 将令牌存储到缓存中
     * @param $cacheValue
     * @param int $expireTime
     * @return string
     * @throws TokenException
     */
    private function saveIntoCache($cacheValue, $expireTime=0)
    {
        $key = parent::generateToken();
        $value = json_encode($cacheValue);
        $option['expire'] = $expireTime?:config('expire_in');

        $request = cache($key, $value, $option);
        if (!$request) {
            throw new TokenException([
                'msg'       => 'Error occurred when using cache',
                'errorCode' => 10005,
            ]);
        }
        return $key;
    }

    /**
     * 用来生成分享使用的唯一Token
     * @param $userID integer 用户表唯一ID
     * @param string $from 分享平台
     * @param integer $expireTime 令牌过期时间 24*60*60*30  七天
     * @return string
     * @throws TokenException
     */

    public function getShareToken($userID, $from="facebook", $expireTime=3456000)
    {
        $cacheValue = $this->prepareCache($userID, $from);
        $token = $this->saveIntoCache($cacheValue, $expireTime);
        return $token;
    }

    /**
     * 用于获取前台从cookie中保存的token找出对应的用户ID
     * @return mixed
     * @throws TokenException
     */
    public static function getCurrentUserID()
    {
        $token = Request::instance()->cookie('token');
        $vars = Cache::get($token);
        if (!$vars) {
            throw new TokenException();
        } else {
            if (!is_array($vars)) {
                $vars = json_decode($vars, true);
            }
            if (array_key_exists('uid', $vars)) {
                return $vars['uid'];
            } else {
                throw new TokenException();
            }
        }
    }


    /**
     * 删除当前用户的token缓存
     * @param $token
     * @return bool
     */
    public function clearUserToken($token)
    {
        return Token::clearVarByToken($token);
    }


}