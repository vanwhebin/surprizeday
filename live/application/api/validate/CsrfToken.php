<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/9/23
 * Time: 16:19
 */

namespace app\api\validate;


class CsrfToken extends BaseValidate
{
    protected  $rule = [
        '__token__' => 'require|isNotEmpty|token'
    ];

    protected  $message = [
        '__token__' => 'Forbidden request'
    ];
}