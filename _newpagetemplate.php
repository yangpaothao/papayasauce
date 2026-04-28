<?php
require_once("./common/page.php");
require_once("./common/pdocon.php");
require_once("./common/prompt.php");
$load_headers = new Page_Loader();
$db = new PDOCON();
$pt = new PROMPT();
if(count($_POST) > 0)
{
    $_REQUEST['cmd']();
    exit();
}
if(count($_GET) > 0)
{
    $keys = array_keys($_GET);
    foreach($keys as $value)
    {
        $_POST[$value] = $_GET[$value];
    }
    if(isset($_GET['cmd']))
    {
        $_REQUEST['cmd']();
        exit();
    }
}?>
<!DOCTYPE html>
<html>
    <head>
        <?php
            $temp_host = filter_input(INPUT_SERVER, 'SERVER_NAME'); // will get 'localhost'
            $temp_page = filter_input(INPUT_SERVER, 'PHP_SELF'); // will look like /index.php or /somedir/somepage.php
            $explode_page = explode("/", $temp_page); //This variable will now be an array and the page name is the last element of this array
            $this_page = end($explode_page); //this variable will hold the page name like index.php
            $load_headers::Load_Header(strtok($this_page, ".")); //by using strtok($this_page, "."), we will get just 'index'.
        ?>
        <script type="text/javascript">
            $('body').data('thishost', '<?= $temp_host ?>');
            $(document).ready(function(){
                $(".flow-slt-engineers").each(function(){
                    $(this).select2({
                        placeholder: "Type to search and or select from list ONLY.",
                        width: 'style',
                    });
                });
            });
            function updateFlow(obj, recno, lineno){
                thisid = $(obj).prop('id');
                if($(obj).prop('id').substr(0, 3) == "slt")
                {
                    thisval = $(obj).find(":selected").val();
                }
                else
                {
                    thisval = $(obj).val();
                }
                if($(obj).hasClass('class-timer')){
                    //We will check to make sure the timer is in good format
                    var thisregex = /^(?:[01][0-9]|2[0-3]):[0-5][0-9](?::[0-5][0-9])?$/;
                    isTimer = thisregex.test(thisval);
                    if(isTimer === false)
                    {
                        alert("Please enter a correct time in it's correct format.");
                        $(obj).val($('body').data($(obj).prop('id')));
                    }
                }
                if($(obj).hasClass('flow-button-depart') || $(obj).hasClass('flow-button-delete')){
                    //Regardless of which one we will remove the row from the table before we update the database.
                    $("#tr"+lineno).remove();
                    
                    //Now after we remove the row, we need to renumber the column.
                    i=1;
                    $(".tdnumbered").each(function(){
                        $(this).text(i);
                        i++;
                    });
                    thisval = 'true'; //We will be in here if we clicked Depart or Delete
                }
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=UpdateFlow&recno='+recno+'&field='+$(obj).prop('id')+'&value='+thisval, function(result){
                    //alert(result);
                    if(result == "Failed"){
                        alert("Failed to update.  Please contact your administrator.");
                        return(false);
                    }
                });
            }
            function saveData(obj){
                $('body').data($(obj).prop('id'), $(obj).val());
            }
        </script>
    </head>
    <body>
        <?php
            Main();
        ?>
    </body>
