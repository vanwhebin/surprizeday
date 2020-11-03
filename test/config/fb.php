<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/6/16
 * Time: 15:26
 */
use think\facade\Env;

return [
    'APP_ID'            => Env::get('fb.app_id','701245580337669'),
    'APP_SECRET'        => Env::get('fb.app_secret','9f474c2b3e411ca27bb9fd6b924fa1b9'),
    'DEFAULT_GRAPH_VERSION'  => 'v3.3',
    'PAGE_ID'           => Env::get('fb.page_id','382821282577179'),
    'WEBHOOK_TOKEN'     => '47Gh7gjtCajBUAmmy9sNrVUM6VA7U6FR82uMBVMCM7tcrzTD3p98RuBvcfsuB4mp',
    'PAGE_ACCESS_TOKEN' => Env::get('fb.page_token','EAAUeYQ6dm70BACneMgYKO1TIMUNCNeENRTJD7KbsYxVSY59nqc2LpKGR8DnF3WFEoMi6sj1925mua1yyrSTbBSmQ5qxnvYGR0Y3WJE3uowZCLaVZBNpQxV20IMqv5Gbtp7u8M0gGbNFHJuDKNMCihnTfzc6jMSNELZBX85XXfXBodWSeZCam'),
    'PAGE_URL'          => 'https://www.facebook.com/surprizeday/',
    'SEND_MESSAGE_API'  => 'https://graph.facebook.com/v3.3/me/messages?access_token=%s',
    'DEFAULT_BOT_MSG'   => [
        'image'         => 'portal/20190612/rift.png',
        'gif'           => 'portal/20190612/instagram_logo.gif',
        'file'          => 'portal/20190612/test.txt',
        'audio'         => 'portal/20190612/sample.mp3',
        'video'         => 'portal/20190612/allofus480.mov',
    ],
    'TOKEN'             => 'token',
    'DEVELOPER_DEFINED_METADATA'  => '',
    /**-------messenger start----------------*/
    'AUTO_MSG_TXT'      => "Thank you for messaging us. We'll get back to you soon.",
    'CONFIRM_JOIN_MSG'  => "Please confirm your entry below.",
    'AUTO_MSG_ATTACHMENT' => "Message with attachment received, we'll get back to you soon",
    'AUTO_MSG_PRIZE_CLAIM' => "Congratulations, you are the winner! To claim your prize, reply to us within 5 days with your name, address, phone number, and email. Please note that your name (first name & last initial) and profile photo icon will be displayed in the draw result page. Remember to like us and stay tuned for our daily prize draws! %s",
    'AUTO_PRIZE_NOTIFY_UPDATE' => "Hi %s, winners have been randomly drawn for '%s'. Click here to view results.",
    'PRIZE_UPDATE_PRIZE'=> 'Prize(s): ',
    'AUTO_OPTIN_MSG'    => "Thanks for entering for your chance to win a free %s! You'll be notified once the winner(s) are randomly drawn. Like our page and stay tuned for our daily prize draws! %s",
    'WINNER_MSG_TITLE'  => 'Winner has been drawn! Click here to view result.',
    'FIRST_MSG_CONFIRM_TITLE' => 'Hey %s, don‘t forget to confirm your entry here! ☝',
    'SECOND_MSG_CONFIRM_TITLE' => "Hey %s, it's Jessica from Surprize team😊 Please confirm your entry here so we can count you in!☝",
    'TEAM_UP_MSG_TITLE' => 'Refer friends to enter and receive extra entries as referral bonus!',
    'TEAM_UP_FEEDBACK'  => '%s has entered via your link. +2 Bonus Entries🚀',
    'MESSENGER_URL'     => 'https://m.me/surprizeday',

    /**--------messenger end---------------*/
    'SHARE_HEIGHT'  => '600',
    'SHARE_WIDTH'   => '600',
    'SURPRIZE_SHARE_DESC'   => 'Enter now for a chance to win! No sign-up required.',
    'HOME_TITLE'        => 'Daily Giveaways: Enter to win Amazon Gift Cards, tech accessories,  room essentials and more!',
    'HOME_DESC'         => 'Join the daily draw today and win amazing tech accessories, room essentials, gift cards and more!',
    'HOME_PIC'          => 'https://www.surprizeday.com/media/20191014/9298a59fa39cbe7ff80518b8c9d525aa.png',
    'RETRIEVE_ID_API'   => 'https://graph.facebook.com/%s/ids_for_apps',
    'RETRIEVE_MESSENGER_INFO_API' => "https://graph.facebook.com/%s?fields=first_name,last_name,profile_pic&access_token=%s",
    'POLICY_TITLE'      => 'Surprize Sweepstakes Official Rules',

    /*------管理员的message_id------*/
    "MANAGER_MSG_ID"    => Env::get('fb.manager_msg_id',"2673976209297714"),
    "MSG_SENT_ERROR_TPL"=> "Error on sending message, detail: %s",
    /*-----发送message_id失败的模板消息-------*/

];