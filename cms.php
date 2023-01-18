<?php
	include_once("class.cmscontrol.php");
	$cms = new cmsControl();
	$cms->startCMS();
	$cms->checkSession();
	$cms->showErrors(false);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php echo $cms->translate(193).' - '.$cms->cn; ?></title>

    <link rel="stylesheet" type="text/css" href="/cms.styles.css" />
    <link type="text/css" href="/_scripts/cms/ui-lightness/jquery-ui-1.8.16.custom.css" rel="Stylesheet" />

	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
	<script type="text/javascript" src="/_scripts/cms/jquery.dragsort.js"></script>
    <script type="text/javascript" src="/_scripts/cms/jquery-ui-1.10.0.custom.min.js"></script>
    <script type="text/javascript" src="/_scripts/cms/ckeditor/ckeditor.js"></script>
	<script type="text/javascript" src="/_scripts/cms/jquery.datepicker.local.js"></script>
    <script type="text/javascript" src="/_scripts/cms/jquery.countdown.js"></script>
    <script type="text/javascript" src="/_scripts/cms/jquery.colorpicker.js"></script>
    <script type="text/javascript" src="/_scripts/cms/jquery.jcrop.js"></script>
    <script type="text/javascript" src="/class.lemon.js"></script>
	<script type="text/javascript">
		/* Set module for Lemon-Art class */
			lemon.module = "<?php echo $cms->p; ?>";
			lemon.lang = "<?php echo $cms->lang; ?>";
			lemon.id = "<?php echo $cms->id; ?>";
			lemon.crop.msgs = {
				ok : "<?php echo $cms->translate(379); ?>",
				error1 : "<?php echo $cms->translate(381); ?>",
				error2 : "<?php echo $cms->translate(380); ?>",
				error3 : "<?php echo $cms->translate(27); ?>",
				restoreok:"<?php echo $cms->translate(383); ?>",
				restoreerror1:"<?php echo $cms->translate(27); ?>",
				restoreerror2:"<?php echo $cms->translate(139); ?>"
			};
			lemon.image_details_title = "<?php echo $cms->translate(45); ?>";
			lemon.newsletter.load_template_msg = "<?php echo $cms->translate(487); ?>";
            lemon.links = "<?php echo $cms->link_types; ?>";

		/* Set up link preview */
		lemon.link_preview();

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

        lemon.ckeditor();

		/*
			loadEditor - creates an instance of CKEditor with given settings
				@param id (string) - id of textarea element
				@param h (string) - the height of CKEditor
				@param bg (string) - background
		*/
		function loadEditor(id,h,bg){
			if(h == "") {var e = "600px";}
			else {var e = h;}
			var instance = CKEDITOR.instances[id];
			if(instance){CKEDITOR.destroy(instance);}
			var editor = CKEDITOR.replace(id,{
				height:e,enterMode : CKEDITOR.ENTER_BR,
        		customConfig : '/_scripts/cms/ckeditor_config.js'
				<?php
					/* Change toolbar for PRODUCT module */
					if($cms->p == "products"){echo ',toolbar:"FullMedium"';}
				?>,
				on: {
					instanceReady : function(evt) {
						if(bg != "") {
							CKEDITOR.instances[id].document.getBody().setStyle("background-color",bg);
						}
					}
				}
			});
		}

		$(document).ready(function() {
			/* Initializing colorPicker	*/
		 	$('#colorPicker').colorpicker({color:"000000",colorFormat:"HEX",showOn: 'focus',showSwatches: true,showNoneButton: true,buttonColorize: true, parts: ['header','footer','map','bar'], altProperties: 'background-color,color',select:function(event,color){$(".colorPickerBox").eq(0).css("background-color",color.formatted);},regional:'<?php echo $cms->cmsL;?>'});

			/* Initializing datePicker */
			$("#datePicker,.datePicker").datepicker({ dateFormat: 'yy-mm-dd' });
			$("#datePickerPromo1").datepicker({
				dateFormat:"yy-mm-dd",
				minDate:0
			});
			$("#datePickerPromo2").datepicker({
				dateFormat:"yy-mm-dd",
				minDate:0,
				beforeShow:function(el,inst) {
					$(el).datepicker("option","minDate",$("#datePickerPromo1").val());
				}
			});
			$.datepicker.setDefaults($.datepicker.regional['<?php echo $cms->cmsL;?>']);

			/* Show info if set in Session */
				var infoSet = <?php echo $cms->is; ?>;
				if(infoSet == 1) {displayInfo('<?php echo $cms->infoT; ?>','<?php echo $cms->esc($cms->info);?>');infoSet=0;}



			/* Session Timer */
				<?php
					if($cms->automatic_logout == true) {
				echo 'setTimer(); ';
					}
				?>

			/* Extend session time */
				$("#sessionExtend").click(function() {
					var now = new Date();
					now.setSeconds(now.getSeconds()  + <?php echo $cms->session_time; ?>);
					$("#timer").countdown("option",{until:now});
					$.post("/ajax/time");
				});

			/* Position change */
				$(".pos-in").each(function() {
					$(this).change(function() {
						var t = '<?php echo $cms->p; ?>,move,'+$(this).attr("name").split("-")[1]+',pos,'+$(this).val();
                        $.post("/ajax/getlink", {"t" : t}, function(d) {
                            window.location.href = d;
                        });
					});
				});

			/* Password change */
				$("#changePass").click(function() {
					$("#changePassDiv").fadeIn(500);
				});

				$("#changePassClose").click(function() {
					$("#changePassDiv").fadeOut(500);
				});

				$("#changePassB").click(function() {
					var oldP = $("#oldPass").val();
					var newP = $("#newPass").val();
					var rr;
					var t = "error";
					if(oldP == "" || newP == "") {
						rr = "<?php echo $cms->translate(23); ?>";
					}
					else if(newP.length < 6) {
						rr = "<?php echo $cms->translate(227); ?>";
					}
					else {
						$.post("/ajax/pchange",{id:<?php echo $_SESSION["userId"];?>,"new":newP,"old":oldP},function(data) {
							var r = data*1;
							if(r == 1) {
								rr ="<?php echo $cms->translate(228); ?>";
								t = "ok";
								$("#changePassDiv").delay(3000).fadeOut(500);
							}
							else if(r == 2) {
								rr = "<?php echo $cms->translate(27); ?>";
							}
							else {
								rr = "<?php echo $cms->translate(229); ?>";
							}
						});
					}
					displayInfo(t,rr);
				});

			$(".menu0").each(function() {
				$(this).stop().hover(function() {
					$(this).not(".active").find(".hoverOn").eq(0).fadeOut(300);
					$(this).not(".active").find(".hoverOff").eq(0).fadeIn(300);
					$(this).find(".submenu").eq(0).fadeIn(300);
				},function() {
					$(this).not(".active").find(".hoverOff").eq(0).fadeOut(300);
					$(this).not(".active").find(".hoverOn").eq(0).fadeIn(300);
					$(this).find(".submenu").eq(0).fadeOut(300);
				});
			});

			$(".topLink").each(function() {
				$(this).stop().hover(function() {
					$(this).stop().animate({top:0});
				},function(){
					$(this).stop().animate({top:-26});
				});
			});

			$(".table tr").each(function() {
				$(this).find("td").first().css("border-left","0px");
				$(this).find("td").last().css("border-right","0px");
			});

			$(".menu0.active").eq(0).find(".hoverOn").eq(0).fadeOut(10);
			$(".menu0.active").eq(0).find(".hoverOff").eq(0).fadeIn(10);

			$(".itemWrap").find("input, select, textarea, option").each(function() {
				$(this).focus(function() {
					$(this).parent().addClass("focus");
				});
				$(this).blur(function() {
					$(this).parent().removeClass("focus");
				});
			});

			/* Checkboxes handling */
			$(".checkElement, .checkElementSmall").each(function() {
				$(this).click(function() {
					if($(this).hasClass("checked") && $(this).find(".disabledOverlay").length == 0){
						$(this).removeClass("checked");
						$(this).find("input").eq(0).prop("checked",false);
						$(this).find("input").eq(0).removeAttr("checked");
					}
					else {
						$(this).addClass("checked");
						$(this).find("input").eq(0).prop("checked",true);
					}
				});
			});

			$(".itemElement.file").each(function() {
				$(this).change(function() {
					$(this).parent().find(".itemElementFileName").eq(0).text($(this).val());
				});
			});

			// Overlay
			/*$(document).on("click",function(e) {
				if($(e.target).parents().is("#overlay-wrap") || $(e.target).hasClass("overlay-build")) {}
				else {
					closeOverlay();
				}
			}); */
			//Positioning of loading image
			$("#overlay-load").css("margin-top",($(window).height - $("#overlay-load").height()) / 2);

			// Close overlay
			$("#overlay-close").click(function() {
				closeOverlay();
			});

			// Buttons
			$("#buttons").css("padding-left", (($("#buttons").parent().width() - (($("#buttons").find(".greenButtonLarge").length * 150) + (($("#buttons").find(".greenButtonLarge").length-1) * 20))) / 2));
		});

		function closeOverlay() {
			$("#overlay-wrap").fadeOut(500,function() {
				$("#overlay-load").css("display","block");
				$("#overlay-wrap-inner").css("display","none");
				$("body").css("overflow","auto");
				if(typeof(overlayCallback) == typeof(Function)) {
					overlayCallback;
				}
			});
		}

		function setTimer() {
			var now = new Date();
			now.setSeconds(now.getSeconds()  + <?php echo $cms->session_time; ?>);
			$("#timer").countdown({until: now, format: 'HMS',layout:'{hnn}:{mnn}:{snn}',expiryUrl:"/logout"});
			$.post("/ajax/time");
		}
	</script>
