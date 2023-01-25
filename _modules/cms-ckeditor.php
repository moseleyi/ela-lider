<script>
	$(document).ready(function() {
		$(".configChange").each(function() {
			$(this).change(function() {
				postConfig($(this).val(),$(this).prop("name"));
			});
		});
		$("#colorPicker").blur(function() {
				postConfig($(this).val(),$(this).prop("name"));
		});
		lemon.object_delete(".delStyle","<?php echo $cms->translate(491); ?>", "name", "del");
	});
	function postConfig(v,t) {
		$.post("<?php echo $cms->get_link("ckeditor,font");?>",{"id":v,"t":t},function(data) {
			var r = $(data).find("#ajaxResult").eq(0).html(), c = "";
			if(r == "1") {
				displayInfo("ok","<?php echo $cms->translate(268);?>");
				c = "green";
			}
			else {
				displayInfo("error","<?php echo $cms->translate(27);?>");
				c = "red";
			}
			c = c == "red" ? "#e5bcbc" : "#bce5bc";
			$("#"+id).find("td").css("background-color",c).css("font-weight","bold");
			setTimeout(function(){$("#"+id).find("td").css("background-color","").css("font-weight","");},3000);
		});
	}
</script>
<?php
	$cms->checkAccess();
	switch($cms->a) {
		case "font":
			$id = $_POST["id"];
			$t = $_POST["t"];
			$cms->executeQuery("UPDATE cms_settings SET featureValue='$id' WHERE featureName='$t'",1);
			if($cms->result1) {
				echo '<div id="ajaxResult">1</div>';
			}
			else {
				echo '<div id="ajaxResult">2</div>';
			}
		break;
		default:
		case "list":
			echo '<div id="addColumnMain">
					<div class="addColumn narrow left">
						<div class="itemWrap">
							<div class="itemLabel">'.$cms->translate(256).'</div>
							<div class="itemElementWrap">
								<div class="itemElementShadow">
									<input class="itemElement text" name="ckeditor_bgcolor" value="'.$cms->ckeditor_bgcolor.'" id="colorPicker" />
								</div>
								<div class="colorPickerBox" style="background-color:#'.$cms->ckeditor_bgcolor.';">&nbsp;</div>
								<div class="itemType optional" title="'.$cms->translate(243).'">?</div>
							</div>
						</div>
						<div class="itemWrap">
							<div class="itemLabel">'.$cms->translate(257).'</div>
							<div class="itemElementWrap">
								<div class="itemElementShadow">
									<select class="itemElement select configChange" name="ckeditor_mainfont">
										<option value="0">-----'.$cms->translate(20).' ------</option>';
										$cms->executeQuery("SELECT * FROM cms_fonts WHERE status='1' ORDER BY name ASC",1);
										while($row = mysqli_fetch_assoc($cms->result1)) {
											echo '<option value="'.$row["id"].'"'.($cms->ckeditor_mainfont == $row["id"] ? 'selected="selected"' : '').'>'.$row["name"].'</option>';
										}
					echo '
									</select>
								</div>
								<div class="itemType optional" title="'.$cms->translate(243).'">?</div>
							</div>
						</div>
						<div class="itemWrap">
							<div class="itemLabel">'.$cms->translate(486).'</div>
							<div class="itemElementWrap">
								<div class="itemElementShadow">
									<select class="itemElement select configChange" name="ckeditor_default_font_size">
										<option value="11"'.($cms->ckeditor_default_font_size == 11 ? 'selected="selected"' : '').'>11px</option>
										<option value="12"'.($cms->ckeditor_default_font_size == 12 ? 'selected="selected"' : '').'>12px</option>
										<option value="13"'.($cms->ckeditor_default_font_size == 13 ? 'selected="selected"' : '').'>13px</option>
										<option value="14"'.($cms->ckeditor_default_font_size == 14 ? 'selected="selected"' : '').'>14px</option>
										<option value="15"'.($cms->ckeditor_default_font_size == 15 ? 'selected="selected"' : '').'>15px</option>
										<option value="16"'.($cms->ckeditor_default_font_size == 16 ? 'selected="selected"' : '').'>16px</option>
										<option value="17"'.($cms->ckeditor_default_font_size == 17 ? 'selected="selected"' : '').'>17px</option>
										<option value="18"'.($cms->ckeditor_default_font_size == 18 ? 'selected="selected"' : '').'>18px</option>
									</select>
								</div>
								<div class="itemType optional" title="'.$cms->translate(243).'">?</div>
							</div>
						</div>
						<div class="itemWrap">
							<div class="itemLabel">'.$cms->translate(258).'</div>
							<div class="itemElementWrap">
								<div class="itemElementShadow">
									<select class="itemElement select configChange" name="ckeditor_toolbar">
										<option value="">----- '.$cms->translate(20).' ------</option>
										<option value="Basic"'.($cms->ckeditor_toolbar=="Basic"?' selected="selected"':'').'>Basic</option>
										<option value="Full"'.($cms->ckeditor_toolbar=="Full"?' selected="selected"':'').'>Full</option>
									</select>
								</div>
								<div class="itemType optional" title="'.$cms->translate(243).'">?</div>
							</div>
						</div>
						<div class="itemWrap">
							<div class="itemLabel">'.$cms->translate(259).'</div>
							<div class="itemElementWrap">
								<div class="itemElementShadow">
									<textarea name="ckeditor_fontsizes" class="itemElement area configChange">'.$cms->ckeditor_fontsizes.'</textarea>
								</div>
								<div class="itemType optional" title="'.$cms->translate(243).'">?</div>
							</div>
							<div class="itemComment">Values separated by semicolon <strong>;</strong><br /> If you need special label use: label/size;label/size</div>
						</div>
					</div>
					<div class="addColumn wide right">
						<a href="'.$cms->get_link("ckeditor,add").'" class="greenButtonSmall  centerContent" style="width:80px;">'.$cms->translate(21).'</a><div class="c"></div><br />
						<table width="690px" align="center">
							<tr>
								<td class="head" width="170px">'.$cms->translate(386).'</td>
								<td class="head" width="169px">'.$cms->translate(387).'</td>
								<td class="head" width="169px">'.$cms->translate(388).'</td>
								<td class="head" width="70px">'.$cms->translate(14).'</td>
								<td class="head" width="70px">'.$cms->translate(15).'</td>
							</tr>';
					$i = 1;
					$cms->executeQuery("SELECT * FROM cms_ckeditor ORDER BY name ASC",1);
					while($row = mysqli_fetch_assoc($cms->result1)) {
						echo '<tr>
								<td class="body lvl0 alignleft"><strong><span class="red">'.strtoupper($row["element"]).'</span></strong> ('.$row["name"].')</td>
								<td class="body lvl0 alignleft"><div>'.str_replace('"',"",implode("</div><div>",explode(",",$row["styles"]))).'</div></td>
								<td class="body lvl0 alignleft"><div>'.str_replace('"',"",implode("</div><div>",explode(",",$row["attributes"]))).'</div></td>
								<td class="body lvl0"><a href="'.$cms->get_link("ckeditor,edit,".$row["id"]).'" class="link_edit plink">&nbsp;</a></td>
								<td class="body lvl0"><a href="#" class="delStyle link_delete plink" name="'.$row["id"].'">&nbsp;</a></td>
							</tr>';
						$i++;
					}
				echo '
						</table>
					</div>
					<div class="c"></div>
				  </div>';
		break;
		case "add":
		case "edit":
			/*** EDIT ***/
			if($cms->a == "edit") {
				$cms->executeQuery("SELECT * FROM cms_ckeditor WHERE id='$cms->id'",1);
				$row = mysqli_fetch_assoc($cms->result1);
				$name = $row["name"];
				$styles = $row["styles"];
				$element = $row["element"];
				$attr = $row["attributes"];
			}
			else {
				$name = $styles = $element = $attr = '';
			}
			if(isset($_POST["submit"])) {
				$name = $_POST["name"];
				$element = $_POST["element"];
				$styles = $_POST["styles"];
				$attr = $_POST["attr"];
				preg_match('/(\"[a-z\-]\")+/i','asdasdasd');
				if(empty($name) || empty($element)) {
					$cms->setInfo(false,$cms->translate(23));
				}
				elseif(!empty($styles) && substr_count($styles,':') == 0) {
					$cms->setInfo(false,"Please check syntax for styles2");
				}
				elseif(!empty($attr) && substr_count($attr,':') == 0) {
					$cms->setInfo(false, "Please check syntax for attributes");
				}
				else {
					$stylesAr = explode(";",$styles);
					$attrAr = explode(",",$attr);
					$e = 0;
					foreach($stylesAr as $stylesPair) {
						preg_match('/([a-z\-]{2,100}:[a-z\-\s0-9\"\#\(\)]{2,100})/i',$stylesPair,$m);
						if($m[0] != $stylesPair) {
							$e++;
						}
					}
					if($e > 0) {
						$cms->setInfo(false, "Please check syntax for styles4");
					}
					else {
						foreach($attrAr as $attrPair) {
							preg_match('/([a-z\-]{2,100}:[a-z\-\s0-9]{2,100})/i',$attrPair,$m);
							if($m[0] != $attrPair) {
								$e++;
							}
						}
						if($e > 0) {
							$cms->setInfo(false, "Please check syntax for attributes");
						}
						else {
							/*** ADD ***/
							if($cms->a == "add") {
								$className = 'ck_'.$element.'_'.($cms->getCount("cms_ckeditor","WHERE element='$element'")+1);
								$cms->executeQuery("INSERT INTO cms_ckeditor (`id`, `name`, `className`, `element`, `styles`, `attributes`) VALUES ('', '$name', '$className', '$element', '$styles', '".$cms->esc($attr)."')",1);
							}
							/*** EDIT ***/
							else {
								$cms->executeQuery("UPDATE cms_ckeditor SET name='$name', element='$element', styles='$styles', attributes='".$cms->esc($attr)."' WHERE id='$cms->id'",1);
							}
							if($cms->result1) {
								$cms->setSessionInfo(true,$cms->translate($cms->a == "add" ? 263 : 264));
								header("Location:".$cms->get_link("ckeditor"));
							}
							else {
								$cms->setInfo(false, $cms->translate(27));
							}
						}
					}
				}
			}
			echo ' <form method="post" enctype="multipart/form-data" action="'.$cms->get_link("ckeditor,".$cms->a.''.($cms->a=="edit"?','.$cms->id:'')).'">
				<div id="addColumnMain">
					<div class="addColumn narrow left">
	<div class="itemWrap ">
							<div class="itemLabel">'.$cms->translate(130).'</div>
							<div class="itemElementWrap">
								<div class="itemElementShadow"><input type="text" class="itemElement text" name="name" value="'.$name.'" /></div>
								<div class="itemType vital" title="'.$cms->translate(241).'">!</div>
							</div>
						</div>
						<div class="itemWrap ">
							<div class="itemLabel">'.$cms->translate(261).'</div>
							<div class="itemElementWrap">
								<div class="itemElementShadow">
									<textarea class="itemElement area" name="styles">'.$styles.'</textarea>
									<div class="itemType optional" title="'.$cms->translate(243).'">?</div>
								</div>
							</div>
						</div>
					</div>
					<div class="addColumn narrow right">
						<div class="itemWrap ">
							<div class="itemLabel">'.$cms->translate(260).'</div>
							<div class="itemElementWrap">
								<div class="itemElementShadow">
									<select name="element" class="itemElement select">
										<option value="">----- '.$cms->translate(20).' -----</option>
										<option value="div"'.($element == "div" ? ' selected="selected"' : '').'>DIV</option>
										<option value="image"'.($element == "image" ? ' selected="selected"' : '').'>IMAGE</option>
										<option value="li"'.($element == "li" ? ' selected="selected"' : '').'>LI</option>
										<option value="p"'.($element == "p" ? ' selected="selected"' : '').'>P</option>
										<option value="span"'.($element == "span" ? ' selected="selected"' : '').'>SPAN</option>
										<option value="table"'.($element == "table" ? ' selected="selected"' : '').'>TABLE</option>
										<option value="ul"'.($element == "ul" ? ' selected="selected"' : '').'>UL</option>
										<option value="h1"'.($element == "h1" ? ' selected="selected"' : '').'>H1</option>
										<option value="h2"'.($element == "h2" ? ' selected="selected"' : '').'>H2</option>
										<option value="h3"'.($element == "h3" ? ' selected="selected"' : '').'>H3</option>
										<option value="h4"'.($element == "h4" ? ' selected="selected"' : '').'>H4</option>
										<option value="h5"'.($element == "h5" ? ' selected="selected"' : '').'>H5</option>
									</select>
								</div>
								<div class="itemType vital" title="'.$cms->translate(241).'">!</div>
							</div>
						</div>
						<div class="itemWrap ">
							<div class="itemLabel">'.$cms->translate(262).'</div>
							<div class="itemElementWrap">
								<div class="itemElementShadow">
									<textarea class="itemElement area" name="attr">'.$attr.'</textarea>
									<div class="itemType optional" title="'.$cms->translate(243).'">?</div>
								</div>
							</div>
						</div>
					</div>
					<div class="c"><br /></div>
					<input type="submit" value="'.$cms->translate($cms->a=="add"?21:14).'" class="greenButtonLarge" name="submit" />
				</div>
			</form>';
		break;
		case "del":
			$cms->executeQuery("DELETE FROM cms_ckeditor WHERE id='$cms->id'",1);
			if($cms->result1) {
				$cms->setSessionInfo(true,$cms->translate(265));
			}
			else {
				$cms->setSessionInfo(false,$cms->translate(27));
			}
			header("Location:".$cms->get_link("ckeditor"));
		break;
	}
?>