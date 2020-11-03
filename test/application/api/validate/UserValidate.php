<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/6/17
 * Time: 11:05
 */

namespace app\api\validate;


class UserValidate extends BaseValidate
{
    protected $rule = [
        'userID' => 'require|isPositiveInteger'
    ];

    public $message = [
        'userID' => 'Invalid Facebook Identity'
    ];
}