<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/9/12
 * Time: 10:10
 */

namespace app\api\job;
use think\queue\Job;
use think\facade\Hook;

class AutoReply
{
    const HANDLER = 'app\api\job\AutoReply';

    const QUEUE_NAME = 'messengerQueue';

    const FIRST_DELAY_SHARE = (3);   // 确认消息3秒延迟发送任务

    const DEBUG_FIRST_DELAY_SHARE = 1;   // 调试状态下延迟1秒发送任务


    /**
     * fire方法是消息队列默认调用的方法
     * @param Job            $job      当前的任务对象
     * @param array|mixed    $data     发布任务时自定义的数据
     */
    public function fire(Job $job,$data)
    {
        $isJobStillNeedToBeDone = $this->checkDatabaseToSeeIfJobNeedToBeDone($data);
        if(!$isJobStillNeedToBeDone){
            $job->delete();
            return;
        }

        $isJobDone = $this->reply($job, $data);
        $job->delete();
        if (!$isJobDone) {

        }
    }

    /**
     * 有些消息在到达消费者时,可能已经不再需要执行了
     * @param array|mixed    $data     发布任务时自定义的数据
     * @return boolean                 任务执行的结果
     */
    private function checkDatabaseToSeeIfJobNeedToBeDone($data){
        return true;
    }


    /**
     * 该方法用于接收任务执行失败的通知，你可以发送邮件给相应的负责人员
     * @param $jobData  string|array|...      //发布任务时传递的 jobData 数据
     */
    public function failed($jobData){
        // send_mail_to_somebody() ;
        // sendMsg2Manager(var_export($jobData,true));
        print("Warning: Job failed after max retries. job data is :".var_export($jobData,true)."\n");
    }


}