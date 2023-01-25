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
    <meta name="robots" content="index, follow" />
    <meta name="googlebot" content="noodp" />
    <meta property="og:type" content="website"/>
    <meta property="og:title" content="<?php echo $cms->metaTitle; ?>">
    <meta property="og:description" content="<?php echo $cms->metaDesc; ?>">
    <meta property="og:image" content="https://elalider.pl/_images/logo.png">
    <meta property="og:image:url"  content="https://elalider.pl/_images/logo.png"  />
    <meta property="og:image:width" content="146"/>
    <meta property="og:image:height" content="122"/>
    <meta property="og:image:secure_url" content="https://elalider.pl/_images/logo.png" />
    <meta property="og:image:type" content="image/png" />
    <meta property="og:image:alt" content="?php echo $cms->metaTitle; ?>" />
    <meta property="og:url" content="https://elalider.pl">
    <?php echo $cms->scripts_css; ?>
    <link rel="apple-touch-icon" sizes="180x180" href="/_images/favicons/favicon_100x100.png">
    <link rel="icon" type="image/png" href="/_images/favicons/favicon_32x32.png" sizes="32x32">
    <link rel="shortcut icon" href="/_images/favicons/favicon.ico">
    <meta name="msapplication-TileImage" content="/_images/favicons/favicon_144x144.png")>
    <meta name="google-site-verification" content="" />
    <title><?php echo $cms->metaTitle; ?></title>
</head>
<?php flush(); ?>
<body class="<?php echo (int)HOME == 1 ? "home" : "page"; ?>">
    <div id="wrap-top">
        <div class="content">
            <a href="/" id="logo" title="Ośrodek Profilaktyki i Edukacji LIDER" class="display-iblock va-middle">
                <img src="/_images/logo.png" width="146" height="122" alt="Ośrodek Profilaktyki i Edukacji LIDER" title="Ośrodek Profilaktyki i Edukacji LIDER" />
            </a>
            <span id="text" class="align-right display-iblock va-middle">
                <span class="colour-grey size-20 font-fjord display-iblock va-middle">
                    OŚRODEK PROFILAKTYKI I EDUKACJI
                </span><span class="colour-grey font-fjord size-50 display-iblock va-middle">
                    &#8222;LIDER&#8221;
                </span>
            </span>
            <img src="/_images/quote.png" width="586" height="94" alt="Myśl. Wierz. Marz. Miej odwagę. - Walt Disney" title="Myśl. Wierz. Marz. Miej odwagę. - Walt Disney" id="quote" />
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
        <div id="wrap-menu">
            <?php echo $cms->createNavigation("menu", 0, array(0)); ?>
            <div id="contact">
                <a href="<?=$cms->obfuscate_string("tel:+48509534733")?>" class="colour-grey-dark size-24">
                    <img src="/_images/phone.png" alt="Zadzwoń!" width="" height="" />
                    <?=$cms->obfuscate_string("509 534 733")?>
                </a>
                <a href="<?=$cms->obfuscate_string("mailto:elalider@interia.pl")?>" class="colour-grey-dark size-24">
                    <img src="/_images/mail.png" alt="Napisz do nas!" width="" height="" />
                    <?=$cms->obfuscate_string("elalider@interia.pl")?>
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
            <h2 class="colour-grey-dark size-30 align-center font-abold">Warsztaty i szkolenia profilaktyczne</h2><br />
            <?php
                $i = 1;
                $cms->executeQuery("SELECT * FROM cms_menu WHERE parentId=1 AND status=1 ORDER BY position ASC",1);
                while($r = mysqli_fetch_assoc($cms->result1)) {
                    echo '<div class="icon">
                            <a href="'.$cms->buildLink($r, "menu").'" title="'.$r["extName"].'">
                                <img src="/_images/icon'.$i.'.png" alt="'.$r["extName"].'" title="'.$r["extName"].'" />
                            </a>
                            <h3>
                                <a href="'.$cms->buildLink($r, "menu").'" title="'.$r["extName"].'" class="colour-grey-dark align-center">
                                    <span class="display-block align-center">'.$r["extName"].'</span>
                                </a>
                            </h3>
                        </div>';
                    $i++;
                }
            ?>
        </div>
    </div>

    <div id="wrap-news" class="bg-grey-dark">
        <div class="content">
            <h2 class="size-32 colour-grey font-abold">Aktualności</h2><br />
            <?php
                $cms->executeQuery("SELECT *,DAY(date) AS 'd', LPAD(MONTH(date), 2, 0) AS 'm' FROM cms_news WHERE status='1' ORDER BY date DESC LIMIT 0,3",1);
                while($r = mysqli_fetch_assoc($cms->result1)) {
                    $c = $cms->trimString(strip_tags($r["content"]), 300);

                    echo '<div class="news-one">
                            <span>
                                <span class="display-block size-18 title">
                                    <a href="'.$cms->buildLink($r, "news").'" title="'.$r["title"].'" class="colour-red size-32 news-date">'.$r["d"].'.'.$r["m"].'</a>
                                    <h3 class="news-title-h3"><a href="'.$cms->buildLink($r, "news").'" class="font-abold news-title" title="'.$r["title"].'">'.$r["title"].'</a></h3>
                                </span>
                                <a href="'.$cms->buildLink($r, "news").'" class="news-text display-block size-14 align-justify">
                                    '.$c.'
                                </a>
                                <a href="'.$cms->buildLink($r, "news").'" title="'.$r["title"].'" class="display-block align-right news-more">
                                    <span class="more bg-red align-center size-18 display-iblock font-abold">więcej</span>
                                </a>
                            </span>
                        </div>';
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
                <a href="<?=$cms->obfuscate_string("tel:+48509534733")?>" class="colour-grey-dark size-32">
                    <img src="/_images/phone.png" alt="Zadzwoń!" width="" height="" />
                    <?=$cms->obfuscate_string("509 534 733")?>
                </a>
                <a href="<?=$cms->obfuscate_string("mailto:elalider@interia.pl")?>" class="colour-grey-dark size-32">
                    <img src="/_images/mail.png" alt="Napisz do nas!" width="" height="" />
                    <?=$cms->obfuscate_string("elalider@interia.pl")?>
                </a>
            </div>
        </div>
    </footer>
    <?php echo $cms->scripts; ?>
    <?php if($cms->scripts_foot != "") {echo '<script type="text/javascript">'.$cms->scripts_foot.'</script>';}?>
    <?php
        echo $cms->cookieInfo();
    ?>
    <script type="application/ld+json">
        {
            "@context": "http://schema.org",
            "@type": "Organization",
            "name": "Ośrodek Profilaktyki i Edukacji LIDER",
            "description": "Główne nurty naszej działalności to warsztaty i szkolenia z zakresu wychowania i profilaktyki oraz promocji zdrowia dla:  uczniów, nauczycieli, wychowawców, pedagogów, psychologów, rodziców, członków GKRPA, pracowników ośrodków pomocy społe",
            "logo": "https://elalider.pl/_images/logo.png",
            "url": "https://elalider.pl",
            "telephone": "+48 509 534 733",
            "address": {
                "@type": "PostalAddress",
                "streetAddress": "ul. Radna 54",
                "addressLocality": "Morąg",
                "postalCode": "14-300",
                "addressCountry": "Poland"
            }
        }
    </script>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-2GZYPL4QS9"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-2GZYPL4QS9');
    </script>
</body>
</html>