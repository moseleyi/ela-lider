<script type="text/javascript">
	$(document).ready(function() {
		lemon.object_delete(".removeImage","<?php echo $cms->translate(44); ?>", "name", "delimg");
		lemon.object_delete(".delPromobox","<?php echo $cms->translate(146); ?>", "name", "del");
		
		$('#colorPicker1').colorpicker({color:"000000",colorFormat:"HEX",showOn: 'focus',showSwatches: true,showNoneButton: true,buttonColorize: true, parts: ['header','footer','map','bar'], altProperties: 'background-color,color',select:function(event,color){$(".colorPickerBox").eq(0).css("background-color",color.formatted);},regional:'<?php echo $cms->cmsL;?>'});
		$('#colorPicker2').colorpicker({color:"000000",colorFormat:"HEX",showOn: 'focus',showSwatches: true,showNoneButton: true,buttonColorize: true, parts: ['header','footer','map','bar'], altProperties: 'background-color,color',select:function(event,color){$(".colorPickerBox").eq(1).css("background-color",color.formatted);},regional:'<?php echo $cms->cmsL;?>'});
		$('#colorPicker3').colorpicker({color:"000000",colorFormat:"HEX",showOn: 'focus',showSwatches: true,showNoneButton: true,buttonColorize: true, parts: ['header','footer','map','bar'], altProperties: 'background-color,color',select:function(event,color){$(".colorPickerBox").eq(2).css("background-color",color.formatted);},regional:'<?php echo $cms->cmsL;?>'}); 
		
		/* Link preview */
		var status = true;
		$("#linkPreview").change(function() {
			var v = $(this).val();
			if(status == true) {
				status = false;
				$.post("/ajax/linkpreview",{id:v,lang:"<?php echo $cms->lang;?>"},function(data) {
					$("#linkPreviewText").text(data);
					status = true;
				});
			}
		});
	});
