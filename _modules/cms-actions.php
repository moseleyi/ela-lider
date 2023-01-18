	<?php   
	$cms->checkAccess();
	switch($cms->a) {  
		case "list":
		case "search":
		default:
			if(isset($_POST["submit"])) {
				$dateStart = $_POST["dateStart"];
				$dateEnd = $_POST["dateEnd"];
				$q = "
					SELECT
						*,
						DATE(timestamp) AS date,
						TIME(timestamp) AS time ,
						a.rank AS rank
					FROM 
						cms_actions a 
					INNER JOIN cms_actions_labels cal ON cal.action = a.action
					LEFT JOIN cms_accounts_ranks ar ON a.rank=ar.rank 
					LEFT JOIN cms_modules m ON m.shortName=a.module
					LEFT JOIN cms_modules_groups cmg ON cmg.id = m.moduleGroup
					LEFT JOIN cms_accounts ac ON ac.name=a.name";
				$m = $_POST["module"];
				$r = $_POST["rank"];
				$u = $_POST["username"]; 
				$q_parts = array();
				// Add last weeks date as starting point if date not chosen, just to avoid returning all actions
				if(empty($dateStart)) { 
					$dateStart = date('Y-m-j',strtotime('-1 day',strtotime(date("y-m-j"))));  
				}
				$q_parts[] = "DATE(a.timestamp) >= '$dateStart'";  
				if($dateEnd != "") {
					$q_parts[] = "DATE(a.timeStamp) <= '$dateEnd'";
				}
				if($m != "") { 
					$q_parts[] = "m.shortName = '$m'";
				}
				if($r != "") {
					$q_parts[] = "ar.rank = '$r'";
				} 
				if($u != "") {
					$q_parts[] = "ac.name = '$u'";
				}	
				if(count($q_parts) > 0) {
					$q .= " WHERE ";
					$q .= implode(" AND ",$q_parts);
				}
				$q .= "
					ORDER BY 
						a.timestamp DESC
				";  
			}
			else { 
			 $m = $r = $u = '';
				$date = date('Y-m-j',strtotime('-1 day',strtotime(date("y-m-j")))); 
				$q = "
					SELECT 
						*,
						DATE(timestamp) AS date,
						TIME(timestamp) AS time 
					FROM 
						cms_actions a 
					INNER JOIN cms_actions_labels cal ON cal.action = a.action
					LEFT JOIN cms_accounts_ranks ar ON a.rank=ar.rank 
					LEFT JOIN cms_modules m ON m.shortName=a.module
					LEFT JOIN cms_modules_groups cmg ON m.moduleGroup = cmg.id
					WHERE
						DATE(timestamp)>='$date'  
					ORDER BY 
					a.timestamp DESC
				";
			} 
			echo '
			 <form method="post" enctype="multipart/form-data" action="'.$cms->get_link("actions").'"> 
					<div id="addColumnMain">
						<div class="addColumn narrow left extraPad">
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(148).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow">
										<select class="itemElement select" name="module">
											<option value="">----- '.$cms->translate(20).' -----</option>';
									$cms->executeQuery("SELECT * FROM cms_modules WHERE status='1' AND lemonOnly='0' ORDER BY position ASC",1);
									while($row = mysqli_fetch_assoc($cms->result1)) {
										echo '<option value="'.$row["shortName"].'"';
										if($m == $row["shortName"]) {
											echo ' selected="selected"';
										}
										echo '>'.$row[$cms->cmsL."Name"].'</option>';
									}
					echo'				</select>
									</div>
									<div class="itemType optional" title="'.$cms->translate(243).'">?</div>
								</div>
							</div>
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(98).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow">
										<select class="itemElement select" name="rank">
											<option value="">----- '.$cms->translate(20).' -----</option>';
									$cms->executeQuery("SELECT * FROM cms_accounts_ranks WHERE id!=1 ORDER BY rank ASC",1);
									while($row = mysqli_fetch_assoc($cms->result1)) {
										echo '<option value="'.$row["rank"].'"';
										if($r == $row["rank"]) {
											echo ' selected="selected"';
										}
										echo '>'.$row["rank"].'</option>';
									}
					echo'				</select> 
									</div>
									<div class="itemType optional" title="'.$cms->translate(243).'">?</div>
								</div>
							</div>
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(149).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow">
										<select class="itemElement select" name="username">
											<option value="">----- '.$cms->translate(20).' -----</option>'; 
									$cms->executeQuery("SELECT * FROM cms_accounts WHERE rank !=1 ORDER BY name ASC",1);
									while($row = mysqli_fetch_assoc($cms->result1)) {
										echo '<option value="'.$row["name"].'"';
										if($u == $row["name"]) {
											echo ' selected="selected"';
										}
										echo '>'.$row["name"].'</option>';
									}
					echo'				</select>
									</div>
									<div class="itemType optional" title="'.$cms->translate(243).'">?</div>
								</div>
							</div>
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(211).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow">
										<input type="text" class="itemElement text datePicker" name="dateStart" value="'.$dateStart.'" />
									</div>
									<div class="itemType optional" title="'.$cms->translate(243).'">?</div>
								</div>
							</div>
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(212).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow">
										<input type="text" class="itemElement text datePicker" name="dateEnd" value="'.$dateEnd.'" />
									</div>
									<div class="itemType optional" title="'.$cms->translate(243).'">?</div>
								</div>
							</div>
						</div>
						<div class="addColumn narrow right"><br />
							<div class="actionItem">
								<div class="actionSquare green">&nbsp;</div>
								<div class="actionText">'.$cms->translate(154).'</div>
								<div class="c"></div>
							</div>
							<div class="actionItem">
								<div class="actionSquare yellow">&nbsp;</div>
								<div class="actionText">'.$cms->translate(155).'</div>
								<div class="c"></div>
							</div>
							<div class="actionItem">
								<div class="actionSquare red">&nbsp;</div>
								<div class="actionText">'.$cms->translate(156).'</div>
								<div class="c"></div>
							</div>
							<div class="actionItem">
								<div class="actionSquare pink">&nbsp;</div>
								<div class="actionText">'.$cms->translate(157).'</div>
								<div class="c"></div>
							</div>
							<div class="actionItem">
								<div class="actionSquare aqua">&nbsp;</div>
								<div class="actionText">'.$cms->translate(158).'</div>
								<div class="c"></div>
							</div>
							<div class="actionItem">
								<div class="actionSquare blue">&nbsp;</div>
								<div class="actionText">'.$cms->translate(159).'</div>
								<div class="c"></div>
							</div>
							<div class="actionItem">
								<div class="actionSquare grey">&nbsp;</div>
								<div class="actionText">'.$cms->translate(160).'</div>
								<div class="c"></div>
							</div>
						</div>
						<div class="c"></div><Br />
						<input type="submit" name="submit" class="greenButtonLarge " value="'.$cms->translate(150).'" />
					</div>
				</form>'; 
			// PAGING
			echo '	<div id="moduleWrap">
					<table class="table actions">
						<tr>
							<td class="head" width="33px"></td>
							<td class="head" width="150px">'.$cms->translate(149).'</td>
							<td class="head" width="180px">'.$cms->translate(151).'</td>';
							// SHOW IP ONLY TO LEMON TEAM
							if($_SESSION["userRankId"] == 1) {
								echo '<td class="head" width="100px">'.$cms->translate(161).'</td>';
							}
							echo '
							<td class="head" width="120px">'.$cms->translate(98).'</td>
							<td class="head" width="120px">'.$cms->translate(420).'</td>
							<td class="head" width="120px">'.$cms->translate(148).'</td>
							<td class="head">'.$cms->translate(152).'</td>
						</tr>'; 
					$cms->executeQuery($q,1);   
					while($row = mysqli_fetch_assoc($cms->result1)) { 
						$mod = $row["module"]; 
						echo '<tr>
								<td class="body lvl0"><div class="actionSquare '.$row["colour"].'"></div>
								<td class="body lvl0">'.$row["name"].'</td>
								<td class="body lvl0">'.$cms->convertDate($row["date"],"",$cms->cmsL).' '.$row["time"].'</td>';
							// SHOW IP ONLY TO LEMON TEAM
							if($_SESSION["userRankId"] == 1) {
								echo '<td class="body lvl0">'.$row["ip"].'</td>';
							}
							echo '
								<td class="body lvl0"><span style="color:#'.$row["color"].';">'.$row["rank"].'</span></td>
								<td class="body lvl0">'.($mod == "CMS"? '' : $row["name_".$cms->cmsL]).'</td>
								<td class="body lvl0">'.($mod == "CMS"? $mod : $row[$cms->cmsL."Name"]).'</td>
								<td class="body lvl0 alignleft bigger">
									'.$row["custom1"].' 
									'.($row["custom2"] != "" ? '('.$row["custom2"].')':'').'
									'.($row["colour"]=="blue"? ' <span style="font-style:italic;">('.$row["label_".$cms->cmsL].')</span>':'').'
									'.($mod == "CMS" ? $row["label_".$cms->cmsL] : '').'
								</td>
							</tr>';
					}
			echo' 	</table>
		</div>';
		break;
	}
?>