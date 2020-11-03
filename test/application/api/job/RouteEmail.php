<?php

namespace app\index\job;
use app\lib\exception\InvalidParamException;
use app\lib\MailgunClient;
use think\facade\Config;
use think\facade\Hook;
use think\queue\Job;

class RouteEmail {
    public $senderName ;
    public $senderEmail ;
    public $subject ;
    public $domain ;

    public function __construct()
    {
        $this->senderName = Config::get('email.senderName');
        $this->senderEmail = Config::get('email.senderEmail');
        $this->domain = Config::get('email.senderDomain');;
    }

    /**
     * fire方法是消息队列默认调用的方法
     * @param Job            $job      当前的任务对象
     * @param array|mixed    $data     发布任务时自定义的数据
     * @return bool
     */
    public function fire(Job $job,$data)
    {
        // 有些消息在到达消费者时,可能已经不再需要执行了
        $isJobStillNeedToBeDone = $this->checkIfJobNeedToBeDone($data);
        if(!$isJobStillNeedToBeDone){
            $job->delete();
            return true;
        }

        $isJobDone = $this->sendEmail($job, $data);
        if ($isJobDone) {
            // 如果任务执行成功， 记得删除任务
            $job->delete();
        }else{
            if ($job->attempts() > 1) {
                $job->delete();
            }
        }
    }

    /**
     * 有些消息在到达消费者时,可能已经不再需要执行了
     * @param array|mixed    $data     发布任务时自定义的数据
     * @return boolean                 任务执行的结果
     */
    private function checkIfJobNeedToBeDone($data){
        // 标题 发件人 收件人 邮件内容
        foreach($data as $item){
            if (!$item) {
                $this->failed($data);
                $data = false;
                break;
            }
        }
        return $data;
    }


    /**
     * 该方法用于接收任务执行失败的通知，你可以发送邮件给相应的负责人员
     * @param $jobData  string|array|...      //发布任务时传递的 jobData 数据
     */
    public function failed($jobData){
        print("Warning: Job failed after max retries. job data is :".var_export($jobData,true)."\n");
    }

    /**
     * 根据消息中的数据进行实际的业务处理...
     * @param $job
     * @param $data
     * @return bool
     */

    private function sendEmail($job, $data)
    {
        try {
            $mailGunClient = new MailgunClient();
            $mailGunClient->subject = $data['subject'];
            $mailGunClient->senderEmail = $data['senderEmail'];
            $mailGunClient->senderName = $data['senderName'];
            $mailGunClient->emailRecipients = $data['emailRecipients'];
            $unsubscribeUrl = config('get.domain').config('email.unsubscribe');
            $mailGunClient->emailTpl = $str = str_replace('{:unsubsribeUrl}', $unsubscribeUrl,$data['emailContent']);
            foreach($data['emailRecipients'] as $key=>$item){
                $data['emailRecipientsVariables'][$item] = ['id' => $key, 'email' => $item];
            }
            $mailGunClient->emailRecipientsVariables = $data['emailRecipientsVariables'];
            $mailGunClient->domain = !empty($data['domain'])?$data['domain']:null;
            $mailGunClient->deliveryTime = !empty($data['deliveryTime'])?$data['deliveryTime']:null;
            return $mailGunClient->send();
        } catch(InvalidParamException $e){
            Hook::listen('queue_failed', $job);
            return false;
        }
    }
}