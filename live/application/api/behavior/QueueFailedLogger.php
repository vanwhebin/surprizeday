<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/8/2
 * Time: 11:32
 * Description: 这是一个行为类，用于处理所有的消息队列中的任务失败回调
 */

namespace app\api\behavior;

use app\api\model\QueueLog;

class QueueFailedLogger
{
    const HOOK_CALLBACK = true;

    public static function logFailedQueueEvents($data)
    {
        return true;
        // $jobObj = $data['request'];
        // $resObj = $data['response'];
        //
        // $failedJobLog = [
        //     'jobHandlerClassName' => $jobObj->getName(),
        //     'queueName'           => $jobObj->getQueue(),
        //     'jobData'             => $jobObj->getRawBody(),
        //     'attempts'            => $jobObj->attempts(),
        // ];
        // QueueLog::create([
        //     'jobHandlerClassName' => $jobObj->getName(),
        //     'queueName'           => $jobObj->getQueue(),
        //     'jobData'             => $jobObj->getRawBody(),
        //     'attempts'            => $jobObj->attempts(),
        //     'response'            => json_encode($resObj->getData()),
        // ]);
        //
        // var_export(json_encode($failedJobLog,true));

        // $jobObj->release();     //重发任务
        // $jobObj->delete();      //删除任务
        // $jobObj->failed();	   //通知消费者类任务执行失败

        // return self::HOOK_CALLBACK;
    }
}