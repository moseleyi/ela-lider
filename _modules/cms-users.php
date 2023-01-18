<script type="text/javascript">
	$(document).ready(function() {	 
		lemon.object_delete(".delUser","<?php echo $cms->translate(89); ?>", "name", "del");
	});
</script>
<?php 
	$cms->checkAccess();
	switch($cms->a) {
		default:
		case "list": 
			echo '
				<div id="moduleWrap">
					<a href="'.$cms->get_link("users,add").'" class="greenButtonWide ">'.$cms->translate(85).'</a><div class="c"></div><br />					 
					<table class="table">
					<tr>
						<td class="head" width="40px">Lp</td>
						<td class="head" width="160px">'.$cms->translate(84).'</td>
						<td class="head">'.$cms->translate(433).'</td>
						<td class="head" width="300px">'.$cms->translate(432).'</td> 
						<td class="head" width="160px">'.$cms->translate(83).'</td> 
						<td class="head" width="70px">'.$cms->translate(14).'</td>
						<td class="head" width="70px">'.$cms->translate(15).'</td>
					</tr>';
			$i=1;
			$cms->executeQuery("SELECT DISTINCT
									cnu.*,
									DATE(dateAdded) AS dA1,
									TIME(dateAdded) AS dA2 
								FROM cms_newsletter_users cnu
									LEFT JOIN cms_newsletter_users_groups cnug ON cnu.id = cnug.user_id
								WHERE 
									lang='$cms->lang'  
									".($cms->id !== 0 ? ($cms->id == 0 ? "AND cnug.group_id IS NULL" : " AND cnug.group_id='$cms->id'") : "")."
								ORDER BY email ASC
							",1); 
			while($row = mysqli_fetch_assoc($cms->result1)) {
				$id = $row["id"];
				$groups = array();
				$cms->executeQuery("SELECT * FROM cms_newsletter_users_groups cnug INNER JOIN cms_newsletter_groups cng ON cnug.group_id = cng.id WHERE cnug.user_id='$id' ORDER BY cng.name ASC",2);
				while($row2 = mysqli_fetch_assoc($cms->result2)) {
					$groups[] = $row2["name"];
				}
				echo '	<tr>
							<td class="body lvl0"><strong>'.$i.'.</strong></td>
							<td class="body lvl0">'.$cms->convertDate($row["dA1"],false,"pl").' '.$row["dA2"].'</td>
							<td class="body alignleft lvl0">'.$row["email"].' ('.$row["name"].', '.$row["phone"].')</td> 
							<td class="body alignleft lvl0">'.implode(", ",$groups).'</td>
							<td class="body lvl0">'.$cms->getCount("cms_newsletter_sends","WHERE user_id='$id'").'</td> 
							<td class="body lvl0"><a href="'.$cms->get_link("users,edit,".$row["id"]).'" class="link_edit plink edit_user" name="'.$row["id"].'|'.$row["email"].'">&nbsp;</a></td>
							<td class="body lvl0"><a href="#" class="link_delete plink delUser" name="'.$row["id"].'">&nbsp;</a></td>
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
				$cms->executeQuery("SELECT * FROM cms_newsletter_users WHERE id='$cms->id'",1);
				$row = mysqli_fetch_assoc($cms->result1);
				$email = $row["email"];
				$name = $row["name"];
				$phone = $row["phone"];
			}
			else {
				$email = $name = $phon = $lang = '';
			}
			if(isset($_POST["submit"])) {
				$email = $_POST["email"];
				$name = $_POST["name"];
				$phone = $_POST["phone"];
				$groups = $_POST["groups"];
				if(empty($email)) {
					$cms->setInfo(false,$cms->translate(23)); 
				}
				elseif($cms->validateEmail($email) == false) {
					$cms->setInfo(false,$cms->translate(127)); 
				}
				else {
					/*** ADD ***/
					if($cms->a == "add") {
						$cms->executeQuery("INSERT INTO cms_newsletter_users (`id`,`dateAdded`, `name`, `email`, `phone`, `lang`) VALUES ('', NOW(), '$name', '$email', '$phone', '$cms->lang')",1);
						$cms->id = $cms->lastInsertId();
					}
					/*** EDIT ***/
					else{
						$cms->executeQuery("UPDATE cms_newsletter_users SET email='$email', name='$name', phone='$phone' WHERE id='$cms->id'",1);
					}
					
					if($cms->result1) { 
						$cms->executeQuery("SELECT * FROM cms_newsletter_groups cng LEFT JOIN cms_newsletter_users_groups cnug ON cng.id = cnug.group_id AND cnug.user_id='$cms->id'",13);
						while($row13 = mysqli_fetch_assoc($cms->result13)) {
							$ud = $row13["user_id"];   
							
							/* Action has been ticket but it hasn't been added in the past */
							if($groups[$row13["id"]] == "true" && $ud == "") {
								$cms->executeQuery("INSERT INTO cms_newsletter_users_groups (`user_id`, `group_id`) VALUES ('$cms->id', '".$row13["id"]."')",12); 
							}
							/* Action is not ticked but extists, delete id */
							else if($ud != "" && $groups[$row13["id"]] != "true") {
								$cms->executeQuery("DELETE FROM cms_newsletter_users_groups WHERE user_id='$cms->id' AND group_id='".$row13["id"]."'",14); 
							}
						}
						
						$cms->setSessionInfo(true,$cms->translate($cms->a == "add" ? 88 : 91));
						$cms->saveAction($email,$lang,"","edit");
						header("Location:".$cms->get_link("users")); 
					} 
					else {
						$cms->setInfo(false,$cms->translate(27)); 
					}
				}
			} 
			echo '<form method="post" enctype="multipart/form-data" action="'.$cms->get_link("users,".$cms->a.''.($cms->a=="edit"?','.$cms->id:'')).'"> 
					<div id="addColumnMain">
						<div class="addColumn narrow left extraPad">
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(86).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"><input type="text" class="itemElement text" name="email" value="'.$email.'" /></div>
									<div class="itemType vital" title="'.$cms->translate(241).'">!</div>
								</div>
							</div>
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(432).'</div>';
						$cc = ceil($cms->getCount("cms_newsletter_groups","WHERE lang='$cms->lang'") / 2);
						$i = 1; 
						echo '<div class="modules_actions">';
						$cms->executeQuery("SELECT * FROM cms_newsletter_groups cng LEFT JOIN cms_newsletter_users_groups cnug ON cng.id = cnug.group_id AND cnug.user_id='$cms->id' WHERE lang='$cms->lang' ORDER BY name ASC",1);
						while($row = mysqli_fetch_assoc($cms->result1)) {
							echo '<div class="itemElementWrap"><div class="checkElementSmall'.($row["user_id"] != "" ? ' checked' : '').'"><input type="checkbox" class="itemElement check" name="groups['.$row["id"].']" value="true"'.($row["user_id"] != "" ? 'checked="checked"' : '').' /></div> <label>'.$row["name"].'</label><div class="c"></div></div>';
							if($i == $cc) {
								echo '</div><div class="modules_actions">';
							}
							$i++;
						}
					echo '		</div>
								<div class="c"></div>
							</div> 	
						</div>
						<div class="addColumn narrow right">
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(104).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"><input type="text" class="itemElement text" name="name" value="'.$name.'" /></div>
									<div class="itemType optional" title="'.$cms->translate(243).'">?</div>
								</div>
							</div>
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(114).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"><input type="text" class="itemElement text" name="phone" value="'.$phone.'" /></div>
									<div class="itemType optional" title="'.$cms->translate(243).'">?</div>
								</div>
							</div>
						</div>
						<div class="c"><br /></div>
						<input type="submit" value="'.$cms->translate($cms->a=="add"?21:14).'" class="greenButtonLarge" name="submit" />
					</div>
				</form>';					
		break;
		case "del":
			$cms->executeQuery("SELECT * FROM cms_newsletter_users WHERE id='$cms->id'",2);
			$row = mysqli_fetch_assoc($cms->result2);
			$cms->executeQuery("DELETE FROM cms_newsletter_users WHERE id='$cms->id'",1);
			if($cms->result1) { 
				$cms->setSessionInfo(true, $cms->translate(128));
				$cms->saveAction($row["email"],$row["lang"]);
			}
			else {
				$cms->setInfo(false,$cms->translate(27));
			}
			header("Location:".$cms->get_link("users"));
		break;
	}
?>