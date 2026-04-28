<?php
/**
 * Description of newDateClass
 *
 * @author Yang Pao Thao
 */
class Email_Class {
    public $cmail;
    public $isdate = false;
    function set_email($email)
    {
        $this->cmail = $email;
    }
    function get_email()
    {
        return($this->cmail);
    }
    public function validate_email()
    {
        //We just want to make sure the email is in good format.
        if(filter_var($this->cmail, FILTER_VALIDATE_EMAIL))
        {
            return(true);
        }  
        else
        {
            return(false);
        }
    }
    public function check_email()
    {
        //Now we want to check if this email already exist. We will get a record back if it exists.
        return("SELECT recno FROM users WHERE email = '".$this->cmail."'");
    }
    public function get_verification_subject()
    {
        global $db;
        $sql = "SELECT * FROM company_info";
        $result = $db->PDOMiniquery($sql);
        foreach($result as $rs)
        {
            $thiscompanyname = $rs['name'];
            $thiscompanyphoneno = $rs['phone_number'];
        }
        
        $subject = "Account verification sent From ".$thiscompanyname;
        
        return($subject);
    }
    public function get_active_subject()
    {
        $subject = "Employee Active report";
        
        return($subject);
    }
    public function get_active_body($db, $thisserver, $thisemployee, $status)
    {
        $sql = "SELECT * FROM company_info";
        $result = $db->PDOMiniquery($sql);
        foreach($result as $rs)
        {
            $thiscompanyname = $rs['name'];
            $thiscompanyphoneno = $rs['phone_number'];
        }
        $body = "This is a system generated email sent to you by ".$thiscompanyname.".  Please do not respond.</br>";
        $body .= "This email is to inform you that $thisemployee status has changed to ".($status == 'true' ? 'Active' : 'Inactive').".";
        return($body);
    }
    public function get_terminated_body($thisserver, $thisemployee, $status)
    {
        global $db;
        $sql = "SELECT * FROM company_info";
        $result = $db->PDOMiniquery($sql);
        foreach($result as $rs)
        {
            $thiscompanyname = $rs['name'];
            $thiscompanyphoneno = $rs['phone_number'];
        }
        
        $body = "This is a system generated email sent to you by ".$thiscompanyname.".  Please do not respond.</br>";
        $body .= "This email is to inform you that $thisemployee status has changed to ".($status == 'true' ? 'Terminated' : 'Un-Terminated').".";
        return($body);
    }
    public function get_terminated_subject()
    {
        $subject = "Employee Termination report";
        
        return($subject);
    }
    public function get_verification_passwordreset()
    {
        global $db;
        $sql = "SELECT * FROM company_info";
        $result = $db->PDOMiniquery($sql);
        foreach($result as $rs)
        {
            $thiscompanyname = $rs['name'];
            $thiscompanyphoneno = $rs['phone_number'];
        }
        
        $subject = "Account password reset sent From ".$thiscompanyname;
        
        return($subject);
    }
    public function get_verification_body($thisserver, $realvericode)
    {
        global $db;
        $sql = "SELECT * FROM company_info";
        $result = $db->PDOMiniquery($sql);
        foreach($result as $rs)
        {
            $thiscompanyname = $rs['name'];
            $thiscompanyphoneno = $rs['phone_number'];
        }
        $body = "This is a system generated email sent to you by ".$thiscompanyname.".  Please do not respond.</br>";
        $body .= "You received this email because your administrator created an account for you.  However, before you can login to<br/>";
        $body .= "the website, you must change your password.  Please follow the link below.<br><br>";
        $body .= "<a href='http://$thisserver/passwordreset.php?vericode=".$realvericode."'>Click here to change your password.</a>";
        
        return($body);
    }
    public function get_registerguest_body($thisserver)
    {
        global $db;
        $sql = "SELECT * FROM company_info";
        $result = $db->PDOMiniquery($sql);
        foreach($result as $rs)
        {
            $thiscompanyname = $rs['name'];
            $thiscompanyphoneno = $rs['phone_number'];
        }
        
        $body = "This is a system generated email sent to you by ".$thiscompanyname.".  Please do not respond.</br>";
        $body .= "You received this email because you registered for an account.  Please use your email to sign in <br/>";
        $body .= "the website, you must change your password.  Please follow the link below.<br><br>";
        $body .= "<a href='http://$thisserver/passwordreset.php?vericode=".$realvericode."'>Click here to change your password.</a>";
        
        return($body);
    }
    public function get_passwordreset_body($thisserver)
    {
        global $db;
        $sql = "SELECT * FROM company_info";
        $result = $db->PDOMiniquery($sql);
        foreach($result as $rs)
        {
            $thiscompanyname = $rs['name'];
            $thiscompanyphoneno = $rs['phone_number'];
        }
        
        $body = "This is a system generated email sent to you by ".$thiscompanyname.".  Please do not respond.</br>";
        $body .= "This email is to let you know that your password has been successfully changed.<br/>";
        $body .= "If you didn't take this action, please contact your administrator right away.<br/><br/>";

        
        return($body);
    }
    public function get_password_reset($thisserver, $realvericode)
    {
        global $db;
        $sql = "SELECT * FROM company_info";
        $result = $db->PDOMiniquery($sql);
        foreach($result as $rs)
        {
            $thiscompanyname = $rs['name'];
            $thiscompanyphoneno = $rs['phone_number'];
        }
        
        $body = "Please do not reply to this email.<br />";
        $body .= "You receive this email from ".$thiscompanyname." because a password reset was requested.<br />";
        $body .= "If you have not requested for a password change, please ignore this email, otherwise, please follow <br />";
        $body .= "the link below to reset your password.<br><br>";
        $body .= "<a href='http://$thisserver/passwordreset.php?vericode=".$realvericode."'>Click here to verify your email and change your password.</a>";
        return($body);
    }
    public function confirm_cancellation($date, $appslot, $from)
    {
        global $db;
        $sql = "SELECT * FROM company_info";
        $result = $db->PDOMiniquery($sql);
        foreach($result as $rs)
        {
            $thiscompanyname = $rs['name'];
            $thiscompanyphoneno = $rs['phone_number'];
        }
        if($from != "Barber")
        {
            $body = "Your appointment with ".$thiscompanyname." on ".date('m/d/Y', strtotime($date))." @ $appslot has been cancelled.  You do not need to take any action.  Hope to see you soon.<br />";
            $body .= "If you didn't make this action, please contact ".$thiscompanyname." @ ".$thiscompanyphoneno." as soon as possible.  Thank you.";
        }
        else
        {
            $body = "Please do not reply to this email.<br />";
            $body .= "Your appointment with ".$_SESSION['companyname']." on ".date('m/d/Y', strtotime($date))."@ $appslot has been cancelled by your barber ".$_SESSION['user'].".<br />";
            $body .= "Please go to our website @ ".$_SESSION['thiswebsite']." and reschedule or call $thiscompanyphoneno for any question.<br />";
            $body .= "We apologize for this inconvenience.  Hope to see you soon.  Thank you.<br />";
        }
        return($body);
    }
    public function get_phonenumber_body($thisserver)
    {
        global $db;
        $sql = "SELECT * FROM company_info";
        $result = $db->PDOMiniquery($sql);
        foreach($result as $rs)
        {
            $thiscompanyname = $rs['name'];
            $thiscompanyphoneno = $rs['phone_number'];
        }
        $body = "This is a system generated email sent to you by ".$thiscompanyname.".  Please do not respond.</br>";
        $body .= "You received this email because you registered for an account.  Please use your email to sign in <br/>";
        $body .= "the website, you must change your password.  Please follow the link below.<br><br>";
        $body .= "<a href='http://$thisserver/passwordreset.php?vericode=".$realvericode."'>Click here to change your password.</a>";
        
        return($body);
    }
    public function get_paymentreceipt_subject()
    {
        $subject = "Payment Receipt";
        
        return($subject);
    }
    public function get_paymentreceipt_body($thisfirstname, $thislastname, $realamount, $square_receiptno,$thisservicetitle)
    {
        $body = "This is a system generated email sent to you by ".$_SESSION['companyname'].".  Please do not respond.</br></br>";

        $body .= "<div style='border: 1px solid black; height: 160px; width: 120px;'>";
        $body .= $_SESSION['companyname']."</br>";
        $body .= date('d M y').'<br/>';
        $body .= "Cost: $realamount <br/>";
        $body .= "Confirmation#: $square_receiptno <br/>";
        $body .= "$thisservicetitle <br/><br/>";
        
        $body .= "Thank you for your service and support.";
        $body .= "</div>";
        
        return($body);
    }
    public function get_refund_subject()
    {
        $subject = "Refund Receipt";
        
        return($subject);
    }
    public function get_refund_body($realamount, $refund_receiptno, $thistimestamp)
    {
        $body = "This is a system generated email sent to you by ".$_SESSION['companyname'].".  Please do not respond.</br></br>";

        $body .= "<div style='border: 1px solid black; height: 160px; width: 120px;'>";
        $body .= $_SESSION['companyname']."</br>";
        $body .= date('d M y', strtotime($thistimestamp)).'<br/>';
        $body .= "Refund: $realamount <br/>";
        $body .= "Confirmation#: $refund_receiptno <br/>";
        
        $body .= "Thank you for your service and support.";
        $body .= "</div>";
        
        return($body);
    }
    public function get_cancelpayment_subject()
    {
        $subject = "Payment Cancel Receipt";
        
        return($subject);
    }
    public function get_cancelpayment_body($cancel_receiptno, $thistimestamp)
    {
        $body = "This is a system generated email sent to you by ".$_SESSION['companyname'].".  Please do not respond.</br></br>";

        $body .= "<div style='border: 1px solid black; height: 160px; width: 120px;'>";
        $body .= $_SESSION['companyname']."</br>";
        $body .= date('d M y', strtotime($thistimestamp)).'<br/>';
        $body .= "Confirmation#: $cancel_receiptno <br/>";
        
        $body .= "Thank you for your service and support.";
        $body .= "</div>";
        
        return($body);
    }
}
