<?php
namespace app\index\controller;

use app\api\model\Activity as ActivityModel;
use app\api\model\Subscription as SubscriptionModel;
use app\api\model\Winner as WinnerModel;
use app\api\service\Activity as ActivityService;
use app\api\validate\activity\ActivityValidate;
use app\api\validate\CountValidate;
use app\api\validate\EmailValidate;
use app\index\service\Index as IndexService;
use app\lib\exception\InvalidParamException;
use app\lib\exception\MissingException;
use think\Controller;
use think\facade\Request;

class Index extends Controller
{
    /**
     * @param int $num
     * @param int $page
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index($num=21, $page=1)
    {
        $activityRes = IndexService::allActivity($num, $page);
        $dealsRes = IndexService::allDeals($num, $page);
        $head = $this->head(
            config('fb.HOME_TITLE'),
            config('fb.HOME_DESC'),
            config('domain'),
            ['url' => config('fb.HOME_PIC')]
        );
        $this->assign('head', $head);
        $this->assign('activity', $activityRes);
        $this->assign('deals', $dealsRes);
        return $this->fetch('index/pc/home');
    }


    /**
     * @url draw/:id PC端抽奖详情
     * @param $activity_id
     * @return mixed
     * @throws MissingException
     * @throws \app\lib\exception\TokenException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */

    public function draw($activity_id)
    {
        if(!ActivityModel::get($activity_id)) {
            throw new MissingException();
        }
        $activityInfo = ActivityService::activityInfo($activity_id);
        $activityUsers = ActivityService::currentUsers($activityInfo['id'], 30);
        $winners = [];
        if ($activityInfo['start_time'] < time()) {
            $winners = IndexService::activityWinners($activityInfo['id']);
        }
        $activityInfo['start_time'] = dateFormat($activityInfo['start_time']);
        $url = getActivityUrl($activityInfo['slug']);

        $shareImgUrl = isset($activityInfo['prize']['pic'][0]) ? $activityInfo['prize']['pic'][0] :$activityInfo['prize']['pic'][0];
        $head = $this->head($activityInfo['seo_title'], config('fb.SURPRIZE_SHARE_DESC'), $url, ['url' => $shareImgUrl]);

        $this->assign('head', $head);
        $this->assign('info', $activityInfo);
        $this->assign('winners', $winners);
        $this->assign('users', $activityUsers);
        $this->assign('activity_id', $activityInfo['id']);
        $this->assign('slug', $activityInfo['slug']);
        return $this->fetch('index/pc/index');
    }


    /**
     * @param int $num
     * @param int $page
     * @return mixed
     * @throws InvalidParamException
     * @throws \think\exception\DbException
     */
    public function home($num=21, $page=1)
    {
        (new CountValidate())->validate();
        // 首页活动
        if (!isMobile()) {
            return $this->index($num, $page);
        }

        $head = $this->head(
            config('fb.HOME_TITLE'),
            config('fb.HOME_DESC'),
            config('domain'),
            ['url' => config('fb.HOME_PIC')]
        );
        $this->assign('head', $head);
        return $this->fetch('home');

    }

    /**
     * 前台用户请求具体活动详情
     * @param $slug
     * @return mixed
     * @throws InvalidParamException
     * @throws MissingException
     * @throws \app\lib\exception\TokenException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */

    public function surprize($slug)
    {
        (new ActivityValidate())->validate();
        $activity_id = ActivityModel::where(['slug' => $slug])->column('id');

        if (!isMobile()) {
            return $this->draw($activity_id);
        }
        // 开奖和未开奖两种情况
        $activityInfo = ActivityService::activityInfo($activity_id);
        if (!$activityInfo) {
            throw new InvalidParamException();
        }
        $activityInfo['start_time'] = dateFormat($activityInfo['start_time']);
        $url = getActivityUrl($slug);
        $shareImgUrl = isset($activityInfo['prize']['pic'][0]) ? $activityInfo['prize']['pic'][0] : $activityInfo['thumb']['url'];
        $head = $this->head($activityInfo['seo_title'], config('fb.SURPRIZE_SHARE_DESC'), $url, ['url' => $shareImgUrl]);
        $this->assign('head', $head);
        $this->assign('activity_id', $activityInfo['id']);
        $this->assign('slug', $slug);
        $this->assign('activity', $activityInfo);
        return $this->fetch('index');
    }


