<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/9/23
 * Time: 12:17
 */

namespace app\lib\enum;


class ActivityEnum
{
    // 活动类型
    const ORDINARY = 1;  // 普通类型
    const TEAMUP = 2;    // 组团类型

    // 字段 status
    const SHOW = 1;
    const HIDE = 0;

    // 字段private
    const PRIVATE = 1;
    const PUBLIC = 0;

    // 读取用户活动推送形式
    const USER_ACTIVITY_RECOMMEND = [
        'LATEST' => 1   //   1  推荐最新的   可能的2 为推荐 特定活动
    ];

}