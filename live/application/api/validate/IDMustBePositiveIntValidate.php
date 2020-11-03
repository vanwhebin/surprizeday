<?php


namespace app\api\validate;


class IDMustBePositiveIntValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|isPositiveInteger',
    ];

    protected $message = [
        'id' => 'ID must be an integer',
    ];


}