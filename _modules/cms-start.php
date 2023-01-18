<?php	
	if(isset($_POST["submit"])) {
		$content = $_POST["content"];
		$cms->executeQuery("UPDATE cms_settings SET featureValue='$content' WHERE id='59'",1);
		if($cms->result1) {
			$cms->setSessionInfo(true,$cms->translate(268));
		}
		else {
			$cms->setInfo(true, $cms->translate(27));
		}
	}
	$cms->executeQuery("SELECT * FROM cms_settings WHERE id='59'",1);
	$row = mysqli_fetch_assoc($cms->result1);
	$cms->promo_html = $row["featureValue"];
	echo '
    	<script type="text/javascript" src="/_scripts/cms/jquery.tinyscrollbar.min.js"></script>
		<script type="text/javascript">
			$(document).ready(function() {
				$("#startScroll").tinyscrollbar();
				$("#startScrollWrap").animate({top:90},1000);	
			});
		</script>';
	/* Get last SQL file */
	if(is_dir("_sql")) {
		if($dh = opendir("_sql")) {
			$i = 1;
			while(($file = readdir($dh)) != false) {
				if($file != "." && $file != ".." && $file != "") { 
						list($a,$b,$c) = explode("_",$file); 
						$files[$i]["file"] = $file;
						$files[$i]["name"] = $cms->convertDate($b,false,$cms->cmsL).'';
						$files[$i]["time"] = str_replace(".sql","",$c);
						$i++; 
				}
			}
		}
	}
	rsort($files); 
	$lastfile = '<a href="/_sql/'.$files[0]["file"].'" target="_blank">'.$files[0]["name"].' ('.$files[0]["time"].')</a>';
	switch($cms->cmsL) {
		case "pl":
			echo '	<div id="start"> 
					<div id="startWrap">
						Witamy w panelu administracyjnym Lemon-Art CMS v'.$cms->version.'
						'.($cms->acm == false ? '<Br />Wszystkie prośby i sugestie prosimy kierować na adres:<br /><br /><a href="mailto:admin.cms@lemon-art.pl" id="startLink">admin.cms@lemon-art.pl</a>' : '').'
					</div>';
					if($cms->promo_html != "") {
						echo '
					<div id="startScrollWrap">
						<div id="startScroll">
							<div class="scrollbar">
								<div class="track"><div class="thumb"><div class="end"></div></div></div>
							</div>
							<div class="viewport">
								<div class="overview">
									'.$cms->promo_html.'
								</div>
							</div>    
						</div>
					</div>';
					}
					echo '
					<div id="dbBackupFile">
						<div>
							'.$cms->translate(340).' '.$lastfile.'
						</div>
					</div>
					 <img src="/_images_cms/img-logo.png" id="startLogo" />
					 <img src="/_images_cms/img-start.jpg" />
					</div>';
		break;
		case "en":
			echo '<div id="start"> 	
					<div id="startWrap">
						Welcome to the Content Management System Lemon CMS v'.$cms->version.'
						'.($cms->acm == false ? '<Br />Any requests and suggestions please send to:<br /><br /><a href="mailto:admin.cms@lemon-art.pl" id="startLink">admin.cms@lemon-art.pl</a>' : '').'
					</div>';
					if($cms->promo_html != "") {
						echo '
					<div id="startScrollWrap">
						<div id="startScroll">
							<div class="scrollbar">
								<div class="track"><div class="thumb"><div class="end"></div></div></div>
							</div>
							<div class="viewport">
								<div class="overview">
									'.$cms->promo_html.'
								</div>
							</div>    
						</div>
					</div>';
					}
					echo '
					<div id="dbBackupFile">
						<div>
							'.$cms->translate(340).' '.$lastfile.'
						</div>
					</div>
					 <img src="/_images_cms/img-logo.png" id="startLogo" />
					 <img src="/_images_cms/img-start.jpg" />
				</div>	';
		break;
	}  
	// Lemon-Art only
	if($_SESSION["userRankId"] == 1) {
		echo '	<form method="post" enctype="multipart/form-data" action="/cms/'.$cms->lang.'/start">	
					<div id="moduleWrap">  <br />
						<div class="itemElementWrap">
							<div class="itemElementShadow"><textarea name="content" class="itemElement area" id="content">'.htmlspecialchars($cms->promo_html).'</textarea></div>
							<script type="text/javascript">loadEditor(\'content\',\'300px\');</script>
							<div class="itemType helpful" title="'.$cms->translate(242).'">?</div>
						</div> <br />
						<input type="submit" value="'.$cms->translate(14).'" class="greenButtonLarge" name="submit" />	 
					</div>
				</form>';
	}
?>