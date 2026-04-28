<?php
/**
 * Description of newDateClass
 *
 * @author Yang Pao Thao
 */
class Employeeno_Class {
    public $cempno;
    function set_employeeno($empno)
    {
        $this->cempno = $empno;
    }
    function get_date()
    {
        return($this->cempno);
    }
    public function check_login()
    {
        //Now we want to check if this email already exist. We will get a record back if it exists.
        return("SELECT recno FROM users WHERE employeenumber = '".$this->cempno."'");
    }
}
