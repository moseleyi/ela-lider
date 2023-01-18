<script type="text/javascript">
	$(document).ready(function() {		
		$(".checkAction").each(function() {
			$(this).click(function(e) {  
				var input = $(this).find("input").eq(0);
				var span = $(this).find("span").eq(0);
				if(span.hasClass("active")) {
					span.removeClass("active");
					input.prop("checked",false);
				}
				else {
					span.addClass("active");
					input.prop("checked",true);
				} 
			});
		});
		
		$(".checkElement, .checkElementSmall").each(function() {
			$(this).click(function() {
				if($(this).hasClass("checked") && $(this).find(".disabledOverlay").length == 0){ 
					$(this).find("input").eq(0).val("true");
				}
				else {
					$(this).find("input").eq(0).val("false");
				}
			});
		}); 	 
		lemon.object_delete(".delRank","<?php echo $cms->translate(111); ?>", "name", "del");
		 
	});
</script>
<?php
	$cms->checkAccess(); 
	switch($cms->a) {
		default:
		case "list":  
			echo '	 
				<div id="moduleWrap">
					<a href="'.$cms->get_link("ranks,add").'" class="greenButtonWide ">'.$cms->translate(231).'</a><div class="c"></div><br />
						<table class="table table_medium">
							<tr>	
								<td class="head" width="40px">Lp.</td>
								<td class="head">'.$cms->translate(98).'</td>
								<td class="head" width="130px">'.$cms->translate(76).'</td>
								<td class="head" width="70px">'.$cms->translate(14).'</td>
								<td class="head" width="70px">'.$cms->translate(15).'</td>
							</tr>';
							
			$i=1;
			$cms->executeQuery("SELECT * FROM cms_accounts_ranks WHERE rank !='Lemon Team' ORDER BY id ASC",11);
			while($row = mysqli_fetch_assoc($cms->result11)) { 
				$id = $row["id"];  
				echo '	<tr>
							<td class="body lvl0"><strong>'.$i.'.</strong></td>
							<td class="body alignleft lvl0"><span style="font-weight:bold;color:#'.$row["color"].'">'.$row["rank"].'</span></td>
							<td class="body lvl0">'.$cms->getCount("cms_accounts","WHERE rank='$id'").'</td>
							<td class="body lvl0"><a href="'.$cms->get_link("ranks,edit,$id").'" class="link_edit plink rank_edit">&nbsp;</a></td>
							<td class="body lvl0"><a href="#" class="link_delete plink delRank" name="'.$id.'">&nbsp;</a></td>
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
				$cms->executeQuery("SELECT * FROM cms_accounts_ranks WHERE id='$cms->id'",1);
				$row = mysqli_fetch_assoc($cms->result1);
				if($row["special"] == 1 && $_SESSION["userRankId"] != 1) {
					$cms->setSessionInfo(false,$row["rank"].$cms->translate(232));
					header("Location:".$cms->get_link("ranks"));
				}
				else{
					$rank = $row["rank"];
					$color = $row["color"];
					$oldcolor = $row["color"]; 
				}
			}
			else {
				$rank = $color = '';
			}
			if(isset($_POST["submit"])) {
				$rank = $_POST["rank"];
				$color = $_POST["color"];
                $actions = $_POST["actions"];
				if(empty($rank) || empty($color) || count($actions) == 0) {
					$cms->setInfo(false, $cms->translate(23));
				}
				elseif($oldcolor != $color && $cms->getCount("cms_accounts_ranks","WHERE color='$color'") > 0) {
					$cms->setInfo(false,$cms->translate(109));
				}
				else {
					
					/*** ADD ***/
					if($cms->a == "add") {
						$cms->executeQuery("INSERT INTO cms_accounts_ranks (`id`, `rank`, `access`, `lang`, `color`, `special`) VALUES ('', '$rank', '$acString', '$cms->lang', '$color', '0')",1);
						$cms->id = $cms->lastInsertId();
					}
					/*** EDIT ***/
					else {
						$cms->executeQuery("UPDATE cms_accounts_ranks SET rank='$rank', color='$color' WHERE id='$cms->id'",1);
					}
					if($cms->result1) {
						
						$cms->executeQuery("SELECT * FROM cms_modules",2);
						while($row2 = mysqli_fetch_assoc($cms->result2)) {
							$module_id = $row2["id"];
							$cms->executeQuery("SELECT * FROM cms_actions_labels",3);
							while($row3 = mysqli_fetch_assoc($cms->result3)) {
								$action_id = $row3["id"];
								
								$cms->executeQuery("SELECT * FROM cms_accounts_ranks_actions WHERE rank_id='$cms->id' AND module_id='$module_id' AND action_id='$action_id'",4);
								$row4 = mysqli_fetch_assoc($cms->result4);
								
								if($actions[$module_id][$action_id] == "true" && $row4["action_id"] == "") {
									$cms->executeQuery("INSERT INTO cms_accounts_ranks_actions (`rank_id`, `module_id`, `action_id`) VALUES ('$cms->id', '$module_id', '$action_id')",5);
								}
								else if($row4["action_id"] != "" && $actions[$module_id][$action_id] != "true") {
									$cms->executeQuery("DELETE FROM cms_accounts_ranks_actions WHERE rank_id='$cms->id' AND module_id='$module_id' AND action_id='$action_id'",5);
								}
							}
						}
						
						$cms->setSessionInfo(true,$cms->translate($cms->a == "add" ? 100 : 110));
						$cms->saveAction($rank,"");
						header("Location:".$cms->get_link("ranks"));
					}
					else {
						$cms->setSessionInfo(false,$cms->translate(27));
					}
				}  
			}
			echo ' <form method="post" enctype="multipart/form-data" action="'.$cms->get_link("ranks,".$cms->a.''.($cms->a=="edit"?','.$cms->id:'')).'"> 
					<div id="addColumnMain">
						<div class="addColumn narrow left extraPad NB">
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(98).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"><input type="text" class="itemElement text" name="rank" value="'.$rank.'" /></div>
									<div class="itemType vital" title="'.$cms->translate(241).'">!</div>
								</div>
							</div>
						</div>
						<div class="addColumn narrow right NB">
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(101).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"><input type="text" class="itemElement text" name="color" value="'.$color.'" id="colorPicker" /></div>
									<div class="colorPickerBox" style="background-color:#'.$color.';">&nbsp;</div>
									<div class="itemType vital" title="'.$cms->translate(241).'">!</div>
								</div>
							</div>
						</div>
						<div class="addColumn full clear NB">
							<div class="itemWrap">
								<div class="itemLabel">'.$cms->translate(102).'</div>';  
									$cms->executeQuery("SELECT * FROM cms_modules WHERE status='1' ORDER BY moduleGroup ASC",1);
									while($row = mysqli_fetch_assoc($cms->result1)) {   
										echo '<div class="rankSection">
												<div class="rankSectionTitle">'.$row[$cms->cmsL."Name"].'</div>
												<div class="rankSectionActions">';
										$id = $row["id"];		
										$cms->executeQuery("SELECT 
																cara.module_id AS 'module_id',
																cal.label_pl,
																cal.label_en,
																cal.id
															FROM
																cms_modules_actions cma
																	INNER JOIN cms_actions_labels cal ON cma.action_id = cal.id
																	LEFT JOIN cms_accounts_ranks_actions cara ON cara.module_id = cma.module_id AND cara.action_id = cal.id AND cara.rank_id='$cms->id'
															WHERE
																cma.module_id = '$id'
															ORDER BY cal.id ASC",2);
										while($row2 = mysqli_fetch_assoc($cms->result2)) {
											echo '
														<div class="checkAction checkActionFloat"> 
															<div class="checkElementSmall'; 
																echo ($row2["module_id"] != "" ?' checked':''); 
															echo '">
																<input type="checkbox" class="itemElement check" name="actions['.$id.']['.$row2["id"].']" value="true"'; 
																echo ($row2["module_id"] != ""?' checked="checked"':''); 
															echo ' />
															</div>
															<label class="checkLabel">'.$row2["label_".$cms->cmsL].'</label><div class="c"></div> 
														</div>
													';  
										}									 
										echo '<div class="c"></div></div><div class="c"></div></div>'; 
									} 
					echo'		<div class="c"></div>
							</div>
						</div>
						<div class="c"></div>
						<input type="submit" value="'.$cms->translate($cms->a=="add"?21:14).'" class="greenButtonLarge" name="submit" />	
					</div>
				</form>';
		break; 
		case "del": 
			$cms->executeQuery("SELECT * FROM cms_accounts_ranks WHERE id='$cms->id'",1);
			$row = mysqli_fetch_assoc($cms->result1);
			if($row["special"] == 1) {
				$cms->setSessionInfo(false,$row["rank"].$cms->translate(232));
				header("Location:".$cms->get_link("ranks"));
			}
			else {
				$cms->executeQuery("DELETE FROM cms_accounts_ranks WHERE id='$cms->id'",1);
				if($cms->result1) {
					$cms->executeQuery("DELETE FROM cms_accounts_ranks_actions WHERE rank_id='$cms->id'",4);
					$cms->setSessionInfo(true,$cms->translate(112));
					$cms->executeQuery("UPDATE cms_accounts SET rank = '0' WHERE rank='$cms->id'",3);
					$cms->saveAction($row["rank"]);
				}
				else {
					$cms->setSessionInfo(false,$cms->translate(27));
				}
			}
			header("Location:".$cms->get_link("ranks"));
		break;
	}
?>