</head>
<body>
	<div id="infowrap"></div>
    <div id="overlay-wrap">
    	<div id="overlay-wrap-bg"></div>
        <div id="overlay-load"><img src="/_images_cms/load.gif" /></div>
    	<div id="overlay-wrap-inner">
        	<div id="overlay-top">
	        	<div id="overlay-title"><?php echo $cms->translate(409); ?></div>
	            <div id="overlay-close">x</div>
                <div id="overlay-action"><?php echo $cms->translate(410); ?></div>
            </div>
            <div id="overlay-body"></div>
        </div>
    </div>
	<div id="top">
    	<div id="topIn">
        	<a href="<?php echo $cms->get_link("start"); ?>" title="Lemon-Art" id="logoLink">&nbsp;</a>
        	<div id="site">
            	<a href="<?php echo $cms->cw; ?>" target="_blank"><?php echo $cms->cn; ?></a><br /><br />
                <a href="<?php echo $cms->cw; ?>" class="greenButtonSmall" target="_blank"><?php echo $cms->translate(363); ?></a>
            </div>
            <div id="langs">
            	<div id="langsText"><?php echo $cms->translate(238); ?>:</div>
                	<?php
						$cms->executeQuery("SELECT * FROM cms_langs WHERE added='1' ORDER BY position ASC",1);
						while($row = mysqli_fetch_assoc($cms->result1)) {
							echo'<a href="'.$cms->get_link($cms->p,$row["shortLang"]).'" class="l'.($cms->lang == $row["shortLang"] ? ' active':'').'" title="'.$row["longLang"].'"><img src="/_images_cms/langs/'.$row["shortLang"].'.png" alt="'.$row["longLang"].'"/></a>';
						}
					?>
            </div>
			<?php //echo $cms->translate(7); ?>
            <div id="loggedas">
                <?php echo $cms->translate(240).' <span id="name">'.$_SESSION["userName"].'</span> <span id="rank" style="color:#'.$_SESSION["userColor"].';">('.$_SESSION["userRank"].')</span>'; ?>
            </div>
            <?php
				if($cms->automatic_logout == true) {
			?>
            <a href="#" id="sessionExtend" class="topLink" title="<?php echo $cms->translate(239); ?>">
                <span id="timer"></span>
            </a>
            <?php
				}
			?>
            <!--<a href="/LemonCMSv<?php echo $cms->version;?>-MANUAL(1.0).pdf" id="linkGreen" class="topLink" target="_blank">
            	<span>MANUAL</span>
            </a>-->
            <a href="/logout" id="linkRed" class="topLink">
            	<span><?php echo $cms->translate(8);?></span>
            </a>
        </div>
    </div>
    <ul id="menu">
    	<?php
			$cms->executeQuery("SELECT * FROM cms_modules_groups cmg ORDER BY position ASC",1);
			while($row = mysqli_fetch_assoc($cms->result1)) {
				if($row["status"] == 1) {
					$mgId = $row["id"];
					$mgName = $row["name_".$cms->cmsL];
					echo '<li class="menu0'.($mgId==$cms->mG?' active':'').'" id="menu0-'.$mgId.'">
							<div class="hoverOn" id="menu'.$mgId.'-on">'.$mgName.'</div>
							<div class="hoverOff" id="menu'.$mgId.'-off">'.$mgName.'</div>';
						if($cms->getCount("cms_modules","WHERE moduleGroup='$mgId'") > 0) {
							echo '<div class="submenu">';
							$and = $_SESSION["userRankId"]!= 1 ? "AND lemonOnly='0'" : "";
							$cms->executeQuery("SELECT * FROM cms_modules WHERE moduleGroup='$mgId' ".$and." ORDER BY position ASC",2);
							while($row2 = mysqli_fetch_assoc($cms->result2)) {
								echo '<a href="'.$cms->get_link($row2["shortName"]).'"'.($cms->p == $row2["shortName"] ? 'class="active"' : '').'><strong>&#8226; </strong>'.$row2[$cms->cmsL."Name"].'</a>';
							}
							echo '</div>';
						}
					echo '</li>';
				}
			}
		?>
    </ul>
    <div id="middle">
    	<div id="breadcrumbs" style="display:<?php echo $cms->p=="start"?'none':'block'?>;">
        	<span id="bread1">CMS</span> &gt; <span id="bread2"><?php echo mb_strtoupper($cms->lang,"UTF8");?></span> &gt; <a id="bread3" href="<?php echo $cms->get_link($cms->p); ?>"><?php echo mb_strtoupper($cms->pB,"UTF8");?></a> &gt; <a id="bread4" href="<?php echo $cms->get_link($cms->p.','.$cms->a); ?>"><?php echo mb_strtoupper($cms->aN,"UTF8");?>
            </a>
        </div>
		<?php
			if(file_exists($cms->incP)) {
				include_once($cms->incP);
			}
			else {
				echo '<div id="noModuleFile">'.$cms->translate(312).'</div>';
			}
		?>
    </div>
    <div id="bottom">
    	<?php
        	if($cms->acm == true) {
				echo '<span id="leaf">Design and code by LemonCMS Team for <a href="http://www.absolutecreative.co.uk">Absolute Creative Media</a></span>';
			}
			else {
				echo '<a href="http://www.lemon-art.pl" id="leaf">Design and coding by Lemon-Art</a>';
			}
		?>
        <a href="<?php echo $cms->cw;?>" id="company">© Copyright <?php echo '2011 - '.date("Y").' '.($cms->acm == true ? "Absolute Creative" : "Lemon-Art").' for '.$cms->cn;?></a>
    </div>
</body>
</html>
<?php $cms->clearBuffer(); ?>