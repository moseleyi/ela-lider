<script type="text/javascript">
	$(document).ready(function() {
		lemon.object_delete(".delTemplate","<?php echo $cms->translate(443); ?>", "name", "del");
	});
</script>
<?php
	$cms->checkAccess();
	switch($cms->a) {
		default:
		case "list":
			echo '
				<div id="moduleWrap">
					<a href="'.$cms->get_link("newsletter_templates,add").'"  class="greenButtonWide ">'.$cms->translate(444).'</a><div class="c"></div><br />
					<table class="table">
						<tr>
							<td class="head" width="40px">Lp.</td>
							<td class="head" width="140px">'.$cms->translate(84).'</td>
							<td class="head" width="160px">'.$cms->translate(446).'</td>
							<td class="head">'.$cms->translate(75).'</td>  
							<td class="head" width="70px">'.$cms->translate(14).'</td>
							<td class="head" width="70px">'.$cms->translate(15).'</td>
						</tr>';
			$i = 1;
			$cms->executeQuery("SELECT *,DATE(date_added) AS dA1,TIME(date_added) AS dA2,DATE(date_updated) AS dA3,TIME(date_updated) AS dA4 FROM cms_newsletter_templates WHERE lang='$cms->lang' ORDER BY date_updated DESC",1);
			while($row = mysqli_fetch_assoc($cms->result1)) {
				echo '<tr>
						<td class="body lvl0"><strong>'.$i.'.</strong></td>
						<td class="body lvl0"><strong>'.$cms->convertDate($row["dA1"],false,"pl").'</strong><br />'.$row["dA2"].'</td>
						<td class="body lvl0"><strong>'.$cms->convertDate($row["dA3"],false,"pl").'</strong><br />'.$row["dA4"].'</td>
						<td class="body lvl0 alignleft">'.$row["name"].'</td>
						<td class="body lvl0"><a href="'.$cms->get_link("newsletter_templates,edit,".$row["id"]).'" class="link_edit plink">&nbsp;</a></td>
						<td class="body lvl0"><a href="#" class="delTemplate link_delete plink" name="'.$row["id"].'">&nbsp;</a></td>
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
				$cms->executeQuery("SELECT * FROM cms_newsletter_templates WHERE id='$cms->id'",1);
				$row = mysqli_fetch_assoc($cms->result1); 
				$name = $row["name"];
				$content = $row["content"];
			}
			else {
				$title = $content = '';
			}
			if(isset($_POST["submit"]) || isset($_POST["save"])) {
				$name = $_POST["name"];
				$content = $_POST["content"];
				if(empty($name) || empty($content)) {
					$cms->setInfo(false,$cms->translate(23));
				}
				else {
					/*** ADD ***/
					if($cms->a == "add") {
						$cms->executeQuery("INSERT INTO cms_newsletter_templates (`id`, `name`, `content`, `lang`, `date_added`, `date_updated`) VALUES ('', '$name', '$content', '$cms->lang', NOW(), NOW())",1);
					}
					/*** EDIT ***/
					else {						
						$cms->executeQuery("UPDATE cms_newsletter_templates SET name='".$cms->esc($name)."',content='$content', date_updated=NOW() WHERE id='$cms->id'",1);
					}
					if($cms->result1) {
						$cms->saveAction($name,"");
						if(!isset($_POST["save"])) {
							$cms->setSessionInfo(true,$cms->translate($cms->a == "add" ? 440 : 441));
							header("Location:".$cms->get_link("newsletter_templates"));
						}
						else {
							$cms->setInfo(true, $cms->translate(441));
						}
					}
					else {
						$cms->setInfo(false,$cms->translate(27));
					}
				}
			}
			echo ' <form method="post" enctype="multipart/form-data" action="'.$cms->get_link("newsletter_templates,".$cms->a.''.($cms->a=="edit"?','.$cms->id:'')).'"> 
					<div id="addColumnMain">
						<div class="addColumn narrow left NB">
							<div class="itemWrap ">
								<div class="itemLabel">'.$cms->translate(445).'</div>
								<div class="itemElementWrap">
									<div class="itemElementShadow"><input type="text" class="itemElement text" name="name" value="'.htmlspecialchars($name).'" /></div>
									<div class="itemType vital" title="'.$cms->translate(241).'">!</div> 
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
			$cms->executeQuery("SELECT * FROM cms_newsletter_templates WHERE id='$cms->id'",1);
			$row = mysqli_fetch_assoc($cms->result1);
			$cms->executeQuery("DELETE FROM cms_newsletter_templates WHERE id='$cms->id'",1);
			if($cms->result1) {
				$cms->setSessionInfo(true,$cms->translate(442));
				$cms->saveAction($row["name"],"");
			}
			else {
				$cms->setSessionInfo(false,$cms->translate(27));
			}
			header("Location:".$cms->get_link("newsletter_templates"));
		break;
	}
?>