<?php
	header("Content-type: text/css");
	include_once("class.cmscontrol.php");
	$cms = new cmsControl();
	$cms->executeQuery("SELECT * FROM cms_fonts WHERE status='1' AND system = 0",1);
	$main = '';
	$r = '';
	while($row = mysqli_fetch_assoc($cms->result1)) {

		if($row["system"] == 0) {
            if($row["external"] == 1) {
                echo '@import url("'.$row["url"].'");';
            }
            else {
                echo '
                @font-face {
                    font-family: "'.$row["name_css"].'";
                    src:    url("../_fonts/'.$row["name_css"].'.woff2") format("woff2"),
                            url("../_fonts/'.$row["name_css"].'.woff") format("woff");
                    font-weight: normal;
                    font-style: normal;
                    font-display:swap;
                }
                ';
            }
		}
	}
	$cms->executeQuery("SELECT * FROM cms_ckeditor ORDER BY name ASC",1);
	while($row = mysqli_fetch_assoc($cms->result1)) {
		if($row["styles"] != "") {
			$r .= '.'.$row["className"].'{'.$row["styles"].'}';
		}
	}
	echo $cms->compressHTML($r);
?>