<?php
namespace PapayasauceClasses;
/**
 * Description of newDateClass
 *
 * @author Yang Pao Thao
 */
class OrderClass {
    public $db = "";
    public $orderrecno = 0;
    public $recnostr = "";
    public $square_receiptno = "";
    public $square_order_id = "";
    
    public $firstname = "";
    public $lastname = "";
    public $address = "";
    public $address2= "";
    public $email = "";
    public $phonenumber = "";
    public $city = "";
    public $state = "";
    public $zipcode = "";
    public $square_receipturl = ""; //link to download the receipt
    public $confirmation = ""; //Confirmation number of this order
    public $total = ""; //Total cost of order
    public $name = []; //Name of the product
    
    function SetReceipt($db, $recnostr, $temp_receiptno, $temp_orderno, $temp_receipturl)
    {
        $this->recnostr = $recnostr;
        $this->square_receiptno = $temp_receiptno;
        $this->square_order_id = $temp_orderno;
        $this->square_receipturl = $temp_receipturl;
        $this->db = $db;
    }
    function SetCustomer($db, $orderrecno)
    {
        $this->db = $db;
        $this->orderrecno = $orderrecno;     
        
        $sqlp = "SELECT products FROM orders WHERE recno = ".$this->orderrecno;
        $resultp = $db->PDOMiniquery($sqlp);
        foreach($resultp as $rsp)
        {
            $thispro = $rsp["products"];
        }
        //$thispro will now be {"31":"1"}
        $explode_thispro = explode(",", $thispro);
        foreach($explode_thispro as $proarray)
        {
            //$proarray = ["pro_recno" : "# of item"] where pro_recno is the recno of table products and # of item is the number of items ordered 
            file_put_contents("./dodebug/debug.txt", "OrderClass proarray = $proarray \n", FILE_APPEND); //proarray = {"31":"1"} 
            $temparray = json_decode($proarray, true); //$temparray = ["31" => "1"]
            $arraykey = array_keys($temparray); //$arraykey = (31)
            //Now we need to get this 31 from this array into another array
            $realkeys[] = $arraykey[0];
        }
        file_put_contents("./dodebug/debug.txt", "arraykeys = ".json_encode($realkeys)." \n", FILE_APPEND); 
        //$temparray = will not be just a single array holding just the recno,ex: $temparray = [1,2,3,...,n]
        $arraystr = implode(',', $realkeys);
        //$arraystr =  should be just a string of 1,2,3,...,n
        
        $sql = "SELECT o.payment_confirmation, o.receipt_url, o.total, o.date, u.firstname, u.lastname, u.address, u.address2, u.city, u.state, u.zipcode, u.email, u.phone_number, p.name FROM users u INNER JOIN orders o ";
        $sql .= "ON u.recno = o.foreign_user_recno ";
        $sql .= "INNER JOIN products p WHERE o.recno = ".$this->orderrecno." AND p.recno IN ($arraystr) ";
        file_put_contents("./dodebug/debug.txt", "OrderClass sql = $sql \n", FILE_APPEND);
        $result = $this->db->PDOminiquery($sql);
        foreach($result as $rs)
        {
            $this->firstname = $rs['firstname'];
            $this->lastname = $rs['lastname'];
            $this->address = $rs['address'];
            $this->address2 = $rs['address2'];
            $this->email = $rs['email'];
            $this->phonenumber = $rs['phone_number'];
            $this->city = $rs['city'];
            $this->state = $rs['state'];
            $this->zipcode = $rs['zipcode'];
            $this->square_receipturl = $rs['receipt_url'];
            $this->confirmation = $rs['payment_confirmation'];
            $this->total = $rs['total'];
            $this->name[] = $rs['name'];
        }
    }
    function GetPaymentordersubject()
    {
        $subject = "Order Report";
        
        return($subject);
    }
    function GetCustomerbody()
    {
        $body = '<div class="align-right cart-div-content-holder-flex-data-container display-inline-block">';
            $body .= '<div class="float-left cart-img-data-container">';
            $body .= '<div class="float-left display-block" ><a href="'.$this->square_receipturl.'">Click to download the receipt (Confirmation#'.$this->confirmation.')</a></div>';
            $body .= '<div>&nbsp;<div>';
            $body .= '<div>';
                $body .= '<div>';
                    $body .= '<div style="text-align: right; float: left; width: 100%; display: inline-block; ">Name:&nbsp;&nbsp;  '.$this->firstname." ".$this->lastname.'<div>';
                $body .= "</div>";
                $body .= '<div>';
                    $body .= '<div style="text-align: right; float: left; display: inline-block; ">Address:&nbsp;&nbsp; </div>';
                $body .= '</div>'; 
                $body .= '<div>';
                    $body .= '<div style="text-align: left;  float: left;">'.$this->address.' &nbsp;&nbsp;</div>';
                    if(!is_null($this->address2))
                    {
                        $body .= '<div style="float: left;">'.$this->address2.'</div>';
                    }
                    $body .= '<div style="float: left;">'.$this->city.', '.$this->state.' '.$this->zipcode.'</div>';
                $body .= '</div>';                            
                $body .= '<div>&nbsp;<div>';
                $body .= '<div>';
                    $body .= '<div style="text-align: right; float: left;">Email: &nbsp;&nbsp;'.$this->email.'<div>';
                $body .= '</div>';
                $body .= '<div>';
                    $body .= '<div style="text-align: right; float: left;">Phone Number: &nbsp;&nbsp;'.$this->phonenumber.'<div>';
                $body .= '</div>';
                $body .= '<div>&nbsp;<div>';

                $i=0;
                foreach($this->name as $proname)
                {
                    $i++;
                    $body .= '<div>';   
                        $body .= '<div style="padding-right: 2px; text-align: right; float: left;">'.$i.'.&nbsp;&nbsp;  '.$proname.'<div>';
                    $body .= '</div>';
                }
                $body .= '<div>';
                    $body .= '<div style="text-align: right; float: left;">Total Cost:&nbsp;&nbsp;  $'.number_format($this->total, 2).'<div>';
                $body .= '</div>';
            $body .= '</div>';
            $body .= '</div>';
        $body .= '</div>';
        return($body);
    }
    function ShowReceipt()
    {
        $thistotal = 0;
        $body = "";
        $lineno = 1;
        $body = "Orders: ".$this->square_order_id."</br>";
        $body .= "Confirmation: ".$this->square_receiptno."<br/><br/>";
        $sql = "SELECT p.*, c.name as cname FROM products p INNER JOIN category c ON p.foreign_cat_recno = c.recno WHERE p.recno IN ($this->recnostr) ORDER BY p.name";
        //file_put_contents("./dodebug/debug.txt", 'Front sql event? '.$sql, FILE_APPEND);
        $result = $this->db -> PDOMiniquery($sql);
        if($this->db ->PDORowcount($result) > 0)
        {
            $i = 1;
            foreach($result as $rs)
            {
                $numberofitems = $_SESSION['CARTRECNOTRACKER'][$rs['recno']];
                //$thisurl = "https://www.papayasauce.com/website_ad583fcd";
                //https://www.papayasauce.com/website_ad583fcd/images/others/products/Sauce/24/mini/s_Two Sister 9 oz.png
                //$thisdir = "$thisurl/images/others/products/".$rs['cname']."/".$rs['recno'];
                $body .= '<div class="align-right cart-div-content-holder-flex-data-container display-inline-block">';
                    $body .= '<div class="float-left cart-img-data-container">';
                        //$body .= '<div class="float-left" ><img id="large_img_container" class="img-cart-review" src="'.$thisdir.'/mini/s_'.substr($rs['attachment'],2).'"/></div>';
                    $body .= '<div class="float-left display-block" >Your statement will show "SQ *KA\'S PAPA SAUCE"</div>';
                    $body .= '<div class="float-left display-block" ><a href="'.$this->square_receipturl.'">Click to download the receipt</a></div>';
                        $body .= '<div class="align-left float-left">';
                            $body .= '<table>';
                                $body .= '<tr>';
                                    $body .= '<td class="font-color-white" colspan="2">'.$rs['name'].'</div><td>';
                                $body .= '</tr>';
                                $body .= '<tr>';
                                    $body .= '<td class="font-color-white align-right">Each: </td>';
                                    $body .= '<td class="font-color-white align-left" >$'.number_format($rs['price'], 2).'</td>';
                                $body .= '</tr>';
                                $body .= '<tr>';
                                    $body .= '<td class="font-color-white align-right">Items: </td>';
                                    $body .= '<td class="font-color-white align-left">'.$numberofitems.'</td>';
                                $body .= '</tr>';
                                $body .= '<tr>';
                                    $body .= '<td class="font-color-white align-right color-darkred"><b>Total:</b>  </td>';
                                    $body .= '<td class="font-color-white align-left color-darkred"><b>$'.number_format($numberofitems*$rs['price'], 2).'</b></td>';
                                $body .= '</tr>';
                            $body .= '</table>';

                        $body .= '</div>';
                    $body .= '</div>';
                $body .= '</div>';
                $thistotal += number_format($numberofitems*$rs['price'], 2);
                $i++;
            }
        }
        $body .= "<div><b>Total Cost: $".number_format($thistotal,2)."</b></div>";       
        return($body);
    }
    function ShowOrderbody()
    {
        $thistotal = 0;
        $body = "";
        $lineno = 1;
        $body = "Orders: ".$this->square_order_id."</br>";
        $body .= "Confirmation: ".$this->square_receiptno."<br/><br/>";
        $sql = "SELECT p.*, c.name as cname FROM products p INNER JOIN category c ON p.foreign_cat_recno = c.recno WHERE p.recno IN ($this->recnostr) ORDER BY p.name";
        //file_put_contents("./dodebug/debug.txt", 'Front sql event? '.$sql, FILE_APPEND);
        $result = $this->db -> PDOMiniquery($sql);
        if($this->db ->PDORowcount($result) > 0)
        {
            $i = 1;
            foreach($result as $rs)
            {
                $numberofitems = $_SESSION['CARTRECNOTRACKER'][$rs['recno']];
                //$thisurl = "https://www.papayasauce.com/website_ad583fcd";
                //https://www.papayasauce.com/website_ad583fcd/images/others/products/Sauce/24/mini/s_Two Sister 9 oz.png
                //$thisdir = "$thisurl/images/others/products/".$rs['cname']."/".$rs['recno'];
                $body .= '<div class="align-right cart-div-content-holder-flex-data-container display-inline-block">';
                    $body .= '<div class="float-left cart-img-data-container">';
                        //$body .= '<div class="float-left" ><img id="large_img_container" class="img-cart-review" src="'.$thisdir.'/mini/s_'.substr($rs['attachment'],2).'"/></div>';
                        $body .= '<div class="float-left display-block" >Your statement will show "SQ *KA\'S PAPA SAUCE"</div>';
                        
                        $body .= '<div class="align-left float-left">';
                            $body .= '<table>';
                                $body .= '<tr>';
                                    $body .= '<td class="font-color-white" colspan="2">'.$rs['name'].'</div><td>';
                                $body .= '</tr>';
                                $body .= '<tr>';
                                    $body .= '<td class="font-color-white align-right">Each: </td>';
                                    $body .= '<td class="font-color-white align-left" >$'.number_format($rs['price'], 2).'</td>';
                                $body .= '</tr>';
                                $body .= '<tr>';
                                    $body .= '<td class="font-color-white align-right">Items: </td>';
                                    $body .= '<td class="font-color-white align-left">'.$numberofitems.'</td>';
                                $body .= '</tr>';
                                $body .= '<tr>';
                                    $body .= '<td class="font-color-white align-right color-darkred"><b>Total:</b>  </td>';
                                    $body .= '<td class="font-color-white align-left color-darkred"><b>$'.number_format($numberofitems*$rs['price'], 2).'</b></td>';
                                $body .= '</tr>';
                            $body .= '</table>';
                        $body .= '</div>';
                        
                        $body .= '<div class="align-left float-left">';
                            $body .= '<table>';
                                $body .= '<tr>';
                                    $body .= '<td class="font-color-white" colspan="2">'.$rs['name'].'</div><td>';
                                $body .= '</tr>';
                                $body .= '<tr>';
                                    $body .= '<td class="font-color-white align-right">Each: </td>';
                                    $body .= '<td class="font-color-white align-left" >$'.number_format($rs['price'], 2).'</td>';
                                $body .= '</tr>';
                                $body .= '<tr>';
                                    $body .= '<td class="font-color-white align-right">Items: </td>';
                                    $body .= '<td class="font-color-white align-left">'.$numberofitems.'</td>';
                                $body .= '</tr>';
                                $body .= '<tr>';
                                    $body .= '<td class="font-color-white align-right color-darkred"><b>Total:</b>  </td>';
                                    $body .= '<td class="font-color-white align-left color-darkred"><b>$'.number_format($numberofitems*$rs['price'], 2).'</b></td>';
                                $body .= '</tr>';
                            $body .= '</table>';
                        $body .= '</div>';
                    $body .= '</div>';
                $body .= '</div>';
                $thistotal += number_format($numberofitems*$rs['price'], 2);
                $i++;
            }
        }
        $body .= "<div><b>Total Cost: $".number_format($thistotal,2)."</b></div>";       
        return($body);
    }
    function ShowOrderproducts($db, $thiscartrecnostr, &$thistotal)
    {
        //This function will show the products that the guests has select, it will list it nicely so they can review it before
        //they Pay.
        $sql = "SELECT p.*, c.name as cname FROM products p INNER JOIN category c ON p.foreign_cat_recno = c.recno WHERE p.recno IN ($thiscartrecnostr) ORDER BY p.name";
        //file_put_contents("./dodebug/debug.txt", 'Front sql event? '.$sql, FILE_APPEND);
        $result = $db -> PDOMiniquery($sql);
        if($db->PDORowcount($result) > 0)
        {
            $i = 1;
            foreach($result as $rs)
            {
                $numberofitems = $_SESSION['CARTRECNOTRACKER'][$rs['recno']];
                $thisdir = $rs['attachment_dir']."/".$rs['recno'];?>
                <div class="align-right cart-div-content-holder-flex-data-container display-inline-block" id="cart_div_container_<?php echo $rs['recno'] ?>" >  
                    <button class="float-right btn-x-cart cursor-pointer" id="btn_x_cart" onclick="removePro(<?php echo $rs['recno'] ?>);" title="Remove this product.">X</button>
                    <div class="float-left cart-img-data-container">
                        <div class="float-left" ><img id="large_img_container" class="img-cart-review" src="<?php echo $thisdir ?>/mini/<?php echo $rs['slt_attachment'] ?>" onerror="this.onerror=null;this.src='./images/others/default.png" /></div>
                        <div class="align-left float-left">
                            <table>
                                <tr>
                                    <td class="font-color-white" colspan="2"><?php echo $rs['name'] ?></div><td>
                                </tr>
                                <tr>
                                    <td class="font-color-white float-left align-right">Each: </td>
                                    <td class="font-color-white float-left align-left" >$<?php echo number_format($rs['price'], 2) ?></td>
                                </tr>
                                <tr>
                                    <td class="font-color-white float-left align-right" style="padding-top: 5px;">Items: </td>
                                    <td class="font-color-white float-left align-left" style="padding-top: 5px; padding-bottom: 5px;">
                                        <div id="item_div_<?php echo $rs['recno'] ?>" class="float-left" style="padding-right: 5px;"><?php echo $numberofitems ?></div>
                                        <div id="btn_dialup" class="cursor-pointer float-left"><img class="pro-img-dial" style="border: 1px solid black;" onclick="updateCart('Up', 'item_div', <?php echo $rs['recno'] ?>);" src="./images/others/orange.png"/></div>
                                        <div id="btn_dialdown" class="cursor-pointer float-left"><img class="pro-img-dial" style="border: 1px solid black;" onclick="updateCart('Down', 'item_div', <?php echo $rs['recno'] ?>);" src="./images/others/reddown.png"/></div>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="font-color-white float-left align-right"><b>Total:</b>  </td>
                                    <td class="font-color-white float-left align-left font-weight-bold" id="item_total_<?php echo $rs['recno'] ?>">$<?php echo number_format($numberofitems*$rs['price'], 2) ?></td>
                                </tr>
                                <tr>
                                    <td colspan="2"><textarea class="cart-txtarea" rows="7" readonly><?php echo $rs['description'] ?></textarea><td>
                                </tr>
                            </table>

                        </div>
                    </div>
                </div><?php
                $thistotal += number_format($numberofitems*$rs['price'], 2);
                $i++;
            }
        }
    }
    function CalculateTotalorders($db)
    {
        //This function will return ONLY the total of this order.
        $thiscartrecno = array_keys($_SESSION['CARTRECNOTRACKER']);
        $thistotal = 0;
        //echo json_encode($thiscartrecno);
        //now that $thiscartrecno is an array with just the recno, we need to convert it into a string separated by ","
        //implode
        $thiscartrecnostr = implode(",", $thiscartrecno);
                
        $sql = "SELECT p.*, c.name as cname FROM products p INNER JOIN category c ON p.foreign_cat_recno = c.recno WHERE p.recno IN ($thiscartrecnostr) ORDER BY p.name";
        //file_put_contents("./dodebug/debug.txt", 'Front sql event? '.$sql, FILE_APPEND);
        $result = $db -> PDOMiniquery($sql);
        if($db->PDORowcount($result) > 0)
        {
            $i = 1;
            foreach($result as $rs)
            {
                $numberofitems = $_SESSION['CARTRECNOTRACKER'][$rs['recno']];
                $thistotal += number_format($numberofitems*$rs['price'], 2);
            }
        }
        //$thistotal = 100.00
        return($thistotal);
    }
    function GetProductprice($db, $thisrecno)
    {
        $sql = "SELECT price from products WHERE recno = $thisrecno";
        $result = $db->PDOMiniquery($sql);
        foreach($result as $rs)
        {
            $thisprice = $rs['price'];
        }
        return($thisprice);
    }
}
