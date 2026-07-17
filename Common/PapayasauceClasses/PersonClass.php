<?php
namespace PapayasauceClasses;
/**
 * Description of newDateClass
 *
 * @author Yang Pao Thao
 */
class PersonClass {
    private $isactive;
    private $inisactive;
    private $isbarber;
    private $isterminated;
    private $thislist;
    private $realperson;
    private $personarray;
    private $searchtype;
    private $recno;
    private $from;
    private $db;   
    function SetUsers($tempperson)
    {
        $this->realperson = $tempperson;
    }
    public function GetUsers($db)
    {
        //file_put_contents("./dodebug/debug.txt", "In GetBarbers isactive: $this->isactive\n", FILE_APPEND);
        $sql = "SELECT recno, firstname, middlename, lastname FROM users WHERE ";
        $sql .= "(firstname LIKE '".$this->realperson."%' OR middlename LIKE '".$this->realperson."%' OR lastname LIKE '".$this->realperson."%') ";
        $sql .= "AND employeenumber IS NULL";
        //when employeenumber is NULL, that means this person is not admin, not barber, NOT an employee.
        $result = $db ->PDOMiniquery($sql);
        return($result);
    }
    function SetPersonno($tempperson, $tempactive, $tempbarber, $tempterminated)
    {
        $this->realperson = $tempperson;
        $this->isactive = $tempactive;
        $this->isbarber = $tempbarber;
        $this->isterminated = $tempterminated;
    }
    public function GetPerson($db)
    {
        $tempexplodeuser = explode(' ', $this->realperson);
        //We get here when user entered just the first name, middle or last name.

        if($this->isactive == true && $this->isterminated == true && $this->isbarber == true)
        {
            $sql = "SELECT recno, firstname, middlename, lastname FROM users WHERE ";
        }
        else
        {
            $sql = "SELECT recno, firstname, middlename, lastname FROM users WHERE ";

            if($this->isactive == true)
            {
                $sql .= "isActive = true ";
            }
            else
            {
                $sql .= "isActive = false ";
            }
            if($this->isbarber == true)
            {
                $sql .= "AND isBarber = true ";
            }
            else
            {
                $sql .= "AND isBarber = false ";
            }
            if($this->isterminated == true)
            {
                $sql .= "AND isTerminated = true ";
            }
            else
            {
                $sql .= "AND isTerminated = false ";
            }
            $sql .= "AND ";
        }
        $sql .= "firstname LIKE '".$this->realperson."%' ";
        $sql .= "OR middlename LIKE '".$this->realperson."%' ";
        $sql .= "OR lastname LIKE '".$this->realperson."%' ";
        $result = $db->PDOMiniquery($sql);
        if($db->PDOrowcount($result) > 0)
        {
            //file_put_contents("./dodebug/debug.txt", "at least 1? \n", FILE_APPEND);
            return($result);
        }
        else
        {
            //file_put_contents("./dodebug/debug.txt", "0? \n", FILE_APPEND);
            return("");
        }
    }
    function SetPersonselect($temparray, $templist)
    {
        $this->personarray = $temparray;
        $this->thislist = $templist;
    }
    function GetPersonselect()
    {
        $i=0;?>  
        <select class="dashboard-slt-mgm-person" name="sltsearchuser" id="sltsearchuser" size="5"><?php
            if(isset($this->personarray)) //Nott sure if isset() will check if some items is returned or at least something in asso array.
            {  
                if($this->thislist == '')
                {
                    foreach($this->personarray as $rs)
                    {
                        $i++;
                        $realname = $rs['firstname'];
                        if(!is_null($rs['middlename']) && $rs['middlename'] != "")
                        {
                            $realname .= " ". substr($rs['middlename'], 0, 1). ".";
                        }
                        $realname .= " ".$rs['lastname'];
                        $thisfunc = "getUserdata(this, ".$rs['recno'].");";
                        if($this->thisfrom == "Schedule")
                        {
                            $thisfunc = "getUserschedule(this, ".$rs['recno'].");";
                        }?>
                        <option class="dashboard-search-user" onclick="<?php echo $thisfunc ?>" value="<?php echo  $rs['recno']?>"><?php echo $i ?>.&nbsp;<?php echo  $realname ?></option><?php
                    }
                }
                else
                {
                    foreach($this->personarray as $key => $value)
                    {
                        $i++;
                        if(strpos(strtolower($value), strtolower($this->thislist)) !== false)
                        {
                            $thisfunc = "getUserdata(this, $key);";
                            if($this->thisfrom == "Schedule")
                            {
                                $thisfunc = "getUserschedule(this, $key);";
                            }
                            ?>
                             <option  class="dashboard-search-user" onclick="<?php echo $thisfunc ?>" value="<?php echo $key ?>"><?php echo $value ?></option><?php 
                        }
                    }
                }                 
            }
            else
            {?>
                <option  class="dashboard-search-user">No Data</option><?php 
            }?>
        </select><?php
    }
    function SetPersoninfo($db, $temprecno)
    {
        $this->db = $db;
        $this->recno = $temprecno;
    }
    function GetPersoninfo()
    {
        //QueryMe($thistype=null, $thistable=null, $thisfields=null, $thiswheres=null, $thisorderby=null, $thisgroupby=null, $ordering=null)
        $thistable = "users";
        $usethisonchange = "";
        $thisfields = array('All');
        $thiswhere = array("recno" => $this->recno);
        //$thiswhere = array("recno" => $_POST['recno']);
        $result = $this->db->PDOQuery($thistable, $thisfields, $thiswhere);
        if(isset($result)) //Nott sure if isset() will check if some items is returned or at least something in asso array.
        {?>
           <div class="div-dashboard-mgm-user" id="div_dashboard">
               <div class="float-left" id="div_dashboard_mgm_barbers_tbl_user">
                    <table id="dashboard_mgm_barbers_tbl_user" class="tbl-dashbard-mgm-user float-left" style="border: 1px solid black;">
                        <?php
                        if($_SESSION['isBarber'] == true)
                        {
                            $usethisonchange = 'onchange="updateUser(this,'.$this->recno.');"';
                        }
                        foreach($result as $rs)
                        {
                            $thismedia = $rs['media_dir'];?>
                            <tr><td class="tbl-dashboard-user-lbl align-right">First Name:</td><td><input class="user-dashboard-input float-left" type="text" id="txt_firstname" name="txtfirstname" onfocus="getVal(this);" onchange="updateUser(this, <?php echo $this->recno ?>);" value="<?= $rs['firstname'] ?>" /></td></tr>
                            <tr><td class="tbl-dashboard-user-lbl align-right">Middle Name:</td><td><input class="user-dashboard-input float-left" type="text" id="txt_middlename" name="txtmiddlename" onfocus="getVal(this);" onchange="updateUser(this, <?php echo $this->recno ?>);" value="<?= $rs['middlename'] ?>" /></td></tr>
                            <tr><td class="tbl-dashboard-user-lbl align-right">Last Name:</td><td><input class="user-dashboard-input float-left" type="text" id="txt_lastname" name="txtlastname" onfocus="getVal(this);" onchange="updateUser(this, <?php echo $this->recno ?>);" value="<?= $rs['lastname'] ?>" /></td></tr>
                            <tr><td class="tbl-dashboard-user-lbl align-right">Login:</td><td><input class="user-dashboard-input float-left" type="text" id="txt_login" name="txtlogin" onfocus="getVal(this);" onchange="updateUser(this, <?php echo $this->recno ?>);" value="<?= $rs['login'] ?>"/></td></tr>
                            <tr><td class="tbl-dashboard-user-lbl align-right">Address:</td><td><input class="user-dashboard-input float-left" type="text" id="txt_address" name="txtaddress" onfocus="getVal(this);" onchange="updateUser(this, <?php echo $this->recno ?>);" value="<?= $rs['address'] ?>" /></td></tr>
                            <tr><td class="tbl-dashboard-user-lbl align-right">Address 2:</td><td><input class="user-dashboard-input float-left" type="text" id="txt_address2" name="txtaddress2" onfocus="getVal(this);" onchange="updateUser(this, <?php echo $this->recno ?>);" value="<?= $rs['address2'] ?>" /></td></tr>
                            <tr><td class="tbl-dashboard-user-lbl align-right">City:</td><td><input class="user-dashboard-input float-left" type="text" id="txt_city" name="txtcity" onfocus="getVal(this);" onchange="updateUser(this, <?php echo $this->recno ?>);" value="<?= $rs['city'] ?>" /></td></tr>
                            <tr><td class="tbl-dashboard-user-lbl align-right">State:</td><td><input class="user-dashboard-input float-left" type="text" id="txt_state" name="txtstate" onfocus="getVal(this);" onchange="updateUser(this, <?php echo $this->recno ?>);" value="<?= $rs['state'] ?>" /></td></tr>
                            <tr><td class="tbl-dashboard-user-lbl align-right">Zip-Code:</td><td><input class="user-dashboard-input float-left" type="text" id="txt_zipcode" name="txtzipcode" onfocus="getVal(this);" onchange="updateUser(this, <?php echo $this->recno ?>);" value="<?= $rs['zipcode'] ?>" /></td></tr>
                            <tr><td class="tbl-dashboard-user-lbl align-right">Email:</td><td><input class="user-dashboard-input float-left" type="text" id="txt_email" name="txtemail" onfocus="getVal(this);" onchange="updateUser(this, <?php echo $this->recno ?>);" value="<?= $rs['email'] ?>" /></td></tr><?php
                    }?>
                    </table>
                </div>
            </div><?php
        }
    }
    function SetUsertabs($db, $temprecno, $tempfrom)
    {
        $this->db = $db;
        $this->recno = $temprecno;
        $this->from = $tempfrom;
    }
    function GetUsertabs()
    {
        //API Pro - div_barber_apipro
        //API Sand - div_barber_apisand
        $temptablename = "sandbox";
        $temp_square_app_id = "";
        $temp_square_client_secret = "";
        $temp_square_api_access_token = "";
        $temp_twillio_sms_phone = "";
        $temp_twillio_api_token = "";
        $temp_twillio_api_id = "";
        $temp_disc_limi = "";
        $thisprosand = "";
        
        $sql = "SELECT ";
        if($this->from == "div_barber_apipro")
        {
            $temptablename = "pro";
            $sql .= "recno, square_application_id_pro, square_client_secret_pro, square_api_access_token_pro, disc_limi_pro";
            $sql .= ", twillio_sms_number_pro, twillio_api_token_pro, twillio_api_id_pro ";
        }
        if($this->from == "div_barber_apisand")
        {
            //Sandbox will be without the pro
            $sql .= "recno, square_application_id, square_client_secret, square_api_access_token, disc_limit";
            $sql .= ", twillio_sms_number, twillio_api_token, twillio_api_id ";
        }
        
        $sql .= "FROM users WHERE recno = ".$this->recno;
        $result = $this->db->PDOminiquery($sql);
        foreach($result as $rs)
        {
            if($this->from == "div_barber_apipro")
            {
                $temp_square_app_id = $rs['square_application_id_pro'];
                $temp_square_client_secret = "square_client_secret_pro";
                $temp_square_api_access_token = "square_api_access_token_pro";
                $thisprosand = "_pro";
            }
            if($this->from == "div_barber_apisand")
            {
                $temp_square_app_id = $rs['square_application_id'];
                $temp_square_client_secret = "square_client_secret";
                $temp_square_api_access_token = "square_api_access_token";
                
            }
            $temp_twillio_sms_phone = "twillio_sms_number";
            $temp_twillio_api_token = "twillio_api_token";
            $temp_twillio_api_id = "twillio_api_id";
            $temp_disc_limi = "disc_limi";
        }
        if($_SESSION['isBarber'] == true || $_SESSION['isAdmin'] || $_SESSION['isDeveloper'])
        {
            $usethisonchange = 'onchange="updateUser(this,'.$this->recno.');"';
        }?>
        <table id="dashboard_mgm_barbers_tbl_<?php echo $temptablename ?>" class="tbl-dashbard-mgm-user float-left">    
            <tr>
                <td class="tbl-dashboard-user-lbl align-right"></td>
                <td>
                    <button class="cursor-pointer dashboard-mgmcompany-renew-api" name="btn_renew_api" id="btn_renew_api" onclick="renewSquareapi();">Click to renew Square API Token</button>
                </td>
            </tr>
            <tr>
                <td class="tbl-dashboard-user-lbl align-right">Square App I.D <img title="Must manually update or enter this value." style="height: 15px; width: 15px;" src="./images/others/question.png"/>: </td>
                <td><input type="text" class="user-dashboard-input" style="width: 98%;" id="txtsquare_application_id<?php echo $thisprosand ?>" name="txtsquare_application_id<?php echo $thisprosand ?>" <?php echo $usethisonchange ?> value="<?php echo $temp_square_app_id ?>" /></td>
            </tr>
            <tr>
                <td class="tbl-dashboard-user-lbl align-right">Square Client Secret:</td>
                <td><input type="text" class="user-dashboard-input" style="width: 98%;" id="txtsquare_client_secret<?php echo $thisprosand ?>" name="txtsquare_client_secret<?php echo $thisprosand ?>" <?php echo $usethisonchange ?> value="<?php echo $temp_square_client_secret ?>" /></td>
            </tr>
            <tr>
                <td class="tbl-dashboard-user-lbl align-right">Square API Token: </td>
                <td><input type="text" class="user-dashboard-input" style="width: 98%;" id="txtsquare_api_access_token<?php echo $thisprosand ?>" name="txtsquare_api_access_token<?php echo $thisprosand ?>" <?php echo $usethisonchange ?> value="<?php echo $temp_square_api_access_token ?>" /></td>
            </tr>
            <tr>
                <td class="tbl-dashboard-user-lbl align-right">Twillio SMS Phone No.: </td>
                <td><input type="text" class="user-dashboard-input" style="width: 98%;" id="txttwillio_sms_number<?php echo $thisprosand ?>" name="txttwillio_sms_number<?php echo $thisprosand ?>" <?php echo $usethisonchange ?> value="<?php echo $temp_twillio_sms_phone ?>" /></td>
            </tr>
            <tr>
                <td class="tbl-dashboard-user-lbl align-right">Twillio API Token: </td>
                <td><input type="text" class="user-dashboard-input" style="width: 98%;" id="txttwillio_api_token<?php echo $thisprosand ?>" name="txttwillio_api_token<?php echo $thisprosand ?>" <?php echo $usethisonchange ?> value="<?php echo $temp_twillio_api_token ?>" /></td>
            </tr>
            <tr>
                <td class="tbl-dashboard-user-lbl align-right">Twillio API I.D: </td>
                <td><input type="text" class="user-dashboard-input" style="width: 98%;" id="txttwillio_api_id<?php echo $thisprosand ?>" name="txttwillio_api_id<?php echo $thisprosand ?>" <?php echo $usethisonchange ?> value="<?php echo $temp_twillio_api_id ?>" /></td>
            </tr>
            <tr>
                <td class="tbl-dashboard-user-lbl align-right">Discount Limit: </td>
                <td><input type="text" class="user-dashboard-input" style="width: 98%;" id="txtdisc_limit<?php echo $thisprosand ?>" name="txtdisc_limit<?php echo $thisprosand ?>" <?php echo $usethisonchange ?> value="<?php echo $temp_disc_limi ?>" /></td>
            </tr>
        </table><?php
    }
}?>
