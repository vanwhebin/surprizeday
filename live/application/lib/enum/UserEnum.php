<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/9/23
 * Time: 12:20
 */

namespace app\lib\enum;


class UserEnum
{
    const FROM_UNKNOWN = 0; // FACEBOOK来源
    const FROM_FACEBOOK = 1; // FACEBOOK来源
    const FROM_EMAIL = 2; // FACEBOOK来源

    const UNKNOWN = 0;  // 未知性别
    const MAN = 1;      // 男
    const WOMAN = 2;    // 女

    const SUBSCRIBE = 1;  // 接受推送
    const UNSUBSCRIBE = 0;  // 不接受推送
}