<?php
	ob_start();
	session_start();
	include_once("class.cmscontrol.php");
    include_once("class.phpmailer.php");
    $cms = new cmsControl();
    $cms->showErrors(true);
	$cms->loadTranslation();
	$cms->getSessionInfo();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php echo $cms->cn; ?> - <?php echo $cms->translate(189);?></title>
	<link rel="stylesheet" type="text/css" href="/cms.styles.css" />
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
    <script type="text/javascript">
		$(document).ready(function() {
			var infoSet = <?php echo $cms->is; ?>;
			if(infoSet == 1) {
				$("#infowrap").html('<?php echo $cms->infoT; ?>','<?php echo $cms->info;?>');
				$("#infowrap").animate({top:0},1000);
				infoSet = 0;
			}
			$(".infoclose").click(function(){$("#infowrap").animate({top:-80},1000)});
			$("#passwordReminder").click(function() {
				var v = $("#fieldLogin").val();
				if(v == "") {
					displayInfo("error","<?php echo $cms->translate(188);?>");
					return false;
				}
				else {
					$("#l").val(v);
					$("#ff").submit();
				}
			});

			$("#fieldLogin, #loginLabel").animate({left:20},1000);
			$("#loginLogo").fadeIn(1500);
			$("#fieldPassword, #passwordLabel").delay(50).animate({left:20},1000,function() {
				$("#loginSubmit").animate({opacity:1},1000);
			});

		});

		/*
			displayInfo - displays message
				@param t (string)  - type of message
				@param rr (string) - message
		*/
		function displayInfo(t,rr) {
			$("#infowrap").html('<div class="info '+t+'">'+rr+'<span class="infoclose"><?php echo $cms->translate(187); ?></span></div>');
			$(".infoclose").click(function(){$("#infowrap").stop().animate({top:-80},1000);});
			$("#infowrap").stop().animate({top:0},1000).delay(3000).animate({top:-80},500);
		}
	</script>
