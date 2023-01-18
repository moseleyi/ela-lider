<?php
	include_once("class.cmscontrol.php");
	$cms = new cmsControl(); 
	$cms->startCMS();
	$cms->activeLangs = $cms->getCount("cms_langs","WHERE added='1' AND status='1'");
	switch($cms->a) { 
		case "cookie":
			if(setcookie("laaccept", "yes", time() + (3600 * 24 * 365), "/")) {
				echo 1;
			}
			else {
				echo 2;
			}
		break;
        case "getlink":
            echo $cms->get_link($_POST["t"]);
        break;
		case "time":
			$_SESSION["timestamp"] = time();
			echo $_SESSION["timestamp"];
		break;
		case "linkpreview":
			$id = $_POST["id"];			
			$cms->lang = $_POST["lang"];
			$cms->executeQuery("SELECT * FROM cms_menu WHERE id='$id'",1);
			$row = mysqli_fetch_assoc($cms->result1);
			$link = $cms->activeLangs > 1 ? '/'.$cms->lang : '';
			if($cms->urls == "current-noid") {
				$link .= '/'.$row["intName"];
			}
			elseif($cms->urls == "current") {
				$link .= '/'.$row["intName"].','.$row["id"];
			}
			else {
				$lvl = $row["level"];
				$tree = array();
				$tree[$lvl]["in"] = $row["intName"];
				$tree[$lvl]["id"] = $row["id"];
				$pId = $row["parentId"];
				for($i=$lvl-1;$i>=0;$i--) {
					$cms->executeQuery("SELECT * FROM cms_menu WHERE id='$pId'",1);
					$row = mysqli_fetch_assoc($cms->result1);
					$pId = $row["parentId"];
					$tree[$i]["in"] = $row["intName"];
					$tree[$i]["id"] = $row["id"];
				}
				ksort($tree);
				foreach($tree as $lev => $arr) {
					$link .= '/'.$arr["in"].($lev == $lvl && $cms->urls == "full" ? ','.$arr["id"] : '');
				}
			}
			echo $link;
		break;
		case "pchange":
			$id = $_POST["id"];
			$newP = $_POST["new"];
			$oldP = $_POST["old"]; 
			$new = hash("sha1",$newP);
			$old = hash("sha1",$oldP);
			$cms->executeQuery("SELECT * FROM cms_accounts WHERE id='$id'",1);
			$row = mysqli_fetch_assoc($cms->result1);
			$oldPDB = $row["password"];
			if($old == $oldPDB) {
				$cms->executeQuery("UPDATE cms_accounts SET password='$new' WHERE id='$id'",2);
				if($cms->result2) {
					echo 1;
				}
				else {
					echo 2;
				}
			}	
			else {
				echo 3;
			} 
		break;
		case "imageDetails":
			$id = $_POST["id"];
			$m = $_POST["m"];
			$cms->executeQuery("SELECT * FROM cms_".$m."_files WHERE id='$id'",1);
			$row = mysqli_fetch_assoc($cms->result1); 
			echo $row["alt"].'|'.$row["description"].'|'.$row["link"];  
		break;
		case "books-name":
		case "books-surname":
		case "books-publisher":
		case "books-series":
			$search = $_POST["search"];
			$field = str_replace("books-","",$cms->a); 
			$cms->executeQuery("SELECT DISTINCT `$field` FROM cms_books WHERE `$field` LIKE '%$search%' ORDER BY `$field` ASC",1);
			while($row = mysqli_fetch_assoc($cms->result1)) {
				echo $row[$field].'|';
			}
		break; 
		case "setBooks":
			$cc = $_POST["cc"];
			$dd = $_POST["dd"];
			$_SESSION[$cc] = $dd;
			echo '1';
		break;
		case "front-newsletter":
			$e = isset($_POST["email"]) ? $_POST["email"] : '';
			$n = isset($_POST["name"]) ? $_POST["name"] : '';
			$p = isset($_POST["phone"]) ? $_POST["phone"] : '';
			if($cms->getCount("cms_newsletter_users","WHERE email='$e'") > 0) {
				echo 2;
			}
			elseif(filter_var($e, FILTER_VALIDATE_EMAIL) == false) {
				echo 4;
			}
			else {
				$cms->executeQuery("INSERT INTO cms_newsletter_users (`id`, `dateAdded`, `name`, `email`, `phone`, `lang`, `ip`) VALUES ('', NOW(), '$n', '$e', '$p', '$cms->lang', '".$_SERVER["REMOTE_ADDR"]."')",1);
				if($cms->result1) {
					echo 1;
				}
				else {
					echo 3;
				}
			}
		break;
		case "crop":
			$x = $_POST["x"]; 
			$y = $_POST["y"]; 
			$w = $_POST["w"];
			$h = $_POST["h"];
			$file = $_POST["file"];
			$m = $_POST["m"];
			if($cms->cropImage($file, $x, $y, $w, $h, $m) == true) {
				if($cms->processImage(array("tmp_name"=>"_images_content/$m/_cropped/$file","name"=>$file),"",false,$m)->errors == 0) {
					list($w,$h) = getimagesize("_images_content/$m/_lemon/$file");  
					/* Debug */
					$cms->executeQuery("INSERT INTO errors (`id`, `error`) VALUES ('', '$x $y $w $h $file $m')",1);
					echo '1-'.$w.'-'.$h; 
				}
				else {
					echo 3;
				}				
			}
			else {
				echo 2;
			}
		break;
		case "restore":
			$file = $_POST["f"]; 
			$m = $_POST["m"];
			if(copy("_images_content/$m/_original/$file","_images_content/$m/_cropped/$file") == true) {
				if($cms->processImage(array("tmp_name"=>"_images_content/$m/_cropped/$file","name"=>$file),"",false,$m)->errors == 0) {
					list($w,$h) = getimagesize("_images_content/$m/_lemon/$file");  
					list($w2, $h2) = getimagesize("_images_content/$m/_original/$file");
					echo '1-'.$w.'-'.$h.'-'.$w2.'-'.$h2; 
				}
				else {
					echo 3;
				}	
			}
			else {
				echo 2;
			}
		break;	
		case "filenamechange":
			$id = $_POST["id"];
			$m = $_POST["m"];
			$name = $_POST["name"];
			$file_name = $cms->createValidName($name);
			$cms->executeQuery("SELECT * FROM cms_products_docs WHERE id='$id'",1);
			$row = mysqli_fetch_assoc($cms->result1);
			$file = $row["file"];
			$ext = pathinfo("_files/products/$file",PATHINFO_EXTENSION);
			if(rename("_files/products/$file","_files/products/$file_name.$ext") == true) {
				$cms->executeQuery("UPDATE cms_products_docs SET name='$name', `file`='".$file_name.".".$ext."' WHERE id='$id'",2);
				if($cms->result2) {
					$cms->saveAction($name,"");
					echo "1|".$file_name;
				}
				else {
					echo "2";
				}
			}
			else {
				echo "3";
			}
		break;	
		case "load":
			$id = $_POST["id"];
			$type = $_POST["type"];
			switch($type) {
				case "newsletter_template":
					$cms->executeQuery("SELECT * FROM cms_newsletter_templates WHERE id='$id'",1);
					$row = mysqli_fetch_assoc($cms->result1);
					echo $cms->compressHTML($row["content"]);
				break;
			}
		break;	
		case "checkcaptcha": 
			$res = false;
			$math_vars = $cms->esc($_POST["vars"]);
			$math_answer = (int)$cms->esc($_POST["answer"]);
			$name =  $cms->esc($_POST["name"]);
			$email = $cms->esc($_POST["email"]);
			$code = $cms->esc($_POST["code"]);
			$text = $_POST["text"];
			$post_id = $cms->esc($_POST["id"]);
			$comment_id = $cms->esc($_POST["comment_id"]);
			$comment_level = $cms->esc($_POST["comment_level"]);
			if($cms->validateEmail($email)) {
				if($cms->blog_cs == "math") { 
					list($f, $d, $t) = explode("|", $math_vars); 
					$r = $d == "+" ? ($f + $t) : ($f - $t);   
					if($r === $math_answer) {
						$res = true;
					} 
					else {
						echo '3';
					}
				}
				else {				
					include_once "_scripts/captcha/securimage.php";
					$securimage = new Securimage();
					if($securimage->check($code) == false) {
						echo '4';
					}
					else {
						$res = true;
					}
				}
				
				if($res == true) {
					$date_added = date("Y-m-d H:m:s");
					$cms->executeQuery("INSERT INTO cms_blog_comments (`id`, `post_id`, `comment_id`, `ip`, `name`, `email`, `content`, `status`, `date_added`) VALUES
																	  ('', '$post_id', '$comment_id', '".$_SERVER["REMOTE_ADDR"]."', '$name', '$email', '".$cms->esc(str_replace("\n", "<br />", $text))."', '".($cms->blog_aa == true ? 1 : 0)."', '$date_added')",1);
					if($cms->result1) {
						$id = $cms->lastInsertId();
						echo '1||'.$cms->compressHTML('<div class="comment comment-level-'.$comment_level.'" id="comment-'.$id.'" style="margin-left:'.($comment_level * 10).'px;">
										<div class="comment-person">'.$name.'</div>
										<div class="comment-date">'.$date_added.'</div>
										<div class="c"></div>
										<div class="comment-text">'.str_replace("\n", "<br />", $text).'</div>
										'./* Nested comments */($cms->blog_nc == true ? '<div class="comment-reply">'.$cms->translate(468).'</div>' : '').'
									</div>');
					}
					else {
						echo '2';
					}
				}
			}
			else {
				echo '5';
			}
		break;
	}
	$cms->clearBuffer();
?>