</script>
<?php
	$cms->checkAccess();
	switch($cms->a) {
		default:
		case "list":
			echo '
			<div id="moduleWrap">
				<a href="'.$cms->get_link("promobox,add").'" class="greenButtonWide ">'.$cms->translate(129).'</a><div class="c"></div><br />
				<table class="table">
					<tr>
						<td class="head" width="40px">Lp.</td> 
						<td class="head" width="40px">'.$cms->translate(208).'</td>
						<td class="head">'.$cms->translate(130).'</td>
						<td class="head" width="230px">'.$cms->translate(374).'</td>
						<td class="head" width="180px">'.$cms->translate(9).'</td>
						<td class="head" width="180px">Link</td>
						<td class="head" width="100px">'.$cms->translate(131).'</td> 
						<td class="head" width="60px">Status</td>
						<td class="head" width="60px">'.$cms->translate(14).'</td>
						<td class="head" width="60px">'.$cms->translate(15).'</td>
					</tr>';
			$i = 1;
			$cms->executeQuery("SELECT *,pm.id AS id,pm.status AS status,DATE(date_start) AS 'date_start', DATE(date_end) AS 'date_end' FROM cms_promobox pm LEFT JOIN cms_menu m ON pm.pageId=m.id ORDER BY pm.name ASC",1);
			while($row = mysqli_fetch_assoc($cms->result1)) {
				if(empty($row["file"])) { 
					$size = '<span class="errorSmall">'.$cms->translate(222).'</span>';
				}
				else {
					list($w,$h) = getimagesize("_images_content/promobox/".$row["file"]); 
					$size = $w.'px x '.$h.'px';
				}
						
				echo '<tr>
						<td class="body lvl0"><strong>'.$i.'.</strong></td> 
						<td class="body lvl0"><a href="'.$cms->get_link("promobox,test,".$row["id"]).'" class="link_preview plink">&nbsp;</a></td>
						<td class="body alignleft lvl0">'.$row["name"].'</td>
						<td class="body lvl0">'.$cms->convertDate($row["date_start"],false,$cms->lang).' - '.$cms->convertDate($row["date_end"], false, $cms->lang).'</td>
						<td class="body lvl0">'.($row["extName"]==""?'<span class="errorSmall">'.$cms->translate(220).'</span>':$row["extName"]).'</td>
						<td class="body lvl0"><a href="'.$row["link"].'" class="promolink">'.$row["link"].'</a></td>
						<td class="body lvl0">'.$size.'</td> 
						<td class="body lvl0"><a href="'.$cms->get_link("promobox,status,".$row["id"]).'" class="link_'.($row["status"]==1?'show':'hide').' plink">&nbsp;</a></td>
						<td class="body lvl0"><a href="'.$cms->get_link("promobox,edit,".$row["id"]).'" class="link_edit plink">&nbsp;</a></td>
						<td class="body lvl0"><a href="#" class="link_delete plink delPromobox" name="'.$row["id"].'">&nbsp;</a></td>
					</tr>';
				$i++;
			}
			echo' </table>
				</div>';
		break;
		case "test":		
			$cms->executeQuery("SELECT * FROM cms_promobox WHERE id='$cms->id'",1);
			$row = mysqli_fetch_assoc($cms->result1);
			if($row["file"] != "") {
				if(!file_exists("_images_content/promobox/".$row["file"])) {
					$cms->setSessionInfo(false, $cms->translate(490));
					header("Location:".$cms->get_link("promobox"));
					die();
				}
				list($w,$h) = getimagesize("_images_content/promobox/".$row["file"]);  
				$html = '<div id="promobox"><div id="promoboxBg" style="background-color:#'.$row["bgColor"].'"></div><div id="promobox_inside" style="padding:20px;background-color:#'.$row["borderColor"].';"><a href="'.$row["link"].'" title="'.$row["name"].'">'.($row["textPosition"]=="top"?'<div id="promoboxText">'.$cms->esc($row["content"]).'</div>':'').'<img src="/_images_content/promobox/'.$row["file"].'" alt="'.$row["name"].'" id="promoboxImage"/>'.($row["textPosition"]=="bottom"?'<div id="promoboxText">'.$cms->esc($row["content"]).'</div>':'').'</a><a href="#" id="closePromobox" style="background-color:#'.$row["borderColor"].';color:#'.$row["closeColor"].'">x</a></div></div>';
			}
			else {
				$w = 600;
				$h = 0;
				$html = '<div id="promobox"><div id="promoboxBg" style="background-color:#'.$row["bgColor"].'"></div><div id="promobox_inside" style="padding:20px;background-color:#'.$row["borderColor"].';"><a href="'.$row["link"].'" title="'.$row["name"].'">'.($row["textPosition"]=="top"?'<div id="promoboxText">'.$cms->esc($row["content"]).'</div>':'').'</a><a href="#" id="closePromobox" style="background-color:#'.$row["borderColor"].';color:#'.$row["closeColor"].'">x</a></div></div>';
			}
			echo '<script type="text/javascript">
					$(document).ready(function() {
						$(document).click(function(e) {
							if ($(e.target).prop("id") != "promobox_inside"){
								$("body").css("overflow","auto");
								$("#promobox").fadeOut(500);
								window.location.href= "/cms/'.$cms->lang.'/promobox";
							} 
						});
						var orgHeight = '.$h.';
						var orgWidth = '.$w.';
						var bodyHeight = $("body").height();
						var bodyWidth = $("body").width();
						var fitW = "'.$row["fitWindow"].'";  
 						
						$("#infowrap").before(\''.$html.'\');
						$("#promobox").show().css("opacity",0);
						var textHeight = $("#promoboxText").height() || 0;
						
						if(fitW == "1") {
							
							// Fit height to window
							 $("#promobox_inside").height(bodyHeight - (textHeight+60));
							 
							// Calculate the width
							 var newWidth = (orgWidth * $("#promobox_inside").height()) / orgHeight;
							 $("#promobox_inside").width(newWidth);
							 
							// If new width is longer than body, set new width and calculate the height again
							 if(newWidth >= bodyWidth+40) {
								 $("#promobox_inside").width(bodyWidth-60);
								 $("#promobox_inside").height(($("#promobox_inside").width() * orgHeight) / orgWidth);
							 
							 }
							// If body height is bigger than calculated height, make the promobx vertically positioned
							if(bodyHeight > ($("#promobox_inside").height() + 60)) {
								$("#promobox_inside").css("margin-top",(bodyHeight - ($("#promobox_inside").height()+40))/2);
							}
							$("#promoboxImage").height( $("#promobox_inside").height() - (textHeight));
						}
						
						else {
							 
							$("#promobox_inside").height(orgHeight+textHeight);
							
							// If body height is smaller than original promobox size, show scroll
							if(bodyHeight < $("#promobox_inside").height()) { 
								$("#promobox_inside").css("margin-top",10);
							}
							else {
								$("#promobox_inside").css("margin-top",(bodyHeight - ($("#promobox_inside").height()+40))/2);
							}
							
							$("#promobox_inside").width(orgWidth);
						
							$(window).resize(function() {
								var bodyHeight = $("body").height();
								if(bodyHeight > ($("#promobox_inside").height() + 60)) {
									$("#promobox_inside").css("margin-top",(bodyHeight - ($("#promobox_inside").height()+40))/2);
								}							
							});
							
						}
						
						$("#closePromobox").click(function() {
							$("body").css("overflow","auto");
							$("#promobox").fadeOut(500);
							window.location.href= "'.$cms->get_link("promobox").'"
						}); 
						$("#closePromobox").css("left",$("#promobox_inside").width()+3);
						$("body").css("overflow","hidden");
						$("#promobox").animate({"opacity":1});
					});
				  </script> ';
		break;
		case "add":
		case "edit":
			/*** EDIT ***/
			if($cms->a == "edit") {
				$cms->executeQuery("SELECT * FROM cms_promobox WHERE id='$cms->id'",1);
				$row = mysqli_fetch_assoc($cms->result1);
				$name = $row["name"];
				$content = $row["content"];
				$link = $row["link"];
				$file_old = $row["file"];
				$cms->fn = $row["file"];
				$page_id = $row["pageId"];
				$color_bg = $row["bgColor"];
				$color_border = $row["borderColor"];
				$color_close = $row["closeColor"];
				$date_start = $row["date_start"];
				$fit_window = $row["fitWindow"];
				$date_end = $row["date_end"];
				$text_position = $row["textPosition"]; 
				$old_date_start = $row["date_start"];
				$old_date_end = $row["date_end"];
			}
			else {
				$name = $content = $link = $page_id = $color_bg = $color_border = $color_close = $date_start = $fit_window = $date_end = $text_position = '';
			}
			if(isset($_POST["submit"])) { 
				$name = $_POST["name"];
				$content = $_POST["content"];
				$page_id = $_POST["page_id"];
				$link = $_POST["link"]; 
				$file = $_FILES["file"];
				$filename = $cms->createValidName($file["name"]);
				$color_bg = $_POST["color_bg"]; 
				$color_border = $_POST["color_border"];
				$color_close = $_POST["color_close"];
				$date_start = $_POST["date_start"];
				$date_end = $_POST["date_end"];
				$fit_window = $_POST["fit_window"];
				$text_position = $_POST["text_position"];
				preg_match("/^http:\/\/|https:\/\$/",$link,$a);
				preg_match("/^www\.$/",$link,$b);     
				$c = strip_tags($content);
				if(empty($name) || empty($page_id) || empty($color_bg) || empty($color_border) || empty($color_close)) {
					$cms->setInfo(false,$cms->translate(23)); 
				}   
				elseif($cms->a == "add" && empty($file["name"]) && empty($c)) {
					$cms->setInfo(false,$cms->translate(221));
				}
				elseif($cms->a == "edit" && empty($file["name"]) && empty($cms->fn) && empty($c)) {
					$cms->setInfo(false, $cms->translate(221));
				}
				else { 
					$e = false;
					if($file["name"] != "") {						
						if(move_uploaded_file($file["tmp_name"], "_images_content/promobox/".$filename) == false) {
							$e = true;
						} 
						else {
							$cms->fn = $filename;
						}
						if($e == true) {
							$cms->fn = $file_old;
						}
						elseif($cms->a == "edit" && $e == false & $file["name"] != $file_old) {
							unlink("_images_content/promobox/".$file_old);
						}
					} 
					if($e == false) {
					/*** ADD ***/
						if($cms->a == "add") {
							$cms->executeQuery("INSERT INTO cms_promobox (`id`, `name`, `pageId`, `link`, `content`, `file`, `bgColor`, `status`, `fitWindow`, `borderColor`, `closeColor`, `textPosition`, `date_start` ,`date_end`) VALUES ('', '$name', '$page_id', '$link', '$content', '$cms->fn', '$color_bg', '0', '$fit_window', '$color_border', '$color_close', '$text_position', '$date_start', '$date_end')",1);
							if($cms->result1) {
								$cms->setSessionInfo(true,$cms->translate(140));
								$cms->saveAction($name,"");
								header("Location:".$cms->get_link("promobox"));
							}
							else {
								$cms->setInfo(false,$cms->translate(27));  
							}
						}
					/*** EDIT ***/
						else {	  
							if($date_start != $old_date_start || $date_end != $old_date_end) {
								$cms->executeQuery("UPDATE cms_promobox SET status='0' WHERE id='$cms->id'",2);
							}
							$cms->executeQuery("UPDATE cms_promobox SET name='$name',pageId='$page_id',link='$link',content='$content',file='$cms->fn',bgColor='$color_bg',borderColor='$color_border',closeColor='$color_close',textPosition='$text_position',fitWindow='$fit_window', date_start='$date_start',date_end='$date_end' WHERE id='$cms->id'",1); 
							if($cms->result1) { 
								$cms->setSessionInfo(true,$cms->translate($cms->a == "edit" ? 145 : 140));
								$cms->saveAction($name,"");
								header("Location:".$cms->get_link("promobox"));
							}
							else { 
								$cms->setInfo(false,$cms->translate(27));  
							} 
						}
					} 
					else {						
						$cms->setInfo(false,$cms->translate(139));
					}
				}
			}
			echo ' <form method="post" enctype="multipart/form-data" action="'.$cms->get_link("promobox,".$cms->a.''.($cms->a=="edit"?','.$cms->id:'')).'"> 
					<div id="addColumnMain">
						<div class="addColumn narrow left extraPad">
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(130).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"><input type="text" class="itemElement text" name="name" value="'.htmlspecialchars($name).'" /></div>
									<div class="itemType vital" title="'.$cms->translate(241).'">!</div>
								</div>
							</div> 
							<div class="itemWrap">
								<div class="itemLabel">'.$cms->translate(9).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow">
										<select class="itemElement select" name="page_id" />
											<option value="0">----- '.$cms->translate(20).' -----</option>';
											$spaces = '';
											function showTree($parentId) {
												global $cms; 
												global $page_id;
												$cms->executeQuery("SELECT * FROM cms_menu WHERE lang='$cms->lang' AND parentId='$parentId' AND type != 5 AND id!='$cms->id' ORDER BY position ASC",$parentId);
												while($row[$parentId] = mysqli_fetch_assoc($cms->{"result".$parentId})) {
													$id = $row[$parentId]["id"];
													$spaces = '';
													for($i=1;$i<=$row[$parentId]["level"];$i++) {
														$spaces .= '&nbsp;&nbsp;&nbsp;&nbsp;';
													}
													echo '<option value="'.$id.'"'.($page_id == $id ? ' selected="selected"' : '').'>'.$spaces.''.$row[$parentId]["extName"];
													if($cms->getCount("cms_menu","WHERE parentId='$id' AND type != 5") > 0) {
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
							</div>   				
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(134).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow itemElementFile">
										<div class="itemElementFileButton">'.$cms->translate(250).'</div>
										<div class="itemElementFileName"></div>
										<input type="file" class="itemElement file" name="file" />
									</div>
									<div class="itemType helpful" title="'.$cms->translate(242).'">?</div>
									'.($cms->a == "edit" && !empty($cms->fn) ? '<div class="itemComment centerContent"><a href="#" class="removeImage" name="'.$cms->id.'">x</a><img src="/_images_content/promobox/'.$cms->fn.'" /></div>' : '').'
								</div>
							</div>
							<div class="itemWrap ">
								<div class="itemLabel">Podgląd linków</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow">
										<select class="itemElement select" id="linkPreview" />
											<option value="0">----- '.$cms->translate(20).' -----</option>'; 
											$spaces = '';
											function showTree2($parentId) {
												global $cms; 
												global $page_id;
												$cms->executeQuery("SELECT * FROM cms_menu WHERE lang='$cms->lang' AND parentId='$parentId' AND type != 5 AND id!='$cms->id' ORDER BY position ASC",$parentId);
												while($row[$parentId] = mysqli_fetch_assoc($cms->{"result".$parentId})) {
													$id = $row[$parentId]["id"];
													$spaces = '';
													for($i=1;$i<=$row[$parentId]["level"];$i++) {
														$spaces .= '&nbsp;&nbsp;&nbsp;&nbsp;';
													}
													echo '<option value="'.$id.'"'.($page_id == $id ? ' selected="selected"' : '').'>'.$spaces.''.$row[$parentId]["extName"];
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
							<div class="itemWrap ">
								<div class="itemLabel">Link</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"><input type="text" class="itemElement text" name="link" value="'.$link.'" /></div>
									<div class="itemType optional" title="'.$cms->translate(243).'">?</div>
								</div>
							</div> 		
						</div>
						<div class="addColumn narrow right">							
							<div class="itemWrap "">
								<div class="itemLabel">'.$cms->translate(211).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"><input type="text" class="itemElement text" id="datePickerPromo1" name="date_start" value="'.$date_start.'" /></div>
									<div class="itemType optional" title="'.$cms->translate(243).'">?</div>
								</div>
							</div> 							
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(212).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"><input type="text" class="itemElement text" id="datePickerPromo2" name="date_end" value="'.$date_end.'" /></div>
									<div class="itemType optional" title="'.$cms->translate(243).'">?</div>
								</div>
							</div> 					
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(135).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"><input type="text" class="itemElement text colorPicker" name="color_bg" value="'.$color_bg.'" id="colorPicker1" /></div>
									<div class="itemType vital" title="'.$cms->translate(241).'">?</div>
									<div class="colorPickerBox" style="background-color:#'.$color_bg.';">&nbsp;</div>
								</div>
							</div> 						
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(214).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"><input type="text" class="itemElement text colorPicker" name="color_border" value="'.$color_border.'" id="colorPicker2" /></div>
									<div class="itemType vital" title="'.$cms->translate(241).'">?</div>
									<div class="colorPickerBox" style="background-color:#'.$color_border.';">&nbsp;</div>
								</div>
							</div> 						
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(215).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"><input type="text" class="itemElement text colorPicker" name="color_close" value="'.$color_close.'" id="colorPicker3" /></div>
									<div class="itemType vital" title="'.$cms->translate(241).'">?</div>
									<div class="colorPickerBox" style="background-color:#'.$color_close.';">&nbsp;</div>
								</div>
							</div> 
							<div class="itemWrap">
								<div class="itemLabel">'.$cms->translate(219).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"> 
										<select name="text_position" class="itemElement select">
											<option value="top"'.($text_position == "top" ? ' selected="selected"' : '').'>'.$cms->translate(217).'</option>
											<option value="bottom"'.($text_position == "bottom" ? ' selected="selected"' : '').'>'.$cms->translate(216).'</option>
										</select> 
									</div>
									<div class="itemType optional" title="'.$cms->translate(243).'">?</div>
								</div>
							</div>
							<div class="itemWrap">
								<div class="itemLabel">'.$cms->translate(218).'</div>
								<div class="itemElementWrap elementWrapCheck">
									<div class="itemElementShadow">
										<div class="checkElement'.($fit_window==1?' checked':'').'">
											<input type="checkbox" class="itemElement check" name="fit_window" value="1"'.($fit_window==1?' checked="checked"':'').' />
										</div>
									</div>
									<div class="itemType optional" title="'.$cms->translate(243).'">?</div>
								</div>
							</div>
						</div>
						<div class="addColumn full clear"><div class="itemWrap">
								<div class="itemLabel">'.$cms->translate(22).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"><textarea name="content" class="itemElement area" id="content">'.htmlspecialchars($content).'</textarea></div>
									<script type="text/javascript">loadEditor(\'content\',\'300px\');</script>
									<div class="itemType optional" title="'.$cms->translate(243).'">!</div>
								</div>
							</div>	
						</div>
						<div class="c"></div>
						<input type="submit" value="'.$cms->translate($cms->a=="add"?21:14).'" class="greenButtonLarge" name="submit" />
					</div>
					</form>'; 
		break;		
		case "status":  
			$cms->executeQuery("SELECT * FROM cms_promobox WHERE id='$cms->id'",1);
			$row = mysqli_fetch_assoc($cms->result1);
			$pageId = $row["pageId"];
			$date_start = $row["date_start"];
			$date_end = $row["date_end"];
			$n = $row["status"] == 0 ? "1" : "0";  
			// Check if the same page already has a promobox
			// If date start and date end are empty, any promobox that is active will cause problems
			if(empty($date_start) && empty($date_end) && $cms->getCount("cms_promobox","WHERE pageId='$pageId' AND status='1' AND id!='$cms->id'") > 0 && $n == 1) {
				$cms->setSessionInfo(false, $cms->translate(375));
			}
			// If there is a promobox active without dates, then those new dates are not important, it would cause problems anyway
			elseif($cms->getCount("cms_promobox","WHERE pageId='$pageId' AND status='1' AND id!='$cms->id' AND DATE(date_start)='' AND DATE(date_end)=''") > 0 && $n == 1) {
				$cms->setSessionInfo(false, $cms->translate(375));
			}
			// If start date is not empty, there can't be a promobox with end date after startdate
			elseif(!empty($date_start) && empty($date_end) && $cms->getCount("cms_promobox","WHERE pageId='$pageId' AND status='1' AND id!='$cms->id' AND DATE(date_end) != '' AND DATE(date_end) >= '$date_start'") > 0 && $n == 1) {
				$cms->setSessionInfo(false, $cms->translate(375));
			}
			// If end date is not empty, there can't be a promobox with start date earlier than enddate
			elseif(!empty($date_end) && empty($date_start) && $cms->getCount("cms_promobox","WHERE pageId='$pageId' AND status='1' AND id!='$cms->id' AND DATE(date_start) != '' AND DATE(date_start) <= '$date_end'") > 0 && $n == 1) {
				$cms->setSessionInfo(false, $cms->translate(375));
			}
			// If both dates are not empty there can't be interacting promoboxes
			elseif(!empty($date_start) && !empty($date_end) && $cms->getCount("cms_promobox","WHERE pageid='$pageId' AND status='1' AND id!='$cms->id' AND (
					(DATE(date_start) >= '$date_start' AND DATE(date_start) <= '$date_end') OR
					(DATE(date_end) >= '$date_start' AND DATE(date_end) <= '$date_end') OR
					(DATE(date_start) <= '$date_start' AND DATE(date_end) >= '$date_end') OR
					(date_start = '' AND date_end = '')
				)") > 0 && $n == 1) {
				$cms->setSessionInfo(false, $cms->translate(375));
			}
			else {
				$cms->executeQuery("UPDATE cms_promobox SET status='$n' WHERE id='$cms->id'",1);
				if($cms->result1) {
					$cms->setSessionInfo(true,$cms->translate(143));
					$cms->saveAction($row["name"],"");
				}
				else {
					$cms->setSessionInfo(false,$cms->translate(27));
				}
			}
			header("Location:".$cms->get_link("promobox"));
		break;
		case "del":
			$cms->executeQuery("SELECT * FROM cms_promobox WHERE id='$cms->id'",1);
			$row = mysqli_fetch_assoc($cms->result1);
			$file = $row["file"];
			$cms->executeQuery("DELETE FROM cms_promobox WHERE id='$cms->id'",2);
			if($cms->result2) {  		
				unlink("_images_content/promobox/".$file); 
				$cms->setSessionInfo(true,$cms->translate(144));
				$cms->saveAction($row["name"],"");
			}
			else {
				$cms->setSessionInfo(false,$cms->translate(27));
			}
			header("Location:".$cms->get_link("promobox"));
		break; 
		case "delimg":
			$cms->executeQuery("SELECT * FROM cms_promobox WHERE id='$cms->id'",1);
			$row = mysqli_fetch_assoc($cms->result1);
			$file = $row["file"];
			$cms->executeQuery("UPDATE cms_promobox SET file='',fitWindow='0' WHERE id='$cms->id'",2);
			if($cms->result2) {
				unlink("_images_content/promobox/".$file); 
				$cms->setSessionInfo(true, $cms->translate(145));
				$cms->saveAction($row["name"], $file);
			}
			else {
				$cms->setSessionInfo(false, $cms->translate(27));
			}
			header("Location:".$cms->get_link("promobox"));
		break;
	}
?>