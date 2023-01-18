<script type="text/javascript">
	$(document).ready(function() {		
		lemon.object_delete(".delAccount","<?php echo $cms->translate(116); ?>", "name", "del"); 
	});
</script>
<?php 
	$cms->checkAccess();
	switch($cms->a) {
		case "change":
			if(empty($_POST["newPass"]) || empty($_POST["oldPass"])) {
				$cms->setSessionInfo(false,$cms->translate(23));
			}
			elseif(strlen($_POST["newPass"]) < 6) {
				$cms->setSessionInfo(false,$cms->translate(227));
			}
			else {
				$new = hash("sha1",$_POST["newPass"]);
				$old = hash("sha1",$_POST["oldPass"]);
				$cms->executeQuery("SELECT * FROM cms_accounts WHERE id='$cms->id'",1);
				$row = mysqli_fetch_assoc($cms->result1);
				$oldP = $row["password"];
				if($old == $oldP) {
					$cms->executeQuery("UPDATE cms_accounts SET password='$new' WHERE id='$cms->id'",2);
					if($cms->result2) {
						$cms->setSessionInfo(true,$cms->translate(228));
					}
					else {
						$cms->setSessionInfo(false,$cms->translate(27));
					}
				}	
				else {
					$cms->setSessionInfo(false,$cms->translate(229));
				}
			}
			header("Location".$cms->get_link("accounts,edit,$cms->id"));
		break;
		default:
		case "list":
			echo '
				<div id="moduleWrap">
					<a href="'.$cms->get_link("accounts,add").'" class="greenButtonWide ">'.$cms->translate(95).'</a> <div class="c"></div><Br />
					<table class="table">
						<tr>
							<td width="40px" class="head">Lp.</td> 
							<td class="head">'.$cms->translate(97).'</td>
							<td width="120px" class="head">Login</td> 
							<td width="120px" class="head">'.$cms->translate(98).'</td>
							<td width="240px" class="head">'.$cms->translate(113).'</td> 
							<td width="160px" class="head">'.$cms->translate(103).'</td> 
							<td width="70px" class="head">'.$cms->translate(12).'</td>
							<td class="head" width="70px">'.$cms->translate(14).'</td>
							<td class="head" width="70px">'.$cms->translate(15).'</td>
						</tr>';
			$i = 1;
			$cms->executeQuery("SELECT *,ac.id AS id,ar.rank AS rank,DATE(dateRegistered) AS dA1,TIME(dateRegistered) AS dA2 FROM cms_accounts AS ac LEFT JOIN cms_accounts_ranks AS ar ON ac.rank=ar.id WHERE ac.rank != '1' ORDER BY ar.id ASC",1);
			while($row = mysqli_fetch_assoc($cms->result1)) {
				echo '<tr>
							<td class="body lvl0"><strong>'.$i.'.</strong></td>	
							<td class="body lvl0 alignleft">'.$row["name"].'</td>
							<td class="body lvl0">'.$row["login"].'</td>
							<td class="body lvl0"><span style="color:#'.$row["color"].'">'.(empty($row["rank"]) ? $cms->translate(488) : $row["rank"]).'</span></td>
							<td class="body lvl0">'.$row["email"].'</td> 	
							<td class="body lvl0">'.$cms->convertDate($row["dA1"],false,$cms->cmsL).' '.$row["dA2"].'</td> 
							<td class="body lvl0"><a href="'.$cms->get_link("accounts,status,".$row["id"]).'" class="plink link_'.($row["active"]==0?'in':'').'active">&nbsp;</a></td>
							<td class="body lvl0"><a href="'.$cms->get_link("accounts,edit,".$row["id"]).'" class="link_edit plink">&nbsp;</a></td>
							<td class="body lvl0"><a href="#" class="link_delete plink delAccount" name="'.$row["id"].'">&nbsp;</a></td>
						</tr>';
				$i++;
			}
			echo '	</table>
				</div>';
		break; 
		case "add":
		case "edit":
			/*** EDIT ***/
			if($cms->a == "edit") {
				$cms->executeQuery("SELECT * FROM cms_accounts WHERE id='$cms->id'",1);
				$row = mysqli_fetch_assoc($cms->result1);
				$name = $row["name"];
				$user_login = $row["login"]; 
				$email = $row["email"];
				$rank = $row["rank"];
			}
			else {
				$name = $user_login = $email = $rank = $user_pass = '';
			}
			if(isset($_POST["submit"])) {
				$name = $_POST["name"];
				$user_login = $_POST["user_login"];
				$user_pass = $_POST["user_pass"];
				$rank = $_POST["rank"];
				$email = $_POST["email"];
				if(empty($name) || empty($rank) || empty($email) || strpos($user_login, " ") !== false) {
					$cms->setInfo(false,$cms->translate(23));
				}
				else if($cms->a == "add" && (empty($user_login) || empty($user_pass))) {
					$cms->setInfo(false,$cms->translate(23));
				}
				else if(strlen($user_pass) < 6 && $cms->a == "add") {
					$cms->setInfo(false, $cms->translate(227));
				}
				else if($cms->getCount("cms_accounts","WHERE LOWER(login)='".strtolower($user_login)."'") > 0 && $cms->a == "add") {
					$cms->setInfo(false,$cms->translate(118));
				} 
				else {
					/*** ADD ***/
					if($cms->a == "add") {
						$pass = hash("sha256",$user_pass);
						$cms->executeQuery("INSERT INTO cms_accounts (`id`, `name`, `dateRegistered`, `login`, `password`, `email`, `rank`, `active`) VALUES ('', '$name', NOW(), '$user_login', '$pass', '$email','$rank', '0')",1);
					}
					/*** EDIT ***/
					else {
						$cms->executeQuery("UPDATE cms_accounts SET name='$name',login='$user_login',rank='$rank',email='$email' WHERE id='$cms->id'",1);
					}
					if($cms->result1) {
						$cms->setSessionInfo(true,$cms->translate($cms->a == "add" ? 107 : 115));
						$cms->executeQuery("SELECT * FROM cms_accounts_ranks WHERE id='$rank'",2);
						$row = mysqli_fetch_assoc($cms->result2);
						$cms->saveAction($name,$row["rank"]);
						header("Location:".$cms->get_link("accounts"));
					}
					else {
						$cms->setInfo(false,$cms->translate(27));
					}
				}
			} 
			echo ' <form method="post" enctype="multipart/form-data" action="'.$cms->get_link("accounts,".$cms->a.($cms->a=="edit"?','.$cms->id:'')).'"> 
					<div id="addColumnMain">
						<div class="addColumn narrow left extraPad">						
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(105).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"><input type="text" class="itemElement text" name="user_login" value="'.$user_login.'" /></div>
									<div class="itemType vital" title="'.$cms->translate(241).'">!</div>
								</div>
							</div>'.($cms->a == "add" ? '
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(106).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"><input type="password" class="itemElement text" name="user_pass" value="'.$user_pass.'" /></div>
									<div class="itemType vital" title="'.$cms->translate(241).'">!</div>
								</div>
							</div>':'').'
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(98).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow">
										<select name="rank" class="itemElement select">
										<option value="">----- '.$cms->translate(20).' -----</option>';                
										$cms->executeQuery("SELECT * FROM cms_accounts_ranks WHERE rank!='Lemon Team' ORDER BY id ASC",1);
										while($row = mysqli_fetch_assoc($cms->result1)) {									 
											echo '<option value="'.$row["id"].'"';
											if($rank == $row["id"]) {
												echo ' selected="selected"';
											}
											echo '>'.$row["rank"].'</option>'; 
										}
								echo '</select>
									</div>
									<div class="itemType vital" title="'.$cms->translate(241).'">!</div>
								</div>
							</div>
						</div>
						<div class="addColumn narrow right">
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(104).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"><input type="text" class="itemElement text" name="name" value="'.$name.'" /></div>
									<div class="itemType vital" title="'.$cms->translate(241).'">!</div>
								</div>
							</div>
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(113).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"><input type="text" class="itemElement text" name="email" value="'.$email.'" /></div>
									<div class="itemType vital" title="'.$cms->translate(241).'">!</div>
								</div>
							</div> 
						</div>
						<div class="c"><br /></div>
						<input type="submit" value="'.$cms->translate($cms->a=="add"?21:14).'" class="greenButtonLarge" name="submit" />	
					</div>
				</form>'; 
		break; 
		case "del":
			$cms->executeQuery("SELECT *,ar.rank AS rank FROM cms_accounts a INNER JOIN cms_accounts_ranks ar ON a.rank=ar.id WHERE a.id='$cms->id'",2);
			$row = mysqli_fetch_assoc($cms->result2);
			$cms->executeQuery("DELETE FROM cms_accounts WHERE id='$cms->id'",1);
			if($cms->result1) {
				$cms->setSessionInfo(true,$cms->translate(117));
				$cms->saveAction($row["name"],$row["rank"]);
			}
			else {
				$cms->setSessionInfo(false,$cms->translate(27));
			}
			header("Location:".$cms->get_link("accounts"));
		break; 
		case "status": 
			$cms->executeQuery("SELECT *,ar.rank AS rank FROM cms_accounts a INNER JOIN cms_accounts_ranks ar ON a.rank=ar.id WHERE a.id='$cms->id'",2);
			$row = mysqli_fetch_assoc($cms->result2);
			$set = $row["active"] == 0 ? 1 : 0;
			$cms->executeQuery("UPDATE cms_accounts SET active='$set' WHERE id='$cms->id'",1);
			if($cms->result1) {
				$cms->setSessionInfo(true,$cms->translate(119));
				$cms->saveAction($row["name"],$row["rank"]);
			}
			else {
				$cms->setSessionInfo(false,$cms->translate(27));
			}
			header("Location:".$cms->get_link("accounts"));
		break;
	}
?>