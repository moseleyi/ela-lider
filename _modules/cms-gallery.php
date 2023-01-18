<script type="text/javascript"> 
	$(document).ready(function() {		
		lemon.crop.construct("multi");
		lemon.object_delete(".delGallery","<?php echo $cms->translate(37); ?>", "name", "del"); 
		lemon.object_delete(".delGalPicture","<?php echo $cms->translate(44); ?>", "name", "delimg");
		lemon.image_sorting();
		lemon.image_edit();
	});
</script>
<?php 
	$cms->checkAccess(); 
	// 	if there's only one gallery redirect to EDIT with ID
	if($cms->gal_mg == false && (!in_array($cms->a, array("editimg","delimg","editd")) || ($cms->a == "editimg" && $cms->id != 0))) {   
		header("Location:".$cms->get_link("gallery,editimg,0")); 
		die();
	} 
	// If multiple galleries are switched on refuse if ID == 0
	elseif($cms->gal_mg == true) {
		if($cms->id === 0 && isset($_GET["id"])) {
			header("Location:".$cms->get_link("gallery"));
			die();
		}
	}
	switch($cms->a) {
		case "list":
		default:
			echo '
				<div id="moduleWrap">
					<a href="'.$cms->get_link("gallery,add").'" class="greenButtonWide  greenButtonFloatLeft">'.$cms->translate(62).'</a>
					'.($cms->gallery_cats == true ? '<a href="'.$cms->get_link("gallery,addcat").'" class="greenButtonWide  greenButtonFloatLeft">'.$cms->translate(364).'</a>' : '').'
					<div class="c"></div> <br />
					<table class="table';if($cms->gal_mg == false){echo ' table_medium';}echo'">
					<tr> 
						<td class="head">'.$cms->translate(57).'</td>
						<td class="head" width="300px">'.$cms->translate(10).'</td>'; 
						if($cms->gal_main == true || $_SESSION["userRankId"] == 1) {
							echo '<td class="head" width="100px">'.$cms->translate(172).'</td>';
						}
						echo ' 
						<td class="head" width="60px">'.$cms->translate(12).'</td> 
						<td class="head" width="60px">'.$cms->translate(14).'</td> 
						<td class="head" width="60px">'.$cms->translate(14).'</td> 
						<td class="head" width="60px">'.$cms->translate(15).'</td>
					</tr>'; 
					
				// CATEGORIES or GALLERIES WITH NO CATEGORY
				$c = $cms->getCount("cms_gallery", "WHERE lang='$cms->lang' AND gallery_id='0'");
				$cms->executeQuery("SELECT * FROM cms_gallery WHERE lang='$cms->lang' AND  (category='1' OR (category='0' AND gallery_id='0')) ORDER BY position ASC",1);
				while($row = mysqli_fetch_assoc($cms->result1)) {								
					echo '<tr> 
							<td class="body alignleft lvl0"><input type="text" maxlength="'.strlen($c).'" class="pos-in" value="'.$row["position"].'" name="pos-'.$row["id"].'"/>
								'.$row["extName"].'</td>
							<td class="body lvl0">'.$row["intName"].'</td>'; 
							if($cms->gal_main == true || $_SESSION["userRankId"] == 1) {
								$main = ($row["category"] == 0 && !empty($row["gallery_id"])) || $cms->gallery_cats == false ? ($row["main"] == 0 ? '<a href="'.$cms->get_link("gallery,main,".$row["id"]).'" class="link_main_on plink">&nbsp;</a>' : '<a href="#" class="link_main_on2 plink">&nbsp;</a>') : '';
								echo '<td class="body lvl0">'.$main.'</td>';
							}echo'
							<td class="body lvl0"><a href="'.$cms->get_link("gallery,status,".$row["id"]).'" class="link_'.($row["status"] == 0?'hide':'show').' plink">&nbsp;</a></td>
							<td class="body lvl0"><a href="'.$cms->get_link("gallery,edit,".$row["id"]).'" class="link_edit plink">&nbsp;</a></td> 
							<td class="body lvl0">'.($row["category"] == 1 ? '' : '<a href="'.$cms->get_link("gallery,editimg,".$row["id"]).'" class="link_img plink">&nbsp;</a>').'</td>
							<td class="body lvl0"><a href="#" class="delGallery link_delete plink" name="'.$row["id"].'">&nbsp;</a></td>
						</tr>';	
						
					// GALLERIES WITH CATEGORY
					$count = $cms->getCount("cms_gallery","WHERE lang='$cms->lang' AND gallery_id='".$row["id"]."'");
					$cms->executeQuery("SELECT * FROM cms_gallery WHERE lang='$cms->lang' AND gallery_id='".$row["id"]."' ORDER BY position ASC",2);
					while($row2 = mysqli_fetch_assoc($cms->result2)) { 					
						echo '<tr> 
								<td class="body alignleft lvl1"><input type="text" maxlength="'.strlen($count).'" class="pos-in" value="'.$row2["position"].'" name="pos-'.$row2["id"].'"/>
									'.$row2["extName"].'</td>
								<td class="body lvl1">'.$row2["intName"].'</td> '; 
								if($cms->gal_main == true || $_SESSION["userRankId"] == 1) {
									$main = $row2["main"] == 0 ? '<a href="'.$cms->get_link("gallery,main,".$row2["id"]).'" class="link_main_on plink">&nbsp;</a>' : '<a href="#" class="link_main_on2 plink">&nbsp;</a>';
									echo '<td class="body lvl1">'.$main.'</td>';
								}echo'
								<td class="body lvl1"><a href="'.$cms->get_link("gallery,status,".$row2["id"]).'" class="link_'.($row2["status"] == 0?'hide':'show').' plink">&nbsp;</a></td>
								<td class="body lvl1"><a href="'.$cms->get_link("gallery,status,".$row2["id"]).'" class="link_edit plink">&nbsp;</a></td> 
								<td class="body lvl1"><a href="'.$cms->get_link("gallery,editimg,".$row2["id"]).'" class="link_img plink">&nbsp;</a></td>
								<td class="body lvl1"><a href="#" class="delGallery link_delete plink" name="'.$row2["id"].'">&nbsp;</a></td>
							</tr>';				
					}
				}
				echo '</table>
			</div>';
		break;
		case "moveimg":
			if($cms->id != 0) {
				$cms->executeQuery("SELECT * FROM cms_gallery WHERE id='$cms->id'",1);
				$row = mysqli_fetch_assoc($cms->result1);
				$extName = $row["extName"];
			}
			else {
				$extName = '';
			} 
			$ids = $_POST["ids"]; 
			for ($idx = 0; $idx < count($ids); $idx+=1) {
				$id = $ids[$idx];
				$ordinal = $idx;
				$cms->executeQuery("UPDATE cms_gallery_files SET position='$ordinal' WHERE id='$id'",1);
			}    
			$cms->saveAction($extName,"","gallery","moveimg");  	
		break;
		case "add":			
		case "edit":
		case "addcat": 	
			$cms->showIntNameInput(1);
			$name_int_old = "";
			/*** EDIT ***/
			if($cms->a == "edit") {	
				$cms->executeQuery("SELECT * FROM cms_gallery WHERE id='$cms->id'",1);
				$row = mysqli_fetch_assoc($cms->result1);
				$name_ext = $row["extName"];
				$name_int = $row["intName"];
				$content = $row["content"];
				$meta_title = $row["metaTitle"];
				$meta_keys = $row["metaKeys"];
				$meta_desc = $row["metaDesc"];
				$name_int_old = $row["intName"];
				$gallery_id = $row["gallery_id"];
				$gallery_id_old = $row["gallery_id"];
				$position = $row["position"];
				$position_old = $row["position"];
				$main = $row["main"];
				$category = $row["category"];
			}
			else {
				$name_int = $name_ext = $meta_title = $meta_keys = $meta_desc = $content = $gallery_id = $gallery_id_old = $position = $position_old = $category = '';
			}
			if(isset($_POST["submit"]) || isset($_POST["save"])) {
				$name_ext = $_POST["name_ext"];
				$name_int = $_POST["name_int"];
				$content = $_POST["content"];
				$meta_title = $_POST["meta_title"];
				$meta_keys = $_POST["meta_keys"];
				$meta_desc = $_POST["meta_desc"];
				$gallery_id = $_POST["gallery_id"];
				if(empty($name_ext))  {
					$cms->setInfo(false,$cms->translate(23)); 
				} 
				elseif($cms->validateInternalName($name_ext,$name_int,1,$name_int_old) == false) {
					$cms->setInfo(false,$cms->translate(24)); 
				}
				elseif($cms->checkExistingNames("cms_gallery") == false && ($cms->urls == "current-noid" || $cms->urls == "full-noid")) {
					$cms->setInfo(false, $cms->translate(411));
				}
				elseif($cms->a == "add" && $cms->getCount("cms_image_dimensions","WHERE moduleId='4' AND name!='Lemon Thumb'") == 0) {
					$cms->setInfo(false, $cms->translate(483));
				}
				else {
					/*** ADD ***/
					if($cms->a == "add" || $cms->a == "addcat") {
						$category = $cms->a == "add" ? 0 : 1;
						$pos = $cms->a == "add" ? $cms->getCount("cms_gallery","WHERE lang='$cms->lang' AND gallery_id = '$gallery_id'") + 1 : $cms->getCount("cms_gallery","WHERE lang='$cms->lang' AND gallery_id = '0'") + 1; 
						$main = 0;
						if($cms->getCount("cms_gallery","WHERE category='0' AND gallery_id='$gallery_id' AND lang='$cms->lang'") == 0) {
							$main = 1;
						}
						$cms->executeQuery("INSERT INTO cms_gallery 
							(`id`, `extName`, `intName`, `content`, `position`, `lang`, `status`, `main`, `metaTitle`, `metaKeys`, `metaDesc`, `category`, `gallery_id`) VALUES 
							('','".$cms->esc($name_ext)."','$cms->finalIntName', '$content', '$pos','$cms->lang','0', '$main', '".$cms->esc($meta_title)."', '".$cms->esc($meta_keys)."', '".$cms->esc($meta_desc)."','$category','$gallery_id')",1); 
					}
					
					/*** EDIT ***/
					else { 
						// Gallery ID has changed
						if((int)$gallery_id_old != (int)$gallery_id) {
							$position = $cms->getCount("cms_gallery","WHERE category='$category' AND gallery_id='$gallery_id' AND lang='$cms->lang'") + 1;
							$main = 0;
							if($cms->getCount("cms_gallery","WHERE category='0' AND gallery_id='$gallery_id' AND lang='$cms->lang'") == 0) {
								$main = 1;
							}
						}
						$cms->executeQuery("UPDATE cms_gallery SET extName='$name_ext',intName='$cms->finalIntName',content='$content',metaTitle='".$cms->esc($meta_title)."', metaKeys='".$cms->esc($meta_keys)."', gallery_id='$gallery_id', metaDesc='".$cms->esc($meta_desc)."',position='$position',main='$main' WHERE id='$cms->id'",1);		
						
						// If edit was successful change positions of gallery old
						if((int)$gallery_id_old != (int)$gallery_id) {
							$cms->executeQuery("UPDATE cms_gallery SET position=position-1 WHERE gallery_id='$gallery_id_old' AND position>'$position_old' AND lang='$cms->lang'",1);
						} 
					}	
									
					if($cms->result1) {
						$cms->saveAction($name_ext,"");
						if(!isset($_POST["save"])) {
							$cms->setSessionInfo(true,$cms->translate($cms->a == "add" ? 49 : ($cms->a == "addcat" ? 365 : 71)));
							header("Location:".$cms->get_link("gallery"));
						}
						else {
							$cms->setInfo(true, $cms->translate(71));
						}
					}
					else {
						$cms->setInfo(false,$cms->translate(27)); 
					} 
				}
			} 
			
			echo ' <form method="post" enctype="multipart/form-data" action="'.$cms->get_link("gallery,".$cms->a.''.($cms->a=="edit"?','.$cms->id:'')).'"> 
					<div id="addColumnMain">
						<div class="addColumn narrow left">
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(!empty($category) || $cms->a == "addcat" ? 373 : 57).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"><input type="text" class="itemElement text" name="name_ext" value="'.htmlspecialchars($name_ext).'" /></div>
									<div class="itemType vital" title="'.$cms->translate(241).'">!</div>
								</div>
							</div>';
							
					/* INTERNAL NAME */
					if($cms->sinf == true) {
						echo '<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(10).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"><input type="text" class="itemElement text" name="name_int" value="'.$name_int.'" /></div>
									<div class="itemType vital" title="'.$cms->translate(241).'">!</div>
									<div class="itemComment">'.$cms->translate(25).'</div>
								</div>
							</div>';
					} 
					
					/* CATEGORY */ 
					if(($cms->a == "add" || $cms->a == "edit")&& $cms->gallery_cats == true && empty($category)) {
						echo '<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(370).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow">
										<select class="itemElement select" name="gallery_id" />
											<option value="">----- '.$cms->translate(20).' -----</option>';
									$cms->executeQuery("SELECT * FROM cms_gallery WHERE category='1' AND lang='$cms->lang' ORDER BY position ASC",1);
									while($row = mysqli_fetch_assoc($cms->result1)) {
										echo '<option value="'.$row["id"].'"'.($gallery_id == $row["id"] ? 'selected="selected"' : '').'>'.$row["extName"].'</option>';
									}
								echo '	</select>
									</div>
									<div class="itemType optional" title="'.$cms->translate(243).'">?</div>
								</div>
							</div>';
					}
					
					/* META TAGS */
					echo'	
						</div>
						<div class="addColumn wide right">
							<div class="itemWrap">
								<div class="itemLabel">'.$cms->translate(16).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"><input type="text" class="itemElement text" name="meta_title" value="'.htmlspecialchars($meta_title).'" /></div>
									<div class="itemType helpful" title="'.$cms->translate(242).'">?</div>
								</div>
							</div>	
							<div class="itemWrap">
								<div class="itemLabel">'.$cms->translate(17).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"><input type="text" class="itemElement text" name="meta_keys" value="'.$meta_keys.'" /></div>
									<div class="itemType helpful" title="'.$cms->translate(242).'">?</div>
								</div>
							</div>	
							<div class="itemWrap">
								<div class="itemLabel">'.$cms->translate(18).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"><textarea class="itemElement area" name="meta_desc">'.$meta_desc.'</textarea></div>
									<div class="itemType helpful" title="'.$cms->translate(242).'">?</div>
								</div>
							</div>	
						</div>';
						
						/* CONTENT */
					echo '
						<div class="addColumn full clear">
							<div class="itemWrap">
								<div class="itemLabel">'.$cms->translate(47).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"><textarea name="content" class="itemElement area" id="content">'.htmlspecialchars($content).'</textarea></div>
									<script type="text/javascript">loadEditor(\'content\',\'300px\');</script>
									<div class="itemType helpful" title="'.$cms->translate(242).'">?</div>
								</div>
							</div>
						</div>	 
						<div class="c"></div>
						<div id="buttons">
							<input type="submit" value="'.$cms->translate($cms->a=="add" || $cms->a == "addcat"?21:14).'" class="greenButtonLarge greenButtonFloatLeft" name="submit" />	
							'.($cms->a == "edit" ? '<input type="submit" value="'.$cms->translate(506).'" name="save" class="greenButtonLarge greenButtonFloatLeft" />' : '').'
							<div class="c"></div>
						</div>
					</form>
				</div>';
		break;
		case "editimg":
			$cms->executeQuery("SELECT * FROM cms_gallery WHERE id='$cms->id'",1);
			$row = mysqli_fetch_assoc($cms->result1);
			$extName = $row["extName"];
			$intName = $row["intName"];
			$oldIntName = $row["intName"];
			if($row["category"] == 1) {
				$cms->setSessionInfo(false, $cms->translate(371));
				header("Location:".$cms->get_link("gallery"));
				exit();
			}
			if(isset($_POST["submit"]) || isset($_POST["save"])) {
                $files = $_FILES["files"];
                
                
                if(count($files["name"]) > 0) {
                    foreach($files["name"] as $i => $fname) {  
                        // Get size of the original image
                        list($w,$h) = getimagesize($files["tmp_name"][$i]);
                        $n = $cms->getRandomName();
                        $cms->executeQuery("SELECT * FROM cms_image_dimensions cid INNER JOIN cms_modules cm ON cid.moduleId = cm.id WHERE cm.shortName='gallery' ORDER BY cid.id DESC",1);
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

                            if($cms->makeThumbnail(array("name" => $fname, "tmp_name" => $files["tmp_name"][$i]),"_images_content/gallery/".$dir,$n, $row["width"], "width",$fit,$fill, $row["quality"]) == false) {
                                $error++;
                            }
                        }
                        if($error == 0) { 
                                $ext = strtolower(pathinfo($fname,PATHINFO_EXTENSION));
                            // Save original in _original folder
                            // If image height is greater than 1200, resize it
                            if($h > 1200) {
                                $cms->makeThumbnail($file,"_images_content/gallery/_original", $n, 1200, "height", 0, 0, $row["quality"]);
                                $cms->makeThumbnail($file,"_images_content/gallery/_cropped", $n, 1200, "height", 0, 0, $row["quality"]);
                            }
                            else {
                                move_uploaded_file($files["tmp_name"][$i],"_images_content/gallery/_original/".$n.".".$ext); 
                                copy("_images_content/gallery/_original/".$n.".".$ext,"_images_content/gallery/_cropped/".$n.".".$ext); 
                            } 

                            $position = $cms->getCount("cms_gallery_files", "WHERE galleryId='$cms->id'") + 1;
                            $fna = $n.'.'.$ext;
                            $cms->executeQuery("INSERT INTO cms_gallery_files (`id`, `file`, `galleryId`, `position`, `alt`, `description`, `link`) VALUES ('','$fna','$cms->id','$position','','','')",1);  
                        } 
                    } 
                    
                    if($error == 0) {
                        $cms->setSessionInfo(true,$cms->translate(41));
                        $cms->saveAction($extName,"","gallery","upload");
                        if(!isset($_POST["save"])) {
                            header("Location:".$cms->get_link("gallery"));
                        }                    
                    }
                    else {
					   $cms->setInfo(false,$cms->translate(299));                    
                    }
                }
			}
            
			echo '
			<div id="addColumnMain">
                <form method="post" enctype="multipart/form-data" action="'.$cms->get_link("gallery,editimg,".$cms->id).'"> 
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
							<div class="itemLabel">'.$cms->translate(414).'</div>
							<div class="itemElementWrap">
								<div class="itemElementShadow">
									<input type="text" class="itemElement text" name="link" value="" id="imageDetailsLink" />
									<div class="itemType optional">?</div>
								</div>
							</div>
						</div>
						<div class="itemWrap"> 
							<div class="itemLabel">'.$cms->translate(47).'</div>
							<div class="itemElementWrap">
								<div class="itemElementShadow"><input name="content" class="itemElement text" value="'.$content.'" id="imageDetailsContent" /></div> 
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
					$cms->executeQuery("SELECT * FROM cms_gallery_files WHERE galleryId='$cms->id' ORDER BY position ASC",1);
					while($row = mysqli_fetch_assoc($cms->result1)) { 
						list($w,$h) = getimagesize("_images_content/gallery/_cropped/".$row["file"]);
						echo '<li id="'.$row["id"].'" class="image-wrap"> 
								<div class="imageCont image-container">
									'.$cms->centerImage('_images_content/gallery/_lemon/'.$row["file"],143,100).'
								</div>
								<div class="imageTools">
									<a href="#" class="editGalPicture  edit-image-details link_edit plink" name="'.$row["id"].'" title="'.$cms->translate(376).'">&nbsp;</a>
									<a href="#" class="delGalPicture link_delete plink" name="'.$row["id"].'" title="'.$cms->translate(377).'">&nbsp;</a>
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
		
		case "status":
			$cms->executeQuery("SELECT * FROM cms_gallery WHERE id='$cms->id'",2);
			$row = mysqli_fetch_assoc($cms->result2);
			$n = $row["status"] == 0 ? 1 : 0;
			$category = $row["category"];
			$cms->executeQuery("UPDATE cms_gallery SET status='$n' WHERE id='$cms->id'",1);
			if($cms->result1) {
				$cms->setSessionInfo(true,$cms->translate($category == 1 ? 368 : 52));
				$cms->saveAction($row["extName"],"");
			}
			else {
				$cms->setSessionInfo(false,$cms->translate(27));
			}
			header("Location:".$cms->get_link("gallery"));
		break;
		
		case "move":
			$pos = $_GET["pos"];
			$cms->executeQuery("SELECT * FROM cms_gallery WHERE id='$cms->id'",1);
			$row = mysqli_fetch_assoc($cms->result1); 
			$oldpos = $row["position"]; 
			$gallery_id = $row["gallery_id"];
			$category = $row["category"];
			if($row["position"] == 0) {
				$cms->setSessionInfo(false,$cms->translate(372));
			}
			else {
				$max = $cms->getCount("cms_gallery","WHERE lang='$cms->lang' AND gallery_id='$gallery_id'");			
				if($pos <= 0 || is_numeric($pos) == false) {
					$pos = 1;
				}
				elseif($pos > $max) {
					$pos = $max;
				}  
				$q = $oldpos < $pos ? "position>'$oldpos' AND position<='$pos'" : "position<'$oldpos' AND position >='$pos'"; 	
				$q2 = $oldpos < $pos ? "position=position-1" : "position=position+1";			
				$cms->executeQuery("UPDATE cms_gallery SET ".$q2." WHERE ".$q."  AND lang='$cms->lang' AND gallery_id='$gallery_id'",1);
				if($cms->result1) {
					$cms->executeQuery("UPDATE cms_gallery SET position='$pos' WHERE id='$cms->id'",2);
					if($cms->result2) {
						$cms->setSessionInfo(true,$cms->translate($category == 1 ? 369 : 53));
					}
					else {
						$cms->setSessionInfo(false,$cms->translate(27));
					}
				}
				else {
					$cms->setSessionInfo(false,$cms->translate(27));
				}  
			}
			header("Location:".$cms->get_link("gallery"));	
		break;
		
		case "main":
			$cms->executeQuery("SELECT * FROM cms_gallery WHERE id='$cms->id'",1);
			$row = mysqli_fetch_assoc($cms->result1);
			$gallery_id = $row["galleryId"];
			$category = $row["category"];
			$name = $row["extName"];
			$cms->executeQuery("UPDATE cms_gallery SET main='0' WHERE lang='$cms->lang' AND galleryId='$gallery_id'",1);
			if($cms->result1) {
				$cms->executeQuery("UPDATE cms_gallery SET main='1' WHERE id='$cms->id'",2);
				if($cms->result2) {
					$cms->setSessionInfo(true,$cms->translate(71));
					$cms->saveAction($name,"");
				}
				else {
					$cms->setSessionInfo(false,$cms->translate(27));
				}
			}
			else {
				$cms->setSessionInfo(false,$cms->translate(27));
			}
			header("Location:".$cms->get_link("gallery"));
		break;
		
		case "del":
			$cms->executeQuery("SELECT * FROM cms_gallery WHERE id='$cms->id'",2);
			$row = mysqli_fetch_assoc($cms->result2);
			$pos = $row["position"];
			$extName = $row["extName"];
			$category = $row["category"];
			$gallery_id = $row["galleryId"];
			$cms->executeQuery("DELETE FROM cms_gallery WHERE id='$cms->id'",1);
			if($cms->result1) { 
				// CHANGE POSITION OF OTHER GALLERIES
				$cms->executeQuery("UPDATE cms_gallery SET position=position-1 WHERE position>'$pos' AND lang='$cms->lang' AND galleryId='$gallery_id'",3); 
				
				if(empty($category)) {
				// REMOVE ALL FILES AND ROWS IN FILES AND DESCRIPTION TABLE
					$cms->executeQuery("SELECT * FROM cms_gallery_files WHERE galleryId='$cms->id'",5);
					while($row = mysqli_fetch_assoc($cms->result5)) {
						$id = $row["id"];
						$file = $row["file"];	
						$cms->executeQuery("SELECT * FROM cms_image_dimensions WHERE module='gallery'",2);
						while($row2 = mysqli_fetch_assoc($cms->result2)) {
							$dir = $row2["name"] == "Lemon Thumb" ? "_lemon" : $row2["width"].'x'.$row2["height"];
							if(unlink("_images_content/gallery/".$dir."/".$file) == false) {
								$e++;	
							}
						}
						$cms->executeQuery("DELETE FROM cms_gallery_files WHERE id='$id'",6); 
					}
				}
				else {
					// IF IT'S A CATEGORY - DO NOT DELETE GALLERIES NOR FILES - JUST RESET GALLERY_ID TO ZERO AND SET STATUS TO 0
					$cms->executeQuery("SELECT * FROM cms_gallery WHERE gallery_id = '$cms->id'",1);
					while($row = mysqli_fetch_assoc($cms->result1)) {
						$cms->executeQuery("SELECT MAX(position)+1 AS 'max' FROM cms_gallery WHERE gallery_id='0' AND lang='$cms->lang'",2);
						$row2 = mysqli_fetch_assoc($cms->result2);
						$max = $row2["max"];
						$id = $row["id"];
						$cms->executeQuery("UPDATE cms_gallery SET status='0', gallery_id='0',position='$max' WHERE id='$id'",1);
					}
				}
				$cms->setSessionInfo(true,$cms->translate($category == 0 ? 50 : 367));
				$cms->saveAction($extName,"");
			}
			else {
				$cms->setSessionInfo(false,$cms->translate(27));
			}
			header("Location:".$cms->get_link("gallery"));
		break;
		
		case "editd":
			$cms->executeQuery("SELECT * FROM cms_gallery_files WHERE id='$cms->id'",2);
			$row = mysqli_fetch_assoc($cms->result2);
			$galleryId = $row["galleryId"];
			$file = $row["file"];
			$cms->executeQuery("SELECT * FROM cms_gallery WHERE id='$galleryId'",3);
			$row2 = mysqli_fetch_assoc($cms->result3);
			$extName = $row2["extName"];
			$alt = $_POST["alt"];
			$link = $_POST["link"];
			$description = $_POST["content"];
			$e = 0;
			$cms->executeQuery("UPDATE cms_gallery_files SET alt='".$cms->esc($alt)."',description='".$cms->esc($description)."', link='$link' WHERE id='$cms->id'",1);
			if($cms->result1) {  
				$cms->setSessionInfo(true,$cms->translate(48));
				$cms->saveAction($extName,$file); 
			}
			else {
				$cms->setSessionInfo(false,$cms->translate(27));
			}  
			header("Location:".$cms->get_link("gallery,editimg,$galleryId"));
		break;
		
		case "delimg":
			$cms->executeQuery("SELECT *,g.id AS id,gf.file AS file FROM cms_gallery_files gf LEFT JOIN cms_gallery g ON gf.galleryId=g.id WHERE gf.id='$cms->id'",1);
			$row = mysqli_fetch_assoc($cms->result1);
			$file = $row["file"];
			$galleryId = $row["id"];
			$cms->executeQuery("DELETE FROM cms_gallery_files WHERE id='$cms->id'",2);
			if($cms->result2) { 
				$cms->setSessionInfo(true,$cms->translate(43));
				$cms->saveAction($row["extName"],$file);
				$e = 0;
				$cms->executeQuery("SELECT cid.name AS 'name', cid.width AS 'width', cid.height AS 'height' FROM cms_image_dimensions cid INNER JOIN cms_modules cm ON cid.moduleId = cm.id WHERE cm.shortName='gallery'",2);
				while($row = mysqli_fetch_assoc($cms->result2)) { 				
					$dir = $row["name"] == "Lemon Thumb" ? "_lemon" : $row["width"].'x'.$row["height"];  
					if(unlink("_images_content/gallery/".$dir."/".$file) == false) {
						$e++;
					} 
				}
				if($e == 0) {
					unlink("_images_content/gallery/_cropped/$file");
				}
			}
			else {
				$cms->setSessionInfo(false,$cms->translate(27));
			}   
			header("Location:".$cms->get_link("gallery,editimg,$galleryId"));
		break;
	} 
?>