<?php
namespace PapayasauceClasses;
/**
 * Description of newDateClass
 *
 * @author Yang Pao Thao
 */
class OrderClass {
    private $recnostr = "";
    private $square_receiptno = "";
    private $square_order_id = "";
    function SetReceipt($recnostr, $temp_receiptno, $temp_orderno)
    {
        $this->recnostr = $recnostr;
        $this->square_receiptno = $temp_receiptno;
        $this->square_order_id = $temp_orderno;
    }
    function ShowReceipt()
    {
        
        $body = "";
        $lineno = 1;
        $body .= "Orders: ".$this->square_order_id."<br/>";
        $body .= "Confirmation: ".$this->square_receiptno."<br/><br/>";
        $sql = "SELECT p.*, c.name as cname FROM products p INNER JOIN category c ON p.foreign_cat_recno = c.recno WHERE p.recno IN ($thiscartrecnostr) ORDER BY p.name";
        //file_put_contents("./dodebug/debug.txt", 'Front sql event? '.$sql, FILE_APPEND);
        $result = $db -> PDOMiniquery($sql);
        if($db->PDORowcount($result) > 0)
        {
            $i = 1;
            foreach($result as $rs)
            {
                $numberofitems = $_SESSION['CARTRECNOTRACKER'][$rs['recno']];
                $thisurl = "https://www.papayasauce.com/website_ad583fcd";
                $thisdir = "$thisurl/images/others/products/".$rs['cname']."/".$rs['recno'];
                $body .= '<div class="align-right cart-div-content-holder-flex-data-container display-inline-block">';
                    $body .= '<div class="float-left cart-img-data-container">';
                        $body .= '<div class="float-left" ><img id="large_img_container" class="img-cart-review" src="'.$thisdir.'/mini/s_'.substr($rs['attachment'],2).' onerror="this.onerror=null;this.src="https://www.papayasauce.com/website_ad583fcd/images/others/default.png"/></div>';
                        $body .= '<div class="align-left float-left">';
                            $body .= '<table>';
                                $body .= '<tr>';
                                    $body .= '<td class="font-color-white" colspan="2">'.$rs['name'].'</div><td>';
                                $body .= '</tr>';
                                $body .= '<tr>';
                                    $body .= '<td class="font-color-white float-left align-right">Each: </td>';
                                    $body .= '<td class="font-color-white float-left align-left" >$'.number_format($rs['price'], 2).'</td>';
                                $body .= '</tr>';
                                $body .= '<tr>';
                                    $body .= '<td class="font-color-white float-left align-right">Items: </td>';
                                    $body .= '<td class="font-color-white float-left align-left">'.$numberofitems.'</td>';
                                $body .= '</tr>';
                                $body .= '<tr>';
                                    $body .= '<td class="font-color-white float-left align-right color-darkred"><b>Total:</b>  </td>';
                                    $body .= '<td class="font-color-white float-left align-left color-darkred"><b>$'.number_format($numberofitems*$rs['price'], 2).'</b></td>';
                                $body .= '</tr>';
                            $body .= '</table>';

                        $body .= '</div>';
                    $body .= '</div>';
                $body .= '</div>';
                $thistotal += number_format($numberofitems*$rs['price'], 2);
                $i++;
            }
        }
        $body .= "<div>Total: $thistotal</div>";       
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
                $thisdir = "./images/others/products/".$rs['cname']."/".$rs['recno'];?>
                <div class="align-right cart-div-content-holder-flex-data-container display-inline-block" >  
                    <div class="float-left cart-img-data-container">
                        <div class="float-left" ><img id="large_img_container" class="img-cart-review" src="<?php echo $thisdir ?>/mini/s_<?php echo substr($rs['attachment'],2) ?>" onerror="this.onerror=null;this.src='./images/others/default.png" /></div>
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
                                    <td class="font-color-white float-left align-right">Items: </td>
                                    <td class="font-color-white float-left align-left"><?php echo $numberofitems ?></td>
                                </tr>
                                <tr>
                                    <td class="font-color-white float-left align-right color-darkred"><b>Total:</b>  </td>
                                    <td class="font-color-white float-left align-left color-darkred"><b>$<?php echo number_format($numberofitems*$rs['price'], 2) ?></b></td>
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
}
