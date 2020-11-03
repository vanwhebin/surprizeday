<?php

use think\facade\Route;
// 用来生成参与用户数据
Route::get('test', 'test/index/winner')->name('test');
Route::get('sql', 'test/index/sql');
Route::get('test-email', 'test/index/swiftMail');
Route::get('test-mailgun', 'test/index/mailgun');
Route::get('mailgun', 'test/index/email');
Route::get('routeMsg', 'test/index/routeMsg');
Route::get('winnerMsg', 'test/index/winnerMsg');
Route::get('freebies', 'index/common/landing');
Route::get('error', 'index/common/errorPop');

//  前台链接
Route::get('', 'index/index/home'); // 首页

Route::get('unsubscribe', 'index/index/unsubscribe');  // 取消订阅
Route::get('surprize/:slug/users', 'index/index/users')->pattern(['slug' => '[\d+|\w+-]+']); // 移动端抽奖活动参与者
Route::get('surprize/:slug/winners', 'index/index/winners')->pattern(['slug' => '[\w+-]+']); // 移动端活动中奖者
Route::get('surprize/:slug', 'index/index/surprize')->pattern(['slug' => '[\d+|\w+-]+']); // 移动端抽奖页面
Route::get('policy', 'index/index/rule'); // 移动端条款页面
Route::get('winners', 'index/index/allWinners'); // 移动端历史记录
Route::get('account', 'index/index/account');  // 移动端个人中心
Route::get('contact', 'index/index/contact'); // 移动端联系我们


/*------即时抽奖-----*/
Route::get('raffle/:slug', 'index/instant/instantPrize')->pattern(['slug' => '[\d+|\w+-]+']); // 即时抽奖
Route::post('raffle/:slug', 'index/instant/claim')->pattern(['slug' => '[\d+|\w+-]+']); // 即时抽奖

 /*-----即时抽奖-----*/



