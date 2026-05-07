<?php
require __DIR__ . '/common/vendor/autoload.php';
use PapayasauceClasses\PdoClass;
use PapayasauceClasses\PageloaderClass;
use PapayasauceClasses\PromptClass;
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
$dotenv->load();
$temp_host = filter_input(INPUT_SERVER, 'SERVER_NAME');// will get 'localhost'

if($temp_host != "localhost")
{
    require_once("/home1/gcwwkite/public_html/common/page.php");
}
else
{
    require_once("./common/page.php");
}
$db = new PdoClass();
$pc = new PageloaderClass();
$pt = new PromptClass();
if(count($_POST) > 0 && isset($_POST['cmd']))
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
            $temp_page = filter_input(INPUT_SERVER, 'PHP_SELF'); // will look like /index.php or /somedir/somepage.php
            $explode_page = explode("/", $temp_page); //This variable will now be an array and the page name is the last element of this array
            $this_page = end($explode_page); //this variable will hold the page name like index.php
            $pc->Load_Header(strtok($this_page, ".")); //by using strtok($this_page, "."), we will get just 'index'.
        ?>
        <script type="text/javascript">
            $(document).ready(function(){
               $("#btn_dialup").click(function(){
                   $("#txt_item_tracker").val(parseInt($("#txt_item_tracker").val()) + 1);
                    
                });
                $("#btn_dialdown").click(function(){
                    if($("#txt_item_tracker").val() != 0){
                        $("#txt_item_tracker").val(parseInt($("#txt_item_tracker").val()) - 1);
                    }
                }); 
            });
            function selectedProduct(product_recno){
                let thisArray = [{
                    "this_recno": product_recno
                }];
                const thisData = JSON.stringify(thisArray);
                fetchAjaxsltproduct(thisData);
            }
            async function fetchAjaxsltproduct(thisData){
                try{
                    const result = await $.ajax({
                    url: '<?=$_SERVER['PHP_SELF']; ?>?cmd=SelectedProduct&thisarray='+thisData,
                    type: 'POST',
                    contentType: "application/json"
                    });
                    if(result == "Success"){
                        window.location.href = "product.php";
                    }
                    else
                    {
                        alert("SESSION variable 'SELECTED_PRODUCT_RECNO' did not get set.");
                        return(false);
                    }
                }
                catch(error){
                    alert("ERROR");
                }
            }
            function addCart(thisrecno){
                thisArray = [{
                        "this_thisrecno": thisrecno,
                        "this_thisval": $("#txt_item_tracker").val()
                    }];
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=AddCart&thisarray='+JSON.stringify(thisArray), function(result){
                    //alert(result);
                    //pc_cart_tracker
                    //responsive_cart_tracker
                    $("#pc_cart_tracker").text(result);
                    $("#responsive_cart_tracker").text(result);
                });
            }
            function miniImgslt(obj, thisrecno, thisattachment, thiscatename){
                thisArray = [{
                        "this_thisrecno": thisrecno,
                        "this_thisattachment": thisattachment,
                        "this_thiscatename": thiscatename
                    }];
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=MiniImgslt&thisarray='+JSON.stringify(thisArray), function(result){
                    //alert(result);
                    if($(obj).hasClass("product-selected-bdr")){
                        //If user clicked the selected one, we will not do anything.
                        return(false);
                    }
                    //When we return, we have the dir to the proper image and we replace the src with it.
                    $("#large_img_container").prop("src", result);
                    
                    //We go through the ele of this class and remote the class "product-selected-bdr".
                    $(".pro-mini-img").each(function(){
                        $(this).removeClass("product-selected-bdr");
                    });
                    //Because we have selected a new image, we will have to show the border color for this new selection
                    //we just add the class to the elemenbt or image the user clicked
                    $(obj).addClass("product-selected-bdr");
                });
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
function AddCart()
{
    global $pt;
    $returnpost = $pt->AnalyzePosts();
    if(!isset($_SESSION['TEMP_CART']))
    {
        $_SESSION['TEMP_CART'] = $returnpost['thisval'];
    }
    else
    {
        $_SESSION['TEMP_CART'] = $_SESSION['TEMP_CART'] + $returnpost['thisval'];
    }
    echo $_SESSION['TEMP_CART'];
}
function MiniImgslt(){
    global $pt;
    $returnpost = $pt->AnalyzePosts();
    echo "./images/others/products/".$returnpost['thiscatename']."/".$returnpost['thisrecno']."/large/l_".substr($returnpost['thisattachment'],2);
}
function Main()
{
    global $db, $pc;
    if(!isset($_SESSION['SELECTED_PRODUCT_RECNO']))
    {?>
        <script type="text/javascript">
            window.location.href = "index.php";
        </script><?php
        exit;
    }
    ?>
    <div class="main-div">
        <div class="pro-div-data-container">
            <div class="main-logo float-left">
                <?php echo $pc->LoadLogo($db);?>
            </div>
            <div class="float-left div-loginpanel"><?php echo $pc->LoginPanel();?></div>
            <div class="div-content-holder-flex align-center"><?php
                //1 month from today only
                $sql = "SELECT p.*, c.name as cname FROM products p INNER JOIN category c ON p.foreign_cat_recno = c.recno WHERE p.recno = ".$_SESSION['SELECTED_PRODUCT_RECNO']." AND p.isActive = true ORDER BY p.name";
                //file_put_contents("./dodebug/debug.txt", 'Front sql event? '.$sql, FILE_APPEND);
                $result = $db -> PDOMiniquery($sql);
                if($db->PDORowcount($result) > 0)
                {
                    $i = 1;
                    
                    foreach($result as $rs)
                    {
                        $thisdir = "./images/others/products/".$rs['cname']."/".$rs['recno'];?>
                        <div class="align-left product-div-content-holder-flex-data-container">  
                            <div class="float-left pro-mini-img-big-container">
                                <div class="float-left white-space-no-wrap pro-mini-img-container"><?php
                                    $dir = new DirectoryIterator($thisdir."/mini/");
                                    foreach ($dir as $fileinfo) {
                                        if (!$fileinfo->isDot() && $fileinfo->isFile()) 
                                        {
                                            //file_put_contents("./dodebug/debug.txt", 'selected?? '.substr($rs['attachment'],2)." == ".substr($fileinfo->getFilename(),2), FILE_APPEND);
                                            //We had to substr because the name is the same but we have a pre str added and this pre str are 'l-' and 's_', they are not he same.  By substr
                                            //it will make them the same and therefore meet our filtration.
                                            if(substr($rs['attachment'],2) == substr($fileinfo->getFilename(), 2))
                                            {
                                                $thisbordercolor = "product-selected-bdr";
                                            }
                                            else
                                            {
                                                $thisbordercolor = "";
                                            }?>
                                            <img id="img_<?php echo $fileinfo->getFilename() ?>" onclick="miniImgslt(this, <?php echo $rs['recno'] ?>, '<?php echo $fileinfo->getFilename() ?>', '<?php echo $rs['cname'] ?>');" class="<?php echo $thisbordercolor ?> pro-mini-img" src="<?php echo $thisdir ?>/mini/<?php echo $fileinfo->getFilename() ?>" /> <?php
                                        }
                                    }?>
                                </div>
                            </div>
                            <div>
                                <div class="float-left" ><img id="large_img_container" class="div-front-event" src="<?php echo $thisdir ?>/large/<?php echo $rs['attachment'] ?>" onerror="this.onerror=null;this.src='./images/others/default.png" /></div>
                            </div>
                            <div class="float-left pro-img-data-container">
                                <div><?php echo $rs['name'] ?></div>
                                <div class="font-weight-bold">$<?php echo number_format($rs['price'], 2) ?></div>
                                <div><textarea class="pro-txtarea" rows="13" readonly><?php echo $rs['description'] ?></textarea></div>
                                <div>
                                    <div class="float-left"><input class="align-center font-size-1p5em" type="text" id="txt_item_tracker" name="txt_item_tracker" style="height: 46px;" size="4" value="1"/></div>
                                    <div class="float-left">
                                        <div id="btn_dialup" class="cursor-pointer display-block"><img class="pro-img-dial" src="./images/others/orange.png"/></div>
                                        <div id="btn_dialdown" class="cursor-pointer display-block"><img class="pro-img-dial" src="./images/others/reddown.png"/></div>
                                    </div>
                                </div>
                                <div class="cursor-pointer"><button name="btn_cart" id="btn_cart" onclick="addCart(<?php echo $rs['recno']?>);">Add to cart</button></div>
                            </div>
                        </div><?php
                        $i++;
                    }
                }?>
            </div>
            <div class="align-center main-div-footer"><?php echo $pc->Load_Footer();?></div>
        </div>
        

    </div><?php
}