<?php
require __DIR__ . '/Common/vendor/autoload.php';
use PapayasauceClasses\PdoClass;
use PapayasauceClasses\PageloaderClass;
use PapayasauceClasses\PromptClass;
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
$dotenv->load();
$temp_host = filter_input(INPUT_SERVER, 'SERVER_NAME');// will get 'localhost'

if($temp_host != "localhost")
{
    require_once("/home1/gcwwkite/public_html/website_ad583fcd/Common/page.php");
}
else
{
    require_once("./Common/page.php");
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
                loadTabs($("#div_main")[0]);
            });
            function mainTabs(obj){
                //If we are clicking the selected tab, nothing needs to be done
                if(!$(obj).hasClass('div-tab-slted')){
                    //If we are here, that means what we clicked is not the selected tab so we do something
                    $(".div-main-tabs").each(function(){
                        if($(obj).prop('id') == $(this).prop('id')){
                            $(obj).removeClass("div-tab-nonslted").addClass("div-tab-slted");
                        }
                        else{
                            $(this).removeClass("div-tab-slted");
                            $(this).addClass("div-tab-nonslted");
                        }
                    });
                }  
                loadTabs(obj);
            }
            function loadTabs(obj){
                let thisArray = [{
                    "this_thistab": $(obj).text()
                }]; 
                const thisData = JSON.stringify(thisArray);
                $.ajax({
                    url: "<?=$_SERVER['PHP_SELF']; ?>?cmd=LoadTabs&thisarray="+thisData,
                    type: "POST"
                }).then(function(result) {
                    // Code here will execute *after* the AJAX request is successful
                    $("#div_content_holder").html(result);
                }).catch(function(error) {
                    alert(error);
                });
            }
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
            function selectedVideo(vlink){
                window.open(vlink, "_blank");
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
function LoadTabs()
{
    global $db, $pt;
    $returnpost = $pt->AnalyzePosts();
    switch($returnpost['thistab'])
    {
        case "Main":
            LoadMaintab($db);
            break;
        case "Videos":
            LoadVideotab($db);
            break;
        default:
            LoadMaintab($db);
            break;
    }
}
function LoadVideotab($db)
{
    $sql = "SELECT * FROM videos WHERE isActive = true ORDER BY entry_date, name";
    //file_put_contents("./dodebug/debug.txt", 'Front sql event? '.$sql, FILE_APPEND);
    $result = $db -> PDOMiniquery($sql);
    if($db->PDORowcount($result) > 0)
    {
        $i = 1;
        foreach($result as $rs)
        {?>
            <iframe width="360" height="215" src="<?php echo $rs['vlink']?>" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
            <?php
            $i++;
        }
    }
    else
    {?>
        <div class="align-left cursor-pointer div-content-holder-flex-data-container" id="div_event"><img class="div-front-event" src="./images/others/no-event.png"/></div><?php 
    }
}
function LoadMaintab($db)
{
    $sql = "SELECT p.*, c.name as cname FROM products p INNER JOIN category c ON p.foreign_cat_recno = c.recno WHERE p.isActive = true ORDER BY p.name";
    //file_put_contents("./dodebug/debug.txt", 'Front sql event? '.$sql, FILE_APPEND);
    $result = $db -> PDOMiniquery($sql);
    if($db->PDORowcount($result) > 0)
    {
        $i = 1;
        foreach($result as $rs)
        {?>
            <div class="align-left cursor-pointer div-content-holder-flex-data-container" onclick="selectedProduct(<?php echo $rs['recno']?>);">  
                <div class="float-left white-space-no-wrap" style="width: 100%; color: white; font-weight: bold; background-color: gray; min-height: 20px;">$<?php echo number_format($rs['price'], 2) ?>, <?php echo $rs['name']?></div>
                <div class="float-left" style="width: 100%;"><img class="div-front-event" src="./images/others/products/<?php echo $rs['cname']?>/<?php echo $rs['recno']?>/large/<?php echo $rs['attachment'] ?>" onerror="this.onerror=null;this.src='./images/others/default.png" /></div>
            </div><?php
            $i++;
        }
    }
    else
    {?>
        <div class="align-left cursor-pointer div-content-holder-flex-data-container" id="div_event"><img class="div-front-event" src="./images/others/no-event.png"/></div><?php 
    }
}
function SelectedProduct()
{
    global $pt;
    $returnpost = $pt->AnalyzePosts();
    $_SESSION['SELECTED_PRODUCT_RECNO'] = $returnpost['recno'];
    echo "Success";
}
function Main()
{
    global $db, $pc;?>
    <div class="main-div">
        <div class="index-div-container">
            <div class="main-logo float-left">
                <?php echo $pc->LoadLogo($db);?>
            </div>
            <div class="float-left div-loginpanel"><?php echo $pc->LoginPanel();?></div>
            <div class="div-main-tabs-container index-div-stripe index-stripe">
                <div class="float-left div-main-tabs div-tab-slted cursor-pointer align-center border-right-1px-white" id="div_main" onclick="mainTabs(this);">Main</div>
                <!--<div class="float-left div-main-tabs cursor-pointer div-main-tab-nonslted align-center" id="div_products" onclick="mainTabs(this);">Products</div>-->
                <div class="float-left div-main-tabs cursor-pointer div-tab-nonslted align-center border-right-1px-white" id="div_videos" onclick="mainTabs(this);">Videos</div>
                <!--<div class="float-left div-main-tabs cursor-pointer div-main-tab-nonslted align-center" id="div_events" onclick="mainTabs(this);">Events</div>-->
                <!--<div class="float-left div-main-tabs cursor-pointer div-main-tab-nonslted align-center" id="div_recipe" onclick="mainTabs(this);">Recipe</div>-->
                <!--<div class="float-left div-main-tabs cursor-pointer div-tab-nonslted align-center border-right-1px-white" id="div_about" onclick="mainTabs(this);">About</div>-->
            </div><?php
            if(!isset($_SESSION['user_recno']))
            {?>
                <div class="cart-div-headline-info align-center font-size-2em">Coming soon!!!</div><?php
            }
            else
            {?>
                <div class="div-content-holder-flex align-center" id="div_content_holder"></div><?php
            }?>
            <div class="align-center main-div-footer"><?php echo $pc->Load_Footer();?></div>
        </div>
    </div><?php
}