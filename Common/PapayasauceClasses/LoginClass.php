<?php
/**
 * Description of newDateClass
 *
 * @author Yang Pao Thao
 */
class Login_Class {
    public $clogin;
    public $islogin = false;
    function set_login($login)
    {
        $this->clogin = $login;
    }
    function get_login()
    {
        return($this->clogin);
    }
    public function check_login()
    {
        //Now we want to check if this email already exist. We will get a record back if it exists.
        return("SELECT recno FROM users WHERE login = '".$this->clogin."'");
    }
}
