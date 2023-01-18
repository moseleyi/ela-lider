<script type="text/javascript">
	$(document).ready(function() {  	
		lemon.object_delete(".delModule","<?php echo $cms->translate(190); ?>", "name", "del");	
		$(".cpos-in").each(function() {
			$(this).change(function() {
				window.location.href = '/cms/<?php echo $cms->lang.'/'.$cms->p;?>/move/c-'+$(this).attr("name").split("-")[1]+',pos='+$(this).val();
			});
		});
	});
</script>
<?php
	$cms->checkAccess();  
	switch($cms->a) { 
		case "list":
		default:
			echo '	<div id="moduleWrap">
						<a href="'.$cms->get_link("modules,add").'" class="greenButtonWide ">'.$cms->translate(294).'</a><div class="c"></div><br />
						<table class="table">
							<tr> 
								<td class="head">'.$cms->translate(148).'</td>  
								<td class="head" width="700px">'.$cms->translate(421).'</td>
								<td class="head" width="70px">'.$cms->translate(12).'</td>
								<td class="head" width="70px">'.$cms->translate(14).'</td>
								<td class="head" width="70px">'.$cms->translate(15).'</td>
							</tr>';		
					$cms->executeQuery("SELECT * FROM cms_modules_groups ORDER BY position ASC",1);
					while($row = mysqli_fetch_assoc($cms->result1)) {
						$id = $row["id"];
						echo '<tr>
								<td class="body lvl0 alignleft" colspan="2"><input type="text" maxlength="2" class="cpos-in" value="'.$row["position"].'" name="pos-'.$row["id"].'"/>'.$row["name_pl"].' ('.$row["name_en"].')</td>
								<td class="body lvl0" colspan="3"><a href="'.$cms->get_link("modules,status,c-".$row["id"]).'" class="link_'.($row["status"] == 1 ? 'show' : 'hide').' plink">&nbsp;</a></td> 
							</tr>';
						$cms->executeQuery("SELECT * FROM cms_modules WHERE moduleGroup='$id' ORDER BY position ASC",2);
						while($row2 = mysqli_fetch_assoc($cms->result2)) {  
							echo '<tr>
									<td class="body alignleft lvl1">
										<input type="text" maxlength="2" class="pos-in" value="'.$row2["position"].'" name="pos-'.$row2["id"].'"/>
										<span>'.$row2["plName"].' ('.$row2["enName"].')</span>
									</td>   
									<td class="body lvl1 alignleft">';
									$id2 = $row2["id"];
									$actions = array();
									$cms->executeQuery("SELECT * FROM cms_modules_actions cma INNER JOIN cms_actions_labels cal ON cma.action_id = cal.id WHERE cma.module_id='$id2' ORDER BY action ASC",3);
									while($row3 = mysqli_fetch_assoc($cms->result3)) {
										$actions[] = $row3["label_".$cms->cmsL];
									}
									echo implode(", ",$actions);
							echo '	</td>
									<td class="body lvl1"><a href="'.$cms->get_link("modules,status,".$row2["id"]).'" class="link_'.($row2["status"] == 1 ? 'show' : 'hide').' plink">&nbsp;</a></td>
									<td class="body lvl1"><a href="'.$cms->get_link("modules,edit,".$row2["id"]).'" class="link_edit plink">&nbsp;</a></td>
									<td class="body lvl1"><a href="#" class="delModule link_delete plink" name="'.$row2["id"].'">&nbsp;</a></td>
								</tr>';
						}
					}
				echo'</table>
				</div>';
		break;		
		case "add":
		case "edit":
			/*** EDIT ***/
			if($cms->a == "edit") {
				$cms->executeQuery("SELECT * FROM cms_modules WHERE id='$cms->id'",1);
				$row = mysqli_fetch_assoc($cms->result1);
				$name_pl = $row["plName"];
				$name_en = $row["enName"];
				$name_short = $row["shortName"];
				$connectable = $row["connectable"];
				$lemon_only = $row["lemonOnly"];
				$module_group = $row["moduleGroup"];
				$module_group_old = $row["moduleGroup"];
				$position = $row["position"];
				$imagesPos = $row["imagesPossible"];
				$cms->executeQuery("SELECT * FROM cms_modules_actions cma INNER JOIN cms_actions_labels ca ON cma.action_id = ca.id WHERE cma.module_id='$cms->id'",2); 
				while($row2 = mysqli_fetch_assoc($cms->result2)) {
					$actions[$row2["action_id"]] = $row2["action"];
				} 
			}
			else {
				$name_pl = $name_en = $name_short = $connectable = $lemon_only = $module_group = $module_group_old = '';
			}
			if(isset($_POST["submit"])) {
				$name_pl = $_POST["name_pl"];
				$name_en = $_POST["name_en"];
				$name_short = $_POST["name_short"];
				$connectable = isset($_POST["connectable"]) ? $_POST["connectable"] : '';
				$lemon_only = isset($_POST["lemon_only"]) ? $_POST["lemon_only"] : '';
				$module_group = $_POST["module_group"];
				$imagesPos = isset($_POST["imagesPos"]) ? $_POST["imagesPos"] : '';
				$actions = $_POST["actions"]; 
				if(empty($name_pl) || empty($name_en) || empty($name_short) || empty($module_group)) {
					$cms->setInfo(false,$cms->translate(23)); 
				}
				else { 
					
					/*** ADD ***/
					if($cms->a == "add") {
						$position = $cms->getCount("cms_modules", "WHERE moduleGroup='$module_group'") + 1;
						$cms->executeQuery("INSERT INTO cms_modules (`id`, `plName`, `enName`, `shortName`, `position`, `status`, `connectable`, `moduleGroup`, `lemonOnly`, `imagesPossible`) VALUES ('', '$name_pl', '$name_en', '$name_short','$position','0', '$connectable', '$module_group', '$lemon_only', '$imagesPos')",1);
						$cms->id = $cms->lastInsertId();
					}
					/*** EDIT ***/
					else {
						if($module_group != $module_group_old) {							
							$cms->executeQuery("UPDATE cms_modules SET position=position-1 WHERE position>'$position' AND moduleGroup='$module_group_old'",1);  
							$position = $cms->getCount("cms_modules", "WHERE moduleGroup='$module_group'") + 1;
						}
						$cms->executeQuery("UPDATE cms_modules SET shortName='$name_short', plName='$name_pl', enName='$name_en', connectable='$connectable', moduleGroup='$module_group', lemonOnly='$lemon_only', imagesPossible='$imagesPos', position='$position' WHERE id='$cms->id'",1);
						
					} 
					if($cms->result1) {
						
						$cms->executeQuery("SELECT *,cal.id AS 'id' FROM cms_actions_labels cal LEFT JOIN cms_modules_actions cma ON cal.id = cma.action_id AND cma.module_id='$cms->id'",13);
						while($row13 = mysqli_fetch_assoc($cms->result13)) {
							$ml = $row13["module_id"];  
							
							/* Action has been ticket but it hasn't been added in the past */
							if($actions[$row13["id"]] == "true" && $ml == "") {
								$cms->executeQuery("INSERT INTO cms_modules_actions (`module_id`, `action_id`) VALUES ('$cms->id', '".$row13["id"]."')",12); 
							}
							/* Action is not ticked but extists, delete id */
							else if($ml != "" && $actions[$row13["id"]] != "true") {
								$cms->executeQuery("DELETE FROM cms_modules_actions WHERE module_id='$cms->id' AND action_id='".$row13["id"]."'",14); 
							}
						}
						$cms->setSessionInfo(true,$cms->translate($cms->a == "add" ? 192 : 277)); 
						header("Location:".$cms->get_link("modules"));
					}
					else {
						$cms->setInfo(false,$cms->translate(27)); 
					}
				}
			}
			echo ' <form method="post" enctype="multipart/form-data" action="'.$cms->get_link("modules,".$cms->a.''.($cms->a=="edit"?','.$cms->id:'')).'"> 
					<div id="addColumnMain">
						<div class="addColumn narrow left extraPad">
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(272).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"><input type="text" class="itemElement text" name="name_pl" value="'.$name_pl.'" /></div>
									<div class="itemType vital" title="'.$cms->translate(241).'">!</div>
								</div>
							</div>
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(273).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"><input type="text" class="itemElement text" name="name_en" value="'.$name_en.'" /></div>
									<div class="itemType vital" title="'.$cms->translate(241).'">!</div>
								</div>
							</div>
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(274).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"><input type="text" class="itemElement text" name="name_short" value="'.$name_short.'" /></div>
									<div class="itemType vital" title="'.$cms->translate(241).'">!</div>
								</div>
							</div> 	
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(270).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow">
										<select name="module_group" class="itemElement select">
											<option value="">----- '.$cms->translate(20).' -----</option>';
											$cms->executeQuery("SELECT * FROM cms_modules_groups ORDER BY position ASC",1);
											while($row = mysqli_fetch_assoc($cms->result1)) {
												echo '<option value="'.$row["id"].'"'.($row["id"] == $module_group ? ' selected="selected"': '').'>'.$row["name_".$cms->cmsL].'</option>';
											}
								echo '	</select>
									</div>
									<div class="itemType vital" title="'.$cms->translate(241).'">!</div>
								</div>
							</div>
						</div>
						<div class="addColumn narrow right">
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(421).'</div>';
						$cc = ceil($cms->getCount("cms_actions_labels") / 2);
						$i = 1; 
						echo '<div class="modules_actions">';
						$cms->executeQuery("SELECT *, cal.id AS 'id' FROM cms_actions_labels cal LEFT JOIN cms_modules_actions cma ON cal.id = cma.action_id AND cma.module_id='$cms->id' ORDER BY cal.id ASC",1);
						while($row = mysqli_fetch_assoc($cms->result1)) {
							echo '<div class="itemElementWrap"><div class="checkElementSmall'.($row["action_id"] != "" ? ' checked' : '').'"><input type="checkbox" class="itemElement check" name="actions['.$row["id"].']" value="true"'.($row["action_id"] != "" ? 'checked="checked"' : '').' /></div> <label>'.$row["label_".$cms->cmsL].'</label><div class="c"></div></div>';
							if($i == $cc) {
								echo '</div><div class="modules_actions">';
							}
							$i++;
						}
					echo '		</div>
								<div class="c"></div>
							</div> 	
							<div class="itemWrap">
								<div class="itemLabel">'.$cms->translate(275).'</div>
								<div class="itemElementWrap elementWrapCheck">
									<div class="itemElementShadow">
										<div class="checkElement'.($connectable==1?' checked':'').'">
											<input type="checkbox" class="itemElement check" name="connectable" value="1"'.($connectable==1?' checked="checked"':'').' />
										</div>
									</div>
									<div class="itemType optional" title="'.$cms->translate(243).'">?</div>
								</div>
							</div>
							<div class="itemWrap">
								<div class="itemLabel">'.$cms->translate(276).'</div>
								<div class="itemElementWrap elementWrapCheck">
									<div class="itemElementShadow">
										<div class="checkElement'.($lemon_only==1?' checked':'').'">
											<input type="checkbox" class="itemElement check" name="lemon_only" value="1"'.($lemon_only==1?' checked="checked"':'').' />
										</div>
									</div>
									<div class="itemType optional" title="'.$cms->translate(243).'">?</div>
								</div>
							</div>
							<div class="itemWrap">
								<div class="itemLabel">'.$cms->translate(327).'</div>
								<div class="itemElementWrap elementWrapCheck">
									<div class="itemElementShadow">
										<div class="checkElement'.($imagesPos==1?' checked':'').'">
											<input type="checkbox" class="itemElement check" name="imagesPos" value="1"'.($imagesPos==1?' checked="checked"':'').' />
										</div>
									</div>
									<div class="itemType optional" title="'.$cms->translate(243).'">?</div>
								</div>
							</div>
						</div>
						<div class="c"></div><br />
						<input type="submit" name="submit" class="greenButtonLarge " value="'.$cms->translate($cms->a == "add" ? 21 : 14).'" />
					</div>
				</form>';
							
		break;
		case "status":  
			if(strpos($cms->id, "c-") > -1) {
				$cms->id = str_replace("c-","", $cms->id);
				$table = "_groups";
				$name = "name_".$cms->cmsL;
			}
			else {
				$name = $cms->cmsL."Name";
			}
			$cms->executeQuery("SELECT * FROM cms_modules".$table." WHERE id='$cms->id'",1);
			$row = mysqli_fetch_assoc($cms->result1);
			$extName = $row[$name];
			$status = $row["status"];
			$n = $status == 0 ? 1 : 0;
			$cms->executeQuery("UPDATE cms_modules".$table." SET status='$n' WHERE id='$cms->id'",1);
			if($cms->result1) {
				$cms->setSessionInfo(true,$cms->translate(415));
				$cms->saveAction($extName,"");
			}
			else {
				$cms->setSessionInfo(false,$cms->translate(27));
			}
			header("Location:".$cms->get_link("modules"));
		break; 
		case "del":			
			$cms->executeQuery("SELECT * FROM cms_modules WHERE id='$cms->id'",1);
			$row = mysqli_fetch_assoc($cms->result1); 
			$position = $row["position"]; 
			$extName = $row[$cms->cmsL."Name"]; 
			$group = $row["moduleGroup"];
			$cms->executeQuery("DELETE FROM cms_modules WHERE id='$cms->id'",2);
			if($cms->result2) { 
				$cms->executeQuery("UPDATE cms_modules SET position=position-1 WHERE position > '$position' AND moduleGroup='$group'",4);  
				/* Reset settings for all pages that are connected with this module */
				$cms->executeQuery("UPDATE cms_menu SET connection='',connection_type='' WHERE connection='$cms->id' AND connection_type='module'",5);
				$cms->setSessionInfo(true,$cms->translate(278));
			}
			else {
				$cms->setSessionInfo(false,$cms->translate(27));   
			} 
			header("Location:".$cms->get_link("modules"));
		break;
		case "move": 
			if(strpos($cms->id, "c-") > -1) {
				$cms->id = str_replace("c-","", $cms->id);
				$table = "_groups";
				$name = "name_".$cms->cmsL;
			}
			else {
				$name = $cms->cmsL."Name"; 
				$table = "";
			}
			$pos = $_GET["pos"];
			$cms->executeQuery("SELECT * FROM cms_modules".$table." WHERE id='$cms->id'",1);
			$row = mysqli_fetch_assoc($cms->result1); 
			$oldpos = $row["position"];
			$group = $row["moduleGroup"];	
			$extName = $row[$name];	
			$max = $table == "" ? $cms->getCount("cms_modules","WHERE moduleGroup='$group'") : $cms->getCount("cms_modules_groups");	
			if($pos <= 0 || is_numeric($pos) == false) {
				$pos = 1;
			}
			elseif($pos > $max) {
				$pos = $max;
			}  
			$q = $oldpos < $pos ? "position>'$oldpos' AND position<='$pos'" : "position<'$oldpos' AND position >='$pos'"; 	
			$q2 = $oldpos < $pos ? "position=position-1" : "position=position+1";			
			$cms->executeQuery("UPDATE cms_modules".$table." SET ".$q2." WHERE ".$q."".($table == "" ? " AND moduleGroup='$group'" : ""),1);
			if($cms->result1) {
				$cms->executeQuery("UPDATE cms_modules".$table." SET position='$pos' WHERE id='$cms->id'",2);
				if($cms->result2) {
					$cms->setSessionInfo(true,$cms->translate(271));
					$cms->saveAction($extName,"");
				}
				else {
					$cms->setSessionInfo(false,$cms->translate(27));
				}
			}
			else {
				$cms->setSessionInfo(false,$cms->translate(27));
			}  
			header("Location:".$cms->get_link("modules"));	 
		break;
	}
?>