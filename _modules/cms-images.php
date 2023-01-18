<script type="text/javascript">
	$(document).ready(function() {    
		lemon.object_delete(".delDimension","<?php echo $cms->translate(493); ?>", "name", "del");
	});
</script>
<?php
	$cms->checkAccess();  
	switch($cms->a) { 
		case "list":
		default:
			echo '	<div id="moduleWrap">
						<a href="'.$cms->get_link("images,add").'" class="greenButtonWide ">'.$cms->translate(21).'</a><div class="c"></div><br />
						<table class="table">
							<tr> 
								<td class="head" width="40px">Lp.</td>
								<td class="head" width="200px">'.$cms->translate(148).'</td>
								<td class="head">'.$cms->translate(130).'</td> 
								<td class="head" width="120px">'.$cms->translate(384).'</td>
								<td class="head" width="120px">'.$cms->translate(131).'</td>
								<td class="head" width="60px">'.$cms->translate(14).'</td>
								<td class="head" width="60px">'.$cms->translate(15).'</td>
							</tr>'; 
				$i = 1;
				$cms->executeQuery("SELECT *,cid.id AS id FROM cms_image_dimensions cid INNER JOIN cms_modules cm ON cid.moduleId = cm.id ".($_SESSION["userRankId"] != 1 ? "WHERE name != 'Lemon Thumb'":"")."ORDER BY cm.".$cms->cmsL."Name ASC, cid.width ASC, cid.height ASC",1);
				while($row = mysqli_fetch_assoc($cms->result1)) {  
					echo '<tr> 
							<td class="body lvl0"><strong>'.$i.'.</strong></td>
							<td class="body lvl0">'.$row[$cms->cmsL."Name"].'</td>
							<td class="body lvl0 alignleft">'.$row["name"].'</td> 
							<td class="body lvl0">'.$row["quality"].'%</td>
							<td class="body lvl0">'.$row["width"].'px x '.$row["height"].'px</td>
							<td class="body lvl0"><a href="'.$cms->get_link("images,edit,".$row["id"]).'" class="link_edit plink">&nbsp;</a></td>
							<td class="body lvl0">'.($row["name"]!='Lemon Thumb'?'<a href="#" class="delDimension link_delete plink" name="'.$row["id"].'">&nbsp;</a>':'').'</td>
						</tr>';
					$i++;
				}
				echo '	</table>
					</div> ';
		break;
		case "main":
			$cms->executeQuery("SELECT * FROM cms_image_dimensions WHERE id='$cms->id'",1);
			$row = mysqli_fetch_assoc($cms->result1);
			$module_id = $row["moduleId"];
			$name = $row[$cms->cmsL."Name"];
			$cms->executeQuery("UPDATE cms_image_dimensions SET main = 0 WHERE moduleId='$module_id'",2);
			if($cms->result2) {
				$cms->executeQuery("UPDATE cms_image_dimensions SET main = 1 WHERE id='$cms->id'",3);
				if($cms->result3) {
					$cms->saveAction($name,"");
					$cms->setSessionInfo(false, $cms->translate(403));
				}
				else {
					$cms->setSessionInfo(false, $cms->translate(27));
				}
			}
			else {
				$cms->setSessionInfo(false, $cms->translate(27));
			}
			header("Location:".$cms->get_link("images"));
		break;
		case "add":
		case "edit":
			/*** EDIT ***/
			if($cms->a == "edit") {
				$cms->executeQuery("SELECT cid.*, cm.shortName FROM cms_image_dimensions cid INNER JOIN cms_modules cm ON cid.moduleId = cm.id WHERE cid.id='$cms->id'",2);
				$row = mysqli_fetch_assoc($cms->result2);
				$name = $row["name"];
				$width = $row["width"];
				$height = $row["height"];
				$moduleId = $row["moduleId"]; 
				$portrait = $row["portrait"];
				$landscape = $row["landscape"];
				$width_old = $row["width"];
				$height_old = $row["height"];
				$module_old = $row["moduleId"];
				$w = $row["width"];
				$h = $row["height"];
				$m = $row["shortName"];
				$quality = $row["quality"];
			}
			else {
				$name = $width = $height = $moduleId = $portrait = $landscape = $width_old = $height_old = $module_old = $quality = '';
			}
			if(isset($_POST["submit"])) {
				$width = $_POST["width"];
				$height = $_POST["height"];
				$moduleId = $_POST["moduleId"];
				$portrait = $_POST["portrait"];
				$landscape = $_POST["landscape"];
				$quality = $_POST["quality"];
				$name = $_POST["name"]; 
				if((empty($width) && empty($height)) || empty($moduleId) || empty($name)) {
					$cms->setInfo(false,$cms->translate(23)); 
				}
				elseif($quality == 0) {
					$cms->setInfo(false, $cms->translate(385));
				}
				elseif((!empty($width) && is_numeric($width) == false) || (!empty($height) && is_numeric($height) == false)) {
					$cms->setInfo(false,$cms->translate(138)); 
				}
				else {
					if($cms->getCount("cms_image_dimensions","WHERE moduleId='$moduleId' AND width='$width' AND height='$height' AND id !='$cms->id' AND name !='Lemon Thumb'") > 0 && ($width_old != $width || $height_old != $height || $module_old != $module) && $cms->a == "add") {
						$cms->setInfo(false,$cms->translate(284)); 
					}
					else {  
						/*** ADD ***/
						if($cms->a == "add") { 
							$cms->executeQuery("SELECT shortName FROM cms_modules WHERE id='$moduleId'",2);
							$row = mysqli_fetch_assoc($cms->result2);
							$module = $row["shortName"];
							$dir = $name == "Lemon Thumb" ? "_lemon" : (int)$width.'x'.(int)$height;
							if(mkdir("_images_content/".$module."/".$dir, 0777) == true) {
								$cms->executeQuery("INSERT INTO cms_image_dimensions (`id`, `name`, `width`, `height`, `portrait`, `landscape`, `moduleId`, `quality`) VALUES ('', '$name', '$width', '$height', '$portrait', '$landscape', '$moduleId', '$quality')",1);
								if($cms->result1) {
									$cms->setSessionInfo(true,$cms->translate(285));
									header("Location:".$cms->get_link("images"));
								}
								else {
									$cms->setInfo(false,$cms->translate(27)); 
								}
							}
							else {
								$cms->setInfo(false,$cms->translate(286)); 
							} 
						}
						/*** EDIT ***/
						else {
							$e = false;
							if(($w != $width || $h != $height) && $row["name"] != "Lemon Thumb") { 
								$cms->executeQuery("SELECT shortName FROM cms_modules WHERE id='$moduleId'",1);
								$row = mysqli_fetch_assoc($cms->result1);
								$module = $row["shortName"];
								if(rename("_images_content/".$m."/".$w."x".$h, "_images_content/".$module."/".$width."x".$height) == false) {
									$e = true;
								}
							}
							if($e == false) {
								$cms->executeQuery("UPDATE cms_image_dimensions SET name='$name',width='$width',height='$height',portrait='$portrait',landscape='$landscape',moduleId='$moduleId',quality='$quality' WHERE id='$cms->id'",1);
								if($cms->result1) {
									$cms->setSessionInfo(true,$cms->translate(287)); 
									header("Location:".$cms->get_link("images"));
								}
								else {
									$cms->setInfo(false,$cms->translate(27)); 
								}
							}
							else {
								$cms->setInfo(false,$cms->translate(286)); 
							}	
						}
					}
				}
			}
			echo ' <form method="post" enctype="multipart/form-data" action="'.$cms->get_link("images,".$cms->a.''.($cms->a=="edit"?','.$cms->id:'')).'"> 
					<div id="addColumnMain">
						<div class="addColumn narrow left extraPad">
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(130).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"><input type="text" class="itemElement text" name="name" value="'.$name.'" /></div>
									<div class="itemType vital" title="'.$cms->translate(241).'">!</div>
								</div>
							</div>
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(133).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"><input type="text" class="itemElement text" name="width" value="'.$width.'" /></div>
									<div class="itemType helpful" title="'.$cms->translate(242).'">?</div>
								</div>
							</div>
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(132).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"><input type="text" class="itemElement text" name="height" value="'.$height.'" /></div>
									<div class="itemType helpful" title="'.$cms->translate(242).'">?</div>
								</div>
							</div>
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(384).' (1-100)</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"><input type="text" class="itemElement text" name="quality" value="'.$quality.'" /></div>
									<div class="itemType vital" title="'.$cms->translate(241).'">!</div>
								</div>
							</div>
						</div>
						<div class="addColumn narrow right">
							<div class="itemWrap">
								<div class="itemLabel">'.$cms->translate(148).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow">
									<select class="itemElement select" name="moduleId">
										<option value="">----- '.$cms->translate(20).' -----</option>';
										$cms->executeQuery("SELECT * FROM cms_modules WHERE imagesPossible='1' ORDER BY ".$cms->cmsL."Name ASC",1);
										while($row = mysqli_fetch_assoc($cms->result1)) {
											echo '<option value="'.$row["id"].'"';
											if($moduleId == $row["id"]) {
												echo ' selected="selected"';
											}
											echo '>'.$row[$cms->lang."Name"].'</option>';
										}
								echo '		</select>
									</div>
									<div class="itemType vital" title="'.$cms->translate(241).'">!</div>
								</div>
							</div>	 
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(280).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow">
										<select name="portrait" class="itemElement select">
											<option value="">----- '.$cms->translate(20).' -----</option>
											<option value="fit"'.($portrait == "fit" ? ' selected="selected"' : '').'>'.$cms->translate(283).'</option>
											<option value="fill"'.($portrait == "fill" ? ' selected="selected"' : '').'>'.$cms->translate(282).'</option>
										</select>
									</div>
									<div class="itemType optional" title="'.$cms->translate(243).'">?</div>
								</div>
							</div>
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(281).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow">
										<select name="landscape" class="itemElement select">
											<option value="">----- '.$cms->translate(20).' -----</option>
											<option value="fit"'.($landscape == "fit" ? ' selected="selected"' : '').'>'.$cms->translate(283).'</option>
											<option value="fill"'.($landscape == "fill" ? ' selected="selected"' : '').'>'.$cms->translate(282).'</option>
										</select>
									</div>
									<div class="itemType optional" title="'.$cms->translate(243).'">?</div>
								</div>
							</div>
						</div>
						<div class="c"></div><br />
						<input type="submit" name="submit" class="greenButtonLarge " value="'.$cms->translate($cms->a == "add" ? 21 : 14).'" />
					</div>
				</form>';
		break;
		case "del":  
				$cms->executeQuery("SELECT * FROM cms_image_dimensions cid INNER JOIN cms_modules cm ON cid.moduleId = cm.id WHERE cid.id='$cms->id'",1);
				$row = mysqli_fetch_assoc($cms->result1);
				$w = $row["width"];
				$h = $row["height"];
				$m = $row["shortName"]; 
			$cms->executeQuery("DELETE FROM cms_image_dimensions WHERE id='$cms->id'",1);
			if($cms->result1) {
				$cms->setSessionInfo(true,$cms->translate(279));
				// Deleting the folder with images 
				$dir = "_images_content/".$m."/".$w."x".$h;
				$dh  = opendir($dir);
				$e = 0;
				while (false !== ($f = readdir($dh))) {
					if($f != "." && $f != "..") {
						if(unlink($dir."/".$f) == false) {
							$e++;
						}
					}
				}
				if($e == 0) {
					rmdir($dir);
					echo $dir;
				} 
			}
			else {
				$cms->setSessionInfo(false,$cms->translate(27));
			}
			header("Location:".$cms->get_link("images")); 
		break;
	}
?>