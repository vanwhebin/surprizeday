<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/7/19
 * Time: 16:11
 */

namespace app\api\controller\v1;

use app\api\model\Log;
use app\api\model\User as UserModel;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use app\api\controller\BaseController;
use think\facade\Request;

class Oauth extends BaseController
{
    protected $conf = [
        'app_id' => '',
        'app_secret' => '',
        'default_graph_version' => '',
    ];

    public function login(Request $request)
    {
        session_start();
        $_SESSION['previous_page'] = $_SERVER['HTTP_REFERER'];
        $fbConf = [
            'app_id' => config('fb.APP_ID'),
            'app_secret' => config('fb.APP_SECRET'),
            'default_graph_version' => config('fb.DEFAULT_GRAPH_VERSION'),
        ];
        $fb = new Facebook($fbConf);
        $helper = $fb->getRedirectLoginHelper();
        $permissions = ['email']; // Optional permissions
        $redirectUrl = config('domain').'/api/v1/callback/';
        $fbRedirectUrl = $helper->getLoginUrl($redirectUrl, $permissions);
        return redirect($fbRedirectUrl);
    }


    public function callback(Request $request)
    {
        session_start();
        if (!empty($_SESSION['previous_page'])) {
            $backUrl = $_SESSION['previous_page'];
            unset($_SESSION['previous_page']);
        } else {
            $backUrl = '/';
        }

        $data = $request::param();
        Log::create(['log' => json_encode($data), 'topic' => 'fb_redirect_info', 'create_time' =>time()]);

        if($request::param('error') == 'access_denied'){
            return redirect('/');
        }
        $fbConf = [
            'app_id' => config('fb.APP_ID'),
            'app_secret' => config('fb.APP_SECRET'),
            'default_graph_version' => config('fb.DEFAULT_GRAPH_VERSION'),
        ];

        $fb = new Facebook($fbConf);
        $helper = $fb->getRedirectLoginHelper();
        if(isset($_GET['state'])){
            $_SESSION['FBRLH_state']=$_GET['state'];
        }

        try {
            $accessToken = $helper->getAccessToken();
        } catch(FacebookResponseException $e) {
            // When Graph returns an error
            $message = 'Graph returned an error: ' . $e->getMessage();
            Log::create(['log'=>'facebook login graph failed:'. $message, 'topic'=> 'fb_redirect_error', 'create_time' =>time()]);
            return redirect($backUrl);
        } catch(FacebookSDKException $e) {
            // When validation fails or other local issues
            $message = 'Facebook SDK returned an error: ' . $e->getMessage();
            Log::create(['log'=>'facebook login sdk failed:'. $message, 'topic'=> 'fb_redirect_error', 'create_time' =>time()]);
            return redirect($backUrl);
        }

        if (! isset($accessToken)) {
            if ($helper->getError()) {
                $message = "Error: " . $helper->getError() . "\n";
                $message .= "Error Code: " . $helper->getErrorCode() . "\n";
                $message .= "Error Reason: " . $helper->getErrorReason() . "\n";
                $message .= "Error Description: " . $helper->getErrorDescription() . "\n";
            } else {
                $message = 'Bad request';
            }
            Log::create(['log'=>'facebook login access_token failed:'. $message, 'topic'=> 'fb_redirect_error', 'create_time' =>time()]);
            return redirect($backUrl);
        }


        $oAuth2Client = $fb->getOAuth2Client();
        $tokenMetadata = $oAuth2Client->debugToken($accessToken);
        $tokenMetadata->validateAppId(config('fb.APP_ID'));
        $tokenMetadata->validateExpiration();

        if (! $accessToken->isLongLived()) {
            try {
                $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
            } catch (FacebookSDKException $e) {
                $message = 'Error getting long-lived access token'. $e->getMessage();
                Log::create(['log'=>'facebook login expired_access_token failed:'. $message, 'topic'=> 'fb_redirect_error']);
                return redirect($backUrl);
            }
        }
        $_SESSION['fb_access_token'] = (string) $accessToken;
        $fb->setDefaultAccessToken($accessToken);
        $response = $fb->get('/me?locale=en_US&fields=id,name,email,picture');
        $userNode = $response->getGraphUser();
        $email = $userNode->getField('email');
        $name = $userNode->getField('name');
        $fb_user_id = $userNode->getField('id');
        $picture = json_decode($userNode->getField('picture'), true);

        $user = UserMOdel::getUserByUserID($fb_user_id);
        if (!$user) {
            UserModel::create([
                'name'      => $name,
                'userid'    => $fb_user_id,
                'email'     => $email ? : "",
                'avatar'    => !empty($picture['url'])? $picture['url'] : "",
            ]);
        }

        return redirect($backUrl);
    }
}