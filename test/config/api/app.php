<?php
use think\facade\Env;
return [
    'img_prefix'                =>  Env::get('media.img_prefix', "https://t.surprizeday.com/media/"),
    'domain'                    =>  Env::get('media.domain', "https://t.surprizeday.com"),
    'expire_in'                 =>  Env::get('token.expire_in', 7200),
    'default_return_type'       => 'json',
];