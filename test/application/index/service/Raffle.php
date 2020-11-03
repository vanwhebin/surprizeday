<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/10/22
 * Time: 16:30
 */

namespace app\index\service;


use app\api\model\Log;
use app\api\model\RafflePrize;
use app\api\model\RaffleUser;
use app\api\model\RaffleWinner;
use think\Db;
use think\Exception;

class Raffle
{
    /**
     * 抽奖活动
     * @param $activityID
     * @param $userID
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function findLuckyDog($activityID, $userID)
    {
        $availablePrizes = RafflePrize::with(['prize' => function($query){
            $query->field(['name', 'id', 'main_img_url' , 'from'])->hidden(['id', 'from']);
        }])->where(['activity_id' => $activityID])
            ->where('stock', 'gt', '0')
            ->select()
            ->toArray();

        // 中奖规则
        if ($availablePrizes) {
            // 依据规则选取中奖用户
            $drawPrize = $this->drawRule($activityID, $userID, $availablePrizes);
            if ($drawPrize !== false) {
                // 处理抽中的情况
                $res = $this->_handleLuckyDog($activityID, $userID, $drawPrize);
                if ($res) {
                    $claimUrl = config('fb.MESSENGER_URL').'?ref=';
                    return [
                        'claim' => $claimUrl,
                        'msg'   =>  sprintf('You won %s, please message us to claim.', $drawPrize['prize']['name']),
                    ];
                }
            }
        }
        // 直接返回未中
        $nextTryTime = (RaffleUser::VALID_PERIOD) / 3600;
        return [
            'msg' => 'This box is empty, try next time ('.  $nextTryTime .' hours later).'
        ];
    }

    /**
     * 处理抽奖环节
     * @param $activityID
     * @param $userID
     * @param $prizes
     * @return bool|int
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function drawRule($activityID, $userID, $prizes)
    {
        // 一些限制条件
        $rolledBefore = $this->_limitedRule($activityID, $userID);
        if (!$rolledBefore) {
            return false;
        }
        $rand = intval(substr(((time() + $userID) . ''), -4));
        // $mod = (count($prizes) * 2);  // 50% 的概率
        $mod = (count($prizes));  // 50% 的概率

        $modRes = $rand % $mod;
        if (empty($prizes[$modRes])) {
            // 抽不中， 返回false
            return false;
        } else {
            // 返回取模结果，用于获取对应的中奖奖品
            // 排除一些情况
            if ($this->_excludeRule($userID, $prizes[$modRes])) {
                // 排除该用户
                return false;
            }
            // 返回抽中的奖品
            return $prizes[$modRes];
        }
    }


    /**
     * 活动参与的一些限制条件
     * @param $activityID
     * @param $userID
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */

    protected function _limitedRule($activityID, $userID)
    {
        // 不可以在6小时内重复参与 查询 raffle_user表是否存在对应记录
        // 如果用户已经中过该奖项，则自动跳过，不可重复中奖
        $validPeriodUser =  RaffleUser::validRaffleUser($activityID, $userID);
        if ($validPeriodUser) {
            $noneJoinedUser = RaffleWinner::getWinnerByActivity($activityID, $userID);
            if (!$noneJoinedUser) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 处理中奖用户排除的一些条件
     * @param $userID
     * @param $wonPrize
     * @return bool
     */
    protected function _excludeRule($userID, $wonPrize)
    {
        // 如果用户之前已经中过该奖项，同款促销码，如果该奖品不可重复申领，则跳过。
        $wonSamePrizeUserID = RaffleWinner::getWinnerByPrize($wonPrize['prize_id'], $userID);
        if($wonSamePrizeUserID) {
            if ($wonPrize['multi']) {
                return false;  // 该奖品能多次中奖比如礼品卡等
            } else {
                return true;   // 用户曾经中奖，并且中奖产品不允许多次领取
            }
        } else {
            return false;  // 用户未中过奖
        }
    }


    /**
     * 处理数据
     * @param $activityID
     * @param $userID
     * @param $prize
     * @return bool
     */
    protected function _handleLuckyDog($activityID, $userID, $prize)
    {
        // 写入数据库表并且对奖品数量进行扣除
        Db::startTrans();
        try{
            RaffleUser::createUser($activityID, $userID);
            RaffleWinner::createWinner($activityID, $userID, $prize['prize_id']);
            RafflePrize::reduceStock($activityID, $prize['prize_id']);
        }catch(Exception $e){
            Log::create([
                'topic' => 'raffle winner',
                'log'   => json_encode(['param' => [$activityID, $userID, $prize], 'error_msg' => $e->getMessage()]),
            ]);
            Db::rollback();
            return false;
        }
        Db::commit();
        return true;
    }



}