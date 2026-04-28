<?php
use Twilio\Rest\Client;
/**
 * @author Yang Pao Thao
 */
class SMSClass {
    function SendSMSpayment($db, $thisphone_number, $square_receiptno, $realamount, $thisuser_recno, $thisserver)
    {
        //https://www.twilio.com/docs/messaging/quickstart
        // $token = go to the account dashboard and go to Admin drop down then click Account Management, once confirmed, you will get to the page, In General Setting
        // you will find your ACCount SID and the API Keys and Token, click on it and go all the way down, you will see the eye with some dots, click on it to review.;
        $thisbody = "Thank you for your payment of $".number_format($realamount, 2)." to ".$_SESSION['companyname'];
        //TEST with Twilio test environment
        $sql = "SELECT * FROM company_info";
        $result = $db->PDOminiquery($sql);
        foreach($result as $rs)
        {
            $api_id = $rs['api_id'];
            $api_token = $rs['api_token'];
        }
        $client = new Client("$api_id", "$api_token");
        $thisreturn = $client->messages
            ->create("8777804236", // The recipient's phone number
                array(
                    "from" => "18557744925",
                    "body" => $thisbody
                )
            );
        /*
        $thisreturn = $this->apicon->messages
            ->create("$thisphone_number", // The recipient's phone number
                array(
                    "from" => $_SESSION['company_phonenumber'],
                    "body" => $thisbody
                )
            );
        */
        // Print the message SID to confirm it was sent
        return($thisreturn);
    }
    function SendSMSappointment($db, $thisphone_number, $thisuser_recno, $thisserver, $thisdate, $thistime)
    {
        //https://www.twilio.com/docs/messaging/quickstart
        // $token = go to the account dashboard and go to Admin drop down then click Account Management, once confirmed, you will get to the page, In General Setting
        // you will find your ACCount SID and the API Keys and Token, click on it and go all the way down, you will see the eye with some dots, click on it to review.;
        //date('Y-m-d', strtotime($_POST['thisdate']))
        $thisbody = "Appointment confirmation with ".$_SESSION['companyname']."\n\n";
        $thisbody .= "Date: ".date('d M Y', strtotime($thisdate))." @ $thistime";
        $sql = "SELECT * FROM company_info";
        $result = $db->PDOminiquery($sql);
        foreach($result as $rs)
        {
            $api_id = $rs['api_id'];
            $api_token = $rs['api_token'];
        }
        $client = new Client("$api_id", "$api_token");
        $thisreturn = $client->messages
            ->create("8777804236", // The recipient's phone number
                array(
                    "from" => "18557744925",
                    "body" => $thisbody
                )
            );
        /*
        $thisreturn = $client->messages
            ->create("$thisphone_number", // The recipient's phone number
                array(
                    "from" => $_SESSION['company_phonenumber'],
                    "body" => $thisbody
                )
            );*/

        // Print the message SID to confirm it was sent
        return($thisreturn);
    }
    function SendSMSrefund($db, $thisphone_number, $square_receiptno, $realamount, $thistimestamp)
    {
        $thisbody = "Refund confirmation from ".$_SESSION['companyname']."\n\n";
        $thisbody .= "Date: ".$thistimestamp."\n";
        $thisbody .= "Amount: ".$realamount;
        $sql = "SELECT * FROM company_info";
        $result = $db->PDOminiquery($sql);
        foreach($result as $rs)
        {
            $api_id = $rs['api_id'];
            $api_token = $rs['api_token'];
        }
        $client = new Client("$api_id", "$api_token");
        $thisreturn = $client->messages
            ->create("8777804236", // The recipient's phone number
                array(
                    "from" => "18557744925",
                    "body" => $thisbody
                )
            );
        /*
        $thisreturn = $client->messages
            ->create("$thisphone_number", // The recipient's phone number
                array(
                    "from" => $_SESSION['company_phonenumber'],
                    "body" => $thisbody
                )
            );*/
    }
}
