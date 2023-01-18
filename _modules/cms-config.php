<script type="text/javascript">
	$(document).ready(function() {  
		$('.colorPicker').colorpicker({color:"000000",colorFormat:"HEX",showOn: 'focus',showSwatches: true,showNoneButton: true,buttonColorize: true, parts: ['header','footer','map','bar'], altProperties: 'background-color,color',select:function(event,color){
			$(this).parents(".itemWrap").first().find(".colorPickerBox").first().css("background-color",color.formatted);$(this).trigger("change");},regional:'<?php echo $cms->cmsL;?>'});
		
		$(".configGroupLink").eq(0).addClass("active"); 
		$(".configGroupLink").each(function() {
			$(this).click(function() {
				var index = $(this).index();
				var old_index = $(".configGroupLink.active").first().index();
				$(".configGroupLink").removeClass("active");
				$(this).addClass("active");
				$(".configGroupWrap").eq(old_index).animate({height:0},500, function() {
					$(".configGroupWrap").eq(index).css({height:0,"visibility":"visible"}).animate({height:$(".configGroupWrap").eq(index).data("height")}, 500);
				});
			});
		});
		
		$(".saveFeature").change(function() {
			var id = $(this).attr("name").replace("feature","").replace("[","").replace("]",""); 
			var value = $(this).val() == 0 ? '' : $(this).val();  
			var def = $(this).parents(".itemWrap").first().data("default");
			ajaxSaveFeature(id,value,this,def); 
		});
		
		$(".saveCkEditor").each(function() {
			$(this).click(function() {
				var textarea = $(this).parents(".addColumn").first().find("textarea").first();
				var id = textarea.attr("name").replace("feature","").replace("[","").replace("]",""); 
				var content = $(this).parents(".addColumn").first().find("iframe").first().contents().find("body").html();
				ajaxSaveFeature(id, content, textarea,0);
			});
		});
	});
	
	$(window).on("load", function() {
		$(".configGroupWrap").each(function() {
			$(this).data("height",$(this).height()).css("height",0);
		});
		$(".configGroupWrap").first().css("height",$($(".configGroupWrap").first()).data("height")); 
	});
	
	/* Save config feature value */
	 function ajaxSaveFeature(id,value,el,def){  
        var t = "config,savefeature,"+id;
        var l = "";
        $.post("/ajax/getlink", {"t":t}, function(d) { 
            $.post(d,{"value":value,"def":def},
                 function(data) {
                    var result = $(data).find("#ajaxResult").html();
                    if(result == 1) {
                        displayInfo("ok","<?php echo $cms->translate(268);?>");
                        var c = "#97bf0d";
                        if(id == 97) {
                            $(".ckeditor11").first().find("iframe").first().contents().find("body").first().css('background-color',"#"+value);
                        }
                    }
                    else if(result == 2) {
                        displayInfo("error","<?php echo $cms->translate(269);?>");
                        var c = "red";
                    }
                    else {
                        displayInfo("error","<?php echo $cms->translate(27);?>");
                        var c = "red";
                    }  
                    if(id.indexOf("module") != -1) {
                        c = c == "red" ? "#e5bcbc" : "#bce5bc";
                        $("#"+id).find("td").css("background-color",c).css("font-weight","bold");
                        setTimeout(function(){$("#"+id).find("td").css("background-color","").css("font-weight","");},3000); 
                    }
                    else {
                        $("#l-"+id).css("color",c);
                        $(el).css("color",c);
                        $("#l-"+id).css("font-weight","bold"); 
                    }
                    // Refresh the CMS if you change interface language
                    if(id == 4 && def == false) {
                        window.location.href = "<?php echo $cms->get_link("config");?>";
                    }
                }
            );
        });		
	} 
