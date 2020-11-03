<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/9/9
 * Time: 15:49
 */
use think\facade\Env;
return [
    'senderDomain'  =>  Env::get('email.domain', "email.surprizeday.com"),
    'senderName'    =>  Env::get('email.fromName', "Surprize"),
    'senderEmail'   =>  Env::get('email.fromEmail', "support@surprizeday.com"),
    'apiKey'        =>  Env::get('email.apiKey', "key-78mhse86jrhi-89bwhxelfe-lzcgfkc1"),
    'unsubscribe'   =>  '/unsubscribe',
    'smtp_host'     =>  Env::get('email.smtp_host', "smtp.exmail.qq.com"),
    'smtp_port'     =>  Env::get('email.smtp_port', "465"),
    'smtp_protocol'     =>  Env::get('email.smtp_protocol', "ssl"),
    'smtp_username' =>  Env::get('email.smtp_username', "support@surprizeday.com"),
    'smtp_password' =>  Env::get('email.smtp_password', "Aukey1234"),
];