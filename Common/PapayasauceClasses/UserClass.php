<?php
namespace PapayasauceClasses;


class UserClass
{
    public $employeenumber;
    public $firstname;
    public $middlename;
    public $lastname;
    public $login;
    public $email;
    public $address;
    public $address2;
    public $city;
    public $state;
    public $zipcode;
    
    function set_users($unique_id)
    {
        //We must pass in a unique identifier
        
        global $db, $load_headers;
        
        $thisfields = Array("password");
        $thistable = "users";
        $getpasssword = $load_headers -> Hash_Me_Password($_POST['txtpassword']); //we hash user's entered pw.     
        $thiswhere = array("recno" => $_SESSION['user_recno'], 'password' => $getpasssword);
        $result = $db->PDOQuery($thistable, $thisfields, $thiswhere);
        if(!isset($result))
        {
            echo "Failed";
        }
        else
        {
            echo "Success";
        }
    }
    function GetEmployeenumber()
    {
        return($this->employeenumber);
    }
    function GetFirstname()
    {
        return($this->firstname);
    }
    function GetMiddlename()
    {
        return($this->middlename);
    }
    function GetLastname()
    {
        return($this->lastname);
    }
    function GetLogin()
    {
        return($this->login);
    }
    function GetEmail()
    {
        return($this->email);
    }
    function GetAddress()
    {
        return($this->address);
    }
    function GetCity()
    {
        return($this->city);
    }
    function GetState()
    {
        return($this->state);
    }
    function GetZipcode()
    {
        return($this->zipcode);
    }
    
}

