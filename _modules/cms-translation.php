<script type="text/javascript">
	$(document).ready(function() {
		$("tr.trans-module").each(function() {
			$(this).click(function() {
			});
		});
		$("a.editTrans").each(function() {
			$(this).click(function() {
				var id = $(this).prop("name");
				$(".t-"+id).fadeOut(500,function() {	
					$(".i-"+id).fadeIn(500);
					$(".i-"+id+".cancelEditTrans").eq(0).css({"position":"relative","display":"inline-block"}); 
					$(".i-"+id+".saveEditTrans").eq(0).css({"position":"relative","display":"inline-block"});  				
				});
			});
		}); 
		$(".cancelEditTrans").each(function() {
			$(this).click(function() {
				var id = $(this).prop("name");  
				$(".i-"+id).fadeOut(500,'',function() {
					$(".t-"+id).fadeIn(500);
				});
			});
		});
		$(".saveEditTrans").each(function() {
			$(this).click(function() {
				var id = $(this).prop("name");
				var pl = $(".i-"+id).eq(0).val();
				var en = $(".i-"+id).eq(1).val();
				if(pl != "" && en != "") {
					$.post("/cms/<?php echo $cms->lang;?>/translation/edit/"+id,{"pl":pl,"en":en},
					function(data){
						var result = $(data).find("#ajaxResult").html();
						if(result == 1) {
							$("tr#tr-"+id).find("td").addClass("transSaved");
							$("span.t-"+id).eq(0).text(pl);
							$("span.t-"+id).eq(1).text(en);
							$(".i-"+id).fadeOut(500,'',function() {
								$(".t-"+id).fadeIn(500);
							});
							displayInfo('ok','<?php echo $cms->translate(307); ?>');
						}
						else {
							displayInfo('error','<?php echo $cms->translate(27); ?>');
						}
					});
				}
				else {
					displayInfo('error','<?php echo $cms->translate(23); ?>');
				}
			});
		});
		$("#addTrans").click(function() {
			var pl = $("#addPl").val();
			var en = $("#addEn").val();
			if(pl != "" && en != "") {
				$.post("/cms/<?php echo $cms->lang;?>/translation/add",{"pl":pl,"en":en},
					function(data){
						var result = $(data).find("#ajaxResult").html().split("|");
						if(result[0] == 1) {
							var tr = $("#trans > tr").eq(1).clone(true,true); 
							var tr = $("#trans").find("tr").eq(1).clone(true,true); 
							var oldId = tr.prop("id").replace("tr-","");  
							var nTid = (tr.attr("name")*1) + 1;
							var nextId = result[1];
							tr.prop("id","tr-"+nextId);	
							tr.find("td").eq(0).text(nTid+".");
							tr.find("span").eq(0).removeClass("t-"+oldId).addClass("t-"+nextId).text(pl);
							tr.find("span").eq(1).removeClass("t-"+oldId).addClass("t-"+nextId).text(en);
							tr.find("textarea").eq(0).removeClass("i-"+oldId).addClass("i-"+nextId);
							tr.find("textarea").eq(0).val(pl);
							tr.find("textarea").eq(1).removeClass("i-"+oldId).addClass("i-"+nextId);
							tr.find("textarea").eq(1).val(en);
							tr.find("a").attr("name",nextId).removeClass("i-"+oldId).removeClass("t-"+oldId);
							tr.find("a").eq(0).addClass("t-"+nextId);
							tr.find("a").eq(1).addClass("i-"+nextId);
							tr.find("a").eq(2).addClass("i-"+nextId); 
							tr.find("td").addClass("transSaved");	
							tr.find(".i-"+nextId).css("display","none");
							tr.find(".t-"+nextId).css("display","block");
							tr.attr("name",nTid);		
							tr.insertBefore($("#trans").find("tr").eq(1));
							displayInfo('ok','<?php echo $cms->translate(305); ?>');
						}
						else {
							displayInfo('error','<?php echo $cms->translate(27); ?>');
						}
					});
			}
			else {
				displayInfo('error','<?php echo $cms->translate(23); ?>');
			}
		});
		$(".delTrans").click(function(){
			delTrans($(this));
		});
	});
	function delTrans(t) { 
		if(confirm("<?php echo $cms->translate(308); ?>")) {
			var id = $(t).prop("name");
			$.get("/cms/<?php echo $cms->lang;?>/translation/del/"+id,function(data) {
				var result = $(data).find("#ajaxResult").html().split("|");  
				if(result[0] == 1) {
					$("#tr-"+id).remove(); 
					displayInfo('ok','<?php echo $cms->translate(306); ?>');
				}
				else {
					displayInfo('error','<?php echo $cms->translate(27); ?>');
				}
			});
		}
	}
