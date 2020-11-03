<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/10/16
 * Time: 12:19
 */

namespace app\api\validate;


class PaginationValidate extends BaseValidate
{
    protected $rule = [
        "num"       =>  'require|isPositiveInteger|between:1,200',
        "page"      =>  'require|isPositiveInteger|between:1,100',
    ];

    protected $message = [
        "num"   =>    "Invalid request number",
        "page"  =>    "Invalid request page number"
    ];
}