<?php
    include_once("class.cmsgetcontent.php");
    $cms = new cmsGetContent();
    $cms->showErrors(false);
    
    define("HOME", $cms->object_type == "page" && $cms->id == $cms->{"pfDef_".$cms->lang});
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?php echo $cms->lang; ?>">
<head> 
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />    
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>    
    <meta name="author" content="Lemon-Art Studio Graficzne" />
    <meta name="keywords" content="<?php echo $cms->metaKeys; ?>" />
    <meta name="description" content="<?php echo $cms->metaDesc; ?>" /> 
    <meta name="robots" content="index, follow" />
    <meta name="googlebot" content="noodp" />
    <link rel="canonical" href="<?php echo $cms->cw.$_SERVER["REQUEST_URI"];?>" />
    <link rel="stylesheet" type="text/css" href="/styles.css?t=<?php echo rand(0,999999); ?>" /> 
    <link rel="stylesheet" type="text/css" href="/styles_ck.css" />
    <?php echo $cms->scripts_css; ?> 
    <meta name="google-site-verification" content="" />      
    <title><?php echo $cms->metaTitle; ?></title>
</head>
<?php flush(); ?>
<body class="<?php echo (int)HOME == 1 ? "home" : "page"; ?>">
    <div id="wrap-top">
        <div class="content">
            <a href="/" id="logo" title="Ośrodek Profilaktyki i Edukacji LIDER" class="display-iblock va-middle">
                <img src="/_images/logo.png" width="146" height="122" alt="Ośrodek Profilaktyki i Edukacji LIDER" />
            </a>
            <span id="text" class="align-right display-iblock va-middle">
                <span class="colour-grey size-20 font-fjord display-iblock va-middle">
                    OŚRODEK PROFILAKTYKI I EDUKACJI
                </span><span class="colour-grey font-fjord size-50 display-iblock va-middle">
                    &#8222;LIDER&#8221;
                </span>
            </span>
            <img src="/_images/quote.png" width="586" height="94" alt="Myśl. Wierz. Marz. Miej odwagę. - Walt Disney" id="quote" />
        </div>
    </div>
    
    <div id="wrap-image">
        <div id="slideshow">
            <?php
                echo $cms->getSlide($cms->slide_id, 1920, 360);
            ?>
        </div>
    </div>
    
    <div id="wrap-content">
        <div id="wrap-menu" class="bg-yellow">
            <?php echo $cms->createNavigation("menu", 0, array(0)); ?>
            <div id="contact">
                <a href="tel:+48509534733" class="colour-grey-dark size-32">
                    <img src="/_images/phone.png" alt="Zadzwoń!" width="" height="" />
                    509 534 733
                </a>
                <a href="mailto:elalider@interia.pl" class="colour-grey-dark size-32">
                    <img src="/_images/mail.png" alt="Zadzwoń!" width="" height="" />
                    elalider@interia.pl
                </a>
            </div>
        </div>
        
        <div class="content">
            <?php echo $cms->pageContent; ?>
            <div class="c"></div>
        </div>
    </div>
    
    <?php
        if($cms->page[0]["id"] == 1 && $cms->curLevel > 0) {
    ?>
    <div id="wrap-text" class="bg-red">
        <div class="content colour-white align-center">
            <?php
                $cms->executeQuery("SELECT * FROM cms_menu WHERE id = 24", 1);
                $r = mysqli_fetch_assoc($cms->result1);
                echo $cms->replaceKcFinderPaths($r["content"]);
            ?>
        </div>
    </div>
    <?php } ?>
    
    <div id="wrap-icons" class="bg-greypink"> 
        <div class="content">
            <div class="colour-yellow size-30 align-center font-abold">Warsztaty i szkolenia profilaktyczne</div><br />
            <?php
                $i = 1;
                $cms->executeQuery("SELECT * FROM cms_menu WHERE parentId=1 AND status=1 ORDER BY position ASC",1);
                while($r = mysqli_fetch_assoc($cms->result1)) {
                    echo '<a href="'.$cms->buildLink($r, "menu").'" class="colour-grey-dark align-center">
                                <img src="/_images/icon'.$i.'.png" />
                                <span class="display-block">'.$r["extName"].'</span>
                        </a>';
                    $i++;
                }
            ?>
        </div>
    </div>
    
    <div id="wrap-news" class="bg-grey-dark">
        <div class="content">
            <div class="size-32 colour-grey font-abold">Aktualności</div><br />
            <?php
                $cms->executeQuery("SELECT *,DAY(date) AS 'd', LPAD(MONTH(date), 2, 0) AS 'm' FROM cms_news WHERE status='1' ORDER BY date DESC LIMIT 0,3",1);
                while($r = mysqli_fetch_assoc($cms->result1)) {
                    $c = $cms->trimString(strip_tags($r["content"]), 300);
                    
                    echo '<a href="'.$cms->buildLink($r, "news").'" class="colour-grey">
                            <span>
                                <span class="display-block size-18">
                                    <span class="colour-red size-32">'.$r["d"].'.'.$r["m"].'</span>
                                    <span class="font-abold">'.$r["title"].'</span>
                                </span>
                                <span class="display-block size-14 align-justify">
                                    '.$c.'
                                </span>
                                <span class="display-block align-right">
                                    <span class="more bg-red align-center size-18 display-iblock font-abold">więcej</span>
                                </span>
                            </span>
                        </a>';
                }
            ?>
        </div>
    </div>
    
    <div id="wrap-red" class="bg-red">
        <div class="content colour-white">
            <?php
                $cms->executeQuery("SELECT * FROM cms_menu WHERE id=15",1);
                $r = mysqli_fetch_assoc($cms->result1);
                echo $r["content"];
            ?>
        </div>
    </div>
    
    <footer class="bg-white">
        <div class="content align-center">
            <div class="size-30">Ośrodek Profilaktyki i Edukacji "LIDER"</div><br />
            <div class="size-24">ul. Radna 54<br />14-300 Morąg</div><br />
            <div>                
                <a href="tel:+48509534733" class="colour-grey-dark size-32">
                    <img src="/_images/phone.png" alt="Zadzwoń!" width="" height="" />
                    509 534 733
                </a>
                <a href="mailto:elalider@interia.pl" class="colour-grey-dark size-32">
                    <img src="/_images/mail.png" alt="Zadzwoń!" width="" height="" />
                    elalider@interia.pl
                </a>
            </div>
        </div>
    </footer>
    <?php echo $cms->scripts; ?> 
    <?php if($cms->scripts_foot != "") {echo '<script type="text/javascript">'.$cms->scripts_foot.'</script>';}?>
    <?php 
        echo $cms->cookieInfo(); 
        $cms->newPromobox();
    ?>
</body>
</html>