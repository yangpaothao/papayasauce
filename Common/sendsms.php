<?php
function sendsms($thisnumber, $thismessage)
{
    require_once(__DIR__ . '/vendor/autoload.php');

    $config = ClickSend\Configuration::getDefaultConfiguration()
        ->setUsername('USERNAME')
        ->setPassword('API_KEY');

    $apiInstance = new ClickSend\Api\SMSApi(new GuzzleHttp\Client(),$config);

    $msg = new \ClickSend\Model\SmsMessage();
    $msg->setSource("SOURCE");
    $msg->setBody("MESSAGE");
    $msg->setTo("TO_PHONE_NUMBER");

    $sms_messages = new \ClickSend\Model\SmsMessageCollection();
    $sms_messages->setMessages([$msg]);

    try {
        $result = $apiInstance->smsSendPost($sms_messages);
        print_r($result);
    } catch (Exception $e) {
        echo 'Exception when calling SMSApi->smsSendPost: ', $e->getMessage(), PHP_EOL;
    }    
    /*
    //https://support.twilio.com/hc/en-us/articles/1260801864489-How-do-I-register-to-use-A2P-10DLC-messaging
    ////Need to register before can send out
    ////Need LLC created before can be register.
    // Required if your environment does not handle autoloading
    require __DIR__ . '/vendor/autoload.php';

    // Your Account SID and Auth Token from console.twilio.com
    $sid = "ACf144e5170ae59b75b7c28f9081b5d503";
    $token = "d2668952cae1e6612be75a0578bd05e7";
    $client = new Twilio\Rest\Client($sid, $token);

    // Use the Client to make requests to the Twilio REST API
    $client->messages->create(
        // The number you'd like to send the message to
        "+14058890899",
        [
            // A Twilio phone number you purchased at https://console.twilio.com
            'from' => '+19183765906',
            // The body of the text message you'd like to send
            'body' => "$thismessage"
        ]
    );
     
     */
}
?>