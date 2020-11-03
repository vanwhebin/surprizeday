<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/9/12
 * Time: 10:10
 */

namespace app\api\job;


class AutoReply
{
    const HANDLER = 'app\api\job\ActivityUser@teamup';

    // const QUEUE_NAME = 'activityUserQueue';
    const QUEUE_NAME = 'messengerQueue';
    const MAX_ENTRY = 200;
    const INC_ENTRY = 2;   // 新增两个entry

    const FIRST_DELAY_SHARE = (3);   // 确认消息3秒延迟发送任务

    const DEBUG_FIRST_DELAY_SHARE = 1;   // 调试状态下延迟1秒发送任务
}