<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/6/16
 * Time: 15:26
 */
return [
    'APP_ID'            => '373581646846834',
    'APP_SECRET'        => '6be59ae37ecb751ed46ff483c275ad9c',
    'DEFAULT_GRAPH_VERSION'  => 'v3.3',
    'PAGE_ID'            => '922651211418763',
    'WEBHOOK_TOKEN'     => '47Gh7gjtCajBUAmmy9sNrVUM6VA7U6FR82uMBVMCM7tcrzTD3p98RuBvcfsuB4mp',
    'PAGE_ACCESS_TOKEN' => 'EAAFTxUFa23IBAGOifM1kWUr8AAFoSh94JO0uA2dIrhasHiQOElpEMoCCPJnWZCZAalcZAceMa73ujpxUIW7xpSk2fcqESuYxP0ttz1jh91ZAjaZBRVB5Xuegj1EeNW3zyvsXejRnBjj2PPcmMolrSISLCJnZCfjR2tB7HZAIsUsWc2bJOHf8hke',
    'PAGE_URL'          => 'https://www.facebook.com/surprizeday/',
    'SEND_MESSAGE_API'  => 'https://graph.facebook.com/v3.3/me/messages?access_token=%s',
    'DEFAULT_BOT_MSG'   => [
        'image' => 'https://scontent.xx.fbcdn.net/v/t39.1997-6/39178562_1505197616293642_5411344281094848512_n.png?_nc_cat=1&_nc_oc=AQn8TzWM3S2M-8VBehnnScNEheh7fNSo2Kq2UVtA-sbainy5E7PMtMLLEryIaVriG4YnNolabRq45c49A860Y2AX&_nc_ad=z-m&_nc_cid=0&_nc_zor=9&_nc_ht=scontent.xx&oh=f3e64a429346fb9d0706d1f0479ad531&oe=5DF3C875',
        'gif' => 'portal/20190612/instagram_logo.gif',
        'file' => 'portal/20190612/test.txt',
        'audio' => 'portal/20190612/sample.mp3',
        'video' => 'portal/20190612/allofus480.mov',
    ],
    'TOKEN'  => 'token',
    'DEVELOPER_DEFINED_METADATA'  => '',
    /**-------messenger start----------------*/
    'AUTO_MSG_TXT' => "Thank you for messaging us. We'll get back to you soon.",
    'CONFIRM_JOIN_MSG' => "Please confirm your entry below.",
    'AUTO_MSG_ATTACHMENT' => "Message with attachment received, we'll get back to you soon",
    'AUTO_MSG_PRIZE_CLAIM' => "Congratulations, you are the winner! To claim your prize, reply to us within 5 days with your name, address, phone number, and email. Please note that your name (first name & last initial) and profile photo icon will be displayed in the draw result page. Remember to like us and stay tuned for our daily prize draws! %s",
    'AUTO_PRIZE_NOTIFY_UPDATE' => "Hi %s, winners have been randomly drawn for '%s'. Click here to view results.",
    'PRIZE_UPDATE_PRIZE' => 'Prize(s): ',
    'AUTO_OPTIN_MSG' => "Thanks for entering for your chance to win a free %s! You'll be notified once the winner(s) are randomly drawn. Like our page and stay tuned for our daily prize draws! %s",
    'WINNER_MSG_TITLE' => 'Winner has been drawn! Click here to view result.',
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
    'HOME_PIC'          => 'https://www.surprizeday.com/media/20191204/ff8602b22051d50d104b2e067086e432.jpg',
    'RETRIEVE_ID_API'   => 'https://graph.facebook.com/%s/ids_for_apps',
    'RETRIEVE_MESSENGER_INFO_API' => "https://graph.facebook.com/%s?fields=first_name,last_name,profile_pic&access_token=%s",
    'POLICY_TITLE' => 'Surprize Sweepstakes Official Rules',

    /*------管理员的message_id------*/
    "MANAGER_MSG_ID" => "2620814157962809",
    "MSG_SENT_ERROR_TPL" => "Error on sending message, detail: %s",
    /*-----发送message_id失败的模板消息-------*/

];