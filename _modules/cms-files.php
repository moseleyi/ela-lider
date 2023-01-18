<script type="text/javascript">
	$(document).ready(function() { 
		lemon.object_delete(".delCat","Czy na pewno chcesz usunąć tę kategorię?", "name", "del");  
		lemon.object_delete(".delFile","Czy na pewno chcesz usunąć ten plik?", "name", "delfile");          
        $("#file-sorting").dragsort({ dragSelector: "div.image-container", dragEnd: saveOrder, placeHolderTemplate: "<li class='placeHolder'><div></div></li>" });		
			function saveOrder() {var data = $("#file-sorting li").map(function() { return $(this).attr("id"); }).get();$.post("/cms/"+lemon.lang+"/"+lemon.module+"/movefile/"+lemon.id, { "ids[]": data }); }; 
                  
	});
</script>
<?php
	$cms->checkAccess(); 
	switch($cms->a) { 
		case "list":
		default:
			echo '
			<div id="moduleWrap">
				<a href="/cms/'.$cms->lang.'/files/add" class="greenButtonWide ">Dodaj kategorie</a><div class="c"></div><br />
				<table class="table">
					<tr> 
						<td class="head">Nazwa kategorii</td>
                        <td class="head" width="300px">Nazwa wewnętrzna/URL</td>
                        <td class="head" width="160px">Pliki</td> 
						<td class="head" width="70px">Status</td>
						<td class="head" width="70px">'.$cms->translate(14).'</td>
						<td class="head" width="70px">'.$cms->translate(15).'</td>
					</tr>';
            $count = $cms->getCount("cms_files_cats", "WHERE lang='$cms->lang'");
            $cms->executeQuery("SELECT * FROM cms_files_cats WHERE lang='$cms->lang' ORDER BY position ASC",1);
            while($r = mysqli_fetch_assoc($cms->result1)) {
                echo '<tr>
                        <td class="body lvl0 alignleft">
						 <input type="text" maxlength="'.strlen($count).'" class="pos-in" value="'.$r["position"].'" name="pos-'.$r["id"].'"/>
							'.$r["name"].'</td>
                        <td class="body lvl0">'.$r["intName"].'</td>
                        <td class="body lvl0"></td>
						<td class="body lvl0"><a href="/cms/'.$cms->lang.'/files/status/'.$r["id"].'" class="link_'.($r["status"] == 1 ? 'show' : 'hide').' plink">&nbsp;</a></td>
						<td class="body lvl0"><a href="/cms/'.$cms->lang.'/files/edit/'.$r["id"].'" class="link_edit plink">&nbsp;</a></td>
						<td class="body lvl0"><a href="#" class="delCat link_delete plink" name="'.$r["id"].'">&nbsp;</a></td>
                    </tr>';
            }
                    
            echo '</table>
            </div>';
        break;
        case "add":
        case "edit":
            if($cms->a == "edit") {
                $cms->executeQuery("SELECT * FROM cms_files_cats WHERE id='$cms->id'",1);
                $r = mysqli_fetch_assoc($cms->result1);
                $name = $r["name"];
                $name_int = $r["intName"];
                $old_name_int = $r["intName"]; 
                $name_old = $r["name"];
            }
            if(isset($_POST["submit"]) || isset($_POST["save"])) {
                $name = $_POST["name"];
                $name_int = $_POST["name_int"];
                $files = $_FILES["files"];
                
                if(empty($name_int) || $name != $name_old) {
                    $name_int = $cms->createIntName($name);
                }
                
                if(empty($name)) {
                    $cms->setInfo(false, $cms->translate(23));
                }
                else if($cms->getCount("cms_files_cats", "WHERE lang='$cms->id' AND intName='$name_int'") > 0) {
                    $cms->setInfo(false, "Podana nazwa wewnętrzna już istnieje");
                }
                else if($cms->validateInternalName($name, $name_int, 1, $old_name_int) == false) {
                    $cms->setInfo(false, "Podana nazwa wewnętrzna jest nieprawidłowa");
                }
                else {
                    if(count($files["name"]) > 0) {
                        foreach($files["name"] as $i => $fname) {
                            $position = $cms->getCount("cms_files", "WHERE category_id='$cms->id'") + 1;
                            $ext = strtolower(pathinfo($fname,PATHINFO_EXTENSION));
                            $n = $cms->createValidName(substr($fname,0,strlen($fname)-(strlen($ext) + 1)));
                            if(move_uploaded_file($files["tmp_name"][$i],"_files/files/".$n.".".$ext) == true) { 
                                $f = $n.'.'.$ext;
                                $n2 = substr($fname,0,strlen($fname)-(strlen($ext) + 1)); 
                                $cms->executeQuery("INSERT INTO cms_files (`id`, `name`, `file`, `category_id`, `position`) VALUES ('', '$n2', '$f', '$cms->id', '$position')",1);  
                            } 
                        }
                    }
                     
                    
                    if($cms->a == "add") {
                        $position = $cms->getCount("cms_files_cats", "WHERE lang='$cms->lang'") + 1;
                        $cms->executeQuery("INSERT INTO cms_files_cats (`id`, `name`, `intName`, `lang`, `position`, `status`) VALUES ('', '$name', '$name_int', '$cms->lang', '$position', '0')",1);
                    }
                    else {
                        $cms->executeQuery("UPDATE cms_files_cats SET name='$name', intName='$name_int' WHERE id='$cms->id'",1);
                    }
                     
                    if($cms->result1) {
                        $cms->saveAction($name,$date);
                        if(!isset($_POST["save"])) {
                            $cms->setSessionInfo(true, "Kategoria dodana pomyślnie");
                            header("Location:/cms/$cms->lang/files");
                        }
                        else {
                            $cms->setInfo(true,$cms->translate(28)); 
                        }
                    }
                    else {
                        $cms->setInfo(false,$cms->translate(27));
                    }
                }
            }
             echo ' <form method="post" enctype="multipart/form-data" action="/cms/'.$cms->lang.'/files/'.$cms->a.''.($cms->a=="edit"?'/'.$cms->id:'').'"> 
                <div id="addColumnMain">
                    <div class="addColumn narrow left NB">
                        <div class="itemWrap ">
                            <div class="itemLabel">Nazwa kategorii</div>
                            <div class="itemElementWrap">
                                <div class="itemElementShadow"><input type="text" class="itemElement text" name="name" value="'.htmlspecialchars($name).'" /></div>
                                <div class="itemType vital" title="'.$cms->translate(241).'">!</div>
                            </div>
                        </div>
                        <div class="itemWrap "> 
                            <div class="itemLabel">'.$cms->translate(10).'</div>
                            <div class="itemElementWrap">
                                <div class="itemElementShadow"><input type="text" class="itemElement text" name="name_int" value="'.$name_int.'" /></div>
                                <div class="itemType vital" title="'.$cms->translate(241).'">!</div>
                                <div class="itemComment">'.$cms->translate(25).'</div>
                            </div>
                        </div>
                    </div>';
                    if($cms->a == "edit") {
                        $names = $_POST["names"];
                        
                        foreach($names as $id => $n) {
                            $cms->executeQuery("UPDATE cms_files SET name='$n' WHERE id='$id'",2);
                        }
                        echo '
                        <div class="addColumn wide right">                        
                            <div class="itemWrap "> 
								<div class="itemLabel">Pliki</div>
								<div class="itemElementWrap itemElementFile">
									<div class="itemElementFileButton">Pliki</div>
									<div class="itemElementFileName"></div>
									<input type="file" class="itemElement file" name="files[]" multiple />
									<div class="itemType optional" title="'.$cms->translate(243).'">?</div>
								</div> 
                            </div> 
                            <style type="text/css">
                                #file-sorting li{width:683px;height:auto;float:none;clear:both;position:relative;}
                                .imageCont{width:90%;height:auto;float:none;clear:both;padding-left:10px;}
                                .imageTools{position:absolute;top:12px;right:20px;height:auto;}
                            </style>
                            <div class="itemWrap">
                                <div class="itemLabel">Pliki załadowane</div>
                                <ul id="file-sorting">';
                                $cms->executeQuery("SELECT * FROM cms_files WHERE category_id='$cms->id' ORDER BY position ASC",1);
                                while($row = mysqli_fetch_assoc($cms->result1)) {  
                                    echo '<li id="'.$row["id"].'" class="image-wrap"> 
                                            <div class="imageCont image-container">
                                                <input type="text" value="'.$row["name"].'" name="names['.$row["id"].']" style="border:0px;font-size:13px;padding:6px;width:400px;margin:10px 10px 10px 0px;color:#333;" /> <span style="font-size:12px;">'.$row["file"].'</span>
                                            </div>
                                            <div class="imageTools"> 
                                                <a href="#" class="delFile link_delete plink" name="'.$row["id"].'" title="'.$cms->translate(377).'">&nbsp;</a> 
                                            </div>
                                            <div class="clear"></div>
                                        </li>';
                                }
                                echo '</ul>
                            </div>
                        </div>';
                    }
                    echo '
						<div class="c"></div><br />
						<div id="buttons">
							<input type="submit" value="'.$cms->translate($cms->a=="add"?21:14).'" class="greenButtonLarge greenButtonFloatLeft" name="submit" />	
							'.($cms->a == "edit" ? '<input type="submit" value="'.$cms->translate(506).'" name="save" class="greenButtonLarge greenButtonFloatLeft" />' : '').'
							<div class="c"></div>
						</div>
					</form>
				</div>';  
        break;
        case "movefile":
			if($cms->id != 0) {
				$cms->executeQuery("SELECT * FROM cms_files_cats WHERE id='$cms->id'",1);
				$row = mysqli_fetch_assoc($cms->result1);
				$name = $row["name"];
			}
			else {
				$name = '';
			} 
			$ids = $_POST["ids"]; 
			for ($idx = 0; $idx < count($ids); $idx+=1) {
				$id = $ids[$idx];
				$ordinal = $idx;
				$cms->executeQuery("UPDATE cms_files SET position='$ordinal' WHERE id='$id'",1);
			}    
			$cms->saveAction($name,"","files","movefiles");
        break;
        case "delfile":
            $cms->executeQuery("SELECT * FROM cms_files WHERE id='$cms->id'",1);
            $r = mysqli_fetch_assoc($cms->result1);
            $name = $r["name"];
            $file = $r["file"];
            $pos = $r["position"];
            $cat_id = $r["category_id"];

            $cms->executeQuery("DELETE FROM cms_files WHERE id='$cms->id'",2);
            if($cms->result2) {
                unlink("_files/files/$file");
                
                $cms->executeQuery("UPDATE cms_files SET position=position-1 WHERE position>'$pos' WHERE lang='$cms->lang'",3);
                $cms->saveAction($name, "");
                $cms->setSessionInfo(true, "Plik usunięty pomyślnie");
            }
            else {
                $cms->setSessionInfo(false, $cms->translate(27));            
            }
            header("Location:/cms/$cms->lang/files/edit/$cat_id");
        break;
        case "del":
            $cms->executeQuery("SELECT * FROM cms_files_cats WHERE id='$cms->id'",1);
            $r = mysqli_fetch_assoc($cms->result1);
            $name = $r["name"];
            $pos = $r["position"];
            
            $cms->executeQuery("DELETE FROM cms_files_cats WHERE id='$cms->id'",2);
            if($cms->result2) {
                $cms->saveAction($name, "");
                
                $cms->executeQuery("SELECT * FROM cms_files WHERE category_id='$cms->id'",3);
                while($r3 = mysqli_fetch_assoc($cms->result3)) {
                    unlink("_files/files/".$r3["file"]);
                }
            }
            else {
                $cms->setSessionInfo(false, $cms->translate(27));
            }
            header("Location:/cms/$cms->lang/files");
        break;
        case "move":
			$pos = $_GET["pos"];
			$cms->executeQuery("SELECT * FROM cms_files_cats WHERE id='$cms->id'",1);
			$row = mysqli_fetch_assoc($cms->result1); 
			$name = $row["name"]; 
			$oldpos = $row["position"]; 
			if($row["position"] == 0) {
				$cms->setSessionInfo(false,$cms->translate(210));
			}
			else {
				$max = $cms->getCount("cms_files_cats","WHERE  lang='$cms->lang' ");			
				if($pos <= 0 || is_numeric($pos) == false) {
					$pos = 1;
				}
				elseif($pos > $max) {
					$pos = $max;
				}  
				$q = $oldpos < $pos ? "position>'$oldpos' AND position<='$pos'" : "position<'$oldpos' AND position >='$pos'"; 	
				$q2 = $oldpos < $pos ? "position=position-1" : "position=position+1";			
				$cms->executeQuery("UPDATE cms_files_cats SET ".$q2." WHERE ".$q." AND lang='$cms->lang'",1);
				if($cms->result1) {
					$cms->executeQuery("UPDATE cms_files_cats SET position='$pos' WHERE id='$cms->id'",2);
					if($cms->result2) {
						$cms->setSessionInfo(true, "Pozycja kategorii zmieniona pomyślnie");
						$cms->saveAction($name,"");
					}
					else {
						$cms->setSessionInfo(false,$cms->translate(27));
					}
				}
				else {
					$cms->setSessionInfo(false,$cms->translate(27));
				}  
			}
			header("Location:/cms/$cms->lang/files");	
        break;        
        case "status":
			$cms->executeQuery("SELECT * FROM cms_files_cats WHERE id='$cms->id'",1);
			$row = mysqli_fetch_assoc($cms->result1);
			$extName = $row["name"];
			$status = $row["status"];
			$n = $status == 0 ? 1 : 0;
			$cms->executeQuery("UPDATE cms_files_cats SET status='$n' WHERE id='$cms->id'",1);
			if($cms->result1) {
				$cms->setSessionInfo(true, "Status kategorii zmieniony pomyślnie");
				$cms->saveAction($extName,"");
			}
			else {
				$cms->setSessionInfo(false,$cms->translate(27));
			}
			header("Location:/cms/$cms->lang/files");
        break;
    }
?>