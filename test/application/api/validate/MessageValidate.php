<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/6/16
 * Time: 15:17
 */

namespace app\api\validate;


class MessageValidate extends BaseValidate
{
    protected $code;
    // protected  $rule = [
    //     'message' => 'require|isNotEmpty|validRequestSignature',
    // ];
    public function __construct(array $rules = [], array $message = [], array $field = [])
    {
        parent::__construct($rules, $message, $field);
    }

    protected  $message = [
        'message' => 'Message is required.'
    ];

    protected function isNotEmpty($value, $rule = '', $data = '', $field = '')
    {
        return parent::isNotEmpty($value, $rule, $data, $field);
    }

    protected function  validRequestSignature()
    {
        $appSecret = config('fb.APP_SECRET');
        $raw_post_data = file_get_contents('php://input');
        $header_signature = $_SERVER['X-Hub-Signature'];
        if (!$header_signature) {
            $this->message = 'Invalid Signature';
            return false;
        }
        // Signature matching
        $expected_signature = hash_hmac('sha1', $raw_post_data, $appSecret);

        if( strlen($header_signature) == 45 &&
            substr($header_signature, 0, 5) == 'sha1='
        ) {
            $signature = substr($header_signature, 5);
            if (hash_equals($signature, $expected_signature)) {
                return true;
            }
        }
        return false;
    }
}