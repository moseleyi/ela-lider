<script type="text/javascript">
	$(document).ready(function() {  
		lemon.object_delete(".delComment","<?php echo $cms->translate(453); ?>", "name", "del");
	});
</script>
<?php 
	$cms->checkAccess();
	switch($cms->a) {
		default:
		case "list": 
		$comment_level = 0;
			function showComments($post_id, $comment_id, $cms, $comment_level, $status) { 
				$cms->executeQuery("SELECT *, DATE(date_added) AS dA1, TIME(date_added) AS dA2
									FROM cms_blog_comments		
									WHERE post_id = '$post_id' AND comment_id='$comment_id' ".($cms->blog_aa == false ? " AND status='$status'" : "")."		
									ORDER BY status ASC, date_added DESC
								",$comment_id.$post_id); 
				while($row[$comment_id.$post_id] = mysqli_fetch_assoc($cms->{"result".$comment_id.$post_id})) {
				if($comment_id == 0){$comment_level = 0;}
					echo '<tr> 
							<td class="body lvl0">'.$cms->convertDate($row[$comment_id.$post_id]["dA1"],false,$cms->cmsL).' '.$row[$comment_id.$post_id]["dA2"].'</td>
							<td class="body lvl0 alignleft">'.$row[$comment_id.$post_id]["name"].' '.($row[$comment_id.$post_id]["email"] != "" ? '('.$row[$comment_id.$post_id]["email"].')' : '').'</td>
							<td class="body lvl0 alignleft" style="padding-left:'.(($comment_level * 10) + 10).'px !important;">'.$row[$comment_id.$post_id]["content"].'</td>
							<td class="body lvl0"><a href="'.$cms->get_link("blog_comments,status,".$row[$comment_id.$post_id]["id"]).'" class="link_'.($row[$comment_id.$post_id]["status"] == 1 ? 'show' : 'hide').' plink">&nbsp;</a></td>
							<td class="body lvl0"><a href="'.$cms->get_link("blog_comments,edit,".$row[$comment_id.$post_id]["id"]).'" class="link_edit plink">&nbsp;</a></td>
							<td class="body lvl0"><a href="#" class="delComment link_delete plink" name="'.$row[$comment_id.$post_id]["id"].'">&nbsp;</a></td>
						</tr>'; 
					if($cms->blog_nc == true) { 
						if($cms->getCount("cms_blog_comments","WHERE comment_id='".$row[$comment_id.$post_id]["id"]."'") > 0) { 
							$comment_level++;
							showComments($post_id, $row[$comment_id.$post_id]["id"], $cms, $comment_level);
						} 
					}
				}
			}
			echo '<div id="moduleWrap">
					<table class="table">
						';
			if($cms->blog_aa == false) {
				echo '<tr><td colspan="6"><span class="itemLabel">'.$cms->translate(505).'</span></td></tr>
						<tr> 
							<td class="head" width="150px">'.$cms->translate(84).'</td>
							<td class="head" width="200px">'.$cms->translate(448).'</td>
							<td class="head">'.$cms->translate(450).'</td>  
							<td class="head" width="70px">'.$cms->translate(12).'</td>
							<td class="head" width="70px">'.$cms->translate(14).'</td>
							<td class="head" width="70px">'.$cms->translate(15).'</td>
						</tr>'; 
				$cms->executeQuery("SELECT *
								FROM cms_blog cb
								WHERE (SELECT COUNT(id) FROM cms_blog_comments WHERE post_id = cb.id AND status='0') > 0
									".($cms->id != 0 ? " AND id = '$cms->id'" : "")."
                                    AND lang='$cms->lang'
								ORDER BY date DESC",11);
				while($row = mysqli_fetch_assoc($cms->result11)) {
					$id = $row["id"];
					echo '	<tr>
								<td class="body lvl0 alignleft special" colspan="6">'.$cms->convertDate($row["date"],false,$cms->lang).' - <span class="colourGreen">'.$row["title"].'</span></td>
							</tr>';
													 
					showComments($id, 0, $cms, $comment_level, 0);				
				}
				echo '</table><br /><table class="table">';
			}
			echo '
						<tr> 
							<td class="head" width="150px">'.$cms->translate(84).'</td>
							<td class="head" width="200px">'.$cms->translate(448).'</td>
							<td class="head">'.$cms->translate(450).'</td>  
							<td class="head" width="70px">'.$cms->translate(12).'</td>
							<td class="head" width="70px">'.$cms->translate(14).'</td>
							<td class="head" width="70px">'.$cms->translate(15).'</td>
						</tr>'; 
			$cms->executeQuery("SELECT *
								FROM cms_blog cb
								WHERE (SELECT COUNT(id) FROM cms_blog_comments WHERE post_id = cb.id AND status='1') > 0
									".($cms->id != 0 ? " AND id = '$cms->id'" : "")."
                                    AND lang='$cms->lang'
								ORDER BY date DESC",11);
			while($row = mysqli_fetch_assoc($cms->result11)) {
				$id = $row["id"];
				echo '	<tr>
							<td class="body lvl0 alignleft special" colspan="6">'.$cms->convertDate($row["date"],false,$cms->lang).' - <span class="colourGreen">'.$row["title"].'</span></td>
						</tr>';
												 
				showComments($id, 0, $cms, $comment_level, 1);				
			}
			echo '	</table>
				</div>';
		break;
		case "edit":
			$cms->executeQuery("SELECT * FROM cms_blog_comments WHERE id='$cms->id'",1);
			$row = mysqli_fetch_assoc($cms->result1);
			$name = $row["name"];
			$email = $row["email"];
			$content = $row["content"];
			if(isset($_POST["submit"])) {
				$name = $_POST["name"];
				$email = $_POST["email"];
				$content = $_POST["content"];
				if(empty($name) || empty($email) || empty($content)) {
					$cms->setInfo(false, $cms->translate(23));
				}
				else {
					$cms->executeQuery("UPDATE cms_blog_comments SET name='$name', email='$email', content='$content' WHERE id='$cms->id'",1);
					if($cms->result1) {
						$cms->setSessionInfo(true, $cms->translate(474));
						$cms->saveAction($name,$content);
						header("Location:".$cms->get_link("blog_comments"));
					}
					else {
						$cms->setSessionInfo(false, $cms->translate(27));
					}
				}
			}
			echo ' <form method="post" enctype="multipart/form-data" action="'.$cms->get_link("blog_comments,edit,$cms->id").'"> 
						<div id="addColumnMain">
							<div class="addColumn left narrow">
								<div class="itemWrap ">
									<div class="itemLabel">'.$cms->translate(413).'</div>
									<div class="itemElementWrap">
										<div class="itemElementShadow"><input type="text" class="itemElement text" name="name" value="'.htmlspecialchars($name).'" /></div>
										<div class="itemType vital" title="'.$cms->translate(241).'">!</div>
									</div>
								</div>
								<div class="itemWrap ">
									<div class="itemLabel">'.$cms->translate(458).'</div>
									<div class="itemElementWrap">
										<div class="itemElementShadow"><input type="text" class="itemElement text" name="email" value="'.htmlspecialchars($email).'" /></div>
										<div class="itemType vital" title="'.$cms->translate(241).'">!</div>
									</div>
								</div>
							</div>
							<div class="addColumn right wide">
								<div class="itemWrap ">
									<div class="itemLabel">'.$cms->translate(459).'</div>
									<div class="itemElementWrap">
										<div class="itemElementShadow"><textarea class="itemElement area" name="content">'.htmlspecialchars($content).'</textarea></div>
										<div class="itemType vital" title="'.$cms->translate(241).'">!</div>
									</div>
								</div>
							</div>
							<div class="c"></div><br />
							<input type="submit" value="'.$cms->translate(14).'" class="greenButtonLarge" name="submit"> 
						</div>
					</form>';
		break;
		case "del":
			$cms->executeQuery("SELECT *, cbc.content AS 'content' FROM cms_blog_comments cbc LEFT JOIN cms_blog cb ON cbc.post_id = cb.id WHERE cbc.id='$cms->id'",2);
			$row = mysqli_fetch_assoc($cms->result2); 
			$cms->executeQuery("DELETE FROM cms_blog_comments WHERE id='$cms->id'",1);
			if($cms->result1) {
				$cms->setSessionInfo(true,$cms->translate(452));
				$cms->saveAction($row["title"],$row["content"]);
			}
			else {
				$cms->setSessionInfo(false,$cms->translate(27));
			}
			header("Location:".$cms->get_link("blog_comments"));
		break;
		case "status":  
			$cms->executeQuery("SELECT *, cbc.status AS 'status' FROM cms_blog_comments cbc LEFT JOIN cms_blog cb ON cbc.post_id = cb.id WHERE cbc.id='$cms->id'",2);
			$row = mysqli_fetch_assoc($cms->result2);
			$title = $row["title"];
			$status = $row["status"];
			$n = $status == 0 ? 1 : 0;
			$cms->executeQuery("UPDATE cms_blog_comments SET status='$n' WHERE id='$cms->id'",1);
			if($cms->result1) {
				$cms->setSessionInfo(true,$cms->translate(454));
				$cms->saveAction($title,"");
			}
			else {
				$cms->setSessionInfo(false,$cms->translate(27));
			}
			header("Location:".$cms->get_link("blog_comments"));
		break;
	}
?>