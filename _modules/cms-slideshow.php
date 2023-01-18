<script type="text/javascript">
	$(document).ready(function() { 
		lemon.object_delete(".delSlideshow","<?php echo $cms->translate(58); ?>", "name", "del"); 
		lemon.object_delete(".delSlidePicture","<?php echo $cms->translate(44); ?>", "name", "delimg");
		lemon.image_edit();
		lemon.crop.construct("multi");
		lemon.image_sorting();
	});
</script>
<?php
	$cms->checkAccess(); 
	// 	if there's only one gallery redirect to EDIT with ID 0
	if($cms->slid_ms == false && (!in_array($cms->a, array("editimg","delimg","editd")) || ($cms->a == "edit" && $cms->id != 0))) {   
		header("Location:".$cms->get_link("slideshow,editimg,0")); 
	} 
	// If multiple galleries are switched on refuse if ID == 0
	elseif($cms->slid_ms == true) {
		if($cms->id === 0 && isset($_GET["id"])) {
			header("Location:".$cms->get_link("slideshow"));
		}
	}
	switch($cms->a) {
		case "moveimg": 
			if($cms->id != 0) {
				$cms->executeQuery("SELECT * FROM cms_slideshow WHERE id='$cms->id'",1);
				$row = mysqli_fetch_assoc($cms->result1);
				$extName = $row["name"];
			}
			else {
				$extName = "";
			}
			$ids = $_POST["ids"]; 
			for ($idx = 0; $idx < count($ids); $idx+=1) {
				$id = $ids[$idx];
				$ordinal = $idx;
				$cms->executeQuery("UPDATE cms_slideshow_files SET position='$ordinal' WHERE id='$id'",1); 
			}    
			$cms->saveAction($extName,"","slideshow","moveimg"); 
		break;
		default: 
		case "list":
			echo '
				<div id="moduleWrap">
					<a href="'.$cms->get_link("slideshow,add").'" class="greenButtonWide ">'.$cms->translate(64).'</a><div class="c"></div><br />				 
					<table class="table">
						<tr>
							<td class="head" width="40px">Lp.</td> 
							<td class="head">'.$cms->translate(56).'</td>     ';
						// if multiple slideshows and default slideshow true
						if($cms->slid_sm == true) {
							echo '<td class="head" width="140px">'.$cms->translate(66).'</td>';
						}
						echo '
							<td class="head" width="60px">'.$cms->translate(14).'</td>
							<td class="head" width="60px">'.$cms->translate(14).'</td>
							<td class="head" width="60px">'.$cms->translate(15).'</td>
						</tr>';
				$max = $cms->getCount("cms_gallery");
				$i = 1;
				$cms->executeQuery("SELECT * FROM cms_slideshow WHERE lang='$cms->lang' ORDER BY position ASC",1);
				while($row = mysqli_fetch_assoc($cms->result1)) {  
					echo '<tr>
							<td class="body lvl0"><strong>'.$i.'.</strong></td>
							<td class="body alignleft lvl0">'.$row["name"].'</td> ';
					// if multiple slideshows and default slideshow true
					if($cms->slid_sm == true) {
						$main = $row["main"] == 0 ? '<a href="'.$cms->get_link("slideshow,main,".$row["id"]).'" class="link_main_on plink">&nbsp;</a>' : '<a href="#" class="link_main_on2 plink">&nbsp;</a>';
						echo '<td class="body lvl0" >'.$main.'</td>';
					}
					echo '	<td class="body lvl0"><a href="'.$cms->get_link("slideshow,edit,".$row["id"]).'" class="link_edit plink edit_slide" name="'.$row["id"].'-'.$row["name"].'">&nbsp;</a></td>
							<td class="body lvl0"><a href="'.$cms->get_link("slideshow,editimg,".$row["id"]).'" class="link_img plink">&nbsp;</a></td>
							<td class="body lvl0"><a href="#" class="delSlideshow link_delete plink" name="'.$row["id"].'">&nbsp;</a></td>
						</tr>';
					$i++;
				}
			echo '</table>
			</div>';
		break;
		case "add":
		case "edit": 
			/*** EDIT ***/
			if($cms->a == "edit") {
				$cms->executeQuery("SELECT * FROM cms_slideshow WHERE id='$cms->id'",1);
				$row = mysqli_fetch_assoc($cms->result1);
				$name = $row["name"];
			}
			else {
				$name = '';
			}
			if(isset($_POST["submit"])) {
				$name = $_POST["name"];
				if(empty($name)) {
					$cms->setInfo(false, $cms->translate(23));
				}
				elseif($cms->a == "add" && $cms->getCount("cms_image_dimensions","WHERE moduleId='3' AND name!='Lemon Thumb'") == 0) {
					$cms->setInfo(false, $cms->translate(483));
				}
				else {
					/*** ADD ***/
					if($cms->a == "add") {
						$main = $cms->getCount("cms_slideshow","WHERE main='1' AND lang='$cms->lang'") > 0 ? 0 : 1;
						$pos = $cms->getCount("cms_slideshow","WHERE lang='$cms->lang'") + 1;
						$cms->executeQuery("INSERT INTO cms_slideshow (`id`, `name`, `position`, `lang`, `main`) VALUES ('', '$name', '$pos', '$cms->lang', '$main')",1);
					}
					/*** EDIT ***/
					else {
						$cms->executeQuery("UPDATE cms_slideshow SET name='$name' WHERE id='$cms->id'",1);
					}
					
					if($cms->result1) {
						$cms->setSessionInfo(true, $cms->translate($cms->a == "add" ? 55 : 72));
						$cms->saveAction($name,"","","add");
						header("Location:".$cms->get_link("slideshow"));
					}
					else {
						$cms->setInfo(false,$cms->translate(27));
					}
				}
			}
			echo '<form method="post" enctype="multipart/form-data" action="'.$cms->get_link("slideshow,".$cms->a.''.($cms->a=="edit"?','.$cms->id:'')).'"> 
					<div id="addColumnMain">
						<div class="addColumn narrow left NB">
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(56).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"><input type="text" class="itemElement text" name="name" value="'.htmlspecialchars($name).'" /></div>
									<div class="itemType vital" title="'.$cms->translate(241).'">!</div>
								</div>
							</div>
						</div>
						<div class="c"><br /></div>
						<input type="submit" value="'.$cms->translate($cms->a=="add"?21:14).'" class="greenButtonLarge" name="submit" />	
					</div>
				</form>';
		break;
		case "editd":  
			$cms->executeQuery("SELECT *,s.id AS id FROM cms_slideshow_files sf INNER JOIN cms_slideshow s ON sf.slideId=s.id WHERE sf.id='$cms->id'",2);
			$row = mysqli_fetch_assoc($cms->result2); 
			$alt = $_POST["alt"];
			$content = $_POST["content"];
			$link = $_POST["link"];
			$id = $row["id"];
			$cms->executeQuery("UPDATE cms_slideshow_files SET alt='".$cms->esc($alt)."', description='$content', link='$link' WHERE id='$cms->id'",1);
			if($cms->result1) { 
				$cms->setSessionInfo(true,$cms->translate(48));		
				$cms->saveAction($row["name"],$row["file"]);				 
			}
			else {
				$cms->setSessionInfo(false,$cms->translate(27));
			}
			header("Location:".$cms->get_link("slideshow,editimg,$id"));
		break;
		case "editimg": 
            
			if(isset($_POST["submit"]) || isset($_POST["save"])) {
                $files = $_FILES["files"];
                
                
                if(count($files["name"]) > 0) {
                    foreach($files["name"] as $i => $fname) {  
                        // Get size of the original image
                        list($w,$h) = getimagesize($files["tmp_name"][$i]);
                        $n = $cms->getRandomName();
                        $cms->executeQuery("SELECT * FROM cms_image_dimensions cid INNER JOIN cms_modules cm ON cid.moduleId = cm.id WHERE cm.shortName='slideshow' ORDER BY cid.id DESC",1);
                        while($row = mysqli_fetch_assoc($cms->result1)) {

                            // If portrait
                            if($h > $w) {
                                $fit = $row["portrait"] == "fit" ? $row["height"] : 0;
                                $fill = $row["portrait"] == "fill" ? $row["height"] : 0;
                            }
                            // If landscape or square
                            else {
                                $fit = $row["landscape"] == "fit" ? $row["height"] : 0;
                                $fill = $row["landscape"] == "fill" ? $row["height"] : 0;
                            }

                            $dir = $row["name"] == "Lemon Thumb" ? "_lemon" : $row["width"].'x'.$row["height"];

                            if($cms->makeThumbnail(array("name" => $fname, "tmp_name" => $files["tmp_name"][$i]),"_images_content/slideshow/".$dir,$n, $row["width"], "width",$fit,$fill, $row["quality"]) == false) {
                                $error++;
                            }
                        }
                        if($error == 0) { 
                                $ext = strtolower(pathinfo($fname,PATHINFO_EXTENSION));
                            // Save original in _original folder
                            // If image height is greater than 1200, resize it
                            if($h > 1200) {
                                $cms->makeThumbnail($file,"_images_content/slideshow/_original", $n, 1200, "height", 0, 0, $row["quality"]);
                                $cms->makeThumbnail($file,"_images_content/slideshow/_cropped", $n, 1200, "height", 0, 0, $row["quality"]);
                            }
                            else {
                                move_uploaded_file($files["tmp_name"][$i],"_images_content/slideshow/_original/".$n.".".$ext); 
                                copy("_images_content/slideshow/_original/".$n.".".$ext,"_images_content/slideshow/_cropped/".$n.".".$ext); 
                            } 

                            $position = $cms->getCount("cms_slideshow_files", "WHERE slideId='$cms->id'") + 1;
                            $fna = $n.'.'.$ext;
                            $cms->executeQuery("INSERT INTO cms_slideshow_files (`id`, `file`, `slideId`, `position`, `alt`, `description`, `link`) VALUES ('','$fna','$cms->id','$position','','','')",1);  
                        } 
                    } 
                }
                    
                if($error == 0) {
                    $cms->setSessionInfo(true,$cms->translate(41));
                    $cms->saveAction($extName,"","slideshow","upload");
                    if(!isset($_POST["save"])) {
                        header("Location:".$cms->get_link("slideshow"));
                    }                    
                }
                else {
                   $cms->setInfo(false,$cms->translate(299));                    
                } 
            }
                
			echo '
			<div id="addColumnMain">
                <form method="post" enctype="multipart/form-data" action="'.$cms->get_link("slideshow,editimg,".$cms->id).'"> 
				<div class="addColumn narrow left NB">  	  
                    <div class="itemWrap "> 
                        <div class="itemLabel">Zdjęcia</div>
                        <div class="itemElementWrap itemElementFile">
                            <div class="itemElementFileButton">Zdjęcia</div>
                            <div class="itemElementFileName"></div>
                            <input type="file" class="itemElement file" name="files[]" multiple />
                            <div class="itemType optional" title="'.$cms->translate(243).'">?</div>
                        </div> 
                    </div>  
                    <div class="c"></div><br /> 
                    <div id="buttons">
                        <input type="submit" value="Wgraj" class="greenButtonLarge greenButtonFloatLeft" name="submit" />	
                        <input type="submit" value="Wgraj i kontynuuj" name="save" class="greenButtonLarge greenButtonFloatLeft" />
                        <div class="c"></div>
                    </div><br /><br />
				</div>
                </form>
				<form method="post" enctype="multipart/form-data" action="/cms/'.$cms->lang.'/slideshow/editd/" class="hidden" id="imageDetails">
					<div class="addColumn wide NB"> 
						<div class="itemWrap"> 
							<div class="itemLabel">'.$cms->translate(46).' '.$cms->translate(247).'</div>
							<div class="itemElementWrap">
								<div class="itemElementShadow">
									<input type="text" class="itemElement text" name="alt" value="" id="imageDetailsAlt" />
									<div class="itemType helpful">?</div>
								</div>
							</div>
						</div>		
						<div class="itemWrap"> 
							<div class="itemLabel">'.$cms->translate(414).'</div>
							<div class="itemElementWrap">
								<div class="itemElementShadow">
									<input type="text" class="itemElement text" name="link" value="" id="imageDetailsLink" />
									<div class="itemType optional">?</div>
								</div>
							</div>
						</div> 		
						<div class="itemWrap ">
							<div class="itemLabel">'.$cms->translate(485).'</div>
							<div class="itemElementWrap">
								<div class="itemElementShadow">
									<select class="itemElement select" id="linkPreview" />
										<option value="0">----- '.$cms->translate(20).' -----</option>';
										$spaces = '';
											function showTree2($parentId) {
												global $cms;  
												$rand = rand(0,9999999999999999);
												$cms->executeQuery("SELECT * FROM cms_menu WHERE lang='$cms->lang' AND parentId='$parentId' AND type != 5 ORDER BY position ASC",$rand);
												while($row[$rand] = mysqli_fetch_assoc($cms->{"result".$rand})) {
													$id = $row[$rand]["id"];
													$spaces = '';
													for($i=1;$i<=$row[$rand]["level"];$i++) {
														$spaces .= '&nbsp;&nbsp;&nbsp;&nbsp;';
													}
													echo '<option value="'.$id.'">'.$spaces.''.$row[$rand]["extName"];
													if($cms->getCount("cms_menu","WHERE parentId='$id' AND type != 5") > 0) {
														$spaces .= '&nbsp;&nbsp;';
														showTree2($id);
													}
												}
											}
											showTree2(0);
							echo '	</select>
								</div>
								<div class="itemType optional" title="'.$cms->translate(243).'">?</div>
								<div class="itemComment" id="linkPreviewText"></div>
							</div>
						</div>			
						<div class="itemWrap"> 
							<div class="itemLabel">'.$cms->translate(47).'</div>
							<div class="itemElementWrap">
								<div class="itemElementShadow"><textarea name="content" class="itemElement area" id="content">'.htmlspecialchars($content).'</textarea></div> 
								<div class="itemType helpful" title="'.$cms->translate(242).'">?</div>
							</div>
						</div>
						<Br /><input type="submit" value="'.$cms->translate(14).'" class="greenButtonLarge" name="submit" />	
		 			</div>
				</form>
				<div class="addColumn full clear fullNB">
					<div class="itemWrap itemWrapNM">
						<div class="itemLabel">'.$cms->translate(42).'</div>
					</div>
					<ul id="image-sorting">';
					$cms->executeQuery("SELECT * FROM cms_slideshow_files WHERE slideId='$cms->id' ORDER BY position ASC",1);
					while($row = mysqli_fetch_assoc($cms->result1)) { 
						list($w,$h) = getimagesize("_images_content/slideshow/_original/".$row["file"]);
						echo '<li id="'.$row["id"].'" class="image-wrap imageSlide"> 
								<div class="image-container imageCont">
									'.$cms->centerImage('_images_content/slideshow/_lemon/'.$row["file"],182,100).'
								</div>
								<div class="imageTools">
									<a href="#" name="'.$row["id"].'" class="editSlidePicture link_edit plink overlay-build edit-image-details">&nbsp;</a>								
									<a href="#" class="delSlidePicture link_delete plink" name="'.$row["id"].'">&nbsp;</a>
									<a href="#" class="crop-image link_crop plink overlay-build" name="'.$row["id"].'" title="'.$cms->translate(378).'" data-size="'.$w.'-'.$h.'">&nbsp;</a>
									<a href="#" class="restore-image link_restore plink" name="'.$row["id"].'" title="'.$cms->translate(382).'">&nbsp;</a>
								</div>
								<div class="clear"></div>
							</li>';
					}
	echo '		 		<div class="c"></div>
					</ul> 
				</div>
			</div>';		
		break;
		case "del":
			$cms->executeQuery("SELECT * FROM cms_slideshow WHERE id='$cms->id'",2);
			$row2 = mysqli_fetch_assoc($cms->result2);
			$pos = $row2["position"];
			$main = $row2["main"];
			$cms->executeQuery("DELETE FROM cms_slideshow WHERE id='$cms->id'",1);
			if($cms->result1) {  
				// REMOVE ALL FILES AND ROWS IN FILES AND DESCRIPTION TABLE
				$cms->executeQuery("SELECT * FROM cms_slideshow_files WHERE slideId='$cms->id'",5);
				while($row = mysqli_fetch_assoc($cms->result5)) {
					$id = $row["id"];
					$file = $row["file"];
					unlink("_slideshow/thumbs/$file");
					unlink("_slideshow/$file");
					$cms->executeQuery("DELETE FROM cms_slideshow_files WHERE id='$id'",6); 
				} 
				$cms->executeQuery("UPDATE cms_slideshow SET position=position-1 position>'$pos'",7); 
				if($main == 1) { 
					$cms->executeQuery("SELECT * FROM cms_slideshow WHERE lang='$cms->lang' ORDER BY position ASC",10);
					$row = mysqli_fetch_assoc($cms->result10);
					$id = $row["id"];
					$cms->executeQuery("UPDATE cms_slideshow SET main='1' WHERE id='$id'",9);
				}
				$cms->executeQuery("UPDATE cms_menu SET slideId='' WHERE slideId='$cms->id'",8);
				$cms->setSessionInfo(true,$cms->translate(59));
				$cms->saveAction($row2["name"],"");
			}
			else {
				$cms->setSessionInfo(false,$cms->translate(27));
			}
			header("Location:".$cms->get_link("slideshow"));
		break; 
		case "delimg":
			$cms->executeQuery("SELECT *,g.id AS id,gf.file AS file FROM cms_slideshow_files gf LEFT JOIN cms_slideshow g ON gf.slideId=g.id WHERE gf.id='$cms->id'",1);
			$row = mysqli_fetch_assoc($cms->result1);
			$file = $row["file"];
			$slideId = $row["id"];
			$cms->executeQuery("DELETE FROM cms_slideshow_files WHERE id='$cms->id'",2);
			if($cms->result2) { 
				$cms->setSessionInfo(true,$cms->translate(43));
				$cms->saveAction($row["extName"],$file);
				$e = 0;
				$cms->executeQuery("SELECT cid.name AS 'name', cid.width AS 'width', cid.height AS 'height' FROM cms_image_dimensions cid INNER JOIN cms_modules cm ON cid.moduleId = cm.id WHERE cm.shortName='slideshow'",2);
				while($row = mysqli_fetch_assoc($cms->result2)) { 				
					$dir = $row["name"] == "Lemon Thumb" ? "_lemon" : $row["width"].'x'.$row["height"];  
					if(unlink("_images_content/slideshow/".$dir."/".$file) == false) {
						$e++;
					} 
				}
				if($e == 0) {
					unlink("_images_content/slideshow/_cropped/$file");
				}
			}
			else {
				$cms->setSessionInfo(false,$cms->translate(27));
			}   
			header("Location:".$cms->get_link("slideshow,editimg,$slideId"));
		break;
		case "main":
			$cms->executeQuery("SELECT * FROM cms_slideshow WHERE id='$cms->id'",1);
			$row = mysqli_fetch_assoc($cms->result1);
			$cms->executeQuery("UPDATE cms_slideshow SET main='0' WHERE lang='$cms->lang'",1);
			if($cms->result1) {
				$cms->executeQuery("UPDATE cms_slideshow SET main='1' WHERE id='$cms->id'",2);
				if($cms->result2) {
					$cms->setSessionInfo(true,$cms->translate(72));
					$cms->saveAction($row["name"],"");
				}
				else {
					$cms->setSessionInfo(false,$cms->translate(27));
				}
			}
			else {
				$cms->setSessionInfo(false,$cms->translate(27));
			}
			header("Location:".$cms->get_link("slideshow"));
		break;
	} 
?>