</script>
<?php
	$cms->checkAccess(); 
	switch($cms->a) { 
		case "savefeature":
			$value = $cms->esc($_POST["value"]);
			$def = (bool)$_POST["def"];
			if(substr($cms->id,0,6) == "module") {
				$shortName = substr($cms->id,7,strlen($cms->id)-5); 
				$cms->executeQuery("SELECT * FROM cms_modules WHERE shortName='$shortName'",2);
				$row = mysqli_fetch_assoc($cms->result2);
				$n = $row["status"] == "1" ? 0 : 1;
				$cms->executeQuery("UPDATE cms_modules SET status='$n' WHERE shortName='$shortName'",1);
				if($cms->result1) {
					echo '<div id="ajaxResult">1</div>';
				}
				else {
					echo '<div id="ajaxResult">0-'.$value.'</div>';
				}
			}
			else {
				if($def == true) {
					$cms->executeQuery("UPDATE cms_settings_defaults SET value='$value' WHERE id='$cms->id'",1);
				}
				else {
					$cms->executeQuery("SELECT * FROM cms_settings WHERE id='$cms->id'",2);
					$row = mysqli_fetch_assoc($cms->result2);
					if($row["valueType"] == "INT" && is_numeric($value) == false) {
						echo '<div id="ajaxResult">2</div>';
					}
					else {
						$cms->executeQuery("UPDATE cms_settings SET featureValue='$value' WHERE id='$cms->id'",1);
					}
				}
				if($cms->result1) {
					echo '<div id="ajaxResult">1</div>';
				}
				else {
					echo '<div id="ajaxResult">0</div>';
				}
			}
		break;
		case "list":
		default:
			echo '	<div id="moduleWrap">
						<div class="addColumn full NB">';
						$cms->executeQuery("SELECT * FROM cms_settings_groups ".($_SESSION["userRankId"] != 1 ? " WHERE id!=9" : "")." ORDER BY position ASC",1);
						while($row = mysqli_fetch_assoc($cms->result1)) {
							$id = $row["id"];
							$name = $row["name_".$cms->cmsL];
							echo '<div class="configGroupLink">'.$name.'</div>';
						}
			echo '		</div>
						<div class="c"></div><br />
						<div id="configWrap"> ';
						
							function showTree($parentId, $lang) {
								global $cms; 
								global $row2;
								$rand = rand(0,999999999999999);
								$cms->executeQuery("SELECT * FROM cms_menu WHERE lang='$lang' AND parentId='$parentId' AND type != 5 AND id!='$cms->id' ORDER BY position ASC",$rand); 
								while($row[$rand] = mysqli_fetch_assoc($cms->{"result".$rand})) {
									$id = $row[$rand]["id"];
									$spaces = '';
									for($i=1;$i<=$row[$rand]["level"];$i++) {
										$spaces .= '&nbsp;&nbsp;&nbsp;&nbsp;';
									}
									echo '<option value="'.$id.'"'.($row2["featureValue"] == $id ? ' selected="selected"' : '').'>'.$spaces.''.$row[$rand]["extName"];
                                    
									if($cms->getCount("cms_menu","WHERE parentId='$id' AND type != 5") > 0 && $row[$rand]["level"]+1 < $cms->menu_sl) {
										$spaces .= '&nbsp;&nbsp;';
										showTree($id, $lang);
									}
								}
							}
							$cms->executeQuery("SELECT * FROM cms_settings_groups ".($_SESSION["userRankId"] != 1 ? " WHERE id!=9" : "")." ORDER BY position ASC",1);
							while($row = mysqli_fetch_assoc($cms->result1)) {
								$id = $row["id"];
								$name = $row["name_".$cms->cmsL];
								$count = $cms->getCount("cms_settings","WHERE featureGroup='$id'".($_SESSION["userRankId"] != 1 ? " AND lemonOnly='0' " : ""));
								if($id == 2) {
									$c = count(explode(",",$cms->default_info));
									$half = ceil(($cms->getCount("cms_settings_defaults") / 2) / $c) * $c;
								}
								/* Cookies */
								else if($id == 11) {
									$half = 2;
								}
								else {
									$half = ceil(($count + $cms->getCount("cms_settings","WHERE featureGroup='$id'".($_SESSION["userRankId"] != 1 ? " AND lemonOnly='0' " : "")." AND featureType='textarea'")) / 2);
								}
								$i = 1;  
								echo '	<div class="configGroupWrap"'.($id == 11 ? ' style="height:580px;"' : '').'>
											<div class="addColumn narrow left extraPad">';
										
										/* For group id 2 (default information) feed the fields from different table */
										if($id == 2) {
											$cms->executeQuery("SELECT csd.*,cl.shortLang FROM cms_settings_defaults csd INNER JOIN cms_langs cl ON csd.lang_id = cl.id ORDER BY lang_id ASC, position ASC",2);
										}
										else {
											$cms->executeQuery("SELECT * FROM cms_settings WHERE featureGroup='$id'".($_SESSION["userRankId"] != 1 ? " AND lemonOnly='0' " : "")."ORDER BY position ASC",2);
										}
										while($row2 = mysqli_fetch_assoc($cms->result2)) { 
											if($id == 2) { 
												switch($row2["type"]) {
													case "default_page":
														$fd = $cms->cmsL == "pl" ? "Domyślna strona dla: {lang}" : "Default page for: {lang}"; 
													break;
													case "meta_title":
														$fd = $cms->cmsL == "pl" ? "Domyślny tytuł META dla: {lang}" : "Default META Title for: {lang}";
													break;
													case "meta_keys":
														$fd = $cms->cmsL == "pl" ? "Domyślne słowa kluczowe META dla: {lang}" : "Default META Keywords for: {lang}";
													break;
													case "meta_desc":
														$fd = $cms->cmsL == "pl" ? "Domyślny opis META dla: {lang}" : "Default META Description for: {lang}";
													break;
												}
												$row2["featureValue"] = $row2["value"];
												$row2["featureDescription_".$cms->cmsL] = str_replace("{lang}",$row2["shortLang"],$fd).' <img src="/_images_cms/langs/'.$row2["shortLang"].'.png" height="12px"/>';
												$row2["featureName"] = $row2["type"];
												$l = $row2["shortLang"];
											}
											
											/* CKEDITOR */
											if($row2["featureType"] == "ckeditor") {
												echo '</div>
														<div class="addColumn full clear NB ckeditor'.$row["id"].'"><div class="itemWrap">
															<div class="itemLabel">'.$row2["featureDescription_".$cms->cmsL].'</div>
															<div class="itemElementWrap">
																<div class="itemElementShadow"><textarea name="feature['.$row2["id"].']" class="itemElement area" id="content'.$row2["id"].'">'.htmlspecialchars($row2["featureValue"]).'</textarea></div>
																<script type="text/javascript">loadEditor(\'content'.$row2["id"].'\',\'200px\',\''.($row2["id"] == 95 ? '#'.$cms->cookies_bg_colour.'' : '').'\');</script>
																<div class="itemType optional" title="'.$row2["featureDescription_".$cms->cmsL].'">?</div>
															</div>
															<div class="c"></div><br />
															<input type="submit" value="'.$cms->translate(267).'" class="greenButtonLarge saveCkEditor" name="submit" />
														</div>	
													</div>';
											}
											else {
												echo '
													<div class="itemWrap"'.($id == 2 ? ' data-default="true"' : '').'>
														<div class="itemLabel">'.$row2["featureDescription_".$cms->cmsL].'</div>
														<div class="itemElementWrap">
															<div class="itemElementShadow">';   
																switch($row2["featureType"]) {
																	case "text":
																	case "text_small":
																		echo '<input type="text" class="itemElement text saveFeature" value="'.$row2["featureValue"].'" name="feature['.$row2["id"].']" />';
																	break; 
																	case "radio":
																		echo '<select class="itemElement select saveFeature" name="feature['.$row2["id"].']">
																				<option value="0">----- '.$cms->translate(20).' -----</option>
																				<option value="true"'.($cms->{$row2["featureName"]} == true ? ' selected="selected"':'').'">TRUE</option>
																				<option value="false"'.($cms->{$row2["featureName"]} == false ? ' selected="selected"':'').'">FALSE</option>
																			</select>'; 
																	break; 
																	case "textarea":
																		if($row2["featureName"] == "promo_html") {
																			
																		}
																		else {
																			echo '<textarea class="itemElement area saveFeature" style="vertical-align:middle;" name="feature['.$row2["id"].']">'.$row2["featureValue"].'</textarea>'; 
																		}
																	break;
																	case "colorpicker":
																		echo '<input type="text" class="itemElement text colorPicker saveFeature" name="feature['.$row2["id"].']" value="'.$row2["featureValue"].'" />';
																	break;
																	case "select":  
																		/* Default Page, Cookie page */
																		if(in_array($row2["featureName"], array("default_page", "cookies_page_id"))) {   
																			echo '
																			<select class="itemElement select saveFeature" name="feature['.$row2["id"].']" id="f-'.$row2["id"].'">
																				<option value="0">----- '.$cms->translate(20).' -----</option>'; 
																				$spaces = '';
																				showTree(0, $l);
																			echo '</select>';  
																		}
																		/* Blog captcha type */
																		else if($row2["featureName"] == "blog_cs") {
																			echo '
																			<select class="itemElement select saveFeature" name="feature['.$row2["id"].']" id="f-'.$row2["id"].'">
																				<option value="0">----- '.$cms->translate(20).' -----</option>
																				<option value="math"';if($row2["featureValue"] == "math"){echo' selected="selected"';}echo'>'.$cms->translate(460).'</option>
																				<option value="image"';if($row2["featureValue"] == "image"){echo' selected="selected"';}echo'>'.$cms->translate(461).'</option>
																			</select>';  
																		}
																		else {
																			echo ' 
																				<select class="itemElement select saveFeature" name="feature['.$row2["id"].']">
																					<option value="0">----- '.$cms->translate(20).' -----</option>';
																			switch($row2["featureName"]) {
																				case "cmsL":
																					echo '<option value="pl"';if($row2["featureValue"] == "pl"){echo' selected="selected"';}echo'>Polski</option>
																							<option value="en"';if($row2["featureValue"] == "en"){echo' selected="selected"';}echo'>English</option>';
																				break; 
																				case "urls":
																						/*	<option value="current"';if($row2["featureValue"] == "current"){echo' selected="selected"';}echo'>Current Id</option>
																							<option value="full"';if($row2["featureValue"] == "full"){echo' selected="selected"';}echo'>Full Id</option>*/
																					echo '	
																							<option value="current-noid"';if($row2["featureValue"] == "current-noid"){echo' selected="selected"';}echo'>CURRENT</option>
																							<option value="full-noid"';if($row2["featureValue"] == "full-noid"){echo' selected="selected"';}echo'>FULL</option>';
																				break;
																			}
																			echo '
																				</select>';
																		} 
																	break;
																}	
													echo ' 	</div>	';															
																if(isset($row["comment"]) && $row["comment"] != "") {
																	echo '<div class="itemComment">'.$row["comment"].'</div>';
																}
																$itemTypes = array("","vital","helpful","optional");
													echo '	<div class="itemType '.$itemTypes[$row2["vitality"]].'" title="'.$cms->translate('24'.$row2["vitality"]).'">'.($row2["vitality"] == 1 ? '!' : '?').'</div>';
															if($row2["featureType"] == "colorpicker") {
																echo '<div class="colorPickerBox" style="background-color:#'.$row2["featureValue"].';">&nbsp;</div>';
															}
													echo '
														</div>
													</div>
												';
											}
											if($i == $half) {
												echo '</div><div class="addColumn narrow right">';
											}
											$i++;
										}
										
										echo '		</div>
												</div>';
									}
			echo '				<div class="c"></div>
							</div> 
					</div>';
		break;
	}
?>