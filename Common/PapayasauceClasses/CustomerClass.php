<?php
namespace PapayasauceClasses;

class CustomerClass
{
    private $db;
    private $recno;
    private $firstname;
    private $middlename;
    private $lastname;
    private $email;
    private $phonenumber;
    private $address;
    private $city;
    private $state;
    private $zipcode;

    public function SetCustomer($db, $recno)
    {
        $this->db = $db;
        $this->recno = $recno;     
        
        $sql = "SELECT firstname, lastname, address, address2, city, state, zipcode, email, phone_number FROM users WHERE recno = ".$this->recno;
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
        }
    }
    public function GetPaymentorderreceipt()
    {
        $subject = "Payment Receipt";
        
        return($subject);
    }
    public function GetCustomerbody()
    {
        $body = '<div class="align-right cart-div-content-holder-flex-data-container display-inline-block">';
            $body .= '<div class="float-left cart-img-data-container">';
            $body .= '<div class="float-left display-block" ><a href="'.$this->square_receipturl.'">Click to download the receipt</a></div>';
                $body .= '<div class="align-left float-left">';
                    $body .= '<table>';
                        $body .= '<tr>';
                            $body .= '<td class="font-color-white">Name:</div><td>';
                            $body .= '<td class="font-color-white">'.$this->firstname." ".$this->lastname.'</div><td>';
                        $body .= '</tr>';
                        $body .= '<tr>';
                            $body .= '<td class="font-color-white align-right">Address: </td>';
                            $body .= '<td class="font-color-white align-left" >';
                                $body .= '<div style="width: 100%; display: inline-block">'.$this->address.'</div>';
                                if(!is_null($this->address2))
                                {
                                    $body .= '<div style="width: 100%; display: inline-block">'.$this->address2.'</div>';
                                }
                                $body .= '<div style="width: 100%; display: inline-block">'.$this->city.', '.$this->state.' '.$this->zipcode.'</div>';
                            $body .= '</td>';                            
                        $body .= '</tr>';
                        $body .= '<tr>';
                            $body .= '<td class="font-color-white">Email:</div><td>';
                            $body .= '<td class="font-color-white">'.$this->email.'</div><td>';
                        $body .= '</tr>';
                        $body .= '<tr>';
                            $body .= '<td class="font-color-white">Phone Number:</div><td>';
                            $body .= '<td class="font-color-white">'.$this->phonenumber.'</div><td>';
                        $body .= '</tr>';
                    $body .= '</table>';

                $body .= '</div>';
            $body .= '</div>';
        $body .= '</div>';
        $thistotal += number_format($numberofitems*$rs['price'], 2);
        $body .= "<div><b>Total Cost: $".number_format($thistotal,2)."</b></div>";
    }

    
}

