<?php


namespace app\api\service;

use app\lib\enum\ScopeEnum;
use app\lib\exception\InvalidParamException;
use app\lib\exception\TokenException;
use think\facade\Cache;
use think\facade\Request;
use think\Exception;

class Token
{
    public static function generateToken($len = 64)
    {
        // 使用随机唯一字符串
        return hash_hmac('sha1', openssl_random_pseudo_bytes($len) , config('secure.token_salt'));

    }

    /**
     * 获取保存在缓存中的变量值
     * @param $key
     * @return mixed
     * @throws TokenException
     */
    public static function getCurrentTokenVar($key)
    {
        $token = Request::instance()->header('token');
        $vars = Cache::get($token);
        if (!$vars) {
            throw new TokenException();
        } else {
            if (!is_array($vars)) {
                $vars = json_decode($vars, true);
            }
            if (array_key_exists($key, $vars)) {
                return $vars[$key];
            } else {
                throw new TokenException();
            }
        }
    }

    /**
     * 从缓存中获取当前用户指定身份标识
     * @param array $keys
     * @return array result
     * @throws \app\lib\exception\TokenException
     */
    public static function getCurrentIdentity($keys)
    {
        $token = Request::instance()
            ->header('token');
        $identities = Cache::get($token);
        //cache 助手函数有bug
//        $identities = cache($token);
        if (!$identities) {
            throw new TokenException();
        } else {
            $identities = json_decode($identities, true);
            $result = [];
            foreach ($keys as $key) {
                if (array_key_exists($key, $identities)) {
                    $result[$key] = $identities[$key];
                }
            }
            return $result;
        }
    }

    /**
     * 当需要获取全局UID时，应当调用此方法,而不应当自己解析UID
     * @return mixed
     * @throws TokenException
     */
    public static function getCurrentUid()
    {
        return self::getCurrentTokenVar('uid');
    }

    public static function verifyToken()
    {
        $token = Request::header(config('fb.TOKEN'));
        $exist = Cache::get($token);
        // return boolval($exist);
        return $exist;
    }

    public static function getVarByToken($token)
    {
        return Cache::get($token);
    }

    public static function clearVarByToken($token)
    {
        return Cache::rm($token);
    }


}