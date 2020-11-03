<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/11/5
 * Time: 16:07
 */

namespace app\api\validate\message;


use app\api\validate\BaseValidate;

class TextMessageValidate extends BaseValidate
{
    protected $rule = [
        'text' => 'require|isNotEmpty|max:640',
        'message_id' => 'require|isNotEmpty',

    ];

    protected $message = [
        'text' => '发送文字不能为空并且不能超过320个字',
        'message_id' => '收件人不能为空',
    ];
}