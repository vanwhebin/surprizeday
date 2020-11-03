<?php

namespace app\api\validate;


class CountValidate extends BaseValidate
{
    protected $rule = [
        'num' => 'isPositiveInteger|between:1,200'
    ];

    protected $message = [
        'num' => 'count must be a positive integer between 1 and 200',
    ];
}