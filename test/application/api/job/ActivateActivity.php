<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/10/7
 * Time: 18:15
 */

namespace app\api\job;


use app\api\model\Activity as ActivityModel;
use app\api\model\Log;
use think\Exception;
use think\facade\Hook;
use think\queue\Job;

class ActivateActivity
{
    const QUEUE_NAME = 'RoutingTaskQueue';

    public function fire(Job $job,$data)
    {
        // 定时触发活动显示在前台
        $isJobStillNeedToBeDone = $this->check($data);
        if (!$isJobStillNeedToBeDone) {
            $job->delete();
            return true;
        }
        try{
            $isJobDone = $this->execute($job, $data);
        }catch (Exception $e){
            Hook::listen('queue_failed', ['request' => $job->getRawBody(), 'response' => $e->getMessage()]);
            return true;
        }

        if ($isJobDone) {
            // 如果任务执行成功， 删除任务
            $job->delete();
            return true;
        } else {
            if ($job->attempts() > 3) {
                //通过这个方法可以检查这个任务已经重试了几次了
                print("<warn>Hello Job has been retried more than 3 times!" . "</warn>\n");
                $job->delete();
                return true;
            }
        }

    }


    private function check($data){
        // TODO 后期可能出现的验证检查
        return true;
    }


    /**
     * 该方法用于接收任务执行失败的通知，你可以发送邮件给相应的负责人员
     * @param $jobData  string|array|...      //发布任务时传递的 jobData 数据
     */
    public function failed($jobData){
        Log::create([
            'topic' => 'RouteTask@'.__CLASS__,
            'log'   => "Warning: Job failed after max retries. job data is :".var_export($jobData,true)."\n"
        ]);
    }

    /**
     * 根据消息中的数据进行实际的业务处理...
     * @param $job
     * @param $data
     * @return bool
     */
    private function execute($job, $data)
    {
        try {
            ActivityModel::where(['id' => $data['id']])->update(['status' => 1]);
            return true;
        } catch(Exception $e){
            Hook::listen('queue_failed', $job);
            return false;
        }
    }
}