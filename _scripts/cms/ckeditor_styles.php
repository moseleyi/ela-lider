<?php
	header('Content-type: text/css');	
	include_once("../../class.cmscontrol.php");
	$cms = new cmsControl(); 
	$cms->executeQuery("SELECT * FROM cms_fonts WHERE status='1'",1);
	$main = '';
	while($row = mysqli_fetch_assoc($cms->result1)) { 
		if($row["system"] == 0) {
            if($row["external"] == 1) {
                echo '@import url("'.$row["url"].'");';
            }
            else {
                echo '
                @font-face {
                    font-family: "'.$row["name_css"].'";
                    src:    url("../../_fonts/'.$row["name_css"].'.woff2") format("woff2"),
                            url("../../_fonts/'.$row["name_css"].'.woff") format("woff");
                    font-weight: normal;
                    font-style: normal;
                }
                ';
            }
		}
		if($row["id"] == $cms->ckeditor_mainfont) {
			$main = $row["name_css"];
		} 
	}
echo '								
body{background-color:#'.$cms->ckeditor_bgcolor.';font-family:'.(!empty($cms->ckeditor_mainfont)?'"'.$main.'",':'').'Arial !important;font-size:'.$cms->ckeditor_default_font_size.'px;} 
body .cke_panel_list{font-family:Arial;}
span.cke_skin_kama{border:0px !important;}
table{border-collapse:collapse;}
td{border:1px dotted #333;}
 .colour-red{color:#ac1204;} .colour-yellow{color:#f4e707;}
.colour-white{color:white;text-shadow: 1px 1px 3px #000000;}
.lightbox img{border:1px dotted red;}';
	$cms->executeQuery("SELECT * FROM cms_ckeditor ORDER BY name ASC",1);
	while($row = mysqli_fetch_assoc($cms->result1)) { 
echo '
.'.$row["className"].'{'.$row["styles"].'}'; 
	}
?>