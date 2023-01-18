<script type="text/javascript">
	$(document).ready(function() { 
	
		lemon.object_delete(".delNewsletter","<?php echo $cms->translate(73); ?>", "name", "del");
		lemon.newsletter.template_loader();
		
	/* Select all groups */
		$("#selectAll").click(function() {
			var checked = $(this).hasClass("checked");
			
			if(checked == true) {
				$(".newsletterGroup .checkElement").addClass("checked");
				$(".newslettergroup input").prop("checked",true);
			}
			else {
				$(".newsletterGroup .checkElement").removeClass("checked");
				$(".newslettergroup input").prop("checked",false);
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
					<a href="'.$cms->get_link("newsletter,add").'"  class="greenButtonWide ">'.$cms->translate(74).'</a><div class="c"></div><br />
					<table class="table">
						<tr>
							<td class="head" width="60px">'.$cms->translate(208).'</td>
							<td class="head" width="160px">'.$cms->translate(75).'</td>
							<td class="head" width="160px">'.$cms->translate(77).'</td>
							<td class="head">'.$cms->translate(33).'</td>
							<td class="head" width="140px">'.$cms->translate(437).'</td>
							<td class="head" width="70px">'.$cms->translate(78).'</td>
							<td class="head" width="70px">'.$cms->translate(14).'</td>
							<td class="head" width="70px">'.$cms->translate(15).'</td>
						</tr>';
				$i=1;
				$cms->executeQuery("SELECT *,DATE(dateAdded) AS dA1,TIME(dateAdded) AS dA2,DATE(dateSent) AS dA3,TIME(dateSent) AS dA4 FROM cms_newsletter WHERE lang='$cms->lang' ORDER BY dateAdded DESC",1);
				while($row = mysqli_fetch_assoc($cms->result1)) {
					$id = $row["id"];
					echo '	<tr>
								<td class="body lvl0"><a href="'.$cms->get_link("newsletter,test,".$row["id"]).'" class="link_preview plink">&nbsp;</a></td>
								<td class="body lvl0"><strong>'.$cms->convertDate($row["dA1"],false,"pl").'</strong><br />'.$row["dA2"].'</td>
								<td class="body lvl0">'.($row["dA3"] == '0000-00-00' ? '-' : '<strong>'.$cms->convertDate($row["dA3"],false,"pl").'</strong><br />'.$row["dA4"]).'</td>
								<td class="body alignleft lvl0">'.$row["title"].'</td>
								<td class="body lvl0">'.$cms->getCount("cms_newsletter_sends","WHERE newsletter_id='$id'").'</td>
								<td class="body lvl0">'.($row["dA3"] == '0000-00-00' ? '<a href="'.$cms->get_link("newsletter,send,".$row["id"]).'" class="link_send plink">&nbsp;</a>' : '<a href="'.$cms->get_link("newsletter,copy,".$row["id"]).'" class="link_copy plink" title="'.$cms->translate(438).'">&nbsp;</a>').'</td>
								<td class="body lvl0"><a href="'.$cms->get_link("newsletter,edit,".$row["id"]).'" class="link_edit plink">&nbsp;</a></td>
								<td class="body lvl0"><a href="#" class="delNewsletter link_delete plink" name="'.$row["id"].'">&nbsp;</a></td>
							</tr>';
				}
				echo '</table>
			</div>';
		break;
		case "test":
			$emails = '';
			$cms->executeQuery("SELECT * FROM cms_newsletter WHERE id='$cms->id'",1);
			$row = mysqli_fetch_assoc($cms->result1); 
			$title = $row["title"];
			$content = $row["content"];
			if(isset($_POST["submit"])) {
				$emails = $_POST["emails"];
				if(empty($emails) || strpos($emails,"@") < 1) {
					$cms->setInfo(false,$cms->translate(23));
				}
				else {
					if(strpos($emails,",") > -1) {
						$e_mails = explode(",",$emails);
					}
					else {
						$e_mails[0] = $emails;
					}
					include_once("class.phpmailer.php");
					$mail = new PHPMailer();
					$mail->IsMail();
					$mail->IsHTML(true);
					$mail->FromName = $cms->newsletter_from_name;
					$mail->From = $cms->newsletter_from_email; 
					$mail->Subject  = $title;
				    $mail->Body = str_replace("/_scripts/kcfinder/upload/images/",$cms->cw.'/_scripts/kcfinder/upload/images/',$content);
					$e = 0;    
					$c = 0;
					foreach($e_mails as $email) {
						$mail->ClearAddresses();
						$mail->AddAddress($email);
						if($mail->Send() == false) {
							$e++;
						} 
						else {
							$c++;
						}
					}
					if($e == 0){ 
						$cms->setSessionInfo(true,$cms->translate(92).' '.$c.' '.$cms->translate(201));
						header("Location:".$cms->get_link("newsletter"));
					}
					else {
						$cms->setInfo(false,$cms->translate(27));
					}
				}
			}
			echo ' <form method="post" enctype="multipart/form-data" action="'.$cms->get_link("newsletter,test,".$cms->id).'"> 
					<div id="addColumnMain">
						<div class="addColumn narrow left NB">
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(205).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"><input type="text" class="itemElement text" name="emails" value="'.$emails.'" /></div>
									<div class="itemType vital" title="'.$cms->translate(241).'">!</div>
									<div class="itemComment">'.$cms->translate(206).'</div>
								</div>
							</div>
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(207).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"><input type="text" class="itemElement text" value="'.htmlspecialchars($title).'" disabled="disabled"/></div> 
								</div>
							</div>
						</div> 
						<div class="c"></div><Br />
						<div class="itemLabel">'.$cms->translate(209).'</div>
						<div class="addColumn full clear NB" id="newsletterPreview">
							'.$content.'
						</div>
						<div class="c"></div>
						<input type="submit" value="'.$cms->translate(208).'" class="greenButtonLarge" name="submit" />	
					</div>
				</form> ';
		break;
		case "send":
			$cms->executeQuery("SELECT * FROM cms_newsletter WHERE id='$cms->id'",1);
			$row = mysqli_fetch_assoc($cms->result1);
			$title = $row["title"];
			$content = $row["content"];  
			
			echo '<div id="newsletterSendTitle">'.$cms->translate(439).': <strong class="colourGreen">'.$title.'</strong></div>';		
			
			if(isset($_POST["submit"]) || $cms->newsletter_ug == false) {
				$selectAll = (int)$_POST["selectAll"];
				$groups = $_POST["groups"];
				
				if($cms->newsletter_ug == false) {
					$selectAll = 1;
				}
				
				include_once("class.phpmailer.php");
				$mail = new PHPMailer();
				$mail->IsMail();
				$mail->IsHTML(true);
				$mail->FromName = $cms->newsletter_from_name;
				$mail->From = $cms->newsletter_from_email;
				$ads = $cms->getCount("cms_newsletter_users");
				$mail->Subject  = $title;
				$mail->Body = str_replace("/_scripts/kcfinder/upload/images/",$cms->cw.'/_scripts/kcfinder/upload/images/',$content);
				
				$e = 0;
				$good = 0;
				if($cms->newsletter_from_email == "" || $cms->cw == "") {
					$cms->setSessionInfo(false, $cms->translate(311));
				}
				else {
					$i = 0;
					$users = array();
					$cms->executeQuery("SELECT
											cnu.id AS 'user_id',
											cng.id AS 'group_id',
											cnu.email AS 'user_email'
										FROM cms_newsletter_users cnu 
											LEFT JOIN cms_newsletter_users_groups cnug ON cnug.user_id = cnu.id
											LEFT JOIN cms_newsletter_groups cng ON cng.id = cnug.group_id
										WHERE
											cnu.lang='$cms->lang' ".
											/* Perform group check only if Users Groups are switched on and you didn't select everybody */
											($cms->newsletter_ug == true && $selectAll != 1 ? 
												/* If we don't have group_id = 0 in the array perform group check only */
												(!in_array("0", array_keys($groups)) ?  " AND cng.id IN(".implode(",",array_keys($groups)).")" : 
													/* If group_id = 0 is the only one, perform only ISNULL check */
													(count(array_keys($groups)) == 1 ? " AND cng.id IS NULL " : /* If not perform group and isnull check */" AND (cng.id IN(".implode(",",array_keys($groups)).") OR cng.id IS NULL)")
												)
												: "")
										,1);
					while($row = mysqli_fetch_assoc($cms->result1)) {						
						$users[$i]["user_id"] = $row["user_id"];
						$users[$i]["user_email"] = $row["user_email"];
						$users[$i]["group_id"] = $row["group_id"];
						$i++;
					}  
					if(count($users) > 0) {
						foreach($users as $i => $u) {
							$user_id = $u["user_id"];
							$user_email = $u["user_email"];
							$group_id = $u["group_id"]; 
							$mail->ClearAddresses();
							$mail->AddAddress($user_email);
							if($mail->Send() == false) {
								$e++;
							}
							else {
								$cms->executeQuery("INSERT INTO cms_newsletter_sends (`id`, `user_id`, `newsletter_id`, `group_id`, `user_email`, `newsletter_title`) VALUES ('', '$user_id', '$cms->id', '$group_id', '$user_email', '$title')",12);
								$good++;
							}
						}
						if($good == 0) {
							$cms->setSessionInfo(false,$cms->translate(27));
						}
						elseif(empty($cms->cw)) {
							$cms->setSessionInfo(false,$cms->translate(248));
						}
						else {
							$cms->executeQuery("UPDATE cms_newsletter SET dateSent=NOW() WHERE id='$cms->id'",1);
							if($cms->result1) {
								$cms->setSessionInfo(true,$cms->translate(92).' '.$good.' '.$cms->translate(201)); 
								$cms->saveAction($title,$good);
							}
							else {
								$cms->setSessionInfo(false,$cms->translate(27));
							}
						}
					} 
					else {
							$cms->setSessionInfo(false,$cms->translate(481));
					}
				} 
				header("Location:".$cms->get_link("newsletter"));
			}
			
			if($cms->newsletter_ug == true) {
				echo ' <form method="post" enctype="multipart/form-data" action="'.$cms->get_link("newsletter,send,".$cms->id).'"> 
						<div id="addColumnMain">
							<div class="addColumn full NB">
								<div class="itemWrap ">
									<div class="itemElementWrap elementWrapCheck itemElementFloat">
										<div class="itemElementShadow">
											<div class="checkElement" id="selectAll">
												<input type="checkbox" class="itemElement check" name="selectAll" value="1" />
											</div>
										</div> 
									</div>
									<div class="itemLabel itemLabelFloat">'.$cms->translate(434).'</div>
									<div class="c"></div>
								</div>
								<div class="itemWrap ">
									<div class="itemLabel">'.$cms->translate(435).'</div>'; 
										$cms->executeQuery("SELECT 
																'0' AS 'id',
																'".$cms->translate(436)."' AS 'name',
																'$cms->lang' AS 'lang'
															UNION SELECT 
																*
															FROM
																cms_newsletter_groups cng  
															WHERE
																lang = '$cms->lang'
															",1);
										while($row = mysqli_fetch_assoc($cms->result1)) {
											echo '<div class="newsletterGroup">
													<div class="itemElementWrap">
														<div class="checkElement'.($row["user_id"] != "" ? ' checked' : '').'">
															<input type="checkbox" class="itemElement check" name="groups['.$row["id"].']" value="true"'.($row["user_id"] != "" ? 'checked="checked"' : '').' />
														</div>
														<label>'.$row["name"].'</label>
														<div class="c"></div>
													</div>
												</div>'; 
										}
						echo '		</div>
									<div class="c"></div>
								</div> 	
							</div> 
							<div class="c"></div>
							<input type="submit" value="'.$cms->translate(78).'" class="greenButtonLarge" name="submit" />	
							<br />
						</div>
					</form>';
			}
		break;
		case "copy":
			$cms->executeQuery("SELECT * FROM cms_newsletter WHERE id='$cms->id'",1);
			$row = mysqli_fetch_assoc($cms->result1); 
			$title = $row["title"];
			$content = $row["content"];
			$lang = $row["lang"];
			$cms->executeQuery("INSERT INTO cms_newsletter (`id`, `dateAdded`, `dateSent`, `title`, `content`, `lang`) VALUES ('', NOW(), '', '$title', '$content', '$lang')",1);
			if($cms->result1) {
				$id = $cms->lastInsertId();
				header("Location:".$cms->get_link("newsletter,send,$id"));
			}
			else {
				$cms->setSessionInfo(false, $cms->translate(27));
				header("Location:".$cms->get_link("newsletter"));
			}
		break;
		case "add":
		case "edit":
			/*** EDIT ***/
			if($cms->a == "edit") {
				$cms->executeQuery("SELECT * FROM cms_newsletter WHERE id='$cms->id'",1);
				$row = mysqli_fetch_assoc($cms->result1); 
				$title = $row["title"];
				$content = $row["content"];
			}
			else {
				$title = $content = '';
			}
			if(isset($_POST["submit"]) || isset($_POST["save"])) {
				$title = $_POST["title"];
				$content = $_POST["content"];
				if(empty($title) || empty($content)) {
					$cms->setInfo(false,$cms->translate(23));
				}
				else {
					/*** ADD ***/
					if($cms->a == "add") {
						$cms->executeQuery("INSERT INTO cms_newsletter (`id`, `dateAdded`, `dateSent`, `title`, `content`, `lang`) VALUES ('', NOW(), '', '".$cms->esc($title)."', '$content', '$cms->lang')",1);
					}
					/*** EDIT ***/
					else {						
						$cms->executeQuery("UPDATE cms_newsletter SET title='".$cms->esc($title)."',content='$content' WHERE id='$cms->id'",1);
					}
					if($cms->result1) {
						$cms->saveAction($title,"");
						if(!isset($_POST["save"])) {
							$cms->setSessionInfo(true,$cms->translate($cms->a == "add" ? 79 : 80));
							header("Location:".$cms->get_link("newsletter"));
						}
						else {
							$cms->setInfo(true, $cms->translate(80));
						}
					}
					else {
						$cms->setInfo(false,$cms->translate(27));
					}
				}
			}
			echo ' <form method="post" enctype="multipart/form-data" action="'.$cms->get_link("newsletter,".$cms->a.''.($cms->a=="edit"?','.$cms->id:'')).'"> 
					<div id="addColumnMain">
						<div class="addColumn narrow left">
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(33).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"><input type="text" class="itemElement text" name="title" value="'.htmlspecialchars($title).'" /></div>
									<div class="itemType vital" title="'.$cms->translate(241).'">!</div>
									<div class="itemComment">'.$cms->translate(249).'</div>
								</div>
							</div>
						</div>
						<div class="addColumn narrow right NB">
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(447).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow">
										<select class="itemElement select" id="load-template">
											<option value="0">----- '.$cms->translate(20).' -----</option>';
											$cms->executeQuery("SELECT * from cms_newsletter_templates WHERE lang='$cms->lang' ORDER BY name ASC",1);
											while($row = mysqli_fetch_assoc($cms->result1)) {
												echo '<option value="'.$row["id"].'">'.$row["name"].'</option>';
											}
							echo '		</select>
										<div class="itemType optional" title="'.$cms->translate(243).'">?</div>
									</div>
								</div> 
							</div>
						</div>
						<div class="addColumn full clear NB">
							<div class="itemWrap">
								<div class="itemLabel">'.$cms->translate(22).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"><textarea name="content" class="itemElement area" id="content">'.htmlspecialchars($content).'</textarea></div>
									<script type="text/javascript">loadEditor(\'content\',\'600px\');</script>
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
					</div>
				</form>'; 
		break; 
		case "del":
			$cms->executeQuery("SELECT * FROM cms_newsletter WHERE id='$cms->id'",1);
			$row = mysqli_fetch_assoc($cms->result1);
			$cms->executeQuery("DELETE FROM cms_newsletter WHERE id='$cms->id'",1);
			if($cms->result1) {
				$cms->setSessionInfo(true,$cms->translate(81));
				$cms->saveAction($row["title"],"");
			}
			else {
				$cms->setSessionInfo(false,$cms->translate(27));
			}
			header("Location:".$cms->get_link("newsletter"));
		break;
	}
?>