<?php
	header("Content-type: text/javascript");
	include_once("../../class.cmscontrol.php");
	$cms = new cmsControl(); 
	$fonts = '';
	$styles = array();
	$cms->executeQuery("SELECT * FROM cms_fonts WHERE status='1' ORDER BY name ASC",1);
	while($row = mysqli_fetch_assoc($cms->result1)) {
		$fonts .= $row["name"].'/'.$row["name_css"].';';
	}
	
	$cms->executeQuery("SELECT * FROM cms_ckeditor ORDER BY name ASC",1);
	while($row = mysqli_fetch_assoc($cms->result1)) {
		$class = '';
		$attr = explode(",",$row["attributes"]);
		foreach($attr as $i => $at) {
			list($key,$value) = explode(":",$at);
			if($key == "class") {
				if($row["className"] != "") {  
					$attr[$i] = '"class":"'.$value.' '.$row["className"].'"';
				}
				else {
					$attr[$i] = '"class":"'.$value.'"';
				}
			}
			else {
				$attr[$i] = '"'.$key.'":"'.$value.'"';
			}
		}
		$attrf = implode(",",$attr);
		$styles[] = '{name:"'.$row["name"].'",element:"'.$row["element"].'"'.($row["attributes"] != "" ? ',attributes:{'.$attrf.'}' : '').''.($row["styles"] != "" ? ',styles:{"'.str_replace(":", '":"', str_replace(";", '","', $row["styles"])).'"}' : '').'}';
	} 
	
	echo '
		CKEDITOR.editorConfig = function( config ){
			config.language = "'.$cms->cmsL.'";	
			config.contentsCss = "/_scripts/cms/ckeditor_styles.css";
			config.font_names = "'.$fonts.'" + config.font_names;	
			config.fontSize_sizes = "'.$cms->ckeditor_fontsizes.'";
			config.toolbar = "'.($_SESSION["userRankId"] == 1 ? 'Full' : $cms->ckeditor_toolbar).'";
			config.indentOffset = "20";
            config.enterMode = CKEDITOR.ENTER_BR;
			config.allowedContent = true;
			config.toolbar_Full =[[
				"Undo","Redo","Bold","Italic","Underline","Strike","Subscript","Superscript","-","TextColor","BGColor","-","NumberedList","BulletedList","Outdent","Indent"],["JustifyLeft","JustifyCenter","JustifyRight","JustifyBlock"],["Link","Unlink","Anchor","Image","Table","HorizontalRule","SpecialChar"],["Iframe","Maximize","ShowBlocks","CreateDiv"],["Find","Replace","PasteFromWord"],["Source"],["Font"],["FontSize"],["Styles"],["RemoveFormat"]]; 
			config.toolbar_FullMedium =[[
				"Undo","Redo","Bold","Italic","Underline","Strike","Subscript","Superscript","-","TextColor","BGColor","-","NumberedList","BulletedList","Outdent","Indent","-","JustifyLeft","JustifyCenter","JustifyRight","JustifyBlock","Link","Unlink","Anchor","Image","Table","HorizontalRule","SpecialChar","Iframe","Maximize"],["ShowBlocks","CreateDiv","Find","Replace","PasteFromWord","Source","Font"],["FontSize","Styles","RemoveFormat"]]; 
			
			config.toolbar_Basic =[["Bold", "Italic","Underline","NumberedList", "BulletedList","Link", "Unlink","Image","Table","JustifyLeft","JustifyCenter","JustifyRight","JustifyBlock","Styles","Font","FontSize","RemoveFormat"]];
            config.toolbar_SuperBasic =[["Styles","JustifyLeft","JustifyCenter","JustifyRight","JustifyBlock","FontSize", "Bold", "Italic", "Underline", "Strike","Font", "Link", "Unlink", "Anchor","Image"]];
			config.filebrowserBrowseUrl = "/_scripts/cms/kcfinder/browse.php?type=files";		
			config.skin = "bootstrapck";
		   config.filebrowserImageBrowseUrl = "/_scripts/cms/kcfinder/browse.php?type=images";
		   config.filebrowserFlashBrowseUrl = "/_scripts/cms/kcfinder/browse.php?type=flash";
		   config.filebrowserUploadUrl = "/_scripts/cms/kcfinder/upload.php?type=files";
		   config.filebrowserImageUploadUrl = "/_scripts/cms/kcfinder/upload.php?type=images";
		   config.filebrowserFlashUploadUrl = "/_scripts/cms/kcfinder/upload.php?type=flash";
		};
		CKEDITOR.stylesSet.add("default",[
			'.implode(",",$styles).'
		]);
	';  
	
	/* Other buttons that can be used: 
		"Save","NewPage","DocProps","Preview","Print","Templates","Scayt","Form", "Checkbox", "Radio", "TextField", "Textarea", "Select", "Button", "ImageButton","HiddenField","Source","Cut","Copy","Paste","PasteText","Undo","Redo","SelectAll","SpellChecker","Blockquote","BidiLtr","BidiRtl","Flash","Smiley","PageBreak"
	*/
?>