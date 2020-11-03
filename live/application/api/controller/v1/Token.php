<?php

namespace app\api\controller\v1;

use app\api\controller\BaseController;
use app\api\service\Token as TokenService;
use app\api\service\UserToken;
use app\api\validate\EmailLoginValidate;
use app\api\validate\TokenValidate;
use app\lib\exception\TokenException;
use think\facade\Request;

class Token extends BaseController
{
    /**
     * @param string $userID
     * @return array
     * @throws TokenException
     * @throws \app\lib\exception\InvalidParamException
     * @throws \think\Exception
     */
    public function token($userID='')
    {
        (new TokenValidate())->batch()->validate();
        // 调用服务交互获取token
        $user = new UserToken();
        $token = $user->getToken($userID);
        if (!$token) {
            throw new TokenException(['errorCode' => '60000']);
        }

        return ['token' => $token];

    }

    public function verify()
    {
        $valid = TokenService::verifyToken();
        return [
            'isValid' => $valid,
        ];
    }

    /**
     * @param Request $request
     * @return array
     * @throws TokenException
     * @throws \app\lib\exception\InvalidParamException
     */
    public function emailLogin(Request $request)
    {
        (new EmailLoginValidate())->validate();
        $param = $request::post();
        $user = new UserToken();
        $token = $user->getEmailToken($param);
        if (!$token) {
            throw new TokenException(['errorCode' => '60000', 'msg' => 'error happened when gain token with email']);
        }
        return ['token' => $token];
    }
}
