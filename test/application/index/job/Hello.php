<?php
/**
 * 文件路径： \application\index\job\Hello.php
 * 这是一个消费者类，用于处理 helloJobQueue 队列中的任务
 */
namespace app\index\job;
use app\api\model\Message as MessageModel;
use think\Exception;
use think\queue\Job;
use think\facade\Hook;

class Hello {
    /**
     * fire方法是消息队列默认调用的方法
     * @param Job            $job      当前的任务对象
     * @param array|mixed    $data     发布任务时自定义的数据
     */
    public function fire(Job $job,$data)
    {
        // 有些消息在到达消费者时,可能已经不再需要执行了
        $isJobStillNeedToBeDone = $this->checkDatabaseToSeeIfJobNeedToBeDone($data);
        if(!$isJobStillNeedToBeDone){
            $job->delete();
            return;
        }

        $isJobDone = $this->doHelloJob($job, $data);
        if ($isJobDone) {
            // 如果任务执行成功， 记得删除任务
            $job->delete();
            // $job->release(5);
            print("<info>Hello Job has been done and deleted"."</info>\n");
        }else{
            if ($job->attempts() > 3) {
                //通过这个方法可以检查这个任务已经重试了几次了
                print("<warn>Hello Job has been retried more than 3 times!"."</warn>\n");
                // $job->release(100);
                $job->delete();

                // 也可以重新发布这个任务
                //print("<info>Hello Job will be availabe again after 2s."."</info>\n");
                //$job->release(2); //$delay为延迟时间，表示该任务延迟2秒后再执行
            }
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

    /**
     * 根据消息中的数据进行实际的业务处理...
     * @param $job
     * @param $data
     * @return bool
     */

    private function doHelloJob($job, $data)
    {
        try {
            // MessageModel::sendTextMessage(config('fb.MANAGER_MSG_ID'), "test queue msg at ".date('Y-m-d H:i:s'));
            // MessageModel::sendConfirmMsg(config('fb.MANAGER_MSG_ID'), $this->testMsg);
            print("<info>执行成功</info> \r\n");
            if(strpos($job->getName(), '10') !==false) {
                print("<info>这是一个延时任务：". $data['name'] ." </info>\r\n");
            }

            print("<info>Hello Job Started. job Data is: ".var_export($data,true)."</info> \r\n");
            print("<info>Hello Job is Fired at " . date('Y-m-d H:i:s') ."</info> \r\n");
            print("<info>Hello Job is Done!"."</info> \r\n");
            return true;
        } catch(Exception $e){
            Hook::listen('queue_failed', $job);
            return false;
        }
    }
}