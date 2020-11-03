<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/6/17
 * Time: 17:56
 */

namespace app\api\validate;


class ActivityValidate extends BaseValidate
{
    protected $rule = [
        'slug' => 'require'
    ];

    protected $message = [
        'slug' => 'Missing activity title or Invalid Param'
    ];
}