</script>
<?php 
	if($_SESSION["userRankId"] != 1) {
		header("Location:".$cms->get_link("start"));
	}
	else {
		switch($cms->a) {
			case "add":
				$pl = $_POST["pl"];
				$en = $_POST["en"];
				$cms->executeQuery("SELECT MAX(tId) AS tId FROM cms_translation",2);
				$row = mysqli_fetch_assoc($cms->result2);
				$tId = $row["tId"] + 1;
				$cms->executeQuery("INSERT INTO cms_translation (`id`, `pl`, `en`,`tId`) VALUES  ('', '".$cms->esc($pl)."', '".$cms->esc($en)."', '$tId')",1);	
				if($cms->result1) {
					$cms->executeQuery("SELECT MAX(id) AS max FROM cms_translation",11);
					$row = mysqli_fetch_assoc($cms->result11);
					$maxId = $row["max"];
					echo '<div id="ajaxResult">1|'.$maxId.'</div>';
				}
				else {
					echo '<div id="ajaxResult">0|</div>';
				}		
			break;
			case "del":
				$cms->executeQuery("DELETE FROM cms_translation WHERE id='$cms->id'",1);	
				if($cms->result1) { 
					echo '<div id="ajaxResult">1|'.$cms->id.'</div>'; 
				}
				else {
					echo '<div id="ajaxResult">0|'.$cms->id.'</div>';
				}		
			break;
			default:
			case "list3":
				echo '	<div id="moduleWrap">
						<div class="addColumn full NB">';
						$cms->executeQuery("SELECT * FROM cms_settings_groups ORDER BY FIELD(id,1,2,3,4,7,5,6,8) ASC",1);
						while($row = mysqli_fetch_assoc($cms->result1)) {
							$id = $row["id"];
							$name = $row["name_".$cms->cmsL];
							echo '<div class="configGroupLink">'.$name.'</div>';
						}
				echo '	</div>
						<div class="c"></div><br />
						<div id="configWrap">
							<div id="configWrapScroll">';
			break;
			case "list":
				echo '
				<div id="addColumnMain">
					<div class="addColumn left narrow extraPad">
						<div class="itemWrap">
							<div class="itemLabel">'.$cms->translate(303).'</div>
							<div class="itemElementWrap">
								<div class="itemElementShadow">
									<input type="text" class="itemElement text" id="addPl" />
								</div>
								<div class="itemType vital" title="'.$cms->translate(241).'">!</div>
							</div>
						</div>
					</div>
					<div class="addColumn narrow right">
						<div class="itemWrap">
							<div class="itemLabel">'.$cms->translate(304).'</div>
							<div class="itemElementWrap">
								<div class="itemElementShadow">
									<input type="text" class="itemElement text" id="addEn" />
								</div>
								<div class="itemType vital" title="'.$cms->translate(241).'">!</div>
							</div>
						</div>
					</div>
					<div class="c"></div> <br />
					<input class="greenButtonLarge" id="addTrans" value="'.$cms->translate(21).'" />
				</div>
				<div id="moduleWrap">
					<table class="table" id="trans">
						<tr>
							<td class="head" width="70px">Lp.</td>
							<td class="head">'.$cms->translate(303).'</td>
							<td class="head" width="462px">'.$cms->translate(304).'</td>
							<td class="head" width="70px">'.$cms->translate(14).'</td>
							<td class="head" width="70px">'.$cms->translate(15).'</td>
						</tr>'; 
					$cms->executeQuery("SELECT * FROM cms_translation ORDER BY tId DESC",2);
					while($row2 = mysqli_fetch_assoc($cms->result2)) {
						/*$pl = strlen($row2["pl"]) > 80 ? '<textarea name="" class="trans-textarea i-'.$row2["id"].'">'.htmlspecialchars($row2["pl"]).'</textarea>' : '<input type="text" class="trans-input i-'.$row2["id"].'" value="'.htmlspecialchars($row2["pl"]).'" />' ;
						$en = strlen($row2["en"]) > 80 ? '<textarea name="" class="trans-textarea i-'.$row2["id"].'">'.htmlspecialchars($row2["en"]).'</textarea>' : '<input type="text" class="trans-input i-'.$row2["id"].'" value="'.htmlspecialchars($row2["en"]).'" />' ;*/
						$pl = '<textarea name="" class="trans-textarea i-'.$row2["id"].'">'.htmlspecialchars($row2["pl"]).'</textarea>';
						$en = '<textarea name="" class="trans-textarea i-'.$row2["id"].'">'.htmlspecialchars($row2["en"]).'</textarea>';
						echo '<tr id="tr-'.$row2["id"].'" name="'.$row2["tId"].'">
								<td class="trans-number body lvl0">'.$row2["tId"].'.</td>
								<td class="trans-polish body lvl0 alignleft"><span class="t-'.$row2["id"].'">'.$row2["pl"].'</span>'.$pl.'</td>
								<td class="trans-english body lvl0 alignleft"><span class="t-'.$row2["id"].'">'.$row2["en"].'</span>'.$en.'</td>
								<td class="trans-edit body lvl0">
									<a href="#edit" class="link_edit plink editTrans t-'.$row2["id"].'" name="'.$row2["id"].'">&nbsp;</a>
									<a class="i-'.$row2["id"].' saveEditTrans" href="#back" name="'.$row2["id"].'">&nbsp;</a>
									<a class="i-'.$row2["id"].' cancelEditTrans" href="#back" name="'.$row2["id"].'">&nbsp;</a>
								</td>
								<td class="trans-edit body lvl0"><a href="#" class="delTrans link_delete plink" name="'.$row2["id"].'">&nbsp;</a></td>
							</tr>';
					} 
				echo '</table>
			</div>';
			break;
			case "edit":
				$pl = $_POST["pl"];
				$en = $_POST["en"];
				$cms->executeQuery("UPDATE cms_translation SET pl='$pl', en='$en' WHERE id='$cms->id'",1);
				if($cms->result1) {
					echo '<div id="ajaxResult">1</div>';
				}
				else {
					echo '<div id="ajaxResult">0</div>';
				}
			break;
		}
	}
?>