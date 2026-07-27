<?php
namespace PapayasauceClasses;


class ProductClass
{
    public $db;
    public $prorecno;

    
    function SetProduct($db, $prorecno)
    {
        //We must pass in a unique identifier
        $this->db = $db;
        $this->prorecno = $prorecno;        
    }
    function GetProductname()
    {
        $sql = "SELECT name FROM products WHERE recno = ".$this->prorecno;
        $result = $this->db->PDOminiquery($sql);
        foreach($result as $rs)
        {
            $proname = $rs['name'];
        }
        return($proname);
    }
}