Route::group('api', function () {
    Route::group('cms', function () {

        Route::group('influencer', function () {
            Route::get('search', 'api/cms.Influencer/search');
            Route::get('activity', 'api/cms.Influencer/activity');
            Route::get('source', 'api/cms.Influencer/source');
            Route::post('bind', 'api/cms.Influencer/bind');
            Route::get('bind', 'api/cms.Influencer/influActivity');
            // 查询所有
            Route::get('', 'api/cms.Influencer/index');
            // 新建
            Route::post('', 'api/cms.Influencer/create');
            // 查询指定bid的
            Route::get(':id', 'api/cms.Influencer/read');
            // 搜索
            // 更新
            Route::put(':id', 'api/cms.Influencer/update');
            // 删除
            Route::delete(':id', 'api/cms.Influencer/delete');
        });
        Route::group('prize', function () {
            // 查询所有
            Route::get('', 'api/cms.Prize/index');
            Route::get('search', 'api/cms.Prize/search');
            Route::get('activity', 'api/cms.Prize/activity');
            // 新建
            Route::post('', 'api/cms.Prize/create');
            // 查询指定bid的
            Route::get(':id', 'api/cms.Prize/read');
            // 更新
            Route::put(':id', 'api/cms.Prize/update');
            // 删除
            Route::delete(':id', 'api/cms.Prize/delete');
        });
        Route::group('sponsor', function () {
            // 查询所有
            Route::get('', 'api/cms.Sponsor/index');
            // 新建
            Route::post('', 'api/cms.Sponsor/create');
            // 查询指定bid的
            Route::get(':id', 'api/cms.Sponsor/read');
            // 更新
            Route::put(':id', 'api/cms.Sponsor/update');
            // 删除
            Route::delete(':id', 'api/cms.Sponsor/delete');
        });
        Route::group('activity', function () {
            // 模糊查询活动信息
            Route::get('search', 'api/cms.Activity/search');
            // 查询所有
            Route::get('', 'api/cms.Activity/index');
            // 新建
            Route::post('', 'api/cms.Activity/create');
            // 查询具体的活动参与用户信息
            Route::get(':activity_id/users', 'api/cms.ActivityUser/index');
            // Route::rule(':id', 'api/cms.Activity/info', 'GET|POST|PUT|DELETE');
            // // 查询指定bid的
            Route::get(':id', 'api/cms.Activity/read');

            Route::post(':id', 'api/cms.Activity/hide');
            // 更新
            Route::put(':activity_id/fakers', 'api/cms.ActivityUser/activityFakers');
            Route::put(':activity_id/winners', 'api/cms.ActivityUser/activityWinners');

            Route::put(':id', 'api/cms.Activity/update');
            //
            // // 删除
            Route::delete(':id', 'api/cms.Activity/delete');
        });
        // 账户相关接口分组
        Route::group('user', function () {
            // 登陆接口
            Route::post('login', 'api/cms.User/login');
            // 刷新令牌
            Route::get('refresh', 'api/cms.User/refresh');
            // 查询自己拥有的权限
            Route::get('auths', 'api/cms.User/getAllowedApis');
            // 注册一个用户
            Route::post('register', 'api/cms.User/register');
            // 更新头像
            Route::put('avatar','api/cms.User/setAvatar');
            // 查询自己信息
            Route::get('information','api/cms.User/getInformation');
            // 查询活动假人信息
            Route::get("fakers", 'api/cms.User/fakers');
            // 查询活动赢家信息
            Route::rule("winners", 'api/cms.User/winners', 'GET|DELETE');
        });
        // 数据相关接口分组
        Route::group('data', function () {
            // 查询活动参与用户信息
            Route::get('participants', 'api/cms.Data/userCount?model=activity_user');
            // 查询用户信息
            Route::get('users', 'api/cms.Data/userCount?model=user');
        });
        // 管理类接口
        Route::group('admin', function () {
            // 查询所有权限组
            Route::get('group/all', 'api/cms.Admin/getGroupAll');
            // 查询一个权限组及其权限
            Route::get('group/:id', 'api/cms.Admin/getGroup');
            // 删除一个权限组
            Route::delete('group/:id', 'api/cms.Admin/deleteGroup');
            // 更新一个权限组
            Route::put('group/:id', 'api/cms.Admin/updateGroup');
            // 新建权限组
            Route::post('group', 'api/cms.Admin/createGroup');
            // 查询所有可分配的权限
            Route::get('authority', 'api/cms.Admin/authority');
            // 删除多个权限
            Route::post('remove', 'api/cms.Admin/removeAuths');
            // 添加多个权限
            Route::post('/dispatch/patch', 'api/cms.Admin/dispatchAuths');
            // 查询所有用户
            Route::get('users', 'api/cms.Admin/getAdminUsers');
            // 修改用户密码
            Route::put('password/:uid', 'api/cms.Admin/changeUserPassword');
            // 删除用户
            Route::delete(':uid', 'api/cms.Admin/deleteUser');
            // 更新用户信息
            Route::put(':uid', 'api/cms.Admin/updateUser');

        });
        // 日志类接口
        Route::get('log/', 'api/cms.Log/getLogs');
        Route::get('log/users', 'api/cms.Log/getUsers');
        Route::get('log/search', 'api/cms.Log/getUserLogs');

        //上传文件类接口
        Route::post('file','api/cms.File/postFile');
    });
    Route::group('v1', function () {
        /*---- 验证接口 ---------*/
        Route::get('webhook', 'api/v1.Message/validation');
        Route::post('webhook', 'api/v1.Message/message');
        Route::get('messenger', 'api/v1.Message/validation');
        Route::post('messenger', 'api/v1.Message/message');
        Route::post('messenger', 'api/v1.Message/message');
        Route::rule('callback/', 'api/v1.Oauth/callback', 'GET|POST');
        Route::get('login', 'api/v1.Oauth/login');
        /*---- 验证接口 ---------*/

        // 最近活动
        Route::get('activity/latest', 'api/v1.Activity/latest');
        Route::post('activity/account', 'api/v1.Activity/account');
        Route::get('activity/expired', 'api/v1.Activity/expired');
        // 所有参与活动用户
        Route::get('activity/:slug/users', 'api/v1.Activity/users')->pattern(['slug' => '[\w+-]+']);
        // 揭示中奖用户情况
        Route::get('activity/:slug/winner', 'api/v1.Activity/winner')->pattern(['slug' => '[\w+-]+']);
        Route::post('activity/:slug/claim', 'api/v1.Activity/claimPrize')->pattern(['slug' => '[\w+-]+']);
        // 活动详情
        Route::get('activity/:slug', 'api/v1.Activity/index')->pattern(['slug' => '[\w+-]+']);
        // 用户参与活动
        Route::post('activity/participant', 'api/v1.Activity/participant');


        // deals
        Route::get('deals/latest', 'api/v1.Deals/latest');


        // 生成Token
        Route::post('token/userEmail', 'api/v1.Token/emailLogin');
        Route::post('token/user', 'api/v1.Token/token');
        // 验证token
        Route::get('token/validation', 'api/v1.Token/verify');

    });
})->middleware(['Auth','ReflexValidate'])->allowCrossDomain();




