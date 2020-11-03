<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/9/27
 * Time: 17:32
 */

namespace app\api\validate\influencer;

use app\api\validate\BaseValidate;

class InfluencerValidate extends BaseValidate
{
    protected $rule = [
        'name'      => 'require|min:1|isNotEmpty',
        'nickname'  => 'require|min:1|isNotEmpty',
        'email'     => 'require|email|isNotEmpty',
        'platform'  => 'require|array|min:1',
    ];

    protected $message = [
        'name'      => 'Name is required',
        'nickname'  => 'Nickname is required',
        'email'     => 'Email is required',
        'platform.required'  => 'Influencer platform is required',
        'platform.array'  => 'Influencer platform param is invalid',
    ];
}