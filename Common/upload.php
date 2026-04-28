<?php
require_once("./page.php");
require_once("./pdocon.php");
$db = new PDOCON();
//file_put_contents("../dodebug/debug.txt", var_dump($_POST), FILE_APPEND);

//WE ARE DOING 3 TYPES OF UPLOADS
//1.  NORMAL UPLOAD SUBMITTION FOR IMPORTANT AND ANNOUNCEMNT
//2.  WE ARE DOING INDIVIUDAL UPDATE/UPLOAD TO CHANGES MADE TO ATTACHMENT FOR ANNOUNCEMENT AND IMPORTANT, 
//      FOR THIS WE HAVE 1 EXTRA PARAMTER BESIDES THE FROM, IT IS THE STATUS AND IT COMES IN AS 'MODIFY'
//3.  THERE MAYBE OTHERS IN THE FUTURE
if(array_key_exists('status', $_POST))
{
    $countfiles = count($_FILES['fileupload']['name']);
    $thisuploadfolder = "";
    if($_POST['from'] == "announcement")
    {
        $thisuploadfolder = 'announcement';
        $thistable = 'announcement';
    }
    else if($_POST['from'] == "important")
    {
        $thisuploadfolder = 'important';
        $thistable = 'important';
    }
    $strattachments = NULL;  
    for($i=0;$i<$countfiles;$i++)
    {
        $filename = $_FILES['fileupload']['name'][$i];
        if($strattachments == "")
        {
            $strattachments = $filename;
        }
        else
        {
            $strattachments .= ";$filename";
        }
       move_uploaded_file($_FILES['fileupload']['tmp_name'][$i],"../uploads/$thisuploadfolder/".$filename);
    }
    $thisdata = array('attachment' => $strattachments);
    $thiswheres = array('recno' => $_POST['recno']);
    $result = $db->PDOUPDATE($thistable, $thisdata, $thiswheres, $_POST['recno']);
    file_put_contents("../dodebug/debug.txt", $_POST['status'], FILE_APPEND);
    if($result)
    {
        echo 'Successful';
    }
    else
    {
        echo 'Failed';
    }
}
else
{
    //NORMAL UPLOADS DONE HERE
    $countfiles = count($_FILES['fileupload']['name']);
    $thisuploadfolder = "";
    // Looping all files
    if($_POST['from'] == "announcement")
    {
        $thisuploadfolder = 'announcement';
        $thistable = 'announcement';
    }
    else if($_POST['from'] == "important")
    {
        $thisuploadfolder = 'important';
        $thistable = 'important';
    }
    $strattachments = NULL;  
    for($i=0;$i<$countfiles;$i++)
    {
        $filename = $_FILES['fileupload']['name'][$i];
        if($strattachments == "")
        {
            $strattachments = $filename;
        }
        else
        {
            $strattachments .= ";$filename";
        }
       move_uploaded_file($_FILES['fileupload']['tmp_name'][$i],"../uploads/$thisuploadfolder/".$filename);
    }
    $thisdata = array('foreignkey_recno' => $_SESSION['user_recno'], 'expiredate' => date('Y-m-d', strtotime($_POST['txtexpiredate'])), 'title' => $_POST['txttitle'], 'data' => $_POST['txtdata'], 'attachment' => $strattachments);
    $result = $db->PDOInsert($thistable, $thisdata);
    if($result)
    {
        echo 'Successful';
    }
    else
    {
        echo 'Failed';
    }
}?>
    

