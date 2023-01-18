<script type="text/javascript">
	$(document).ready(function() { 
		lemon.object_delete(".delPage","<?php echo $cms->translate(29); ?>", "name", "del");
		lemon.link_preview();
	});
</script>
<?php
	$cms->checkAccess(); 
	switch($cms->a) { 
		case "list":
		default:
		
			/* Depending on page type, you can assign certain actions 
				Pulling all types data from DB */
			$types = array();
			$cms->executeQuery("SELECT * FROM cms_menu_page_types ORDER BY pageType ASC",1);
			while($row = mysqli_fetch_assoc($cms->result1)) {
				$types[$row["pageType"]] = array(
							/* Internal Name */	$row["internalName"],
							/* Page type */		$row["pageType"],
							/* Slideshow */		$cms->menu_ps == true ? 1 : 0,
							/* Gallery */		$cms->menu_pg == true && $cms->gal_mg == true ? 1 : 0,
							/* Status */		$row["status"], 
							/* Edit */			$row["edit"],
							/* Delete */		$row["delete"]);
			} 
			/* Select all types from current cms_menu data to work out whether to show empty cells or not*/
			$cms->executeQuery("SELECT type FROM cms_menu GROUP BY type",1);
			while($row = mysqli_fetch_assoc($cms->result1)) {
				$allTypes[] = $row["type"];
			}
			
			function getSlideGallery($idd, $t = "slideId") {
				global $cms;
				$rand = rand(0,999999);
				$cms->executeQuery("SELECT * FROM cms_menu WHERE id='$idd'",$rand);
				$row[$rand] = mysqli_fetch_assoc($cms->{"result".$rand});  
				if($row[$rand]["parentId"] == 0 && $row[$rand][$t] == 0) {
					return "";
				}
				if($row[$rand][$t] == 0) { 
					return getSlideGallery($row[$rand]["parentId"], $t);
				}
				else { 
					return $row[$rand][$t];
				}
			}
			 
			function showRow($row) {  
				global $cms, $types, $allTypes;
				$id = $row["id"];   
				
				/* Setting a link to change page type from clickable to not clickable if it has submenu */   
				$clickable = '<a href="'.$cms->get_link("menu,click,$id").'" class="clickable">'.$cms->translate(($row["clickable"] == 0 ? 195 : 194)).'</a>';
				$click = $cms->getCount("cms_menu","WHERE parentId='$id' AND type!='5'") > 0 ? $clickable : '';	  
				
				// Get main slideshow if none is set	
				if(empty($row["slideName"])) { 
					/* Try to get slideshow of the first parent that has slideshow assigned */
					if($cms->slid_inherit == true) {
						$sId = getSlideGallery($row["parentId"]);   
					}
					
					/* Get main if still not found */
					if($cms->slid_sm == true) {
						$cms->executeQuery("SELECT name from cms_slideshow WHERE lang='$cms->lang' AND ".(empty($sId) ? "main='1'" : "id='$sId'"),5);
						$row5 = mysqli_fetch_assoc($cms->result5);
						$row["slideName"] = $row5["name"];
					}
				} 
				
				// Get main gallery if none is set
				if(empty($row["galleryId"])) {
					/* Try to get slideshow of the first parent that has slideshow assigned */
					$gId = getSlideGallery($row["parentId"], "galleryId");   
					
					/* Get main if still not found */
					if($cms->gal_main == true) {
						$cms->executeQuery("SELECT extName from cms_gallery WHERE ".(empty($gId) ? "main='1' AND gallery_id='0' AND category='0' ORDER BY position ASC LIMIT 0,1" : "id='$gId'"),5);
						$row5 = mysqli_fetch_assoc($cms->result5);
						$row["galName"] = $row5["extName"];			
					}
				}
				
				$level = $row["level"];
				$type = $row["type"];
				
				/* Get count of all pages on current level */
				$count = $cms->getCount("cms_menu","WHERE lang='$cms->lang' AND level='$level' AND type!='5'");	
				$r = '<tr>
						<td class="body alignleft lvl'.$level.'">
						 <input type="text" maxlength="'.strlen($count).'" class="pos-in" value="'.$row["position"].'" name="pos-'.$row["id"].'"/>
							'.$row["extName"].' <span style="color:#333;font-size:10px;">'.$click.'</span>
						</td>';  
						 
				foreach($types[$type] as $i => $val) {   
					switch($i) {
						case 0: /* Internal Name */
							$r .= '<td class="body lvl'.$level.'">'.($row["external_site"] == 1 ? '<a href="'.$row["external_site_link"].'" class="promolink">'.$row["external_site_link"].'</a>' : $row["intName"]).'</td>';
						break;
						case 1: /* Page type */
							if($row["external_site"] == 1 && $row["external_site_link"] != "") {
								$colour = '#00b9e9';
								$type = $cms->cmsL == "pl" ? 'Zewnętrzna' : 'External';
							}
							else {
								$colour = $row["colour"];
								$type  = $row[$cms->cmsL."Type"];
								if($row["type"] == 2 && $row["connection_type"] == "module" && $row["plModule"] != "") { 
									$type .= ' ('.$row[$cms->cmsL."Module"].')';
								}
								elseif($row["connection_type"] == "exception" && $row["plException"] != "") {
									$type .= ' ('.$row[$cms->cmsL."Exception"].')';
								}
							}
							$r .= '<td class="body lvl'.$level.'"><span style="color:'.$colour.';">'.$type.'</span></td>';
						break;
						case 2: /* Slideshow */
							if($cms->menu_ps == true) {
								$r .= '<td class="body lvl'.$level.'">'.$row["slideName"].'</td>';
							}
						break;
						case 3: /* Gallery */									
							if($cms->menu_pg == true) {
								$r .= '<td class="body lvl'.$level.'">'.$row["galName"].'</td>';
							}
						break;
						case 4: /* Status */ 
							$r .= '<td class="body lvl'.$level.'">';
							if($_SESSION["userRankId"] == 1 || $row["type"] != 3) {
								$r .= '<a href="'.$cms->get_link("menu,status,".$row["id"]).'" class="link_'.($row["status"] == 1 ? 'show' : 'hide').' plink">&nbsp;</a>';
							}
							$r .= '</td>';
						break;  
						case 5: /* Edit */
							$r .= '<td class="body lvl'.$level.'"><a href="'.$cms->get_link("menu,edit,".$row["id"]).'" class="link_edit plink">&nbsp;</a></td>';
						break;
						case 6: /* Delete */
							$r .= '<td class="body lvl'.$level.'"><a href="#" class="delPage link_delete plink" name="'.$row["id"].'">&nbsp;</a></td>';
						break;
					}  
				}					  
				$r .= '</tr>';
				return $r;
			}
			echo '
				<div id="moduleWrap">
					<a href="'.$cms->get_link("menu,add").'" class="greenButtonWide ">'.$cms->translate(61).'</a><div class="c"></div><br />
					<table class="table">
						<tr> 
							<td class="head">'.$cms->translate(9).'</td> 
								<td class="head" width="220px">'.$cms->translate(10).'</td>';
								
						/* PAGE TYPE */
							echo '<td class="head" width="200px">'.$cms->translate(244).'</td>';
							
						/* SLIDESHOW */
							if($cms->menu_ps == true) {
								echo '<td class="head" width="150px">'.$cms->translate(11).'</td>';
							}
							
						/* GALLERY */
							if($cms->menu_pg == true) {
								echo '<td class="head" width="150px">'.$cms->translate(39).'</td>';
							}
							
						/* STATUS */
								echo '<td class="head" width="60px">'.$cms->translate(12).'</td>';
							
						/* EDIT */
								echo '<td class="head" width="60px">'.$cms->translate(14).'</td>';
							
						/* DYNAMIC MENU / DELETE */
								echo '<td class="head" width="60px">'.$cms->translate(15).'</td>';
				echo '</tr>';   
				$q_a = "SELECT 
							mpt.colour AS 'colour', 
							mpt.plLabel AS 'plType', 
							mpt.enLabel AS 'enType', 
							cs.name AS slideName,
							cm.*, 
							cmo.plName AS 'plModule',
							cmo.enName AS 'enModule',
							cme.name_pl AS 'plException',
							cme.name_en AS 'enException',
							cg.extName AS 'galName'
						FROM cms_menu AS cm 
						LEFT JOIN cms_slideshow AS cs ON cm.slideId=cs.id  
						LEFT JOIN cms_menu_page_types AS mpt ON mpt.pageType = cm.type 
						LEFT JOIN cms_modules AS cmo ON cmo.id = cm.connection AND cm.connection_type = 'module'
						LEFT JOIN cms_menu_exceptions AS cme ON cme.id = cm.connection AND cm.connection_type = 'exception'
						LEFT JOIN cms_gallery AS cg ON cm.galleryId=cg.id
						WHERE 
						cm.lang='$cms->lang' AND ";
				$q_a2 = $_SESSION["userRankId"] != 1 ? "AND cm.type != 5 " : "";
				$q_b = " ORDER BY cm.position ASC";
				$level = 0; 
				function showLevel($parentId) { 
					$rand = rand(0,9999999999999999);
					global $q_a, $q_b, $q_a2, $cms, $types, $allTypes; 
					$cms->executeQuery($q_a."cm.parentId='$parentId'".$q_a2.$q_b,$rand); 
					while($row[$rand] = mysqli_fetch_assoc($cms->{"result".$rand})) {
						$id = $row[$rand]["id"];
						echo showRow($row[$rand]);
						if($cms->getCount("cms_menu","WHERE parentId='$id'")) {
							echo showLevel($id);
						}
					}
				}
				showLevel(0); 
				echo '</table>
					</div>'; 
		break;    
		case "add": 
		case "edit":  
			/*** EDIT ONLY ***/
			$page_type = 1;
            $url_use = 1;
			if($cms->a == "edit") { 
				$cms->executeQuery("SELECT * FROM cms_menu WHERE id='$cms->id'",1);
				$row = mysqli_fetch_assoc($cms->result1);
				$name_ext = $row["extName"];
				$name_int = $row["intName"];
				$meta_title = $row["metaTitle"];
				$meta_desc = $row["metaDesc"];
				$meta_keys = $row["metaKeys"];
				$level = $row["level"];
				$level_old = $row["level"];
				$slide_id = $row["slideId"];
				$page_id = $row["parentId"];
				$galleryId = $row["galleryId"];
				$slogan = $row["slogan"];
				$page_type = $row["type"];
                $url_use = $row["url_use"];
				$status = $row["status"];
				$content = $row["content"];
				$position = $row["position"]; 
				$connection = $row["connection"];
				$connection_type = $row["connection_type"]; 
				$external_site = $row["external_site"] == 1 ? true : false;
				$external_site_link = $row["external_site_link"]; 			
				$page_type_old = $row["type"]; 
				$cms->finalIntName = $row["intName"]; 
				
				// If page type is "special", visible only for Lemon-Art, disallow
				if(in_array($page_type_old,array(5)) == true && $_SESSION["userRankId"] != 1) { 
					header("Location:/cms/$cms->lang/menu");
					die();
				}
				
				$name_int_old = $row["intName"];
				$page_id_old = $row["parentId"];
				$position_old = $row["position"];  
				$connection_old = $row["connection"];
				$connection_type_old = $row["connection_type"];
			}
			else {
				$name_int_old = empty($_POST["name_int"]) ? '' : $_POST["name_int"];
				$name_ext = $name_int = $slogan = $meta_title = $meta_keys = $meta_desc = $content = $external_site_link = $external_site = $slide_id = $galleryId = $page_id = $connection = $connection_type = '';
			}
			$cms->showIntNameInput($page_type);
			if(isset($_POST["submit"]) || isset($_POST["save"])) { 
				$name_ext = $_POST["name_ext"];
				$name_int = $_POST["name_int"];
				$meta_title = $_POST["meta_title"];
                $url_use = $_POST["url_use"];
				$meta_keys = $_POST["meta_keys"];
				$meta_desc = $_POST["meta_desc"];
				$slide_id = $_POST["slide_id"];
				$slogan = $_POST["slogan"];
				$galleryId = $_POST["galleryId"];
				$content = $_POST["content"];
				$page_id = empty($_POST["page_id"]) ? 0 : $_POST["page_id"]; 
				if(!in_array($_SESSION["userRankId"],array(1,2))) {
					$page_type = 1;
					$connection = $connection_old;
					$connection_type = $connection_type_old;
				}
				else {
					$conn = explode("-",$_POST["connection"]);
					$connection = $conn[1];
					$connection_type = $conn[0];
					$page_type = empty($_POST["page_type"]) ? $page_type_old : $_POST["page_type"];
				}
				$external_site = $_POST["external_site"] == "on" ? true : false;
				$external_site_link = $_POST["external_site_link"]; 
				
				/* Check page details: now content can be empty to create empty pages */
				if(empty($name_ext)) {
					$cms->setInfo(false,$cms->translate(23));
				}
				/* Validate internal name if it's not to be external page */
				elseif($cms->validateInternalName($name_ext,$name_int,$page_type, $name_int_old) == false) {
					$cms->setInfo(false,$cms->translate(197));
				}  
				elseif(($cms->urls == "current-noid" || $cms->urls == "full-noid") && $cms->checkExistingNames("cms_menu", "page_id", $page_id) == false && $page_type == 1 && $external_site == false) {
					$cms->setInfo(false, $cms->translate(411));
				}
				/* If page is to point to external link, the value of the link can't be empty */
				elseif($external_site == true && empty($external_site_link)) {
					$cms->setInfo(false,$cms->translate(200));
				}
				/* If page type is set to "module page" you need a module connection value + if type is different, you can't choose a module connection (only exception) */
				elseif(($page_type == 2 && empty($connection)) || (!empty($connection) && $connection_type == "module" && !in_array($page_type,array(2,5)))) {
					$cms->setInfo(false,$cms->translate(246));
				}
				/* If page type is invisble, you can't point it to external link */
				elseif($page_type == 3 && $external_site == true) {
					$cms->setInfo(false, $cms->translate(362));
				}	
				else {	 		
						
					/*** ADD ***/		
					if($cms->a == "add") { 
						/* Get parent page and set level: default 0 */
						$level = 0;
						if(!empty($page_id)) {
							$cms->executeQuery("SELECT level FROM cms_menu WHERE id='$page_id'",1);
							$row = mysqli_fetch_assoc($cms->result1);
							$level = $row["level"]+1;			
						} 
						
						/* Get position of the page */
						$position = $cms->getCount("cms_menu","WHERE lang='$cms->lang' AND parentId='$page_id' AND type!='5'") + 1;
						 	  
						/* If page type is 5 - position is 0, it is a hidden page only shown to Lemon Team */
						if($page_type == 5) {
							$position = 0;
						}
						
						/* Insert new entry to database */
						$cms->executeQuery("INSERT INTO cms_menu 
							(`id`, `extName`, `intName`, `slogan`, `metaTitle`,`metaDesc`, `metaKeys`, `content`, `lang`, `status`, `level`, `parentId`, `position`, `slideId`,`galleryId`,`clickable`,`type`,`external_site`,`external_site_link`,`moveable`,`connection`, `connection_type`, `url_use`) VALUES 
							('','".$cms->esc($name_ext)."','$cms->finalIntName', '".$cms->esc($slogan)."', '".$cms->esc($meta_title)."','".$cms->esc($meta_desc)."','".$cms->esc($meta_keys)."','$content','$cms->lang','0','$level','$page_id','$position','$slide_id','$galleryId','1','$page_type','$external_site','$external_site_link','1','$connection','$connection_type', '$url_use')"
						,1); 
						if($cms->result1) {  

							$cms->setSessionInfo(true,$cms->translate(26)); 
							$cms->saveAction($name_ext,"");
							header("Location:".$cms->get_link("menu"));
						}
						else {
							$cms->setInfo(false,$cms->translate(27));
						} 
					}				
					/*** EDIT ***/
					else{ 
						$e = 0;
						
						// Get details of chosen parent
						$cms->executeQuery("SELECT * FROM cms_menu WHERE id='$page_id'",1);
						$row = mysqli_fetch_assoc($cms->result1);
						if(count($row) > 0) {
							$parentLevel = $row["level"];
						}
						else {
							$parentLevel = 'none';
						}
						   
						/* Change page type to invisible*/
						if($page_type_old != 5 && $page_type == 5) {
							$position = 0; 
							$cms->executeQuery("UPDATE cms_menu SET position=position-1 WHERE parentId='$page_id_old' AND type!='5' AND position>'$position_old' AND lang='$cms->lang'",1);  
						}
						
						/* Change page type from invisible */
						elseif($page_type_old == 5 && $page_type != 5) {  
							$position = $cms->getCount("cms_menu","WHERE parentId='$page_id' AND type!='5' AND lang='$cms->lang'") + 1; 
						}
						
						/* Type doesn't change, or it's changed without '5' in any variables (old,new) */
						else { 
							if($page_id != $page_id_old && $page_type != 5) {
								$position = $cms->getCount("cms_menu","WHERE parentId='$page_id' AND type!='5' AND lang='$cms->lang'") + 1;
								$cms->executeQuery("UPDATE cms_menu SET position=position-1 WHERE parentId='$page_id_old' AND type!='5' AND position>'$position_old' AND lang='$cms->lang'",1);
							} 
							if($page_type == 5) {
                                echo 'page type is 5';
								$position = 0;
							}
						} 
						$level = $parentLevel == 'none' ? 0 : $parentLevel + 1; 	
						
						/* If level changes, make sure all children of current page have level changed as well */
						if($level != $level_old) {
							function updateLevel($lev,$id) {
								global $cms;  
								$rand = rand(0,99999);
								$cms->executeQuery("SELECT * FROM cms_menu WHERE parentId='$id' AND type!=5",$rand);
								while($row[$rand] = mysqli_fetch_assoc($cms->{"result".$rand})) {
									$idd = $row[$rand]["id"];  
									$cms->executeQuery("UPDATE cms_menu SET level='".($lev+1)."' WHERE id='$idd'",$rand+1);
									if($cms->getCount("cms_menu","WHERE parentId='$idd'") > 0){ 
										updateLevel(($lev+1), $idd);
									}
								}
							}
							
							updateLevel($level,$cms->id);
						}
										
						if($e > 0) {						
							$cms->setInfo(false,$cms->translate(27));				
						}
						else  {   
							$cms->executeQuery("UPDATE cms_menu SET 
								extName='".$cms->esc($name_ext)."', intName='$cms->finalIntName', slogan='".$cms->esc($slogan)."' ,content='$content', metaTitle='".$cms->esc($meta_title)."', metaKeys='".$cms->esc($meta_keys)."', metaDesc='".$cms->esc($meta_desc)."', slideId='$slide_id', galleryId='$galleryId' ,external_site='$external_site', external_site_link='$external_site_link',type='$page_type',level='$level',position='$position',parentId='$page_id',connection='$connection',connection_type='$connection_type', url_use='$url_use' WHERE id='$cms->id'"
							,1); 
							 
							if($cms->result1) {
								$cms->saveAction($name_ext,"");
								if(!isset($_POST["save"])) {
									$cms->setSessionInfo(true,$cms->translate(28));
									header("Location:".$cms->get_link("menu"));
								}
								else {
									$cms->setInfo(true,$cms->translate(28)); 
								}
							}
							else {
								$cms->setInfo(false,$cms->translate(27));
							}
						}
					} 	
				}
			} 
			 
			echo ' <form method="post" enctype="multipart/form-data" action="'.$cms->get_link("menu,".$cms->a.''.($cms->a=="edit"?','.$cms->id:'')).'"> 
					<div id="addColumnMain">
						<div class="addColumn narrow left">
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(9).'</div>
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
					
					/* SLOGAN */
					echo '<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(295).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"><input type="text" class="itemElement text" name="slogan" value="'.$slogan.'" /></div>
									<div class="itemType optional" title="'.$cms->translate(243).'">?</div> 
								</div>
							</div>';
					
					/* PARENT PAGE */
						/* get children information */
						if($cms->menu_sl > 0) {
							echo '<div class="itemWrap ">
									<div class="itemLabel">'.$cms->translate(19).'</div>
									<div class="itemElementWrap">
										<div class="itemElementShadow">
											<select class="itemElement select" name="page_id" />
												<option value="0">----- '.$cms->translate(20).' -----</option>';
												$spaces = '';
												function showTree($parentId) {
													global $cms; 
													global $page_id;
													$rand = rand(0,9999999999999999);
													$cms->executeQuery("SELECT * FROM cms_menu WHERE lang='$cms->lang' AND parentId='$parentId' AND type != 5 AND id!='$cms->id' ORDER BY position ASC",$rand);
													while($row[$rand] = mysqli_fetch_assoc($cms->{"result".$rand})) {
														$id = $row[$rand]["id"];
														$spaces = '';
														for($i=1;$i<=$row[$rand]["level"];$i++) {
															$spaces .= '&nbsp;&nbsp;&nbsp;&nbsp;';
														}
														echo '<option value="'.$id.'"'.($page_id == $id ? ' selected="selected"' : '').'>'.$spaces.''.$row[$rand]["extName"];
														if($cms->getCount("cms_menu","WHERE parentId='$id' AND type != 5") > 0 && $row[$rand]["level"]+1 < $cms->menu_sl) {
															$spaces .= '&nbsp;&nbsp;';
															showTree($id);
														}
													}
												}
												showTree(0);
									echo '	</select>
										</div>
										<div class="itemType optional" title="'.$cms->translate(243).'">?</div>
									</div>
								</div>';
						}
					
					/* SLIDESHOW */
					if($cms->menu_ps == true) {
						echo '<div class="itemWrap ">
									<div class="itemLabel">'.$cms->translate(11).'</div>
									<div class="itemElementWrap">
										<div class="itemElementShadow">
											<select class="itemElement select" name="slide_id" />
												<option value="0">----- '.$cms->translate(20).' -----</option>';
											$cms->executeQuery("SELECT * FROM cms_slideshow WHERE lang='$cms->lang' ORDER BY position ASC",1);
											while($row = mysqli_fetch_assoc($cms->result1)) {
												echo '<option value="'.$row["id"].'"';
												if($slide_id == $row["id"]) {
													echo ' selected="selected"';
												}
												echo '>'.$row["name"].'</option>';
											}
							echo '			</select>
										</div>
										<div class="itemType optional" title="'.$cms->translate(243).'">?</div>
									</div>
								</div>';
					}
					
					/* GALLERY */ 
					if($cms->gal_mg == true && $cms->menu_pg == true) {
						echo '	<div class="itemWrap ">
									<span class="itemLabel">'.$cms->translate(39).'</span>
									<div class="itemElementWrap">
										<div class="itemElementShadow">
											<select class="itemElement select" name="galleryId">
												<option value="0">----- '.$cms->translate(20).' -----</option>';
												$cms->executeQuery("SELECT * from cms_gallery WHERE lang='$cms->lang' AND category='0' ORDER BY position ASC",1);
												while($row = mysqli_fetch_assoc($cms->result1)) {
													echo '<option value="'.$row["id"].'"';
													if($galleryId == $row["id"]) {
														echo ' selected="selected"';
													}
													echo '>'.$row["extName"].'</option>';
												}
								echo '		</select>
											<div class="itemType optional" title="'.$cms->translate(243).'">?</div>
										</div>
									</div>
								</div>';
					}	
					
					/* LINK PREVIEW */ 
						echo '<div class="itemWrap ">
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
							</div>'; 
					
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
							</div>'.($_SESSION["userRankId"] == 1 ? '
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(509).'</div>
								<div class="itemElementWrap elementWrapCheck">
									<div class="itemElementShadow">
										<div class="checkElement'.($url_use==1?' checked':'').'">
											<input type="checkbox" class="itemElement check" name="url_use" value="on"'.($url_use==true?' checked="checked"':'').' />
										</div>
									</div>
									<div class="itemType optional" title="'.$cms->translate(243).'">?</div>
								</div>
							</div>' : '').'
						</div>';
						
						/* CONTENT */
					echo '
						<div class="addColumn full clear">
							<div class="itemWrap">
								<div class="itemLabel">'.$cms->translate(22).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"><textarea name="content" class="itemElement area" id="content">'.htmlspecialchars($content).'</textarea></div> 
									<script type="text/javascript">loadEditor(\'content\',\'450px\');</script>
									<div class="itemType helpful" title="'.$cms->translate(242).'">?</div>
								</div>
							</div>					
						</div>';
					/* Hide page type and module/exception from anyone other than Lemon-Art and Administrator*/
					if(in_array($_SESSION["userRankId"],array(1,2))) {
						echo '
						<div class="addColumn narrow left"> 
							<div class="itemWrap">
								<div class="itemLabel">'.$cms->translate(244).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow">
										<select class="itemElement select" name="page_type">';
											$cms->executeQuery("SELECT * FROM cms_menu_page_types ORDER BY pageType ASC",1);
											while($row = mysqli_fetch_assoc($cms->result1)) { 
												if($_SESSION["userRankId"] == 1 || ($_SESSION["userRankId"] != 1 && $row["pageType"] != 5)) {
													echo '<option value="'.$row["pageType"].'"';
													if($page_type == $row["pageType"]) {
														echo ' selected="selected"';
													}
													echo '>'.$row["pageType"].' - '.$row["pageName".$cms->cmsL].'</option>'; 
												}
											}
						echo '			</select>
									</div>
									<div class="itemType vital" title="'.$cms->translate(241).'">!</div>
								</div>
							</div>	'; 
						
						/* MODULE OR EXCEPTION CONNECTION */
						echo '
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(245).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow">
									<select class="itemElement select" name="connection">
										<option value="">----- '.$cms->translate(20).' -----</option>';
										if($cms->getCount("cms_modules","WHERE status='1' AND connectable='1' AND (id NOT IN (SELECT DISTINCT connection FROM cms_menu WHERE lang='$cms->lang' AND connection_type='module') OR id = '$connection')") > 0) {
											echo '
										<option value="" disabled="disabled">'.mb_strtoupper($cms->translate(353), "utf8").'</option>';
										$cms->executeQuery("SELECT * FROM cms_modules WHERE status='1' AND connectable='1' AND (id NOT IN(SELECT DISTINCT connection FROM cms_menu WHERE lang='$cms->lang' AND connection_type='module') OR id = '$connection') ORDER BY id ASC",1);
										while($row = mysqli_fetch_assoc($cms->result1)) {
											echo '<option value="module-'.$row["id"].'"';
											if($connection == $row["id"] && $connection_type == "module") {
												echo ' selected="selected"';
											}
											echo '>&nbsp;&nbsp;&nbsp;'.$row[$cms->cmsL."Name"].'</option>';
										}
										}
										if($cms->getCount("cms_menu_exceptions") > 0) {
									echo '<option value="" disabled="disabled">'.mb_strtoupper($cms->translate(354),"utf8").'</option>';
										$cms->executeQuery("SELECT * FROM cms_menu_exceptions ORDER BY id ASC",1);
										while($row = mysqli_fetch_assoc($cms->result1)) {
											echo '<option value="exception-'.$row["id"].'"';
											if($connection == $row["id"] && $connection_type == "exception") {
												echo ' selected="selected"';
											}
											echo '>&nbsp;&nbsp;&nbsp;'.$row["name_".$cms->cmsL].'</option>';
										}
										}
								echo '		</select>
									</div>
									<div class="itemType optional" title="'.$cms->translate(242).'">?</div>
								</div>
							</div>	
						</div>';
					}
						
						/* EXTERNAL SITE REDIRECTION */
					echo '
						<div class="addColumn narrow '.(!in_array($_SESSION["userRankId"],array(1,2)) ? 'left NB':'right').'">
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(199).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow">
										<input class="itemElement text" name="external_site_link" value="'.$external_site_link.'" />
									</div>
									<div class="itemType optional" title="'.$cms->translate(243).'">?</div>
								</div>
							</div>
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(198).'</div>
								<div class="itemElementWrap elementWrapCheck">
									<div class="itemElementShadow">
										<div class="checkElement'.($external_site==1?' checked':'').'">
											<input type="checkbox" class="itemElement check" name="external_site" value="on"'.($external_site==true?' checked="checked"':'').' />
										</div>
									</div>
									<div class="itemType optional" title="'.$cms->translate(243).'">?</div>
								</div>
							</div>
						</div>
						<div class="c"></div><br />
						<div id="buttons">
							<input type="submit" value="'.$cms->translate($cms->a=="add"?21:14).'" class="greenButtonLarge greenButtonFloatLeft" name="submit" />	
							'.($cms->a == "edit" ? '<input type="submit" value="'.$cms->translate(506).'" name="save" class="greenButtonLarge greenButtonFloatLeft" />' : '').'
							<div class="c"></div>
						</div>
					</form>
				</div>';
		break; 
		case "click":
			$cms->executeQuery("SELECT clickable,extName FROM cms_menu WHERE id='$cms->id'",1);
			$row = mysqli_fetch_assoc($cms->result1);
			$c = $row["clickable"] == 0 ? 1 : 0;
			$cms->executeQuery("UPDATE cms_menu SET clickable='$c' WHERE id='$cms->id'",2);
			if($cms->result2) {
				$cms->setSessionInfo(true,$cms->translate(28));
				$cms->saveAction($row["extName"],"");
				header("Location:".$cms->get_link("menu"));
			}
			else {
				$cms->setInfo(false,$cms->translate(27));
			}
			header("Location:".$cms->get_link("menu"));
		break;
		case "move":
			$pos = $_GET["pos"];
			$cms->executeQuery("SELECT * FROM cms_menu WHERE id='$cms->id'",1);
			$row = mysqli_fetch_assoc($cms->result1);
			$level = $row["level"];
			$name = $row["extName"];
			$parentId = $row["parentId"];
			$oldpos = $row["position"];
			$moveable = $row["moveable"];
			if($moveable == 0 || $row["position"] == 0) {
				$cms->setSessionInfo(false,$cms->translate(210));
			}
			else {
				$max = $cms->getCount("cms_menu","WHERE parentId='$parentId' AND level='$level' AND lang='$cms->lang' AND type!='5'");			
				if($pos <= 0 || is_numeric($pos) == false) {
					$pos = 1;
				}
				elseif($pos > $max) {
					$pos = $max;
				}  
				$q = $oldpos < $pos ? "position>'$oldpos' AND position<='$pos'" : "position<'$oldpos' AND position >='$pos'"; 	
				$q2 = $oldpos < $pos ? "position=position-1" : "position=position+1";			
				$cms->executeQuery("UPDATE cms_menu SET ".$q2." WHERE ".$q." AND level='$level' AND parentId='$parentId' AND lang='$cms->lang'",1);
				if($cms->result1) {
					$cms->executeQuery("UPDATE cms_menu SET position='$pos' WHERE id='$cms->id'",2);
					if($cms->result2) {
						$cms->setSessionInfo(true,$cms->translate(31));
						$cms->saveAction($name,"");
					}
					else {
						$cms->setSessionInfo(false,$cms->translate(27));
					}
				}
				else {
					$cms->setSessionInfo(false,$cms->translate(27));
				}  
			}
			header("Location:".$cms->get_link("menu"));	
		break;
		case "status":  
			$cms->executeQuery("SELECT * FROM cms_menu WHERE id='$cms->id'",1);
			$row = mysqli_fetch_assoc($cms->result1);
			$extName = $row["extName"];
			$status = $row["status"];
			$n = $status == 0 ? 1 : 0;
			$cms->executeQuery("UPDATE cms_menu SET status='$n' WHERE id='$cms->id'",1);
			if($cms->result1) {
				$cms->setSessionInfo(true,$cms->translate(32));
				$cms->saveAction($extName,"");
			}
			else {
				$cms->setSessionInfo(false,$cms->translate(27));
			}
			header("Location:".$cms->get_link("menu"));
		break;
		case "del":
			$cms->executeQuery("SELECT * FROM cms_menu WHERE id='$cms->id'",1);
			$row = mysqli_fetch_assoc($cms->result1);
			$level = $row["level"];
			$position = $row["position"];
			$parentId = $row["parentId"];
			$type = $row["type"];
			$extName = $row["extName"];
			$e = 0;
			$cms->executeQuery("DELETE FROM cms_menu WHERE id='$cms->id'",2);
			if($cms->result2) {
				if($type != 5) {
					$cms->executeQuery("SELECT id,position FROM cms_menu WHERE parentId='$parentId' AND lang='$cms->lang' AND position>'$position' AND level='$level'",3);
					while($row = mysqli_fetch_assoc($cms->result3)) {
						$npos = $row["position"] - 1;
						$id = $row["id"];
						$cms->executeQuery("UPDATE cms_menu SET position='$npos' WHERE id='$id'",4);
						if(!$cms->result4) {$e++;}
					}
				}
				if($e > 0) {
					$cms->setSessionInfo(false,$cms->translate(27));
				}
				else {
					/* Check if this page is last child of its parent (parentId different than 0), if so change clickable to 1*/
					if($parentId > 0) {
						$count = $cms->getCount("cms_menu","WHERE parentId='$parentId' AND lang='$cms->lang'");
						if($cms->getCount("cms_menu","WHERE parentId='$parentId' AND lang='$cms->lang'") == 0) {
							$cms->executeQuery("UPDATE cms_menu SET clickable='1' WHERE id='$parentId'",1);
						}
					}
					$cms->setSessionInfo(true,$cms->translate(30));
					$cms->saveAction($extName,"");
				}
			}
			else {
				$cms->setSessionInfo(false,$cms->translate(27));
			}
			header("Location:".$cms->get_link("menu"));
		break;
	}
?>