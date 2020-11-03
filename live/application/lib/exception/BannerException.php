<?php


namespace app\lib\exception;


class BannerException extends BaseException
{

    public $code = 404;
    public $msg = 'No data';
    public $errorCode = 40000;

}