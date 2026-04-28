<?php
require_once("./common/classes/pageloaderclass.php");
require_once("common/pdocon.php");

$load_headers = new PageloaderClass();
$db = new PDOCON();

class UserClass
{
    public $employeenumber;
    public $firstname;
    public $middlename;
    public $lastname;
    public $login;
    public $email;
    public $address;
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
    function set_employeenumber($employeenumber)
    {
        $this->name = $employeenumber;
    }
    function set_firstname($firstname)
    {
        $this->firstname = $firstname;
    }
    function set_middlename($middlename)
    {
        $this->middlename = $middlename;
    }
    function set_lastname($lastname)
    {
        $this->lastname = $lastname;
    }
    function set_login($login)
    {
        $this->login = $login;
    }
    function set_email($email)
    {
        $this->email = $email;
    }
    function set_address($address)
    {
        $this->address = $address;
    }
    function set_city($city)
    {
        $this->city = $city;
    }
    function set_state($state)
    {
        $this->state = $state;
    }
    function set_zipcode($zipcode)
    {
        $this->zipcode = $zipcode;
    }
    
    function get_employeenumber()
    {
        return($this->name);
    }
    function get_firstname()
    {
        return($this->firstname);
    }
    function get_middlename()
    {
        return($this->middlename);
    }
    function get_lastname()
    {
        return($this->lastname);
    }
    function get_login()
    {
        return($this->login);
    }
    function get_email()
    {
        return($this->email);
    }
    function get_address()
    {
        return($this->address);
    }
    function get_city()
    {
        return($this->city);
    }
    function get_state()
    {
        return($this->state);
    }
    function get_zipcode()
    {
        return($this->zipcode);
    }
    
}

