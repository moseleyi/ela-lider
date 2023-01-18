<?php
	$cms->checkAccess(); 
	switch($cms->a){
		case "list":
		default:
			if(isset($_POST["submit"])) {
				$id = $_SESSION["userId"];
				$pass_new = $_POST["pass_new"];
				$pass_old = $_POST["pass_old"]; 
				$new = hash("sha1",$pass_new);
				$old = hash("sha1",$pass_old);
				$cms->executeQuery("SELECT * FROM cms_accounts WHERE id='$id'",1);
				$row = mysqli_fetch_assoc($cms->result1);
				$oldPDB = $row["password"];
				if($old == $oldPDB) {
					$cms->executeQuery("UPDATE cms_accounts SET password='$new' WHERE id='$id'",2);
					if($cms->result2) {
						$cms->setSessionInfo(true,$cms->translate(228));
						header("Location:".$cms->get_link("start"));
					}
					else {
						$cms->setSessionInfo(false,$cms->translate(27));
						header("Location:".$cms->get_link("passchange"));
					}
				}	
				else {
					$cms->setSessionInfo(false,$cms->translate(229));
					header("Location:".$cms->get_link("passchange"));
				} 
			}
			echo '	<div id="moduleWrap">
						<form method="post" enctype="multipart/form-data" action="'.$cms->get_link("passchange").'">
							<div class="addColumn narrow left NB">
								<div class="itemWrap">
									<div class="itemLabel">'.$cms->translate(224).'</div>
									<div class="itemElementWrap">
										<div class="itemElementShadow"><input type="password" class="itemElement text" name="pass_old" /></div>
										<div class="itemType vital" title="'.$cms->translate(241).'">!</div>
									</div>
								</div>
								<div class="itemWrap">
									<div class="itemLabel">'.$cms->translate(225).'</div>
									<div class="itemElementWrap">
										<div class="itemElementShadow"><input type="password" class="itemElement text" name="pass_new" /></div>
										<div class="itemType vital" title="'.$cms->translate(241).'">!</div>
									</div>
								</div>
							</div>
							<div class="c"></div>
							<input type="submit" name="submit" class="greenButtonLarge" value="'.$cms->translate(223).'" />
						</form>
					</div>';
		break;
	}
?>