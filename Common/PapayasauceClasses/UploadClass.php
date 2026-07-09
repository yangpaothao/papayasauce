<?php
namespace PapayasauceClasses;
/**
 * Description of newDateClass
 *
 * @author Yang Pao Thao
 */
class UploadClass {
    private $uploadedfile = "";
    private $thisdir = "";
    private $thisfile = "";
    private $tempfile = "";
    function SetFile($tempfile)
    {
        $this->uploadedfile = $tempfile;
    }
    function GetFile()
    {
        return($this->uploadedfile);
    }
    function GetFilename($i)
    {
        $this->thisfile = $this->uploadedfile['name'][$i];
        return($this->thisfile);
    }
    function GetTempfile()
    {
        
    }
    function CountFiles()
    {
        //Return the number of files.
        //Return 2 if user is uploading 2 and so forth.
        return(count($this->uploadedfile["name"])); 
    }
    function SetDir($tempdir)
    {
        //We want to set the dir
        $this->thisdir = $tempdir;
    }
    function GetDir()
    {
        //Check the dir, if it doesn't exist, then, create the folder, otherwise, do nothing.
        if(!file_exists($this->thisdir)) {
            mkdir($this->thisdir, 0777, true);
        }
        return($this->thisdir);
    }
    function MoveFile($i)
    {
        //$tempfile = $thisfile['tmp_name']
        move_uploaded_file($this->tempfile[$i],$this->thisdir."/".$this->thisfile);
    }
    function GetFinfotype()
    {
        
    }
    function GetFiletype($i)
    {
        return(exif_imagetype($this->tempfile[$i]));
    }
    function SetTempfile()
    {
        $this->tempfile = $this->uploadedfile['tmp_name'];
    }
}?>
