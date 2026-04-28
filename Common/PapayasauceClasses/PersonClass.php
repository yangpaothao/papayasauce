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
    function SetBarbers($tempperson, $tempactive, $tempinactive, $tempterminated)
    {
        $this->realperson = $tempperson;
        $this->isactive = $tempactive;
        $this->inactive = $tempinactive;
        $this->isterminated = $tempterminated;
    }
    public function GetBarbers($db)
    {
        //file_put_contents("./dodebug/debug.txt", "In GetBarbers isactive: $this->isactive\n", FILE_APPEND);
        $sql = "SELECT recno, firstname, middlename, lastname FROM users WHERE ";
        $sql .= "(firstname LIKE '".$this->realperson."%' OR middlename LIKE '".$this->realperson."%' OR lastname LIKE '".$this->realperson."%') ";
        //If all the checkboxes are checked, we don't go inside this if, we just get everything that matches this search.
        if($this->isactive != false || $this->inactive != false || $this->isterminated != false)
        {    
            //If we are here, that means 
            if($this->isactive == true && $this->inactive ==  true)
            {
                $sql .= "AND (isActive = true OR isActive = false)";
            }
            else if($this->isactive == true && $this->inactive ==  false)
            {
                $sql .= "AND isActive = true ";
            }
            else if($this->isactive == false && $this->inactive == true)
            {
                $sql .= "AND isActive = false ";
            }
            if($this->isterminated == true)
            {
                $sql .= "AND isTerminated = true ";
            }
        }
        //file_put_contents("./dodebug/debug.txt", "In GetBarbers: $sql\n", FILE_APPEND);
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
    function SetPersonselect($tempfrom, $temparray, $templist)
    {
        $this->personarray = $temparray;
        $this->thislist = $templist;
        $this->thisfrom = $tempfrom;
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
        {
            if($_SESSION['isBarber'] == true)
            {?>
                <div>
                    <div class="cursor-pointer float-left dashboard-mgm-barbers-tabs dashboard-mgm-barbers-tabs-selected dashboard-mgm-barbers-tabs-border-left dashboard-mgm-barbers-tabs-border-top align-center" id="div_barber_user" onclick="showUsertbs(this, <?php echo $this->recno ?>, 'Menu');">User</div>
                    <div class="cursor-pointer float-left dashboard-mgm-barbers-tabs dashboard-mgm-barbers-tabs-noselect dashboard-mgm-barbers-tabs-border-left dashboard-mgm-barbers-tabs-border-top align-center" id="div_barber_apipro" onclick="showUsertbs(this, <?php echo $this->recno ?>, 'Menu');">Square API Production</div><?php
                    if($_SESSION['isLive'] == false && $_SESSION['isDeveloper'] == true)
                    {?>
                        <div class="cursor-pointer float-left dashboard-mgm-barbers-tabs dashboard-mgm-barbers-tabs-noselect dashboard-mgm-barbers-tabs-border-left dashboard-mgm-barbers-tabs-border-top dashboard-mgm-barbers-tabs-border-right align-center" id="div_barber_apisand" onclick="showUsertbs(this, <?php echo $this->recno ?>, 'Menu');">Square API Sandbox</div><?php
                    }
                    if($_SESSION['isDeveloper'] == true)
                    {?>
                        <div class="cursor-pointer float-left dashboard-mgm-barbers-tabs dashboard-mgm-barbers-tabs-noselect dashboard-mgm-barbers-tabs-border-left dashboard-mgm-barbers-tabs-border-top dashboard-mgm-barbers-tabs-border-right align-center" id="div_barber_permissionpro" name="div_barber_permissionpro" onclick="showUsertbs(this, <?php echo $this->recno ?>, 'Menu');">Square Production Permissions</div><?php
                    }
                    if($_SESSION['isLive'] == false && $_SESSION['isDeveloper'] == true)
                    {?>
                        <div class="cursor-pointer float-left dashboard-mgm-barbers-tabs dashboard-mgm-barbers-tabs-noselect dashboard-mgm-barbers-tabs-border-left dashboard-mgm-barbers-tabs-border-top dashboard-mgm-barbers-tabs-border-right align-center" id="div_barber_permission" name="div_barber_permission" onclick="showUsertbs(this, <?php echo $this->recno ?>, 'Menu');">Square Sandbox Permissions</div><?php
                    }?>
                    <div class="cursor-pointer float-left dashboard-mgm-barbers-tabs dashboard-mgm-barbers-tabs-noselect dashboard-mgm-barbers-tabs-border-left dashboard-mgm-barbers-tabs-border-top dashboard-mgm-barbers-tabs-border-right align-center" id="div_barber_apitwillio" onclick="showUsertbs(this, <?php echo $this->recno ?>, 'Menu');">Twillio API</div>
                </div><?php
            }?>
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
                            <tr><td class="tbl-dashboard-user-lbl align-right">First Name:</td><td><input class="user-dashboard-input float-left" type="text" id="txtfirstname" name="txtfirstname" onfocus="getVal(this);" onchange="updateUser(this, <?php echo $this->recno ?>);" value="<?= $rs['firstname'] ?>" /></td></tr>
                            <tr><td class="tbl-dashboard-user-lbl align-right">Middle Name:</td><td><input class="user-dashboard-input float-left" type="text" id="txtmiddlename" name="txtmiddlename" onfocus="getVal(this);" onchange="updateUser(this, <?php echo $this->recno ?>);" value="<?= $rs['middlename'] ?>" /></td></tr>
                            <tr><td class="tbl-dashboard-user-lbl align-right">Last Name:</td><td><input class="user-dashboard-input float-left" type="text" id="txtlastname" name="txtlastname" onfocus="getVal(this);" onchange="updateUser(this, <?php echo $this->recno ?>);" value="<?= $rs['lastname'] ?>" /></td></tr>
                            <tr><td class="tbl-dashboard-user-lbl align-right">Birthday:</td><td class="datepicker-mgmbarbers-tabs"><input class="user-dashboard-input float-left" type="text" id="txtbirthday" name="txtbirthday" onchange="updateUser(this, <?php echo $this->recno ?>);" value="<?= empty($rs['birthday']) ? '' : date('m/d/Y', strtotime($rs['birthday'])) ?>" style="margin-top: -10px;" onfocus="getVal(this);getJDate(this);" onchange="updateUser(this, <?php echo $this->recno ?>);" placeholder="dd/mm/yyy ex: 01/22/2022" /></td></tr>
                            <tr><td class="tbl-dashboard-user-lbl align-right">Hire Date:</td><td class="datepicker-mgmbarbers-tabs"><input class="user-dashboard-input float-left" type="text" id="txthiredate" name="txthiredate" onchange="updateUser(this, <?php echo $this->recno ?>);" value="<?= empty($rs['hiredate']) ? '' : date('m/d/Y', strtotime($rs['hiredate'])) ?>" style="margin-top: -10px;" onfocus="getVal(this);getJDate(this);" onchange="updateUser(this, <?php echo $this->recno ?>);" placeholder="dd/mm/yyy ex: 01/22/2022" /></td></tr>
                            <tr><td class="tbl-dashboard-user-lbl align-right">Login:</td><td><input class="user-dashboard-input float-left" type="text" id="txtlogin" name="txtlogin" onfocus="getVal(this);" onchange="updateUser(this, <?php echo $this->recno ?>);" value="<?= $rs['login'] ?>"/></td></tr>
                            <tr><td class="tbl-dashboard-user-lbl align-right">Address:</td><td><input class="user-dashboard-input float-left" type="text" id="txtaddress" name="txtaddress" onfocus="getVal(this);" onchange="updateUser(this, <?php echo $this->recno ?>);" value="<?= $rs['address'] ?>" /></td></tr>
                            <tr><td class="tbl-dashboard-user-lbl align-right">City:</td><td><input class="user-dashboard-input float-left" type="text" id="txtcity" name="txtcity" onfocus="getVal(this);" onchange="updateUser(this, <?php echo $this->recno ?>);" value="<?= $rs['city'] ?>" /></td></tr>
                            <tr><td class="tbl-dashboard-user-lbl align-right">State:</td><td><input class="user-dashboard-input float-left" type="text" id="txtstate" name="txtstate" onfocus="getVal(this);" onchange="updateUser(this, <?php echo $this->recno ?>);" value="<?= $rs['state'] ?>" /></td></tr>
                            <tr><td class="tbl-dashboard-user-lbl align-right">Zip-Code:</td><td><input class="user-dashboard-input float-left" type="text" id="txtzipcode" name="txtzipcode" onfocus="getVal(this);" onchange="updateUser(this, <?php echo $this->recno ?>);" value="<?= $rs['zipcode'] ?>" /></td></tr>
                            <tr><td class="tbl-dashboard-user-lbl align-right">Email:</td><td><input class="user-dashboard-input float-left" type="text" id="txtemail" name="txtemail" onfocus="getVal(this);" onchange="updateUser(this, <?php echo $this->recno ?>);" value="<?= $rs['email'] ?>" /></td></tr>
                            <tr><td class="tbl-dashboard-user-lbl align-right">Show FB:</td><td><input class="dashboard-tbl-chkbox float-left" type="checkbox" id="chkisShowfb" name="chkisShowfb" onfocus="getVal(this);" <?php echo $usethisonchange ?> <?php echo ($rs['isShowfb'] == true ? 'checked' : '') ?> /></td></tr>
                            <tr><td class="tbl-dashboard-user-lbl align-right nowrap">Show Cancel:</td><td><input class="dashboard-tbl-chkbox float-left" type="checkbox" id="chkisShowcancel" name="chkisShowcancel" onfocus="getVal(this);" <?php echo $usethisonchange ?> <?php echo ($rs['isShowcancel'] == true ? 'checked' : '') ?> /></td></tr>
                            <tr><td class="tbl-dashboard-user-lbl align-right nowrap">Show Refund:</td><td><input class="dashboard-tbl-chkbox float-left" type="checkbox" id="chkisShowrefund" name="chkisShowrefund" onfocus="getVal(this);" <?php echo $usethisonchange ?> <?php echo ($rs['isShowrefund'] == true ? 'checked' : '') ?> /></td></tr>
                            <tr><td class="tbl-dashboard-user-lbl align-right">Active:</td><td><input class="dashboard-tbl-chkbox float-left" type="checkbox" id="chkisActive" name="chkisActive" onfocus="getVal(this);" <?php echo $usethisonchange ?> <?php echo ($rs['isActive'] == true ? 'checked' : '') ?> /></td></tr>
                            <tr><td class="tbl-dashboard-user-lbl align-right">Admin:</td><td><input class="dashboard-tbl-chkbox float-left" type="checkbox" id="chkisAdmin" name="chkisAdmin" onfocus="getVal(this);" <?php echo $usethisonchange ?> <?php echo ($rs['isAdmin'] == true ? 'checked' : '') ?> <?php echo ($rs['isAdmin'] == true ? '' : 'disabled') ?> /></td></tr>
                            <tr><td class="tbl-dashboard-user-lbl align-right">Barber:</td><td><input class="dashboard-tbl-chkbox float-left" type="checkbox" id="chkisBarber" name="chkisBarber" <?php echo $usethisonchange ?> <?php echo ($rs['isBarber'] == true ? 'checked' : '') ?> /></td></tr><?php
                            if($rs['isDeveloper'] == true)
                            {?>
                                <tr><td name="td_live" id="td_live" class="tbl-dashboard-user-lbl align-right <?php echo ($rs['isLive'] == true ? 'flashing-background' : '') ?>">LIVE:</td><td><input class="dashboard-tbl-chkbox float-left" type="checkbox" id="chkisLive" name="chkisLive" <?php echo $usethisonchange ?> <?php echo ($rs['isLive'] == true ? 'checked' : '') ?> /></td></tr><?php 
                            }
                        }?>
                    </table>
                </div><?php
                if($_SESSION['isAdmin'] == true || $_SESSION['isBarber'] == true)
                {
                        $sql = "SELECT * FROM attachments WHERE foreign_ur = ".$_SESSION['user_recno']." AND name='profile_image' OR name='thumb_nail'";
                        $result = $this->db->PDOMiniquery($sql);
                        if($this->db->PDORowcount($result) > 0)
                        {
                            foreach($result as $rs)
                            {
                                if($rs['name'] == "profile_image")
                                {
                                    $profileimage = $rs['file'];
                                }
                                if($rs['name'] == "thumb_nail")
                                {
                                    $thumbnail = $rs['file'];
                                }
                            }
                        }?>
                    <div class="float-left" id="div_barbers_img_container">
                        <div class="float-left">
                            <img class="cursor-pointer profile-img-size" id="img_profile" src="./images/others/<?php echo $thismedia ?>/avatar/<?php echo $profileimage ?>" onerror="this.onerror=null; this.src='./images/others/<?php echo $thismedia ?>/avatar/defaultimage.png'"><br />
                            <span style="font-size: .7em;">Profile Image:<br /> <span id="span_profile_image"><?php echo $profileimage ?></span></span>
                        </div>
                        <div class="float-left">
                            <img class="cursor-pointer profile-img-size" id="img_thumbnail" src="./images/others/<?php echo $thismedia ?>/avatar/<?php echo $thumbnail ?>" onerror="this.onerror=null; this.src='./images/others/<?php echo $thismedia ?>/avatar/defaultimage.png'"><br />
                            <span style="font-size: .7em;">Thumbnail Image::<br /> <span id="span_profile_thumbnail"><?php echo $thumbnail ?></span></span>
                        </div>
                    </div><?php
                }?>
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
