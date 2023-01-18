<?php	
	 
		echo '	<form method="post" enctype="multipart/form-data" action="/cms/'.$cms->lang.'/start">	
					<div id="moduleWrap">  <br />
						<div class="itemElementWrap">
							<div class="itemElementShadow">
                                <strong> FULL TOOLBAR, 100px HEIGHT</strong>
                                <textarea name="content" class="ck-editor itemElement area" id="content" data-ck-height="100" data-ck-toolbar="Full">'.htmlspecialchars($cms->promo_html).'</textarea>
                                <strong> FULL TOOLBAR, 200px HEIGHT</strong>
                                <textarea name="content2" class="ck-editor itemElement area" id="content2" data-ck-height="200" data-ck-toolbar="Full">'.htmlspecialchars($cms->promo_html).'</textarea>  
                                <div style="width:730px;float:left;">
                                <strong> FULLMEDIUM TOOLBAR (same icons, different line breaks), 300px HEIGHT, BLUE BACKGROUND</strong><textarea style="width:500px;" name="content3" class="ck-editor itemElement area" id="content3" data-ck-height="300" data-ck-toolbar="FullMedium" data-ck-bg="blue">'.htmlspecialchars($cms->promo_html).'</textarea></div>
                                <div style="width:400px;float:left;">
                                <strong> BASIC TOOLBAR, 200px HEIGHT, RED BACKGROUND</strong>
                                    <textarea name="content5" class="ck-editor itemElement area" id="content5" data-ck-height="200" data-ck-toolbar="Basic" data-ck-bg="red">'.htmlspecialchars($cms->promo_html).'</textarea>
                                </div>
                                <div class="c"></div>                                
                                <strong> SUPER BASIC TOOLBAR, 100px HEIGHT</strong>
                                <div style="width:255px;"><textarea name="content6" class="ck-editor itemElement area" id="content6" data-ck-height="100" data-ck-toolbar="SuperBasic" data-ck-bg="#cccccc">'.htmlspecialchars($cms->promo_html).'</textarea> </div>
                                <strong> FULL TOOLBAR, 300px HEIGHT, GREY BACKGROUND</strong>
                                <textarea name="content4" class="ck-editor itemElement area" id="content4" data-ck-height="300" data-ck-toolbar="Full" data-ck-bg="#cccccc">'.htmlspecialchars($cms->promo_html).'</textarea> 
                            </div> 
                        </div> 
                        <div class="itemType helpful" title="'.$cms->translate(242).'">?</div>
                    </div> <br />
                    <input type="submit" value="'.$cms->translate(14).'" class="greenButtonLarge" name="submit" />	 
                </div>
            </form>'; 
?>