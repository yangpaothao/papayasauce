<?php
use Square\SquareClient;

require(__DIR__ .'/vendor/autoload.php');
//https://developer.squareup.com/reference/square
//sandbox https://developer.squareup.com/docs/devtools/sandbox/overview
//credit card exs, //https://developer.squareup.com/docs/devtools/sandbox/payments
class SquareClass extends SquareClient {
    private $recno;
    private $firstname;
    private $lastname;
    private $thisemail;
    private $thisphone;
    private $thistoken;
    
    public function set_customer($tempuserrecno, $tempfirstname, $templastname, $tempthisemail, $tempthisphone, $tempthistoken)
    {
        $this->recno = $tempuserrecno;
        $this->firstname = $tempfirstname;
        $this->lastname = $templastname;
        $this->thisemail = $tempthisemail;
        $this->thisphone = $tempthisphone;
        $this->thistoken = $tempthistoken;
    }
    public function create_customer()
    {
        
    }

    
}
