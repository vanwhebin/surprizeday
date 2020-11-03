<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/5/24
 * Time: 15:06
 */

namespace app\lib\exception;


class AddressException extends BaseException
{
    public $code = 404;
    public $msg = 'No data';
    public $errorCode = 60001;
}