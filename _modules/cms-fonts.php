<script>
	$(document).ready(function() {
		setText();
		$("#setFonts").click(function() {
			setText();
		}); 
		lemon.object_delete(".delFont","<?php echo $cms->translate(492); ?>", "name", "del");
	});
	
	function setText() {
		if($("#testPhrase").val() != "") { 
			$(".testPhrase").text($("#testPhrase").val());
		}
		if($("#fontSize").val() * 1 == $("#fontSize").val()) {
			$(".testPhrase").css("font-size",$("#fontSize").val()+"px");
		}
	}
</script>
<?php 
	$cms->checkAccess(); 
	switch($cms->a) {
		default:
		case "list":
			echo '
				<div id="moduleWrap">
					<a href="'.$cms->get_link("fonts,add").'" class="greenButtonWide  greenButtonFloatLeft">'.$cms->translate(253).'</a><a href="/cms/'.$cms->lang.'/fonts/test" class="greenButtonWide  greenButtonFloatLeft">Test</a><div class="c"></div><br />
					<table class="table">
						<tr>
							<td class="head" width="40px">Lp</td>
							<td class="head">'.$cms->translate(254).'</td>
							<td class="head" width="150px">'.$cms->translate(251).'</td>  
                            <td class="head" width="150px">'.$cms->translate(509).'</td>
                            <td class="head" width="350px">URL</td>
                            <td class="head" width="70px">System</td>
							<td class="head" width="60px">'.$cms->translate(12).'</td>
							<td class="head" width="60px">'.$cms->translate(14).'</td>
							<td class="head" width="60px">'.$cms->translate(15).'</td>
						</tr>';
			$i = 1;
			$cms->executeQuery("SELECT * FROM cms_fonts ORDER BY name ASC",1);
			while($row = mysqli_fetch_assoc($cms->result1)) {
				echo '<tr>
						<td class="body lvl0"><strong>'.$i.'.</strong></td>
						<td class="body alignleft lvl0">'.$row["name"].'</td>
						<td class="body lvl0">'.$row["name_css"].'</td>  
                        <td class="body lvl0"><span class="tick tick-'.$row["external"].'">&nbsp;</span></td>
                        <td class="body lvl0 alignleft" style="font-size:10px;">'.$row["url"].'</td>
                        <td class="body lvl0"><span class="tick tick-'.$row["system"].'">&nbsp;</span></td>
						<td class="body lvl0"><a href="'.$cms->get_link("fonts,status,".$row["id"]).'" class="link_'.($row["status"] == 1 ? 'show' : 'hide').' plink">&nbsp;</a></td>
						<td class="body lvl0"><a href="'.$cms->get_link("fonts,edit,".$row["id"]).'" class="link_edit plink">&nbsp;</a></td>
						<td class="body lvl0"><a href="#" class="delFont link_delete plink" name="'.$row["id"].'">&nbsp;</a></td>
					</tr>';
				$i++;
			}
			echo '</table>
		</div>';
		break;
		case "status":
			$cms->executeQuery("SELECT * FROM cms_fonts WHERE id='$cms->id'",1);
			$row = mysqli_fetch_assoc($cms->result1); 
			$n = $row["status"] == 0 ? 1 : 0;
			$cms->executeQuery("UPDATE cms_fonts SET status='$n' WHERE id='$cms->id'",1);
			if($cms->result1) {
				$cms->setSessionInfo(true,"Font status changed successfully"); 
			}
			else {
				$cms->setSessionInfo(false,$cms->translate(27));
			}
			header("Location:".$cms->get_link("fonts"));
		break;
		case "add":
		case "edit":
			/*** EDIT ***/
			if($cms->a == "edit") {						
				$cms->executeQuery("SELECT * FROM cms_fonts WHERE id='$cms->id'",1);
				$row = mysqli_fetch_assoc($cms->result1);
				$name = $row["name"];
                $name_old = $row["name"];
                $name_css = $row["name_css"];
                $name_css_old = $row["name_css"];
                $external = $row["external"];
                $url = $row["url"]; 
                
			}
			else {
				$name = $fn = $name_css = $external = $url = "";
			}
			if(isset($_POST["submit"])) {
				$name = $_POST["name"];
				$name_css = $_POST["name_css"]; 
				$woff = $_FILES["woff"];
                $woff2 = $_FILES["woff2"];
                $external = $_POST["external"];
                $url = $_POST["url"];
                 
                $name_css = str_replace(array(" ", ".", "!","@","#","$","%","^","&","*","(",")","+","=","[","{","]","}","\\","|","<",",",">","/","?","~","`","'",'"',":"), "", empty($name) ? $name : $name_css); 
               
                
				/* ADD */
				if($cms->a == "add") {
					if(empty($name) || empty($name_css) || ($external == 0 && (empty($woff["name"]) || empty($woff2["name"])))) {
						$cms->setInfo(false,$cms->translate(23));
					}
                    else if($external == 1 && empty($url)) {
                        $cms->setInfo(false, $cms->translate(510));
                    }
					else {
						$woff_ext = pathinfo($woff["name"],PATHINFO_EXTENSION);
                        $woff2_ext = pathinfo($woff2["name"], PATHINFO_EXTENSION);
                        
						if($external == 0 && ($woff_ext != "woff" || $woff2_ext != "woff2")) {
							$cms->setInfo(false,"Make sure you upload correct files. At least one extension is not right!");
						}
						else if($cms->getCount("cms_fonts","WHERE name='$name'") > 0) {
							$cms->setInfo(false,"Font with given name already exists");
						}
						else if($cms->getCount("cms_fonts","WHERE name_css='$name_css'") > 0) {
							$cms->setInfo(false,"Css reference already exists");
						}
						else if(file_exists("_fonts/".$name_css.".woff2") || file_exists("_fonts/".$name_css.".woff")) {
							$cms->setInfo(false,"At least one file already exists on the server");
						}
						else {
                            $er = 0;
							if($external == 0 && (move_uploaded_file($woff2["tmp_name"],"_fonts/".$name_css.".woff2") == false || move_uploaded_file($woff["tmp_name"],"_fonts/".$name_css.".woff") == false)) {
                                $er++;
                            }
                            if($er == 0) {
								$cms->executeQuery("INSERT INTO cms_fonts (`id`, `name`, `name_css`, `status`, `system`, `external`, `url`) VALUES ('', '$name', '$name_css', '0', '0', '$external', '$url')",1);
								if($cms->result1) {
									$cms->setSessionInfo(true,"New font added successfully");
									header("Location:/cms/$cms->lang/fonts");
								}
								else {
									$cms->setInfo(false, $cms->translate(27));
                                    unlink("_fonts/".$name_css.".woff");
                                    unlink("_fonts/".$name_css.".woff2");
								}
							}
							else {
								$cms->setInfo(false,"At least one file failed while saving to the server. All files have been removed, please try again");
							}
						}
					}
				}
				/*** EDIT ***/
				else {
					if(empty($name) || empty($name_css)) {
						$cms->setInfo(false,$cms->translate(23));
					}							 
                    else if($external == 1 && empty($url)) {
                        $cms->setInfo(false, $cms->translate(510));
                    }
					else if($cms->getCount("cms_fonts","WHERE name='$name'") > 0 && $name_old != $name) {
						$cms->setInfo(false,"Font with given name already exists");
					}
					else if($cms->getCount("cms_fonts","WHERE name_css='$name_css'") > 0 && $name_css_old != $name_css) {
						$cms->setInfo(false,"Css reference already exists");
					}
					else { 
						$e = 0;  
						if($woff2["name"] != "") {
							$woff2_ext = pathinfo($woff2["name"],PATHINFO_EXTENSION);
							if($woff2_ext != "woff2") {
								$e++; 
							}
							else {
								if($name_old == $name) { 
									if(move_uploaded_file($woff2["tmp_name"],"_fonts/".$name_css.".woff2") == false) {
										$e++; 
									}
								}
								elseif(file_exists("_fonts/".$name_css.".woff2") == false) { 
									if(unlink("_fonts/".$name_css_old.".woff2")) {
										if(move_uploaded_file($ttf["tmp_name"],"_fonts/".$name_css.".woff2") == false) {
											$e++; 
										}
									}
								}
							}
						}
						else {
							if($name_css_old != $name_css) {
								if(rename("_fonts/".$name_css_old.".woff2","_fonts/".$name_css.".woff2") == false) {
									$e++;
								}
							}
						}
						if($woff["name"] != "") {
							$woffExt = pathinfo($woff["name"],PATHINFO_EXTENSION);
							if($woffExt != "woff") {
								$e++; 
							}
							else {
								if($name_old == $name) { 
									if(move_uploaded_file($woff["tmp_name"],"_fonts/".$name_css.".woff") == false) {
										$e++; 
									}
								}
								elseif(file_exists("_fonts/".$name_css.".woff") == false) {
									if(unlink("_fonts/".$name_css_old.".woff")) {
										if(move_uploaded_file($woff["tmp_name"],"_fonts/".$name_css_old.".woff") == false) {
											$e++; 
										}
									}
								}
							}
						}
						else {
							if($name_css_old != $name_css) {
								if(rename("_fonts/".$name_css_old.".woff","_fonts/".$name_css.".woff") == false) {
									$e++;
								}
							}
						}		 
						$cms->executeQuery("UPDATE cms_fonts SET name='$name', name_css='$name_css', external='$external', url='$url' WHERE id='$cms->id'",1);
						if($cms->result1) {
							if($e == 0) {
								$cms->setSessionInfo(true,"Font edited successfully");
								header("Location:".$cms->get_link("fonts"));
							}
							else {
								$cms->setInfo(false,"At least one error occurred while uploading new files. Make sure all files are correct");
							}
						}
						else {
							$cms->setInfo(false, $cms->translate(27));
						}  
					}
                     
				}    
			}
			echo ' <form method="post" enctype="multipart/form-data" action="'.$cms->get_link("fonts,".$cms->a.''.($cms->a=="edit"?','.$cms->id:'')).'"> 
					<div id="addColumnMain">
						<div class="addColumn narrow left">
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(130).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"><input type="text" class="itemElement text" name="name" value="'.htmlspecialchars($name).'" /></div>
									<div class="itemType vital" title="'.$cms->translate(241).'">!</div>
								</div>
							</div>
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(251).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"><input type="text" class="itemElement text" name="name_css" value="'.$name_css.'" /></div>
									<div class="itemType vital" title="'.$cms->translate(241).'">!</div>
								</div>
							</div>	 	 
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(509).'</div>
								<div class="itemElementWrap elementWrapCheck">
									<div class="itemElementShadow">
										<div class="checkElement'.($external == 1 ? ' checked' : '').'">
											<input type="checkbox" class="itemElement check" name="external" value="1"'.($external == 1 ? ' checked="checked"' : '').' />
										</div>
									</div>
									<div class="itemType optional" title="'.$cms->translate(243).'">?</div>
								</div>
							</div>
                            <div class="itemWrap ">
								<div class="itemLabel">URL</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow">
										<input class="itemElement text" name="url" value="'.$url.'" />
									</div>
									<div class="itemType optional" title="'.$cms->translate(243).'">?</div>
								</div>
							</div>
						</div>
						<div class="addColumn wide right">							
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(255).' *.WOFF</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow itemElementFile">
										<div class="itemElementFileButton">'.$cms->translate(250).'</div>
										<div class="itemElementFileName"></div>
										<input type="file" class="itemElement file" name="woff" />
									</div>
									<div class="itemType helpful" title="'.$cms->translate(242).'">?</div>
									'.(file_exists("_fonts/".$name_css.".woff")?'<div class="itemComment">Uploaded file: <strong>'.$name_css.'.woff</strong></div>':'').'
								</div>
							</div>
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(255).' *.WOFF2</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow itemElementFile">
										<div class="itemElementFileButton">'.$cms->translate(250).'</div>
										<div class="itemElementFileName"></div>
										<input type="file" class="itemElement file" name="woff2" />
									</div>
									<div class="itemType helpful" title="'.$cms->translate(242).'">?</div>
									'.(file_exists("_fonts/".$name_css.".woff2")?'<div class="itemComment">Uploaded file: <strong>'.$name_css.'.woff2</strong></div>':'').'
								</div>
							</div> 	
						</div>
						<div class="c"><br /></div>
						<input type="submit" value="'.$cms->translate($cms->a=="add"?21:14).'" class="greenButtonLarge" name="submit" />	
					</div>
				</form>'; 
		break; 
		case "del":
			$cms->executeQuery("SELECT * FROM cms_fonts WHERE id='$cms->id'",1);
			$row = mysqli_fetch_assoc($cms->result1);
			$fileName = $row["fileName"];
			$cms->executeQuery("DELETE FROM cms_fonts WHERE id='$cms->id'",2);
			if($cms->result2) {
				if(!unlink("_fonts/".$fileName.".eot") || !unlink("_fonts/".$fileName.".svg") || !unlink("_fonts/".$fileName.".ttf") || !unlink("_fonts/".$fileName.".woff")) {
					$cms->setSessionInfo(false,"Font deleted but some file may have been left due to an error");
				}
				else {
					$cms->setSessionInfo(true,"Font and all files deleted successfully");
				}
			}
			else {
				$cms->setSessionInfo(false,$cms->translate(27));
			}
			header("Location:".$cms->get_link("fonts"));
		break;
		case "test":
			echo '<div id="addColumnMain"><a href="/cms/'.$cms->lang.'/fonts" class="greenButtonWide ">'.$cms->translate(352).'</a><div class="c"></div><br />
					<div class="addColumn narrow full NB">
						<div class="itemWrap">  
							<div class="itemLabel">'.$cms->translate(300).'</div>
							<div class="itemElementWrap">
								<div class="itemElementShadow"><input type="text" class="itemElement text" id="testPhrase" value="Myślę, fruń z płacht gąsko, jedź wbić nóż" /></div> 
							</div>
						</div>
						<div class="itemWrap">  
							<div class="itemLabel">'.$cms->translate(301).'</div>
							<div class="itemElementWrap">
								<div class="itemElementShadow"><input type="text" class="itemElement text" maxlength="2" id="fontSize" style="margin-right:30px;" value="14" />
								<a href="#set" id="setFonts" class="greenButtonSmall">'.$cms->translate(302).'</a> </div> 
							</div>
						</div>
					</div>
					<div class="addColumn full NB">
						<table id="fontsTable">';
					$cms->executeQuery("SELECT * FROM cms_fonts ORDER BY name",1);
					while($row = mysqli_fetch_assoc($cms->result1)) {
                        echo '<style type="text/css">';
						if($row["system"] == 0) {
                            if($row["external"] == 1) {
                                echo '@import url("'.$row["url"].'");';
                            }
                            else {
                                echo '
                                @font-face {
                                    font-family: "'.$row["name_css"].'";
                                    src:    url("/_fonts/'.$row["name_css"].'.woff2") format("woff2"),
                                            url("/_fonts/'.$row["name_css"].'.woff") format("woff");
                                    font-weight: normal;
                                    font-style: normal;
                                }
                                ';
                            }
                        }
                        echo '</style>
                            <tr>
								<td class="fontTest">'.$row["name"].'</td>
								<td class="testPhrase" style="font-family:'.$row["name_css"].' !important;"></td>  
							</tr>';
					}
			echo '		</table>
					</div>
				</div>';
		break;
	} 
?>