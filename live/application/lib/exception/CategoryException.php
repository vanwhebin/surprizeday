<?php


namespace app\lib\exception;


class CategoryException extends BaseException
{
    public $code = 404;
    public $msg = 'No data';
    public $errorCode = 50000;

}