    /**
     * 活动赢家页面
     * @param $slug
     * @return mixed
     * @throws InvalidParamException
     * @throws \think\exception\DbException
     */

    public function winners($slug)
    {
        (new ActivityValidate())->validate();
        $activityID = ActivityModel::where(['slug' => $slug])->value('id');
        $winners = WinnerModel::getWinnerByActivityID(999,1, $activityID);
        if (!$winners) {
            throw new InvalidParamException();
        }
        $url = getActivityUrl($slug).'/winners';
        $head = $this->head('Winners', 'Winners', $url, []);
        $this->assign('head', $head);
        $this->assign('winners', $winners);
        return $this->fetch('winners');
    }


    /**
     * 活动给规则页面
     * @return mixed
     */
    public function rule()
    {
        $title =  config('fb.POLICY_TITLE');
        $head = $this->head($title, '', config('domain').'/policy', []);
        $this->assign('head', $head);
        if (!isMobile()) {
            return $this->fetch('index/pc/rule');
        }

        return $this->fetch('rule');
    }

    /**
     * 活动给规则页面
     * @param $slug
     * @return mixed
     * @throws InvalidParamException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */


    public function users($slug)
    {
        (new ActivityValidate())->validate();
        $activity = ActivityModel::getInfoBySlug($slug);
        if (!$activity) {
            throw new InvalidParamException();
        }
        $title =  'Users in '. ucfirst($activity->title);
        $head = $this->head($title, '', getActivityUrl($slug).'/users', []);
        $this->assign('head', $head);
        $this->assign('activity_id', $activity->id);
        $this->assign('slug', $slug);
        return $this->fetch('users');
    }


    /**
     * 页面的META信息
     * @param string $title
     * @param string $desc
     * @param string $url
     * @param array $image
     * @return array
     */
    public function head($title='', $desc='', $url='/', $image=[])
    {
        return [
            'token' =>  Request::instance()->header('token') ?: cookie('token'),
            'og_title' => $title,
            'og_desc' => $desc,
            'og_url' => $url,
            'og_image' => isset($image['url']) ? $image['url'] : '' ,
            'og_image_width' => isset($image['height']) ? $image['height'] : config('fb.SHARE_HEIGHT'),
            'og_image_height' => isset($image['width']) ? $image['width'] : config('fb.SHARE_WIDTH'),
        ];
    }


    public function account()
    {
        $title =  'Surprize account';
        $head = $this->head($title, '', config('domain').'/account', []);
        $this->assign('head', $head);
        return $this->fetch();
    }

    /**
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function allWinners()
    {
        $title =  'Surprize Draw Winners';
        $head = $this->head($title, '', config('domain').'/winners', []);
        $this->assign('head', $head);
        if (!isMobile()) {
            return $this->winnersPC();
        }

        return $this->fetch('winners');
    }

    /**
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function winnersPC()
    {
        $winners = ActivityService::expired(10, 1);
        $this->assign('winners', $winners);
        return $this->fetch('index/pc/winners');
    }

    public function contact()
    {
        $title =  'Contact Surprize';
        $head = $this->head($title, '', config('domain').'/contact', []);
        $this->assign('head', $head);
        return $this->fetch();
    }

    public function unsubscribe()
    {
        (new EmailValidate())->validate();
        $email = Request::get('email');
        $res = SubscriptionModel::where(['email' => $email, 'status' => 0])->find();
        if (!$res) {
            SubscriptionModel::create([
                'email' => $email,
                'status' => 0
            ]);
        }
        $head = $this->head('Unsubscribe Surprize', '', config('domain').'/unsubscribe', []);
        $this->assign('head', $head);
        return $this->fetch('index/pc/subscription');
    }
}
