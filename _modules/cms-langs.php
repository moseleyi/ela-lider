<script type="text/javascript">
	$(document).ready(function() { 
		lemon.object_delete(".delLang","<?php echo $cms->translate(125); ?>", "name", "del");
	});
</script>
<?php
	$cms->checkAccess();
	switch($cms->a) {
		case "list":
		default:
			echo ' 
				<div id="addColumnMain">
					<div class="addColumn left narrow">
						<form method="post" enctype="multipart/form-data" action="'.$cms->get_link("langs,add").'">
							<div class="itemWrap"> 
								<div class="itemLabel">'.$cms->translate(122).'</div>
								<div class="itemElementWrap">
									<select name="lang" class="itemElement select">
										<option value="">----- '.$cms->translate(20).' -----</option>';
										$cms->executeQuery("SELECT * FROM cms_langs WHERE added='0' ORDER BY position ASC",1);
										while($row = mysqli_fetch_assoc($cms->result1)) {
											echo '<option value="'.$row["shortLang"].'">'.$row["longLang"].'</option>';
										}
										echo'
									</select>
									<div class="itemType optional" title="'.$cms->translate(243).'">?</div>
								</div>
								<br />	
								<input type="submit" name="submit" value="'.$cms->translate(21).'" class="greenButtonLarge " />
							</div> 
						</form>
					</div>
					<div class="addColumn right narrow">
						<div class="itemWrap">
							<div class="itemLabel">'.$cms->translate(266).'</div>  
						<table class="table" style="width:690px;">
							<tr>
								<td class="head" width="40px">Lp.</td>
								<td class="head">'.$cms->translate(87).'</td>
								<td class="head" width="90px">'.$cms->translate(358).'</td>
								<td class="head" width="70px">'.$cms->translate(12).'</td>
								<td class="head" width="70px">'.$cms->translate(15).'</td>
							</tr>';
							$i = 1;
							$cms->executeQuery("SELECT * FROM cms_langs WHERE added='1'",1);
							while($row = mysqli_fetch_assoc($cms->result1)) {
					$status = $row["status"] == 0 ? '<a href="'.$cms->get_link("langs,status,".$row["id"]).'" class="link_hide plink">&nbsp;</a>' : '<a href="'.$cms->get_link("langs,hide,".$row["id"]).'" class="link_show plink">&nbsp;</a>';
								echo '<tr>
										<td class="body lvl0"><strong>'.$i.'.</strong></td>
										<td class="body alignleft lvl0">'.$row["longLang"].'</td>
										<td class="body lvl0">'.($row["main"] == 0 ? '<a href="'.$cms->get_link("langs,main,".$row["id"]).'" class="link_main_on plink">&nbsp;</a>' : '<a href="#" class="link_main_on2 plink">&nbsp;</a>').'</td>
										<td class="body lvl0"><a href="'.$cms->get_link("langs,status,".$row["id"]).'" class="link_'.($row["status"] == 0 ? 'hide' : 'show').' plink">&nbsp;</a></td>
										<td class="body lvl0"><a href="#" class="delLang link_delete plink" name="'.$row["shortLang"].'">&nbsp;</a></td>
									</tr>';
								$i++;
							}
							echo'
						</table>
						</div>
					</div>
					<div class="c"></div>
				</div>';
		break;
		case "main":
			$cms->executeQuery("SELECT * FROM cms_langs WHERE id='$cms->id'",1);
			$row = mysqli_fetch_assoc($cms->result1);
			if($row["status"] == 0) {
				$cms->setSessionInfo(false, $cms->translate(360));
			}
			else {
				$cms->executeQuery("UPDATE cms_langs SET main='0'",1);
				if($cms->result1) {
					$cms->executeQuery("UPDATE cms_langs SET main='1' WHERE id='$cms->id'",2);
					if($cms->result2) {
						$cms->setSessionInfo(true,$cms->translate(355));
						$cms->saveAction($row["name"],"");
					}
					else {
						$cms->setSessionInfo(false,$cms->translate(27));
					}
				}
				else {
					$cms->setSessionInfo(false,$cms->translate(27));
				}
			}
			header("Location:".$cms->get_link("langs"));
		break;
		case "status": 
			$cms->executeQuery("SELECT * FROM cms_langs WHERE id='$cms->id'",1);
			$row = mysqli_fetch_assoc($cms->result1);
			if($row["main"] == 1) {
				$cms->setSessionInfo(false, $cms->translate(359));
			}
			else {
				$lang = $row["longLang"];
				$n = $row["status"] == 0 ? 1 : 0;
				$cms->executeQuery("UPDATE cms_langs SET status='$n' WHERE id='$cms->id'",1);
				if($cms->result1) {
					$cms->setSessionInfo(true,$cms->translate(196));
					$cms->saveAction($lang,"");
				}
				else {
					$cms->setSessionInfo(false,$cms->translate(27));
				}
			}
			header("Location:".$cms->get_link("langs"));
		break; 
		case "add":
			$l = $_POST["lang"];
			$cms->executeQuery("SELECT * FROM cms_langs WHERE shortLang='$l'",2);
			$row = mysqli_fetch_assoc($cms->result2);
			$id = $row["id"];
			if(empty($l)) {
				$cms->setSessionInfo(false,$cms->translate(126));
			}
			else {
				$cms->executeQuery("UPDATE cms_langs SET added = '1', main = (CASE WHEN (SELECT t.id FROM (SELECT COUNT(*) AS 'id' FROM cms_langs WHERE added = '1') AS t) = 0 THEN 1 ELSE 0 END) WHERE shortLang = '$l'",1);
				if($cms->result1) {
					$cms->setSessionInfo(true,$cms->translate(124));
					$cms->saveAction($row["longLang"],"");
					$defaults = explode(",",$cms->default_info);
					$pos = 1;
					foreach($defaults as $default) {
						list($var_name, $var_type, $var_vit) = explode(":",$default);
						$cms->executeQuery("INSERT INTO cms_settings_defaults (`id`, `type`, `value`, `lang_id`, `position`, `featureType`, `vitality`) VALUES ('', '$var_name', '', '$id', '$pos', '$var_type', '$var_vit')",3);
						$pos++;
					}
				}
				else {
					$cms->setSessionInfo(false,$cms->translate(27));
				}
			}
			header("Location:".$cms->get_link("langs"));
		break;
		case "del":  
			if($cms->getCount("cms_langs","WHERE added='1'") == 0) {
				$cms->setSessionInfo(false, $cms->translate(356));
			}
			else {
				$cms->executeQuery("SELECT * FROM cms_langs WHERE shortLang='$cms->id'",2);
				$row = mysqli_fetch_assoc($cms->result2);
				$id = $row["id"];
				if($row["main"] == 1) {
					$cms->setSessionInfo(false, $cms->translate(357));
				}
				else {
					$cms->executeQuery("UPDATE cms_langs SET status='0',added='0',main='0' WHERE shortLang='$cms->id'",1);
					if($cms->result1) {
						$cms->setSessionInfo(true,$cms->translate(123));
						$cms->saveAction($row["longLang"],""); 
						$cms->executeQuery("DELETE FROM cms_settings_defaults WHERE lang_id='$id'",3);
					}
					else {
						$cms->setSessionInfo(false,$cms->translate(27));
					}
				}
			}
			if($cms->lang == $cms->id) {
				$cms->executeQuery("SELECT * FROM cms_langs WHERE main='1'",1);
				$row = mysqli_fetch_assoc($cms->result1);
				$t = $row["shortLang"];
			}
			else {
				$t = $cms->lang;
			}
			header("Location:".$cms->get_link("langs"));
		break;
	}
?>