</head>
<body>
	<div id="infowrap"></div>
	<div id="loginWrap">
    	<div id="login">
            <div id="loginLeft">
            	<div id="loginWelcome"><?php echo $cms->translate(236); ?></div>
                <div id="loginText"><?php echo $cms->translate(237); ?></div>
                <form method="post" enctype="multipart/form-data" id="ff" action="/login">
                    <input type="hidden" id="l" value="" name="l" />
                    <input type="hidden" name="rem" value="yes"/>
                    <input type="button" name="remind"  id="passwordReminder" title="<?php echo $cms->translate(185); ?>" value="<?php echo $cms->translate(186); ?>" />
                </form>
            </div>
            <div id="loginRight">
				<?php
                    if(isset($_POST["submit"])) {
                       	$cms->login();
					}
                    elseif(isset($_GET["l"])) {
                        $cms->saveAction("","","CMS","logout");
                        $cms->destroySession();
                        $cms->setInfo(true,$cms->translate(6));
                    }
                    else {
                        if(isset($_POST["rem"])) {
                            $login = $_POST["l"];
                            $cms->executeQuery("SELECT * FROM cms_accounts WHERE login='$login'",1);
                            $row = mysqli_fetch_assoc($cms->result1);
                            $id = $row["id"];
                            $email = $row["email"];
                            $rank_id = $row["rank"];
                            $chars = array("a","b","c","d","e","f","g","h","i","j","l","m","n","o","p","r","s","t","u","w","x","y","z","1","2","3","4","5","6","7","8","9");
                            $h = '';
                            for($i=0;$i<10;$i++) {
                                $h .= $chars[rand(0,count($chars)-1)];
                            }
                            $hash = hash("sha1",$h);
                            if($cms->getCount("cms_hash","WHERE user_id='$id'") > 0) {
                                $cms->executeQuery("UPDATE cms_hash SET hash='$hash',datetime=NOW() WHERE user_id='$id'",1);
                            }
                            else {
                                $cms->executeQuery("INSERT INTO cms_hash (`id`, `user_id`, `login`, `email`, `hash`, `datetime`) VALUES ('', '$id', '$login', '$email', '$hash', NOW())",1);
                            }
                            if($cms->result1) {
                                // SEND E-MAIL WITH LINK HASH
                                $mail = new PHPMailer();
                                $mail->IsMail();
                                $mail->IsHTML(true);
                                $mail->FromName = $cms->cn;
                                $mail->From = $cms->newsletter_from_email;
                                $mail->AddAddress($email);
                                $mail->Subject  = $cms->cn.' - '.$cms->translate(174);
                                $mail->Body = '<div style="padding:20px;text-align:center;width:700px;color:#333;text-decoration:none;font-family:Verdana;font-size:13px;">'.$cms->translate(175).':<br /><br /><a style="text-decoration:none;font-family:Verdana;font-size:18px;color:#e99035;font-weight:bold;" href="'.$cms->cw.'/cms.login.php?hash='.$hash.'">'.$cms->translate(176).'</a></div>';
                                if($mail->Send() == true) {
                                    $cms->setInfo(true,$cms->translate(177));
                                }
                                else {
                                    $cms->setInfo(false,$cms->translate(178));
                                    $cms->executeQuery("DELETE FROM cms_hash WHERE user_id='$id'",1);
                                }
                            }
                            else {
                                $cms->setInfo(false,$cms->translate(179));
                            }
                        }
                        elseif(isset($_GET["hash"])) {
                            $hash = strip_tags($_GET["hash"]);
                            $cms->executeQuery("SELECT * FROM cms_hash WHERE hash='$hash'",1);
                            $row = mysqli_fetch_assoc($cms->result1);
                            if($row["id"] != "") {
                                $chars = array("a","b","c","d","e","f","g","h","i","j","l","m","n","o","p","r","s","t","u","w","x","y","z","1","2","3","4","5","6","7","8","9");
                                $h = '';
                                for($i=0;$i<10;$i++) {
                                    $h .= $chars[rand(0,count($chars)-1)];
                                }
                                $ps = hash("sha1",$h);
                                $login = $row["login"];
                                $id = $row["user_id"];
                                $cms->executeQuery("SELECT * FROM cms_accounts WHERE id='$id'",2);
                                $row = mysqli_fetch_assoc($cms->result2);
                                $oldps = $row["password"];
                                $email = $row["email"];
                                $cms->executeQuery("UPDATE cms_accounts SET password='$ps' WHERE id='$id'",1);
                                if($cms->result1) {
                                    $mail = new PHPMailer();
                                    $mail->IsMail();
                                    $mail->IsHTML(true);
                                    $mail->FromName = $cms->cn;
                                    $mail->AddAddress($email);
                                    $mail->From = $cms->newsletter_from_email;
                                    $mail->Subject  = $cms->cn.' - '.$cms->translate(180);
                                    $mail->Body = '<div style="padding:20px;text-align:center;width:700px;color:#333;text-decoration:none;font-family:Verdana;font-size:13px;">'.$cms->translate(180).' '.$cms->translate(181).' '.$login.':<br /><br /><span style="text-decoration:none;font-family:Verdana;font-size:18px;color:#e99035;font-weight:bold;">'.$h.'</span></div>';
                                    if($mail->Send() == true) {
                                        $cms->setInfo(true,$cms->translate(182));
                                        $cms->executeQuery("DELETE FROM cms_hash WHERE login='$login'",2);
                                    }
                                    else {
                                        $cms->setInfo(false,$cms->translate(178));
                                        $cms->executeQuery("UPDATE cms_accounts SET password='$oldps' WHERE id='$id'",1);
                                    }
                                }
                                else {
                                    $cms->setInfo(false, $cms->translate(183));
                                }
                            }
                            else {
                                $cms->setInfo(false, $cms->translate(184));
                            }
                        }
                        else {
                            $cms->setInfo(true,$cms->translate(3));
                        }
                    }
                ?>
                <img src="/_images_cms/img-logo_login.png" id="loginLogo" /><span id="ver"><?php echo $cms->version; ?></span>
                <div id="loginFor"><?php echo $cms->translate(235).' '.$cms->cn;?></div>
                <form method="post" enctype="multipart/form-data" action="/login">
                	<span id="loginLabel"><?php echo $cms->translate(149); ?></span>
                    <input type="text" name="login" id="fieldLogin" />
                    <span id="passwordLabel"><?php echo $cms->translate(106);?></span>
                    <input type="password" name="password" id="fieldPassword" /><br />
                    <input type="submit" name="submit" id="loginSubmit" value="<?php echo $cms->translate(1); ?>"/>
                </form>
            </div>
            <div class="c"></div>
        </div>
    </div>
</body>
</html>
<?php $cms->clearBuffer(); ?>
