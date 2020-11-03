<?php
/**
 * Created by PhpStorm.
 * User: a2
 * Date: 2019/10/21
 * Time: 10:53
 */

namespace tests;

use app\api\model\Email;
use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase
{
    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testEmail()
    {
        $emailModel = new Email();
        $emailClient = $emailModel->emailClient;

        $str = '<div class="email-div %recipient.id%" style="background: rgb(255, 255, 255); width: 840px; margin: auto;">
    <div>
        <div class="email-con" style="width: 500px;padding: 30px 0;margin: auto;">
            <a href="https://www.surprizeday.com" class="logo" style="text-align: center;text-decoration: none;"><img class="logo-b" src="http://www.surprizeday.com/static/img/logo_full.png" style="display: block;margin: auto;"/></a>
            <div class="main-con" style="margin: auto;padding: 50px 0;padding: 36px 42px;box-shadow: 0 5px 19px #ddd;margin-top: 10px;background: #fff;">
                <img class="banner" src="https://www.surprizeday.com/media/images/ysl/0eda01ed-f9d5-45ce-b932-44be1d9a10591.gif?v=643652345" style="margin-bottom: 5px;width:100%"/>
                <p style="font-size: 16px;line-height: 24px;color: #000;">
                    <span style="font-family: arial, helvetica, sans-serif;">Hello,</span>
                </p>
                <p style="padding: 10px 0;font-size: 16px;line-height: 24px;color: #000;">
                    <span style="background-color: rgb(255, 255, 255); font-family: arial, helvetica, sans-serif;">Enter our featured event of the month for a chance to get a Yves Saint Laurent Lipsticks Bundle! (6 winners)</span>
                </p>
                <ul class=" list-paddingleft-2" style="list-style-type: disc;">
                    <li>
                        <p>
                            <span style="font-family: arial, helvetica, sans-serif;">1st: YSL Lipsticks Bundle (3 lipsticks of your choice + $50 Amazon Gift Card</span>
                        </p>
                    </li>
                    <li>
                        <p>
                            <span style="font-family: arial, helvetica, sans-serif;">2nd & 3rd: 1 YSL Lipstick of your choice + $20 Amazon Gift Card</span>
                        </p>
                    </li>
                    <li>
                        <p>
                            <span style="font-family: arial, helvetica, sans-serif;">4th to 6th: $15 Amazon Gift Card</span>
                        </p>
                    </li>
                </ul>
                <p>
                    <span style="font-family: arial, helvetica, sans-serif;">It takes 10 seconds to enter and you could be the next winner!</span>
                </p><a href="https://www.surprizeday.com/surprize/featured-giveaway-ysl-lipsticks-amazon-gift-card-bundle-6-winners" style="text-decoration:none"><button type="button" style="width: 110px;height: 38px;background: #ff8f17;border: none;line-height: 38px;color: #fff;text-align: center;text-transform: uppercase;font-size: 14px;display: block;margin: auto;cursor: pointer;"><span style="font-family: arial, helvetica, sans-serif;">Enter now</span></button></a>
                <p>
                    <span style="font-family: arial, helvetica, sans-serif;">All giveaways are free to enter and no sign-up is required. Winner will be announced on <strong>09/24/2019</strong>.</span>
                </p>
                <p style="padding: 10px 0;font-size: 16px;line-height: 24px;color: #000;">
                    <span style="font-family: arial, helvetica, sans-serif;">Thanks, <span style="font-family: arial, helvetica, sans-serif; display: block;">Surprize Team</span></span><span style="display: block;"></span>
                </p>
            </div>
            <div class="share" style="margin: auto;display: table;padding: 20px 0 10px;">
                <a href="https://www.facebook.com/surprizeday/" style="text-decoration: none;">                    <img class="logo-b" src="https://www.surprizeday.com/static/img/fb.png" style="float: left;margin: 0 19px;"/>                </a>
            </div><span class="reserved" style="font-size: 14px;color: #666;text-align: center;display: block;margin-top:5px">© 2019 Surprize. All rights reserved.</span>            <a href="{:unsubsribeUrl}?email=%recipient.email%" style="text-decoration: none"><span style="font-size: 14px;color: #666;text-align: center;display: block;margin-top:5px;">unsubscribe</span></a>
        </div>
    </div>
</div>';
        $recipients =[
            'wanweibin@aukeys.com',
            'vanwhebin@gmail.com',
            'kent@aukeys.com'
        ];

        $recipientsVar = [
            'wanweibin@aukeys.com' => ['id' =>1, 'email' => 'wanweibin@aukeys.com'],
            'vanwhebin@gmail.com' => ['id' =>2, 'email' => 'vanwhebin@gmail.com'],
        ];

        // $emailClient->domain = app()->config('email.senderDomain');
        $emailClient->domain = "us.surprizeday.com";
        $emailClient->senderName = "Surprize";
        $emailClient->emailRecipients = $recipients;
        $emailClient->emailRecipientsVariables = $recipientsVar;
        $emailClient->senderEmail = "biz@surprizeday.com";
        $emailClient->sender = $emailClient->senderName. ' <'. $emailClient->senderEmail. '>';
        $emailClient->emailTpl = $str;
        $emailClient->subject = "hello test mailgun from t.surprizeday.com";

        $res = $emailClient->send();

        $this->assertEquals(true, $res);
    }
}
