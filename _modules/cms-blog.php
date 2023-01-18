<script type="text/javascript">
	$(document).ready(function() {		  
		lemon.object_delete(".delPost","<?php echo $cms->translate(37); ?>", "name", "del");
		lemon.object_delete(".removeImage","<?php echo $cms->translate(44); ?>", "name", "delimg"); 		 
		lemon.image_sorting();
		lemon.crop.construct("multi");
		lemon.image_edit();
	});
</script>
<?php 
	$cms->checkAccess();
	switch($cms->a) {
		default:
		case "list":
			if(isset($cms->id) && $cms->id != "") {
				list($year,$month) = explode("-",$cms->id);
			}
			else {
				$year = date("Y"); 
			} 
			echo '
			<div id="moduleWrap">
				<a href="'.$cms->get_link("blog,add").'" class="greenButtonWide ">'.$cms->translate(416).'</a>
				<div id="blog-archive">';
				$months = array(
					"pl"=>array("1"=>"Styczeń","2"=>"Luty","3"=>"Marzec","4"=>"Kwiecień","5"=>"Maj","6"=>"Czerwiec","7"=>"Lipiec","8"=>"Sierpien","9"=>"Wrzesień","10"=>"Październik","11"=>"Listopad","12"=>"Grudzień"),
					"en"=>array("1"=>"January","2"=>"February","3"=>"March","4"=>"April","5"=>"May","6"=>"June","7"=>"July","8"=>"August","9"=>"September","10"=>"October","11"=>"November","12"=>"December")
				);
				$cms->executeQuery("SELECT DISTINCT YEAR(date) AS 'Year' FROM cms_blog WHERE lang='$cms->lang' ORDER BY YEAR(date) DESC",1);
				while($row = mysqli_fetch_assoc($cms->result1)) {
					echo '<div class="blog-year">
							<a href="'.$cms->get_link("blog,list,".$row["Year"]).'" class="year'.($year == $row["Year"] ? ' active' : '').'">'.$row["Year"].' ('.$cms->getCount("cms_blog","WHERE lang='$cms->lang' AND YEAR(date)='".$row["Year"]."'").')</a>
							<span class="months">';
					foreach($months[$cms->cmsL] as $mi => $mo) {
						echo '<a href="'.$cms->get_link("blog,add,list".$row["Year"].'-'.$mi).'" class="month'.($month == $mi && $year == $row["Year"] ? ' active' : '').'">'.$mo.' ('.$cms->getCount("cms_blog","WHERE lang='$cms->lang' AND YEAR(date)='".$row["Year"]."' AND MONTH(date)='".$mi."'").')</a>';
					}
					echo '
								<div class="c"></div>
							</span>
							<div class="c"></div>
						</div>';
				}
			echo '
				</div><div class="c"></div><br />
				<table class="table">
					<tr>
						<td class="head" width="40px">Lp.</td>
						<td class="head" width="140px">'.$cms->translate(34).'</td>
						<td class="head">'.$cms->translate(33).'</td>
						<td class="head" width="350px">'.$cms->translate(296).'</td> 
						<td class="head" width="140px">'.$cms->translate(470).'</td>
						<td class="head" width="100px">'.$cms->translate(455).'</td> 
						<td class="head" width="70px">'.$cms->translate(12).'</td>
						<td class="head" width="70px">'.$cms->translate(14).'</td>
						<td class="head" width="70px">'.$cms->translate(15).'</td>
					</tr>';
			$i=1;
			$cms->executeQuery("SELECT * FROM cms_blog WHERE lang='$cms->lang' AND YEAR(date)='$year' ".(!empty($month) ? "AND MONTH(date)='$month'" : "")." ORDER BY date DESC",1);
			while($row = mysqli_fetch_assoc($cms->result1)) {
				$id = $row["id"];
				echo '<tr>
						<td class="body lvl0"><strong>'.$i.'.</strong></td>
						<td class="body lvl0">'.$cms->convertDate($row["date"],false,$cms->cmsL).'</td>
						<td class="body alignleft lvl0">'.$row["title"].'</td>
						<td class="body alignleft lvl0">'.$row["intName"].'</td> 
						<td class="body lvl0"><a href="'.$cms->get_link("blog,status-c,".$row["id"]).'" class="colourGreen commentsLink '.($row["comments_enabled"] == 1 ? " enabled" : " disabled").'">'.($row["comments_enabled"] == 1 ? $cms->translate(471) : $cms->translate(472)).'</a></td> 
						<td class="body lvl0"><a href="'.$cms->get_link("blog_comments,list,".$row["id"]).'" class="plink link_comments">'.$cms->getCount("cms_blog_comments","WHERE post_id='$id'").'</a> <span class="tobe-approved" title="'.$cms->translate(477).'">('.$cms->getCount("cms_blog_comments","WHERE post_id='$id' AND status='0'").')</span></td>
						<td class="body lvl0"><a href="'.$cms->get_link("blog,status,".$row["id"]).'" class="link_'.($row["status"] == 1 ? 'show' : 'hide').' plink">&nbsp;</a></td>
						<td class="body lvl0"><a href="'.$cms->get_link("blog,edit,".$row["id"]).'" class="link_edit plink">&nbsp;</a></td>
						<td class="body lvl0"><a href="#" class="delPost link_delete plink" name="'.$row["id"].'">&nbsp;</a></td>
					</tr>';
				$i++;
			}
			echo '</table>
		</div> ';
		break;
		case "status-c":
			$cms->executeQuery("SELECT * FROM cms_blog WHERE id='$cms->id'",1);
			$row = mysqli_fetch_assoc($cms->result1);
			$name = $row["title"];
			$cs = $row["comments_enabled"] == 0 ? 1 : 0;
			$cms->executeQuery("UPDATE cms_blog SET comments_enabled ='$cs' WHERE id='$cms->id'",2);
			if($cms->result2) {
				$cms->setSessioninfo(true, $cms->translate(473));
				$cms->saveAction($title,"");
			}
			else {
				$cms->setSessionInfo(false, $cms->translate(27));
			}
			header("Location:".$cms->get_link("blog"));
		break;
		case "add":
		case "edit":
			$cms->showIntNameInput(1);
			/*** EDIT ONLY ***/
			if($cms->a == "edit"){				
				$cms->executeQuery("SELECT * FROM cms_blog WHERE id='$cms->id'",1);
				$row = mysqli_fetch_assoc($cms->result1);
				$title = $row["title"]; 
				$date = $row["date"];
				$content = $row["content"];
				$meta_title = $row["metaTitle"];
				$meta_desc = $row["metaDesc"];
				$cms->fn = $row["file"];
				$file_old = $row["file"];
				$meta_keys = $row["metaKeys"]; 
				$url = $row["intName"];
				$url_old = $row["intName"];
				$t_arr = array();
				$cms->executeQuery("SELECT * FROM cms_blog_tags WHERE post_id='$cms->id' ORDER BY tag_name ASC",11);
				while($row = mysqli_fetch_assoc($cms->result11)) {
					$t_arr[] = $row["tag_name"];
				}
				$tags = implode(", ",$t_arr);
			}
			else {
				$url_old = isset($_POST["url"]) ? $_POST["url"] : '';
				$title = $date = $meta_desc = $meta_keys = $meta_title = $url = $content = '';
			}
			if(isset($_POST["submit"]) || isset($_POST["save"])) {
				$title = $_POST["title"];
				$date = $_POST["date"]; 
				$content = $_POST["content"];
				$meta_title = $_POST["meta_title"];
				$file = $_FILES["file"];
				$tags = $_POST["tags"];
				$meta_keys = $_POST["meta_keys"];
				$meta_desc = $_POST["meta_desc"];
				$url = empty($_POST["url"]) ? $cms->createIntName($title) : $_POST["url"];
				if(empty($title) || empty($date) || empty($content)) {
					$cms->setInfo(false,$cms->translate(23));
				}
				elseif($cms->news_int_auto == false && empty($url)) {
					$cms->setInfo(false, $cms->translate(23));
				}
				elseif($cms->validateInternalName($url, $url, 1, $url_old) == false && !empty($url)) {
					$cms->setInfo(false, $cms->translate(297));
				}
				elseif($cms->checkExistingNames("cms_blog") == false && ($cms->urls == "current-noid" || $cms->urls == "full-noid")) {
					$cms->setInfo(false, $cms->translate(412));
				}
				else {				
                    $files = $_FILES["files"];
                    $error = 0;

                    if(count($files["name"]) > 0) {
                        foreach($files["name"] as $i => $fname) {  
                            // Get size of the original image
                            list($w,$h) = getimagesize($files["tmp_name"][$i]);
                            $n = $cms->getRandomName();
                            $cms->executeQuery("SELECT * FROM cms_image_dimensions cid INNER JOIN cms_modules cm ON cid.moduleId = cm.id WHERE cm.shortName='blog' ORDER BY cid.id DESC",1);
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

                                if($cms->makeThumbnail(array("name" => $fname, "tmp_name" => $files["tmp_name"][$i]),"_images_content/blog/".$dir,$n, $row["width"], "width",$fit,$fill, $row["quality"]) == false) {
                                    $error++;
                                }
                            }
                            if($error == 0) { 
                                    $ext = strtolower(pathinfo($fname,PATHINFO_EXTENSION));
                                // Save original in _original folder
                                // If image height is greater than 1200, resize it
                                if($h > 1200) {
                                    $cms->makeThumbnail($file,"_images_content/blog/_original", $n, 1200, "height", 0, 0, $row["quality"]);
                                    $cms->makeThumbnail($file,"_images_content/blog/_cropped", $n, 1200, "height", 0, 0, $row["quality"]);
                                }
                                else {
                                    move_uploaded_file($files["tmp_name"][$i],"_images_content/blog/_original/".$n.".".$ext); 
                                    copy("_images_content/blog/_original/".$n.".".$ext,"_images_content/blog/_cropped/".$n.".".$ext); 
                                } 

                                $position = $cms->getCount("cms_blog_files", "WHERE post_id='$cms->id'") + 1;
                                $fna = $n.'.'.$ext;
                                $cms->executeQuery("INSERT INTO cms_blog_files (`id`, `file`, `post_id`, `position`, `alt`, `description`, `link`, `main`) VALUES ('','$fna','$cms->id','$position','','','', '0')",1);  
                            } 
                        } 
                    }
                    
					$url = $cms->finalIntName;		
					/*** ADD ***/	
					if($cms->a == "add") {
						$cms->executeQuery("INSERT INTO cms_blog (`id`, `date`, `title`, `content`, `lang`, `metaTitle`, `metaDesc`, `metaKeys`, `intName`, `status`, `comments_enabled`, `visits_count`) VALUES ('','$date','".$cms->esc($title)."','$content','$cms->lang','".$cms->esc($meta_title)."','".$cms->esc($meta_desc)."','".$cms->esc($meta_keys)."', '$url', '0', '1', '0')",1); 
						$cms->id = $cms->lastInsertId(); 
					}
					
					/*** EDIT ***/
					else {
							$cms->executeQuery("UPDATE cms_blog SET title='".$cms->esc($title)."',date='$date',intName='$url',content='$content',metaTitle='".$cms->esc($meta_title)."',metaDesc='".$cms->esc($meta_desc)."',metaKeys='".$cms->esc($meta_keys)."' WHERE id='$cms->id'",1);
					 }
					
					if($cms->result1) {
					
						/* Tags */
						$cms->executeQuery("DELETE FROM cms_blog_tags WHERE post_id='$cms->id'",11);
						if($cms->result11) {
							$tags_ar = explode(",",$tags);
							foreach($tags_ar as $tag) {
								if($tag != "") {
									$cms->executeQuery("INSERT INTO cms_blog_tags (`tag_name`, `post_id`) VALUES ('".trim($tag)."', '$cms->id')",12);
								}
							}
						}	
						
						$cms->saveAction($title,$date);
						if(!isset($_POST["save"])) {
							list($year,$month,$day) = explode("-",$date);
							$cms->setSessionInfo(true,$cms->translate($cms->a == "edit" ? 508 : 507));
							header("Location:".$cms->get_link("blog,list,$year-$month"));
						}
						else {
							$cms->setInfo(true, $cms->translate(508));
						}
					}
					else {
						$cms->setInfo(false,$cms->translate(27));
					} 
                    if($error == 0) {
                        $cms->setSessionInfo(true, $cms->translate(41));
                    }
                    else {
                        $cms->setSessionInfo(false, $cms->translate(299));
                    }
				}
			} 
			echo ' <form method="post" enctype="multipart/form-data" action="'.$cms->get_link("blog,".$cms->a.''.($cms->a=="edit"?','.$cms->id:'')).'"> 
					<div id="addColumnMain">
						<div class="addColumn narrow left">
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(33).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"><input type="text" class="itemElement text" name="title" value="'.htmlspecialchars($title).'" /></div>
									<div class="itemType vital" title="'.$cms->translate(241).'">!</div>
								</div>
							</div>
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(34).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"><input type="text" class="itemElement text" name="date" value="'.$date.'" id="datePicker" /></div>
									<div class="itemType vital" title="'.$cms->translate(241).'">!</div>
								</div>
							</div>';
							
							/* SEO FRIENDLY LINK */
							if($cms->sinf == true ) {
								echo '
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(296).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"><input type="text" class="itemElement text" name="url" value="'.$url.'"/></div>
									<div class="itemType '.($cms->news_int_auto == false ? 'vital' : 'helpful').'" title="'.$cms->translate((24).($cms->news_int_auto == false ? 1 : 2)).'">'.($cms->news_int_auto == false ? '!' : '?').'</div>
									<div class="itemComment">'.$cms->translate(25).'</div>
								</div>
							</div>';
							}
							
						  
					echo '	
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
						</div>
						<div class="addColumn full clear NB	">
							<div class="itemWrap fullInput">
								<div class="itemLabel">'.$cms->translate(417).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"><input type="text" class="itemElement text" name="tags" value="'.$tags.'" /></div>
									<div class="itemType helpful" title="'.$cms->translate(242).'">?</div>
								</div>
							</div>	
						</div>			
						<div class="addColumn full clear">
							<div class="itemWrap">
								<div class="itemLabel">'.$cms->translate(22).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"><textarea name="content" class="itemElement area" id="content">'.htmlspecialchars($content).'</textarea></div>
									<script type="text/javascript">loadEditor(\'content\',\'300px\');</script>
									<div class="itemType vital" title="'.$cms->translate(241).'">!</div>
								</div>
							</div>	
						</div>
						<div class="c"></div>
						<div id="buttons">
							<input type="submit" value="'.$cms->translate($cms->a=="add"?21:14).'" class="greenButtonLarge greenButtonFloatLeft" name="submit" />	
							'.($cms->a == "edit" ? '<input type="submit" value="'.$cms->translate(506).'" name="save" class="greenButtonLarge greenButtonFloatLeft" />' : '').'
							<div class="c"></div>
						</div>
					</form>';
						
						/* Show galleries if $cms->blog_galleries is true */
						if($cms->blog_galleries == true && $cms->a == "edit") {
							echo '
					<a name="edit"></a>  
				<div class="c"></div> 
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
				</div>
				<form method="post" enctype="multipart/form-data" action="/cms/'.$cms->lang.'/blog/editd/" class="hidden" id="imageDetails">
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
					<div class="addColumn full clear NB">
						<div class="itemWrap "> 
							<a name="del"></a><a name="crop"></a><a name="restore"></a><a name="gallery"></a>
							<div class="itemLabel"><strong>'.$cms->translate(39).'</strong>: '.$cms->translate(42).'</div>
							<ul id="image-sorting">';
								$cms->executeQuery("SELECT * FROM cms_blog_files WHERE post_id='$cms->id' ORDER BY position ASC",1);
								while($row = mysqli_fetch_assoc($cms->result1)) { 
									list($w,$h) = getimagesize("_images_content/blog/_cropped/".$row["file"]);
									echo '<li id="'.$row["id"].'" class="image-wrap"> 
											<div class="image-container imageCont">
												'.$cms->centerImage('_images_content/blog/_lemon/'.$row["file"],143,100).'
											</div>
											<div class="imageTools">
												<a href="#edit" class="editGalPicture edit-image-details link_edit plink" name="'.$row["id"].'" title="'.$cms->translate(376).'">&nbsp;</a>
												<a href="#del" class="removeImage link_delete plink" name="'.$row["id"].'" title="'.$cms->translate(377).'">&nbsp;</a>
												<a href="#crop" class="crop-image cropImage link_crop plink overlay-build" name="'.$row["id"].'" title="'.$cms->translate(378).'" data-size="'.$w.'-'.$h.'">&nbsp;</a>
												<a href="#restore" class="restore-image restoreImage link_restore plink" name="'.$row["id"].'" title="'.$cms->translate(382).'">&nbsp;</a>
												'.($row["main"] == 0 ? '<a href="/cms/'.$cms->lang.'/blog/main/'.$row["id"].'" class="link_main_on plink blog_image_main">&nbsp;</a>' : '<a href="#" class="link_main_on2 plink blog_image_main">&nbsp;</a>').'
											</div>
											<div class="clear"></div>
										</li>';
								}
			echo ' 				<div class="c"></div>
							</ul> 
						</div>
					</div>	';
						}
						
				echo '
					</div>';
		break;
		case "del":
			$cms->executeQuery("SELECT * FROM cms_blog WHERE id='$cms->id'",1);
			$row = mysqli_fetch_assoc($cms->result1); 
			list($year,$month,$day) = explode("-",$row["date"]);
			$cms->executeQuery("DELETE FROM cms_blog WHERE id='$cms->id'",2); 
			if($cms->result1) {
				$cms->setSessionInfo(true,$cms->translate(419));
				$cms->saveAction($row2["title"],$row["date"]);
				$cms->executeQuery("DELETE FROM cms_blog_comments WHERE post_id='$cms->id'",3);
				$cms->executeQuery("SELECT * FROM cms_blog_files WHERE post_id='$cms->id'",4);
				while($row = mysqli_fetch_assoc($cms->result4)) {
					$id = $row["id"];
					$file = $row["file"];	
					$cms->executeQuery("SELECT * FROM cms_image_dimensions WHERE module='blog'",5);
					while($row2 = mysqli_fetch_assoc($cms->result5)) {
						$dir = $row2["name"] == "Lemon Thumb" ? "_lemon" : $row2["width"].'x'.$row2["height"];
						if(unlink("_images_content/blog/".$dir."/".$file) == false) {
							$e++;	
						}
					}
					$cms->executeQuery("DELETE FROM cms_blog_files WHERE id='$id'",6); 
				}
				$cms->executeQuery("SELECT * FROM cms_blog_tags WHERE post_id='$cms->id'",6);
			}
			else {
				$cms->setSessionInfo(false,$cms->translate(27));
			}
			header("Location:".$cms->get_link("blog,list,$year-$month"));
		break;
		case "status":  
			$cms->executeQuery("SELECT * FROM cms_blog WHERE id='$cms->id'",1);
			$row = mysqli_fetch_assoc($cms->result1);
			$title = $row["title"];
			list($year,$month,$day) = explode("-",$row["date"]);
			$status = $row["status"];
			$n = $status == 0 ? 1 : 0;
			$cms->executeQuery("UPDATE cms_blog SET status='$n' WHERE id='$cms->id'",1);
			if($cms->result1) {
				$cms->setSessionInfo(true,$cms->translate(418));
				$cms->saveAction($title,"");
			}
			else {
				$cms->setSessionInfo(false,$cms->translate(27));
			}
			header("Location:".$cms->get_link("blog,list,$year-$month"));
		break;
		case "delimg":
			$cms->executeQuery("SELECT *,g.id AS id,gf.file AS file FROM cms_blog_files gf LEFT JOIN cms_blog g ON gf.post_id=g.id WHERE gf.id='$cms->id'",1);
			$row = mysqli_fetch_assoc($cms->result1);
			$file = $row["file"];
			$post_id = $row["id"]; 
			$cms->executeQuery("DELETE FROM cms_blog_files WHERE id='$cms->id'",2);
			if($cms->result2) { 
				$cms->setSessionInfo(true,$cms->translate(43));
				$cms->saveAction($row["title"],$file);
				$e = 0;
				$cms->executeQuery("SELECT cid.name AS 'name', cid.width AS 'width', cid.height AS 'height' FROM cms_image_dimensions cid INNER JOIN cms_modules cm ON cid.moduleId = cm.id WHERE cm.shortName='blog'",2);
				while($row = mysqli_fetch_assoc($cms->result2)) { 				
					$dir = $row["name"] == "Lemon Thumb" ? "_lemon" : $row["width"].'x'.$row["height"];  
					if(unlink("_images_content/blog/".$dir."/".$file) == false) {
						$e++;
					} 
				}
				if($e == 0) {
					unlink("_images_content/blog/_cropped/$file");
				}
			}
			else {
				$cms->setSessionInfo(false,$cms->translate(27));
			}   
			header("Location:".$cms->get_link("blog,edit,$post_id"));
		break;
		case "moveimg": 
			$cms->executeQuery("SELECT * FROM cms_blog WHERE id='$cms->id'",1);
			$row = mysqli_fetch_assoc($cms->result1);
			$title = $row["title"]; 
			$ids = $_POST["ids"]; 
			for ($idx = 0; $idx < count($ids); $idx+=1) {
				$id = $ids[$idx];
				$ordinal = $idx;
				$cms->executeQuery("UPDATE cms_blog_files SET position='$ordinal' WHERE id='$id'",1);
			}     
			$cms->saveAction($title,"","blog","moveimg");  	
		break;
		case "editd":
			$cms->executeQuery("SELECT * FROM cms_blog_files WHERE id='$cms->id'",2);
			$row = mysqli_fetch_assoc($cms->result2);
			$post_id = $row["post_id"];
			$file = $row["file"];
			$cms->executeQuery("SELECT * FROM cms_blog WHERE id='$post_id'",3);
			$row2 = mysqli_fetch_assoc($cms->result3);
			$title = $row2["title"];
			$alt = $_POST["alt"]; 
			$desc = $_POST["content"];
			$link = $_POST["link"]; 
			$cms->executeQuery("UPDATE cms_blog_files SET alt='".$cms->esc($alt)."', description='".$cms->esc($desc)."', link='".$cms->esc($link)."' WHERE id='$cms->id'",1);
			if($cms->result1) {  
				$cms->setSessionInfo(true,$cms->translate(48));
				$cms->saveAction($title,$file); 
			}
			else {
				$cms->setSessionInfo(false,$cms->translate(27));
			}  
			header("Location:".$cms->get_link("blog,edit,$post_id"));
		break;
		case "main":
			$cms->executeQuery("SELECT * FROM cms_blog_files WHERE id='$cms->id'",1);
			$row = mysqli_fetch_assoc($cms->result1);
			$post_id = $row["post_id"];
			$file = $row["file"];
			$cms->executeQuery("SELECT * FROM cms_blog WHERE id='$post_id'",2);
			$row2 = mysqli_fetch_assoc($cms->result2);
			$title = $row2["title"];
			$cms->executeQuery("UPDATE cms_blog_files SET main='0' WHERE post_id='$post_id'",3);
			if($cms->result3) {
				$cms->executeQuery("UPDATE cms_blog_files SET main='1' WHERE id='$cms->id'",4);
				if($cms->result4) {
					$cms->setSessionInfo(true, $cms->translate(480));
					$cms->saveAction($title, $file);
				}
				else {
					$cms->setSessionInfo(false, $cms->translate(27));
				}
			}
			else {
				$cms->setSessionInfo(false, $cms->translate(27));
			}
			header("Location:".$cms->get_link("blog,edit,$post_id"));
		break;
	}
?>