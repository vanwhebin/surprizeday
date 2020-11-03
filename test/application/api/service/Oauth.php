<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/7/19
 * Time: 16:11
 */

namespace app\api\service;


use app\api\model\Log;

class Oauth
{
    public function log($request)
    {
        $data = $request::param();
        Log::create(['log' => json_encode([$data]), 'topic' => 'fb_redirect_info']);
        return true;
    }
}