</html>
<?php
function UpdateFlow()
{
    global $db;
    $tempfield = substr($_POST['field'], 3); //txtflightnumber_1 -> flightnumber_1, now we have to get rid of the _1
    $realfield = substr($tempfield, 0, strpos($tempfield, "_"));
    $thistable = "flow";
    $thisvalue = $_POST['value'];
    if($_POST['value'] == "true")
    {
        $thisvalue = true;
    }
    if($realfield == "isdeparted")
    {
        $thistime = date('H:i');
        $thisdata = array($realfield => $thisvalue, 'actualdeparture' => $thistime); 
    }
    else
    {
        $thisdata = array($realfield => $thisvalue); //First we remove the first 3 char at start then we remove the last character
    }
    $thiswhere = array("recno" => $_POST['recno']);
    //PDOUpdate($thistable=null, $thisdata = null, $thiswhere = null)
    $result = $db->PDOUpdate($thistable, $thisdata, $thiswhere, $_POST['recno']);
    //file_put_contents("./dodebug/debug.txt", var_dump($_POST), FILE_APPEND);
    if(isset($result))
    {
        echo 'Success';
    }
    else
    {
        echo 'Failed';
    }    
}
function Main()
{
    global $db, $load_headers, $pt;
    $thisfields = array('All');
    $thistable = "flow";
    $thiswhere = array('isdeparted' => false, 'isdeleted' => false);
    $sql = "SELECT *  FROM flow WHERE isdeparted=false AND isdeleted = false ORDER BY date, customer";
    $result = $db->PDOMiniquery($sql);?>
    <div class="main-div"><?php
        $load_headers::Load_Header_Logo_Marquee();?>
        <br><br> <?php
        $load_headers::Load_Header_Logo_Main();?>        
        <div class="main-div-body">
            <div id="div_flowdata_containter" class="div-flowdata-containter">
                <div class="div-header-legend">
                    <div class="div-flow-legend"><div class="status-scheduled">Scheduled</div><div class="status-ontime">On Time</div><div class="status-early">Early</div><div class="status-late">Late</div><div class="status-delayed">Ad-Hog</div></div>
                    <div class="div-flow-legend"><marquee direction="left" scrollamount="10">This is a note test</marquee></div>
                </div>
                <div class="div-flow-body-container">
                    <table id="tbl_flow_data" class="tbl-flow-data">
                        <tr style="background-color: #173346;">
                            <th style="width: 10px !important;"></th>
                            <th style="width: 160px !important;">Cust</th>
                            <th style="width: 60px !important;">A/C Type</th>
                            <th>Flt No.</th>
                            <th>SArr.</th>
                            <th>EArr.</th>
                            <th>AArr.</th>
                            <th>Gate</th>                                  
                            <th>SDep.</th>
                            <th>EDep.</th>
                            <th>ADep.</th>
                            <th style="width: 200px !important;">Engineer</th>
                            <th style="width: 300px !important;">Note</th>
                        </tr><?php
                        $i=1;
                        if(isset($result))
                        {
                            foreach($result as $rs)
                            {
                                if($rs['schedulearrival'] != "" && $rs['estimatearrival'] == "" && $rs['actualarrival'] == "")
                                {
                                   $thisbgcolor = 'white';
                                   $thisfontcolor = 'Black'; 
                                }
                                if($rs['estimatearrival'] != "" && $rs['actualarrival'] == "")
                                {
                                   $thisbgcolor = 'green';
                                   $thisfontcolor = 'white'; 
                                }
                                if($rs['actualarrival'] != "")
                                {
                                   $thisbgcolor = 'yellow';
                                   $thisfontcolor = 'black';
                                }
                                
                                
                                ?>
                                <tr id="tr<?=$i?>" style="background-color: <?= $thisbgcolor?>; <?= $thisfontcolor ?>";>
                                    <td style="width: 10px !important; color: white;"><?= $i ?></td>
                                    <td><?= $rs['customer']?></td>
                                    <td><?= $rs['actype']?></td>
                                    <td><?= $rs['flightnumber']?></td>
                                    <td><?= $rs['schedulearrival']?></td>
                                    <td><?= $rs['estimatearrival']?></td>
                                    <td><?= $rs['actualarrival']?></td>
                                    <td><?= $rs['gate']?></td>
                                    <td><?= $rs['scheduledeparture']?></td>
                                    <td><?= $rs['estimatedeparture']?></td>
                                    <td><?= $rs['actualdeparture']?></td>
                                    <td style="width: 300px !important; color: white;"><?php
                                        $thisrecno = $rs['recno'];
                                        $pt->SltEngineer()->GetString($rs['engineers']);?>
                                    </td>
                                    <td style="width: 300px !important;"><?= $rs['note']?></td>
                                </tr><?php
                                $i++;
                            }
                        }
                        else
                        {?>
                        <tr><td>There is no data</td><tr>
                        <?php
                        }?>  
                    </table>
                </div>
            </div>
        </div>
        <?php
        $load_headers::Load_Footer();?>
    </div><?php
}