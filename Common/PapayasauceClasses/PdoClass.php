<?php
namespace PapayasauceClasses;
use PDO;
class PdoClass {
    private $conn = null;
    private $local_database;
    private $local_user;
    private $local_password;
    function __construct() 
    {
        $temp_host = filter_input(INPUT_SERVER, 'SERVER_NAME'); //www.domainname.com
        
        if($temp_host == "localhost")
        {
            $this->local_database = $_ENV['local_database'];
            $this->local_user = $_ENV['local_user'];
            $this->local_password = $_ENV['local_password'];
        }
        else
        {
            $this->local_database = $_ENV['production_papayasauce_database'];
            $this->local_user = $_ENV['production_papayasauce_user'];
            $this->local_password = $_ENV['production_papayasauce_password'];
            
        }
        try{
            $this -> conn = new PDO("mysql:host=localhost;dbname=".$this->local_database, $this->local_user, $this->local_password, [PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        }
        catch(PDOException $e)
        {
            echo "Connection failed: ".$e->getMessage();
        }
    }
    function PDOQuery($thistable=null, $thisfields=null, $thiswhere=null, $thisorderby=null, $thisgroupby=null, $ordering=null, $ons=null, $distinct=null)
    {
        //0.  string - $thistype, INSERT, UPDATE, DELETE, QUERY
        //1.  string - $thistable, 
        //2.  array - $thisfields, ex: field1, field2,...fieldn OR 'All' for *, 
        //3.  asso array - $wheres, will be an associate array with key and value, key being the fieldname.
        //      -The key could be in format of 'something' => 'somevalue' OR if this WERE has 'LIKE' operans, it will come with 'Something LIKE' => 'somevalue'
        //      -so we have to check for LIKES to handle LIKES
        //      -NOTE: IF YOU HAVE A OPERAN LIKES, PUT THE LIKES AT THE END OF ARRAY
        //4.  array - $thisorderby, will be ORDER BY, 
        //5.  array - $tghisgroupby, GROUP BY and such
        //6.  array - $ordering will be either ASC or DESC, defaulting to ASC
        //7.  string - $ons is for joins
        $realfields = "";
        if($thisfields[0]=='All')
        {
            $realfields = "*";
        }
        else
        {
            $realfields = implode(',', $thisfields);
        }
        $sql = "SELECT ";
        if(!is_null($distinct))
        {
            $sql .= "DISTINCT "; //Can be $distinct or can be explicite, doesn't matter.
        }
        $sql .= "$realfields FROM $thistable ";
        //So this is saying, if $thisfields = 'All', then we will use the asterisk '*' otherwise if it is not 'All' and it contains some fields, then we will do the implode.
        if(isset($ons))
        {
            $sql .= "$ons ";
        }
        $sql .= "WHERE 1=1 "; //If array is 1 or null the ', ' delimiter will not be present.
        $isOnce = false;
        if(isset($thiswhere) > 0)
        {
            //We should have atleast 1 where clause
            foreach($thiswhere as $key => $value)
            {
                //file_put_contents("./dodebug/debug.txt", 'what is sub? '.substr($key, 0, 6), FILE_APPEND);
                if(strpos($key, ' LIKE', 0))
                {
                    if($isOnce == false)
                    {
                        $isOnce = true;
                        $sql .= "AND ";
                    }
                    //If $key contgains 'LIKE' ('field LIKE'), we know we are doing a LIKE operans, otherwise, a normal equal comparisons.
                    $likesarray = explode(' ', $key); //$likesarray should now be an array['realkey', 'LIKE'];
                    $sql .= "$key :$likesarray[0] OR ";  //$sql .= "AND $key :$key, in sql reality, be like $sql .= "AND $key LIKE :$field
                }
                else
                {
                    $sql .= "AND $key = :$key "; 
                }
            }
            if($isOnce == true)
            {
                //If we are here, we know we have atlest one 'LIKE' operans, we want to remove the last 'OR' from the sql.
                $sql = substr($sql, 0, -3);
            }
        }
        if(isset($thisorderby))
        {
            $sql .= "ORDER BY ".implode(', ', $thisorderby); 
        }
        if(isset($thisgroupby))
        {
            $sql .= "GROUP BY ".implode(', ', $thisgroupby); 
        }
        /*
        if(isset($thisorderby))
        {
            $sql .= "ORDER BY :".implode(', :', $thisorderby); 
        }
        if(isset($thisgroupby))
        {
            $sql .= "GROUP BY ".implode(', ', $thisgroupby); 
        }*/
        //echo $sql;
        //file_put_contents("./dodebug/debug.txt", $sql, FILE_APPEND);
        //Now we should have an sql that is ready to be prepared
        $q = $this->conn->prepare($sql);
        if(isset($thiswhere))
        {
            //We should have atleast 1 where clause
            foreach($thiswhere as $key => $value)
            {
                if(is_numeric($value) && !is_float($value))
                {
                    $q->bindValue(":$key", $value, PDO::PARAM_INT);
                }
                else if(is_bool($value))
                {
                    $q->bindValue(":$key", $value, PDO::PARAM_BOOL);
                }
                else if(is_null($value) || $value == "")
                {
                    $q->bindValue(":$key", $value, PDO::PARAM_NULL);
                }
                else
                {
                    if(strpos($key, ' LIKE', 0))
                    {
                        $likesarray = explode(' ', $key); //$likesarray should now be an array['realkey', 'LIKE'];
                        $tempval = "%$value%";
                        $q->bindValue(":$likesarray[0]", $tempval, PDO::PARAM_STR);  //$sql .= "AND $key :$key, in sql reality, be like $sql .= "AND $key LIKE :$field
                    }
                    else
                    {
                        $q->bindValue(":$key", $value, PDO::PARAM_STR);
                    }
                }
            }
        }/*
        if(isset($thisorderby))
        {
            foreach($thisorderby as $value)
            {
                $q->bindValue(":$value", $value, PDO::PARAM_STR);
            }
        }
        if(isset($thisgroupby))
        {
            foreach($thisgroupby as $value)
            {
                $q->bindValue(":$value", $value, PDO::PARAM_STR);
            }
        }*/
        $q->execute();
        if ($q->rowCount() > 0){
            //file_put_contents("./dodebug/debug.txt", "admin exist? : 5555", FILE_APPEND);
            return $q->fetchAll(PDO::FETCH_ASSOC);
        }
        else
        {
            return null;
        }
    }
    function PDOInsert($thistable=null, $thisdata=null, $thisrecno = null, $thisignore = "")
    {
        //0.  string - $thistable will just be the table name
        //1.  associative array - $thisdata will have associative arrayt key and values for the fields and the values,
        //2.  For insert, $thisrecno will always be null
        
        $historyfields = "";
        $historyvalues = "";
        $tempignore = $thisignore;
        $sql = "INSERT $tempignore INTO $thistable (";
        $firsttime = false;
        foreach($thisdata as $key => $value)
        {
            if($firsttime == false)
            {
                $firsttime = true;
                $sql .= "$key";
                
                $historyfields = $key;
            }
            else
            {
                $sql .= ", $key";
                $historyfields .= ", $key";
            }
        }
        $sql .= ") VALUES(";
        $firsttime = false;
        foreach($thisdata as $key => $value)
        {
            if($firsttime == false)
            {
                $firsttime = true;
                $sql .= ":$key";
                
                $historyvalues = $value;
            }
            else
            {
                $sql .= ", :$key";
                
                $historyvalues .= "; $value";
            }
        }
        $sql .= ")";
        //file_put_contents("./dodebug/debug.txt", 'update: '.$sql, FILE_APPEND);
        try 
        {
            $q = $this->conn->prepare($sql);
            foreach($thisdata as $key => $value)
            {
                if(is_numeric($value) && !is_float($value))
                {
                    $q->bindValue(":$key", $value, PDO::PARAM_INT);
                }
                else if(is_bool($value))
                {
                    $q->bindValue(":$key", $value, PDO::PARAM_BOOL);
                }
                else if(is_null($value) || $value == "")
                {
                    $q->bindValue(":$key", $value, PDO::PARAM_NULL);
                }
                else
                {
                    $q->bindValue(":$key", $value, PDO::PARAM_STR);
                }
            }
            $q ->execute();
            $insertid = $this->conn->lastInsertId();
            
            $thissql = "INSERT INTO history (tablename, associativerecno, action, field, value, modifiedby) VALUES(:tablename, :associativerecno, :action, :field, :value, :modifiedby)";
            $q = $this->conn->prepare($thissql);
            $q->bindValue(":tablename", "$thistable", PDO::PARAM_STR);
            $q->bindValue(":associativerecno", $insertid, PDO::PARAM_INT);
            $q->bindValue(":action", 'INSERT', PDO::PARAM_STR);
            $q->bindValue(":field", "$historyfields", PDO::PARAM_STR);
            $q->bindValue(":value", "$historyvalues", PDO::PARAM_STR);
            if(isset($_SESSION['user_recno']))
            {
                $q->bindValue(":modifiedby", $_SESSION['user_recno'], PDO::PARAM_INT);
            }
            else
            {
                $q->bindValue(":modifiedby", 0, PDO::PARAM_INT);
            }
            $q ->execute();
            return($insertid);
 
        } 
        catch (Exception $ex) {
            throw $ex;
            return("Failed Insert");
        }  
    }
    function PDOUpdate($thistable=null, $thisdata = null, $thiswhere = null, $thisrecno = null, $thiscat = null)
    {
        //0.  string - $thistype, INSERT, UPDATE, DELETE, QUERY
        //1.  string - $thistable, 
        //2.  array - $thisfields, ex: field1, field2,...fieldn OR 'All' for *, 
        //3.  asso array - $wheres, will be an associate array with key and value, key being the fieldname.
        //4.  array - $thisorderby, will be ORDER BY, 
        //5.  array - $tghisgroupby, GROUP BY and such
        //6.  array - $ordering will be either ASC or DESC, defaulting to ASC
        $historyfields = "";
        $historyvalues = "";
        $sql = "UPDATE $thistable SET "; //If array is 1 or null the ', ' delimiter will not be present.
        //So this is saying, if $thisfields = 'All', then we will use the asterisk '*' otherwise if it is not 'All' and it contains some fields, then we will do the implode.
        if(count($thisdata) > 0)
        {
            //We should have atleast 1 where clause
            foreach($thisdata as $key => $value)
            {
                //The reson for this concat here is so when we do an update to a field that contains a string and we want the new value to get tag onto the end of the value string.
                //ex, 1,2,3,4 and when we do an update we would end up with 1,2,3,4,5; this has been tested and works.
                if(!is_null($thiscat))
                {
                    $sql .= "$key = CONCAT_WS(',', $key, :$key),"; 
                }
                else
                {
                    $sql .= "$key = :$key,"; 
                }
                if($historyfields == "")
                {
                    $historyfields = $key;
                    $historyvalues = $value;
                }
                else
                {
                    $historyfields .= ", $key";
                    $historyvalues .= ", $value";
                }
            }
            //We will need to remove the last char, ',' from the string
            $sql = rtrim($sql, ",");
        }
        $sql .= " WHERE ";
        if(isset($thiswhere))
        {
            //We should have atleast 1 where clause
            foreach($thiswhere as $key => $value)
            {
                $sql .= "$key = :$key AND "; 
            }
            $sql = rtrim($sql, " AND ");
        }
        //file_put_contents("./dodebug/debug.txt", 'update: '.$sql.'\n', FILE_APPEND);
         try {
            $q = $this->conn->prepare($sql);
            foreach($thisdata as $key => $value)
            {
                if(is_numeric($value) && !is_float($value) && $value != true && $value != false)
                {
                    //file_put_contents("./dodebug/debug.txt", 'in num', FILE_APPEND);
                    $q->bindValue(":$key", $value, PDO::PARAM_INT);
                }
                else if(is_bool($value) || $value === true || $value === false)
                {
                   //file_put_contents("./dodebug/debug.txt", 'in bool', FILE_APPEND);
                    $q->bindValue(":$key", $value, PDO::PARAM_BOOL);
                }
                else if(is_null($value) || $value == "")
                {
                    //file_put_contents("./dodebug/debug.txt", 'in null', FILE_APPEND);
                    $q->bindValue(":$key", $value, PDO::PARAM_NULL);
                }
                else
                {
                    //file_put_contents("./dodebug/debug.txt", 'in string', FILE_APPEND);
                    $q->bindValue(":$key",  $value, PDO::PARAM_STR);
                }
            }
            //file_put_contents("./dodebug/debug.txt", 'value?: '.$value.'\n', FILE_APPEND);
            foreach($thiswhere as $key => $value)
            {
                if(is_numeric($value) && !is_float($value) && $value != true && $value != false)
                {
                    $q->bindValue(":$key", $value, PDO::PARAM_INT);
                    //file_put_contents("./dodebug/debug.txt", 'value: NOt Here1 - '.$value, FILE_APPEND);
                }
                else if(is_bool($value) || $value === true || $value === false)
                {
                    if($value == true)
                    {
                        $q->bindValue(":$key", 1, PDO::PARAM_BOOL);
                        //file_put_contents("./dodebug/debug.txt", 'value: NOt Here2 - should be here'.$value, FILE_APPEND);
                    }
                    else
                    {
                        $q->bindValue(":$key", 0, PDO::PARAM_BOOL);
                    }
                    //file_put_contents("./dodebug/debug.txt", 'value: NOt Here2 - '.$value, FILE_APPEND);
                }
                else if(is_null($value) || $value == "")
                {
                    $q->bindValue(":$key", $value, PDO::PARAM_NULL);
                    //file_put_contents("./dodebug/debug.txt", 'value: NOt Here3 - '.$value, FILE_APPEND);
                }
                else
                {
                    $q->bindValue(":$key", $value, PDO::PARAM_STR);
                    //file_put_contents("./dodebug/debug.txt", 'value: NOt Here4 - '.$value, FILE_APPEND);
                    
                }
            }
            //file_put_contents("./dodebug/debug.txt", 'value: '.$value.'\n', FILE_APPEND);
            $q ->execute();   
            //$insertid = $this->conn->lastInsertId();
            
            $thissql = "INSERT INTO history (tablename, associativerecno, action, field, value, modifiedby) VALUES(:tablename, :associativerecno, :action, :field, :value, :modifiedby)";
            $q = $this->conn->prepare($thissql);
            $q->bindValue(":tablename", "$thistable", PDO::PARAM_STR);
            $q->bindValue(":associativerecno", $thisrecno, PDO::PARAM_INT);
            $q->bindValue(":action", 'UPDATE', PDO::PARAM_STR);
            $q->bindValue(":field", "$historyfields", PDO::PARAM_STR);
            $q->bindValue(":value", "$historyvalues", PDO::PARAM_STR);
            $q->bindValue(":modifiedby", $thisrecno, PDO::PARAM_INT);
            $q ->execute();
            
            return("Success");
        } 
        catch (Exception $ex) 
        {
            return($ex);
        }
    }
    function PDORowcount($result)
    {
        $thiscount = $result->rowCount();
        return($thiscount);
    }
    function PDOMiniresult($sql)
    {
        //echo $sql;
        $result= $this->conn->query($sql, PDO::FETCH_ASSOC);
        return($result);
    }
    function PDOMiniquery($sql)
    {
        //echo $sql;
        $result = $this->conn->query($sql, PDO::FETCH_ASSOC);
        return($result);
    }
    function PDOMiniinsert($sql)
    {
        //echo $sql;
        $q = $this->conn->exec($sql);
        return($q);
    }
    function PDOMinilastinsertid()
    {
        //echo $sql;
        $q = $this->conn->exec("SELECT LAST_INSERT_ID()");
        return($q);
    }
    function PDODataseek($result)
    {
        $q = $result->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_ABS, 0);
        return($q);
    }
    function PDOGetdatabase($sql)
    {
        $q = $this->conn->query($sql, PDO::FETCH_ASSOC);
        return($q);
    }
}?>
