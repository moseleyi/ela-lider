<script type="text/javascript">
	$(document).ready(function(){ 
		lemon.object_delete(".delGroup","<?php echo $cms->translate(430); ?>", "name", "del");
	});
</script>
<?php
	$cms->checkAccess(); 
	switch($cms->a) {
		default: 
		case "list":
			echo '<div id="moduleWrap">
					<a href="'.$cms->get_link("users_groups,add").'" class="greenButtonWide ">'.$cms->translate(426).'</a><div class="c"></div><br />				 
					<table class="table">
						<tr>
							<td class="head" width="40px">Lp.</td> 
							<td class="head">'.$cms->translate(424).'</td>  
							<td class="head" width="140px">'.$cms->translate(425).'</td> 
							<td class="head" width="110px">'.$cms->translate(431).'</td>     
							<td class="head" width="60px">'.$cms->translate(14).'</td>
							<td class="head" width="60px">'.$cms->translate(15).'</td>
						</tr>';
			$i = 1;
			$cms->executeQuery("SELECT '0' AS 'id', 'Użytkownicy bez grupy' AS 'name', '$cms->lang' AS 'lang' UNION (SELECT * FROM cms_newsletter_groups ORDER BY name ASC)",1);
			while($row = mysqli_fetch_assoc($cms->result1)) {
				$id = $row["id"]; 
				$count = 0;
				if($id == 0) {
					$cms->executeQuery("SELECT 
										   COUNT(id) AS 'count'
										FROM
											cms_newsletter_users cnu
										WHERE
											lang = '$cms->lang'
											AND (
												SELECT 
													COUNT(group_id)
												FROM
													cms_newsletter_users_groups
												WHERE
													user_id = cnu.id) = 0"
									,3);
					$row3 = mysqli_fetch_assoc($cms->result3);
					$count = $row3["count"];
				}
				else {
					$count = $cms->getCount("cms_newsletter_users_groups","WHERE group_id='$id'");
				}
				echo '	<tr>
							<td class="body lvl0"><strong>'.$i.'.</strong></td>
							<td class="body lvl0 alignleft">'.$row["name"].'</td>
							<td class="body lvl0">'.$count.'</td>
							<td class="body lvl0"><a href="'.$cms->get_link("users,list,".$row["id"]).'" class="plink link_active">&nbsp;</a></td>
							<td class="body lvl0">'.($id != '0' ? '<a href="'.$cms->get_link("users_groups,edit,".$row["id"]).'" class="link_edit plink edit_slide" name="'.$row["id"].'">&nbsp;</a>' : '').'</td>
							<td class="body lvl0">'.($id != '0' ? '<a href="#" class="delGroup link_delete plink" name="'.$row["id"].'">&nbsp;</a>' : '').'</td>
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
				$cms->executeQuery("SELECT * FROM cms_newsletter_groups WHERE id='$cms->id'",1);
				$row = mysqli_fetch_assoc($cms->result1);
				$name = $row["name"];
			}
			else {
				$name = '';
			}
			if(isset($_POST["submit"])) {
				$name = $_POST["name"];
				if(empty($name)) {
					$cms->setInfo(false, $cms->translate(23));
				}
				else {
					/*** ADD ***/
					if($cms->a == "add") { 
						$cms->executeQuery("INSERT INTO cms_newsletter_groups (`id`, `name`, `lang`) VALUES ('', '$name', '$cms->lang')",1);
					}
					/*** EDIT ***/
					else {
						$cms->executeQuery("UPDATE cms_newsletter_groups SET name='$name' WHERE id='$cms->id'",1);
					}
					
					if($cms->result1) {
						$cms->setSessionInfo(true, $cms->translate($cms->a == "add" ? 427 : 428));
						$cms->saveAction($name,"");
						header("Location:".$cms->get_link("users_groups"));
					}
					else {
						$cms->setInfo(false,$cms->translate(27));
					}
				}
			}
			echo '<form method="post" enctype="multipart/form-data" action="'.$cms->get_link("users_groups,".$cms->a.''.($cms->a=="edit"?','.$cms->id:'')).'"> 
					<div id="addColumnMain">
						<div class="addColumn narrow left NB">
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(424).'</div>	
								<div class="itemElementWrap">
									<div class="itemElementShadow"><input type="text" class="itemElement text" name="name" value="'.htmlspecialchars($name).'" /></div>
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
			$cms->executeQuery("SELECT * FROM cms_newsletter_groups WHERE id='$cms->id'",2);
			$row2 = mysqli_fetch_assoc($cms->result2);
			$name = $row2["name"];
			$cms->executeQuery("DELETE FROM cms_newsletter_groups WHERE id='$cms->id'",1);
			if($cms->result1) {  
				$cms->executeQuery("DELETE FROM cms_newsletter_users_groups WHERE group_id='$cms->id'",3);
				$cms->setSessionInfo(true,$cms->translate(429));
				$cms->saveAction($name,"");
			}
			else {
				$cms->setSessionInfo(false,$cms->translate(27));
			}
			header("Location:".$cms->get_link("users_groups"));
		break;
	}
?>