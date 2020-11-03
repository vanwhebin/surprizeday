<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/9/23
 * Time: 14:56
 */

namespace app\api\validate;


class EmailLoginValidate extends BaseValidate
{
    protected $rule = [
        'nickname' => 'require|isNotEmpty',
        'email' => 'require|email',
        // '__token__' => 'require|email|token',
    ];

    protected $message = [
        'nickname' => 'User name is required',
        'email' => 'Email is required',
        // '__token__' => 'Invalid request',
    ];
}