<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/9/5
 * Time: 12:11
 */

namespace app\api\validate;


class EmailValidate extends BaseValidate
{
    protected $rule = [
        'email' => 'require|email|isNotEmpty'
    ];

    protected $message = [
        'email' => 'Invalid email',
    ];
}