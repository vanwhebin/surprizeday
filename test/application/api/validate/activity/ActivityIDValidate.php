<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/6/17
 * Time: 17:56
 */

namespace app\api\validate\activity;


use app\api\validate\BaseValidate;

class ActivityIDValidate extends BaseValidate
{
    protected $rule = [
        'activity_id' => 'require|isPositiveInteger'
    ];

    protected $message = [
        'activity_id' => 'Missing activity id or Invalid Param'
    ];
}