<?php
/**
 * Description of newDateClass
 *
 * @author Yang Pao Thao
 */
class Password_Class {
    public $cpassword;
    function set_password($password)
    {
        $this->cpassword = $password;
    }
    function get_password()
    {
        return($this->cpassword);
    }
    public function validate_password()
    {
        $regex = "/^(?=.*[0-9])(?=.*[!@#$%^&*])[a-zA-Z0-9!@#$%^&*]{6,16}$/";
        if(strlen($this->cpassword) < 8 && !preg_match_all($regex, $this->cpassword))
        {
            return(false);
        }      
        else
        {
            return(true);
        }

    }
}
