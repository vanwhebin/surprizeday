<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/9/27
 * Time: 17:32
 */

namespace app\api\validate\influencer;

use app\api\validate\BaseValidate;

class InfluencerSearchValidate extends BaseValidate
{
    protected $rule = [
        'query'      => 'require|min:1|isNotEmpty',
    ];

    protected $message = [
        'query'      => 'Name is required',
    ];
}