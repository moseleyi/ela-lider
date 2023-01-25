<script type="text/javascript">
	$(document).ready(function() {
		lemon.object_delete(".delNews","<?php echo $cms->translate(37); ?>", "name", "del");
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
			echo '
			<div id="moduleWrap">
				<a href="'.$cms->get_link("articles,add").'" class="greenButtonWide ">Dodaj artykuł</a><div class="c"></div><br />
				<table class="table">
					<tr>
						<td class="head" width="40px">Lp.</td>
						<td class="head" width="140px">'.$cms->translate(34).'</td>
						<td class="head">'.$cms->translate(33).'</td>
						<td class="head" width="60px">'.$cms->translate(12).'</td>
						<td class="head" width="250px">'.$cms->translate(296).'</td>';
					// if galleries for each news
						if($cms->news_ga == true) {
							echo '<td class="head" width="140px">'.$cms->translate(39).'</td>';
						}
						echo '
						<td class="head" width="70px">'.$cms->translate(14).'</td>
						<td class="head" width="70px">'.$cms->translate(15).'</td>
					</tr>';
			$i=1;
			$cms->executeQuery("SELECT * FROM cms_articles WHERE lang='$cms->lang' ORDER BY date DESC",1);
			while($row = mysqli_fetch_assoc($cms->result1)) {
				echo '<tr>
						<td class="body lvl0"><strong>'.$i.'.</strong></td>
						<td class="body lvl0">'.$cms->convertDate($row["date"],false,$cms->cmsL).'</td>
						<td class="body alignleft lvl0">'.$row["title"].'</td>
						<td class="body lvl0"><a href="'.$cms->get_link("articles,status,".$row["id"]).'" class="link_'.($row["status"] == 1 ? 'show' : 'hide').' plink">&nbsp;</a></td>
						<td class="body alignleft lvl0">'.$row["intName"].'</td>';
					// if galleries for each page
						if($cms->news_ga == true) {
							$galleryId = $row["galleryId"];
							$cms->executeQuery("SELECT * FROM cms_gallery WHERE id='$galleryId'",2);
							$row2 = mysqli_fetch_assoc($cms->result2);
							echo '<td class="body lvl0">'.$row2["extName"].'</td>';
						}
						echo '
						<td class="body lvl0"><a href="'.$cms->get_link("articles,edit,".$row["id"]).'" class="link_edit plink">&nbsp;</a></td>
						<td class="body lvl0"><a href="#" class="delNews link_delete plink" name="'.$row["id"].'">&nbsp;</a></td>
					</tr>';
				$i++;
			}
			echo '</table>
		</div>';
		break;
		case "add":
		case "edit":
			$cms->showIntNameInput(1);
			/*** EDIT ONLY ***/
			if($cms->a == "edit"){
				$cms->executeQuery("SELECT * FROM cms_articles WHERE id='$cms->id'",1);
				$row = mysqli_fetch_assoc($cms->result1);
				$title = $row["title"];
				$gallery_id = $row["galleryId"];
				$date = $row["date"];
				$content = $row["content"];
				$meta_title = $row["metaTitle"];
				$meta_desc = $row["metaDesc"];
				$cms->fn = $row["file"];
				$file_old = $row["file"];
				$meta_keys = $row["metaKeys"];
				$url = $row["intName"];
				$url_old = $row["intName"];
			}
			else {
				$url_old = isset($_POST["url"]) ? $_POST["url"] : '';
				$title = $date = $meta_desc = $meta_keys = $meta_title = $url = $content = '';
			}
			if(isset($_POST["submit"]) || isset($_POST["save"])) {
				$title = $_POST["title"];
				$date = $_POST["date"];
				$gallery_id = $_POST["gallery_id"];
				$content = $_POST["content"];
				$meta_title = $_POST["meta_title"];
				$file = $_FILES["file"];
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
				elseif($cms->checkExistingNames("cms_articles") == false && ($cms->urls == "current-noid" || $cms->urls == "full-noid")) {
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
                            $cms->executeQuery("SELECT * FROM cms_image_dimensions cid INNER JOIN cms_modules cm ON cid.moduleId = cm.id WHERE cm.shortName='articles' ORDER BY cid.id DESC",1);
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

                                if($cms->makeThumbnail(array("name" => $fname, "tmp_name" => $files["tmp_name"][$i]),"_images_content/articles/".$dir,$n, $row["width"], "width",$fit,$fill, $row["quality"]) == false) {
                                    $error++;
                                }
                            }
                            if($error == 0) {
                                    $ext = strtolower(pathinfo($fname,PATHINFO_EXTENSION));
                                // Save original in _original folder
                                // If image height is greater than 1200, resize it
                                if($h > 1200) {
                                    $cms->makeThumbnail($file,"_images_content/articles/_original", $n, 1200, "height", 0, 0, $row["quality"]);
                                    $cms->makeThumbnail($file,"_images_content/articles/_cropped", $n, 1200, "height", 0, 0, $row["quality"]);
                                }
                                else {
                                    move_uploaded_file($files["tmp_name"][$i],"_images_content/articles/_original/".$n.".".$ext);
                                    copy("_images_content/news/_original/".$n.".".$ext,"_images_content/articles/_cropped/".$n.".".$ext);
                                }

                                $position = $cms->getCount("cms_articles_files", "WHERE news_id='$cms->id'") + 1;
                                $fna = $n.'.'.$ext;
                                $cms->executeQuery("INSERT INTO cms_articles_files (`id`, `file`, `news_id`, `position`, `alt`, `description`, `link`, `main`) VALUES ('','$fna','$cms->id','$position','','','', '0')",1);
                            }
                        }
                    }
					$url = $cms->finalIntName;

					/*** ADD ***/
					if($cms->a == "add") {
						$cms->executeQuery("INSERT INTO cms_articles (`id`, `date`, `title`, `content`, `lang`,`galleryId`, `metaTitle`, `metaDesc`, `metaKeys`, `intName`, `status`, `file`) VALUES ('','$date','".$cms->esc($title)."','$content','$cms->lang','$gallery_id','".$cms->esc($meta_title)."','".$cms->esc($meta_desc)."','".$cms->esc($meta_keys)."', '$url', '0', '$cms->fn')",1);
					}

					/*** EDIT ***/
					else {
						$cms->executeQuery("UPDATE cms_articles SET title='".$cms->esc($title)."',date='$date',intName='$url',content='$content',galleryId='$gallery_id',metaTitle='".$cms->esc($meta_title)."',metaDesc='".$cms->esc($meta_desc)."',metaKeys='".$cms->esc($meta_keys)."',file='$cms->fn' WHERE id='$cms->id'",1);
					}

					if($cms->result1) {
						$cms->saveAction($title,$date);
						if(!isset($_POST["save"])) {
							$cms->setSessionInfo(true,$cms->translate($cms->a == "edit" ? 36 : 35));
							header("Location:".$cms->get_link("articles"));
						}
						else {
							$cms->setInfo(true, $cms->translate(36));
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
			echo ' <form method="post" enctype="multipart/form-data" action="'.$cms->get_link("articles,".$cms->a.''.($cms->a=="edit"?','.$cms->id:'')).'">
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

							/* GALLERY */
							if($cms->news_ga == true) {
								echo '<div class="itemWrap ">
									<span class="itemLabel">'.$cms->translate(39).'</span>
									<div class="itemElementWrap">
										<div class="itemElementShadow">
											<select class="itemElement select" name="gallery_id">
												<option value="0">----- '.$cms->translate(20).' -----</option>';
												$cms->executeQuery("SELECT * from cms_gallery WHERE lang='$cms->lang' AND category='0' ORDER BY position ASC",1);
												while($row = mysqli_fetch_assoc($cms->result1)) {
													echo '<option value="'.$row["id"].'"';
													if($gallery_id == $row["id"]) {
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
						if($cms->news_go == true && $cms->a == "edit") {
							echo  '
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
					<div class="addColumn full clear NB">
						<div class="itemWrap ">
							<a name="del"></a><a name="crop"></a><a name="restore"></a><a name="gallery"></a><a name="edit"></a>
							<div class="itemLabel"><strong>'.$cms->translate(39).'</strong>: '.$cms->translate(42).'</div>
							<ul id="image-sorting">';
								$cms->executeQuery("SELECT * FROM cms_articles_files WHERE news_id='$cms->id' ORDER BY position ASC",1);
								while($row = mysqli_fetch_assoc($cms->result1)) {
									list($w,$h) = getimagesize("_images_content/articles/_cropped/".$row["file"]);
									echo '<li id="'.$row["id"].'" class="image-wrap">
											<div class="image-container imageCont">
												'.$cms->centerImage('_images_content/articles/_lemon/'.$row["file"],143,100).'
											</div>
											<div class="imageTools">
												<a href="#edit" class="editGalPicture edit-image-details link_edit plink" name="'.$row["id"].'" title="'.$cms->translate(376).'">&nbsp;</a>
												<a href="#del" class="removeImage link_delete plink" name="'.$row["id"].'" title="'.$cms->translate(377).'">&nbsp;</a>
												<a href="#crop" class="crop-image cropImage link_crop plink overlay-build" name="'.$row["id"].'" title="'.$cms->translate(378).'" data-size="'.$w.'-'.$h.'">&nbsp;</a>
												<a href="#restore" class="restore-image restoreImage link_restore plink" name="'.$row["id"].'" title="'.$cms->translate(382).'">&nbsp;</a>
												'.($row["main"] == 0 ? '<a href="'.$cms->get_link("articles,main,".$row["id"]).'" class="link_main_on plink blog_image_main">&nbsp;</a>' : '<a href="#" class="link_main_on2 plink blog_image_main">&nbsp;</a>').'
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
			$cms->executeQuery("SELECT * FROM cms_articles WHERE id='$cms->id'",1);
			$row = mysqli_fetch_assoc($cms->result1);
			$cms->executeQuery("DELETE FROM cms_articles WHERE id='$cms->id'",1);
			if($cms->result1) {
				$cms->setSessionInfo(true,$cms->translate(38));
				$cms->saveAction($row["title"],$row["date"]);
			}
			else {
				$cms->setSessionInfo(false,$cms->translate(27));
			}
			header("Location:".$cms->get_link("articles"));
		break;
		case "status":
			$cms->executeQuery("SELECT * FROM cms_articles WHERE id='$cms->id'",1);
			$row = mysqli_fetch_assoc($cms->result1);
			$title = $row["title"];
			$status = $row["status"];
			$n = $status == 0 ? 1 : 0;
			$cms->executeQuery("UPDATE cms_articles SET status='$n' WHERE id='$cms->id'",1);
			if($cms->result1) {
				$cms->setSessionInfo(true,$cms->translate(334));
				$cms->saveAction($title,"");
			}
			else {
				$cms->setSessionInfo(false,$cms->translate(27));
			}
			header("Location:".$cms->get_link("articles"));
		break;
		case "delimg":
			$cms->executeQuery("SELECT *,g.id AS id,gf.file AS file FROM cms_articles_files gf LEFT JOIN cms_articles g ON gf.news_id=g.id WHERE gf.id='$cms->id'",1);
			$row = mysqli_fetch_assoc($cms->result1);
			$file = $row["file"];
			$news_id = $row["id"];
			$cms->executeQuery("DELETE FROM cms_articles_files WHERE id='$cms->id'",2);
			if($cms->result2) {
				$cms->setSessionInfo(true,$cms->translate(43));
				$cms->saveAction($row["title"],$file);
				$e = 0;
				$cms->executeQuery("SELECT cid.name AS 'name', cid.width AS 'width', cid.height AS 'height' FROM cms_image_dimensions cid INNER JOIN cms_modules cm ON cid.moduleId = cm.id WHERE cm.shortName='news'",2);
				while($row = mysqli_fetch_assoc($cms->result2)) {
					$dir = $row["name"] == "Lemon Thumb" ? "_lemon" : $row["width"].'x'.$row["height"];
					if(unlink("_images_content/news/".$dir."/".$file) == false) {
						$e++;
					}
				}
				if($e == 0) {
					unlink("_images_content/news/_cropped/$file");
				}
			}
			else {
				$cms->setSessionInfo(false,$cms->translate(27));
			}
			header("Location:".$cms->get_link("articles,edit,$news_id#edit"));
		break;
		case "moveimg":
			$cms->executeQuery("SELECT * FROM cms_articles WHERE id='$cms->id'",1);
			$row = mysqli_fetch_assoc($cms->result1);
			$title = $row["title"];
			$ids = $_POST["ids"];
			for ($idx = 0; $idx < count($ids); $idx+=1) {
				$id = $ids[$idx];
				$ordinal = $idx;
				$cms->executeQuery("UPDATE cms_articles_files SET position='$ordinal' WHERE id='$id'",1);
			}
			$cms->saveAction($title,"","news","moveimg");
		break;
		case "editd":
			$cms->executeQuery("SELECT * FROM cms_articles_files WHERE id='$cms->id'",2);
			$row = mysqli_fetch_assoc($cms->result2);
			$news_id = $row["news_id"];
			$file = $row["file"];
			$cms->executeQuery("SELECT * FROM cms_articles WHERE id='$post_id'",3);
			$row2 = mysqli_fetch_assoc($cms->result3);
			$title = $row2["title"];
			$alt = $_POST["alt"];
			$desc = $_POST["desc"];
			$link = $_POST["link"];
			$cms->executeQuery("UPDATE cms_articles_files SET alt='".$cms->esc($alt)."', description='".$cms->esc($desc)."', link='".$cms->esc($link)."' WHERE id='$cms->id'",1);
			if($cms->result1) {
				$cms->setSessionInfo(true,$cms->translate(48));
				$cms->saveAction($title,$file);
			}
			else {
				$cms->setSessionInfo(false,$cms->translate(27));
			}
			header("Location:".$cms->get_link("articles,edit,$news_id#edit"));
		break;
		case "main":
			$cms->executeQuery("SELECT * FROM cms_articles_files WHERE id='$cms->id'",1);
			$row = mysqli_fetch_assoc($cms->result1);
			$news_id = $row["news_id"];
			$file = $row["file"];
			$cms->executeQuery("SELECT * FROM cms_articles WHERE id='$news_id'",2);
			$row2 = mysqli_fetch_assoc($cms->result2);
			$title = $row2["title"];
			$cms->executeQuery("UPDATE cms_articles_files SET main='0' WHERE news_id='$news_id'",3);
			if($cms->result3) {
				$cms->executeQuery("UPDATE cms_articles_files SET main='1' WHERE id='$cms->id'",4);
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
			header("Location:".$cms->get_link("articles,edit,$news_id#edit"));
		break;
	}
?>