<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/6/17
 * Time: 16:11
 */

namespace app\api\service;

use app\api\job\ActivityUser as ActivityUserJob;
use app\api\job\RouteNewMsg as RouteNewMsgJob;
use app\api\model\Activity as ActivityModel;
use app\api\model\ActivityTeam as ActivityTeamModel;
use app\api\model\ActivityUser;
use app\api\model\ActivityUser as ActivityUserModel;
use app\api\model\Log;
use app\api\model\Message as MessageModel;
use app\api\model\PrizeMore as PrizeMoreModel;
use app\api\model\User;
use app\api\model\Winner as WinnerModel;
use app\api\service\Token as TokenService;
use think\facade\Cache;
use think\facade\Request;

class Activity
{
    /**
     * @param int $num
     * @param int $page
     * @return array|mixed
     * @throws \app\lib\exception\TokenException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public static function home($num=3, $page=1)
    {
        // 鉴别用户是否参与
        // 获取最新，分页懒加载
        $lastActivityListKey = "activity:list:latest:num:".$num. ":page:".$page;
        $engagedActivity = [];
        $allActivity = Cache::store('redis')->get($lastActivityListKey);
        if (!$allActivity) {
            $latest= ActivityModel::allActivityWithPagination($num, $page)->toArray();
            Cache::store('redis')->set($lastActivityListKey, json_encode($latest), 60);
        } else {
            $latest = json_decode($allActivity, true);
        }

        if (Request::instance()->header('token')) {
            $userID = TokenService::getCurrentUid();
            $engagedActivity = ActivityUserModel::where(['user_id' => $userID])
                ->column('activity_id');
        }
        foreach ($latest['data'] as $key=>$value) {
            if (in_array($value['id'], $engagedActivity)) {
                $latest['data'][$key]['engaged'] = true;
            }
        }
        $latest['data'] = array_map(function($item){
            $item['start_time'] = dateFormat($item['start_time']);
            $item['url'] = getActivityUrl($item['slug']);
            return $item;
        }, $latest['data']);

        return $latest;
    }

    /**
     * @param int $num
     * @param int $page
     * @param bool $expired
     * @return array
     * @throws \app\lib\exception\TokenException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public static function account($num=3, $page=1, $expired=false)
    {
        $userID = TokenService::getCurrentUid();
        if ($expired) {
            $activityJoined = ActivityUserModel::with(['activity' => function($query){
                $query->where('start_time' ,'LT', time());
            }, 'activity.thumb']);
        } else {
            $activityJoined = ActivityUserModel::with(['activity', 'activity.thumb']);
        }

        $activityJoined->where(['user_id' => $userID])
            ->order('create_time desc')
            ->field('activity_id')
            ->hidden(['activity_id']);
        $res = $activityJoined->paginate($num, false, ['page' => $page])->toArray();

        $res['data'] = array_map(function($item){
            $item['activity']['start_time'] = dateFormat($item['activity']['start_time']);
            return $item;
        }, $res['data']);

        return $res;
    }


    /**
     * @param $activityID
     * @return array|null|\PDOStatement|string|\think\Model
     * @throws \app\lib\exception\TokenException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function one($activityID)
    {
        // 以后的推荐活动肯定会根据用户已有的参与活动进行判断推荐
        $alreadyEngaged = [$activityID];
        if (Request::instance()->header('token')) {
            $userID = TokenService::getCurrentUid();
            // 排除用户已参与的活动，并只返回一条最新活动数据
            $alreadyEngagedID = ActivityUser::where(['user_id' => $userID])->distinct(true)->column('activity_id');
            $alreadyEngaged = array_merge($alreadyEngaged, $alreadyEngagedID);
        }

        $recommend = ActivityModel::with(['thumb'])
            ->where(['status' => 1, 'private' => 0])
            ->whereNotIn('id', $alreadyEngaged)
            ->order('create_time desc')
            ->field(['title', 'description', 'activity_img_id', 'start_time', 'slug', 'id'])
            ->limit(1)
            ->find();
        return $recommend;
    }


    /**
     * @param int $num
     * @param int $page
     * @param $activity_id
     * @return array
     * @throws \app\lib\exception\TokenException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function more($num=1, $page=1, $activity_id)
    {
        // 以后的推荐活动肯定会根据用户已有的参与活动进行判断推荐
        $userID = TokenService::getCurrentUid();
        // 排除用户已参与的活动，并只返回一条最新活动数据
        $alreadyEngaged = ActivityUser::where(['user_id' => $userID])->column('activity_id');
        array_push($alreadyEngaged, intval($activity_id));
        array_unique($alreadyEngaged);
        $id = ActivityModel::where(['status' => 1])
            ->whereNotIn('id', $alreadyEngaged)
            ->order('create_time desc')
            ->page($page)
            ->limit($num)
            ->column('id');
        $activity = self::activityInfo($id);
        $users = ActivityUserModel::with(['user'=>function($query){$query->visible(['avatar', 'name']);}])
            ->where(['activity_id' => $id])
            ->visible(['user'])
            ->order('id desc')
            ->limit(7)
            ->select();
        return ['info' => $activity,  'user' => ['info' => $users, 'total' =>$users->count()]];
    }

    /**
     * @return bool
     * @throws \app\lib\exception\TokenException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function enroll()
    {
        $userID = TokenService::getCurrentTokenVar('uid');
        $activityID = input('post.activity_id');
        $refer = input('post.refer');

        $existed = ActivityUserModel::getOne($activityID, $userID);
        if (!$existed) {
            $participant = ActivityUserModel::create([
                'user_id'       =>  $userID,
                'activity_id'   =>  $activityID,
                'from'          =>  isMobile() ? 1 : 2,
            ]);

            $user = User::get($userID);
            $routeData = $data = [
                'userID' => $user->id,
                'messageID' => $user->message_id,
                'email' => $user->email,
                'confirm' => $user->confirm,
                'userName' => explode(" ", $user->name)[0],
                'activityID' => $activityID,
            ];
            if ($user->message_id) {
                // 发送用户参与信息
                $activityInfo = ActivityModel::getSimpleInfo($activityID)->toArray();
                $info['title'] = config('fb.CONFIRM_JOIN_MSG');
                $info['image_url'] = $activityInfo['thumb']['url'];
                $info['subtitle'] = $activityInfo['title'];
                $info['url'] = getActivityUrl($activityInfo['slug']);
                $data['info'] = $info;
                MessageModel::sendConfirmMsg($user->message_id, $data);
            }

            // 用户来自推介
            if ($refer) {
                ActivityUserJob::handleChanel($activityID, $user, $participant, $refer);
            }

            // 给用户增加每日推送
            (new RouteNewMsgJob())->createRouteMsg($routeData, true);
            return boolval($participant);
        } else {
            return true;
        }
    }


    // 用户同意推送消息，已点击前台按钮
    public static function connected($userID, $activityID, $messageID)
    {
        $activityUser = ActivityUserModel::getOne($activityID, $userID);
        if (!$activityUser) {
            ActivityUserModel::create([
                'user_id' => $userID,
                'activity_id' => $activityID,
            ]);
        }
        return true;
    }

    /**
     * @param $activityID
     * @return array|null|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function activityInfo($activityID)
    {
        $prizeCount = ActivityModel::prizeCount($activityID);
        if ($prizeCount > 1) {
            $res = ActivityModel::getActivityWithMultiPrize($activityID);
            $res = $res->toArray();
            if ($res['prize']) {
                $prize = array_reduce($res['prize'], function($prize, $item){
                    $prize[] = $item['main_img']['url'];
                    return $prize;
                });
                $video = array_shift($res['prize']);
                unset($res['prize']);
                $res['prize']['pic'] = $prize;
                $res['prize']['video'] = !empty($video['video']) ? $video['video']['url'] : '';
            }
        } else {
            $res = ActivityModel::getActivityWithSinglePrize($activityID);
            if ($res) {
                $res = $res->toArray();
                if ($res['prize']) {
                    $prize = array_reduce($res['prize'][0]['album'], function($prize, $item){
                        $prize[] = $item['img']['url'];
                        return $prize;
                    });
                    $video = !empty($res['prize'][0]['video']) ? $res['prize'][0]['video']['url'] : '';
                    unset($res['prize']);
                    $res['prize']['pic'] = $prize;
                    $res['prize']['video'] = $video;
                }
            }
        }

        return $res;
    }

    /**
     * 获取用户是否参与当前活动，如果有，返回用户头像名字
     * @param $activityID
     * @param int $num
     * @param int $page
     * @return array
     * @throws \app\lib\exception\TokenException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function currentUsers($activityID, $num=6, $page=1)
    {
        $res = [];
        $joinedCurrentUser = null;
        $res['entry'] = 0;
        $res['cur'] = $userID = null;
        $participants = (new ActivityUserModel())->getUsersByActivityID($num, $page, $activityID)->toArray();
        if (Request::instance()->header('token')) {
            $userID = TokenService::getCurrentUid();
            $res['cur'] = User::where(['id' => $userID])->field(['name', 'avatar', 'message_id'])->find();
            $joinedCurrentUser = ActivityUserModel::getUserInfo($userID, $activityID);
        }
        if (!empty($userID) && !empty($joinedCurrentUser)) {
            foreach ($participants['data'] as $key=>$value) {
                if ($userID == $value['user']['id']) {
                    unset($participants['data'][$key]);
                    break;
                }
            }
            array_unshift($participants['data'], $joinedCurrentUser);
            if (count($participants['data']) > 6) {
                array_pop($participants['data']);
            }
            $refers = ActivityTeamModel::where([
                'activity_id' => $activityID,
                'user_id'     => $joinedCurrentUser->user_id
            ])->value('cur_entry');
            $res['entry'] = $refers;
        }
        $res['users'] = $participants;
        $res['curUser'] = $joinedCurrentUser;

        return $res;
    }


    /**
     * 返回当前活动的开奖结果
     * @param $participants
     * @param $activityID
     * @return array
     * @throws \app\lib\exception\TokenException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function checkResult($participants, $activityID)
    {
        $winner = $check = false;
        $moreInfo = [];
        if ($participants['curUser']) {
            $userID = TokenService::getCurrentUid();
            $check = ActivityUserModel::check($activityID, $userID);
            $winner = WinnerModel::with(['info' => function($query){
                $query->field(['name', 'avatar', 'id'])->hidden(['id']);
            }])->where([
                'user_id'       =>  $userID,
                'activity_id'   =>  $activityID,
            ])->field(['user_id','level'])->visible(['info', 'level'])->find();
        }
        $winners = WinnerModel::getWinnerByActivityID(100, 1, $activityID);
        $winners = array_map(function($item){
            $item['info']['name'] = hideUserName($item['info']['name']);
            return $item;
        }, $winners['data']);

        $recommend = self::one($activityID);
        $recommend['start_time'] = dateFormat($recommend['start_time']);
        $moreInfo['recommend'] = $recommend;

        if (!$check && Request::instance()->header('token')) {
            $prizeMore = PrizeMoreModel::with(['code'=>function($query){
                $query->field(['code', 'id', 'platform'])->hidden(['id']);
            }])->where(['activity_id' => $activityID])->select();
            $moreInfo['sale'] =  $prizeMore;
        }

        $userInfo = ['checked' => $check, "winner" => $winner, 'winners' => $winners];
        return ['userInfo' => $userInfo, 'moreInfo' => $moreInfo];
    }

    /**
     * @param $slug
     * @return bool
     * @throws \app\lib\exception\InvalidParamException
     * @throws \app\lib\exception\TokenException
     * @throws \think\Exception
     */
    public static function claim($slug)
    {
        // 后台推送消息到用户messenger
        $userID = TokenService::getCurrentTokenVar('uid');
        $activity_id = ActivityModel::where(['slug' => $slug])->column('id');
        $user = User::get($userID);
        $msg = sprintf(config('fb.AUTO_MSG_PRIZE_CLAIM'), config('fb.PAGE_URL'));
        $res = MessageModel::sendTextMessage($user->message_id, $msg);
        if (!$res) {
            Log::create([
                'log' => '用户'. $user->name. '领取活动奖品失败， id:'. $userID,
                'topic' => 'claim failed',
            ]);
            return false;
        };

        return WinnerModel::claim($activity_id, $userID);
    }


    /**
     * @param int $num
     * @param int $page
     * @return array
     * @throws \think\exception\DbException
     */
    public static function expired($num=3, $page=1)
    {
        $exActivityPag = ActivityModel::with([
            'prize' => function($query){ $query->visible(['name']);},
            'thumb' =>function($query){$query ->visible(['url']);},
            'winner' => function($query){$query->where(['level'=>1])->field(['name', 'activity_id'])->visible(['name']);},
            ])->where(['status' => 1, 'private' => 0])
            ->where('start_time' ,'LT', time())
            ->visible(['thumb', 'winner', 'slug', 'start_time'])
            ->order('start_time desc, order desc, id desc')
            ->paginate($num, false, ['page' => $page])
            ->toArray();

        $exActivityPag['data'] = array_map(function($item){
            $item['start_time'] = dateFormat($item['start_time']);
            $item['winner'] = ltrim(array_reduce($item['winner'], function($i, $t){
                $name = hideUserName($t['name']);
                $i .= (', '. $name);
                return $i;
            }),', ');

            return $item;
        }, $exActivityPag['data']);
        return $exActivityPag;
    }


}