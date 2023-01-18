<script type="text/javascript">
	$(document).ready(function() { 
		$(".delScript").click(function(){delEntry("<?php echo $cms->translate(291); ?>",$(this).attr("name"))});  
	});
</script>
<?php
	$cms->checkAccess(); 
	switch($cms->a) { 
		case "list":
		default:
			echo '	<div id="moduleWrap">
						<div class="addColumn left narrow">
							<div class="itemWrap">
								<div class="itemLabel">'.$cms->translate(290).'</div>
								 <table class="table" style="width:425px !important">
									<tr>
										<td class="head" width="40px">Lp.</td>
										<td class="head" width="200px">Name</td>
										<td class="head" width="70px">Version</td>
										<td class="head" width="70px">Status</td> 
										<td class="head" width="70px">Async</td> 
									</tr>';
							$i = 1;
							$cms->executeQuery("SELECT * FROM cms_scripts GROUP BY name ORDER BY id ASC",1);
							while($row = mysqli_fetch_assoc($cms->result1)) { 
								echo '<tr>
										<td class="body lvl0"><strong>'.$i.'.</strong></td>
										<td class="body alignleft lvl0">'.$row["name"].'</td>
										<td class="body lvl0">'.$row["version"].'</td>
										<td class="body lvl0"> 
											<a class="'.($row["status"] == 0 ? 'link_hide' : 'link_show').' plink" href="/cms/'.$cms->lang.'/scripts/status/'.$row["id"].'">&nbsp;</a>
										</td> 
										<td class="body lvl0"> 
											<a class="'.($row["async"] == 0 ? 'link_hide' : 'link_show').' plink" href="/cms/'.$cms->lang.'/scripts/status/as'.$row["id"].'">&nbsp;</a>
										</td> 
									</tr>';
								$i++;
							}
					echo '		</table>
							</div>
						</div>
						<div class="addColumn wide right">
							<form method="post" enctype="multipart/form-data" action="'.$cms->get_link("scripts,update").'">
								<div class="itemWrap ">
									<div class="itemLabel">'.$cms->translate(289).'</div>
									<div class="itemElementWrap">
										<div class="itemElementShadow"><textarea class="itemElement area" name="footer">'.$cms->scripts_foot.'</textarea></div>
										<div class="itemType optional" title="'.$cms->translate(243).'">?</div>
									</div>
								</div>
								<div class="c"></div><br />
								<input type="submit" name="submit" class="greenButtonLarge " value="'.$cms->translate(14).'" />
							</form>
						</div>
						<div class="c"></div>
					</div>';
		break;
		case "status": 
			if(strpos('----'.$cms->id, "as") > 0) {
				$id = str_replace("as", "", $cms->id);
				$cms->id = $id;
				$cms->executeQuery("SELECT * FROM cms_scripts WHERE id='$cms->id'",1);
				$row = mysqli_fetch_assoc($cms->result1);
				$extName = $row["name"];
				$status = $row["async"];
				$ver = $row["version"];
				$n = $status == 0 ? 1 : 0;  
				$cms->executeQuery("UPDATE cms_scripts SET `async`='$n' WHERE name='$extName' AND version='$ver' AND type='JS'",1);
				if($cms->result1) { 
					$cms->setSessionInfo(true,$cms->translate(292));	
					$cms->saveAction($extName,"","scripts","async");
				}
				else { 
					$cms->setSessionInfo(false,$cms->translate(27));
				}
			}
			else {
				$cms->executeQuery("SELECT * FROM cms_scripts WHERE id='$cms->id'",1);
				$row = mysqli_fetch_assoc($cms->result1);
				$extName = $row["name"];
				$status = $row["status"];
				$ver = $row["version"];
				$n = $status == 0 ? 1 : 0;
				$cms->executeQuery("UPDATE cms_scripts SET status='$n' WHERE name='$extName' AND version='$ver'",1);
				if($cms->result1) {
					$cms->setSessionInfo(true,$cms->translate(292));
					$cms->saveAction($extName,"");
				}
				else {
					$cms->setSessionInfo(false,$cms->translate(27));
				}
			}
			header("Location:".$cms->get_link("scripts"));
		break;
		case "update": 
			$footer = $_POST["footer"]; 
			$cms->executeQuery("UPDATE cms_settings SET featureValue='".$cms->esc($footer)."' WHERE id='4'",2);
			if($cms->result2) {
				$cms->setSessionInfo(true,$cms->translate(293));
				$cms->saveAction("","");
			}
			else {
				$cms->setSessionInfo(false,$cms->translate(27));
			}
			header("Location:".$cms->get_link("scripts"));
		break;
	}
?>