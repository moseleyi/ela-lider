
/* class.lemon.js
.---------------------------------------------------------------------------.
|  Software: LemonCMS - Content Management System                           |
|   Version: 2.7.9                                                          |
|  Released: 17 October 2017                                                |
|   Contact: michal@lemon-art.pl, dawid@lemon-art.pl                        |
|      Info: http://lemon-art.pl                                            |
| ------------------------------------------------------------------------- |
|    Author: <coding> Dawid Nawrot                                          |
|    Author: <design> Michał Kortas                                         |
| Thanks to: <manual> Paulina Kortas                                        |
| Copyright: (c) 2009-2017, Lemon-Art Studio Graficzne. All Rghts Reserved. |
| ------------------------------------------------------------------------- |
|   License: Distributed by Lemon-Art Studio Graficzne. You can't modify    |
|			 redistribute, or sell this copy of CMS. One copy of this       |
|            software is allowed to run on one website. Multiple licensing  |
|            available.                                                     |
'---------------------------------------------------------------------------'
*/
var jcrop_api; 
(function(window, undefined){ 
	lemon = { 
        get_link : function(t, ln) {
        
            if(typeof t == "undefined")
                t = "";
                
            if(typeof ln == "undefined")
                ln = "";
            
            /* first always add the beginning of the link to the array */
            var l = "/" + (lemon.links == "get" ? "cms.php?l=" + lemon.lang : lemon.links + '/' + (ln == "" ? lemon.lang : ln)) + (t.length > 0 ? (lemon.links == "get" ? '&' : '/') : '');
        
            var lt = [];
        
            if(t != "") {
                var e = t.split(",");

                lt.push((lemon.links == "get" ? 'p=' : '') + e[0]);

                if(e.length > 1) {
                    lt.push((lemon.links == "get" ? 'a=' : '') + e[1]);

                    if(e.length > 2) {
                        lt.push((lemon.links == "get" ? "id=" : "") + e[2]);

                        if(e.length > 3) {
                            lt.push(((lemon.links == "get" ? e[3] + "=" : ",") + e[3]) + e[4]);
                        }
                    }
                }
            }

            return l + lt.join(lemon.links == "get" ? "&" : "/");
        },
        
        ckeditor : function() {
            $(document).ready(function() {
                $("textarea.ck-editor").each(function() {
                    var height = $(this).data("ck-height") || 600,
                        toolbar = $(this).data("ck-toolbar") || "Full",
                        bg = $(this).data("ck-bg"),
                        id = $(this).prop("id"),
                        instance = CKEDITOR.instances[id];
                    if(instance){CKEDITOR.destroy(instance);}
                    var editor = CKEDITOR.replace(id,{
                        height:height,
                        enterMode : CKEDITOR.ENTER_BR,
                        customConfig : '/_scripts/ckeditor_config.js',
                        toolbar:toolbar,
                        on: {
                            instanceReady : function(evt) {	
                                if(bg != "") { 
                                    CKEDITOR.instances[id].document.getBody().setStyle("background-color",bg); 
                                }
                            }
                        }
                    }); 
                });
            });
        },
	
		image_edit	:	function() {
			$(document).on("click", ".edit-image-details", function() {
				var object_id = $(this).attr("name");
				$.post("/ajax/imageDetails",{id:object_id,m:lemon.module},function(data) {
					
					/* Receive the data from AJAX */
					var r = data.split("|");
                    
                        if(!!CKEDITOR.instances["content"]) {                        
                            CKEDITOR.instances["content"].destroy();                            
                        }
                        
					$("#imageDetailsAlt").val(r[0]);
					if(lemon.module == "slideshow") {
						$("#content").val(r[1]); 
					}
					else if (lemon.module == "gallery" || lemon.module == "blog" || lemon.module == "news") {
						$("#imageDetailsContent").val(r[1]); 
					}
					$("#imageDetailsLink").val(r[2]); 
					
					
					$("#overlay-body").html($("#imageDetails"));
					
					$("#imageDetails").removeClass("hidden");
					$("#overlay-wrap-inner").css({visibility:"hidden",display:"block", "width":"730px"});		
							
					$("#overlay-wrap").css("display","block");
					
					if(lemon.module == "slideshow") {
						if(($(window).height() - ($("#overlay-wrap-inner").height()+(!CKEDITOR.instances["content"] ? 165 : 0)) / 2) > 0){
							$("#overlay-wrap-inner").css("margin-top",($(window).height() - ($("#overlay-wrap-inner").height()+(!CKEDITOR.instances["content"] ? 165 : 0))) / 2);
						}
					}
					else if(lemon.module == "gallery" || lemon.module == "blog" || lemon.module == "news") {
						$("#overlay-wrap-inner").css("margin-top",($(window).height() - $("#overlay-wrap-inner").height()) / 2); 
					}
					
					$("#overlay-wrap").css("display","none");
					
					$("#overlay-wrap-inner").css({display:"none",visibility:"visible"});
					
					if(lemon.module == "slideshow") { 
						if(!CKEDITOR.instances["content"]) {
							 loadEditor('content','200px');
						}
					} 
					
					$("#overlay-body").css("padding",20);
					 
                   $("form#imageDetails").attr("action", lemon.get_link(lemon.module+",editd,"+object_id+"#edit")); 
					
					$("#overlay-action").css("display","none");
					
					$("#overlay-title").text(lemon.image_details_title);
					
					$("#overlay-load").css("display","none");
					//$("body,html").animate({scrollTop:0});
					$("#overlay-wrap").fadeIn(300); 
					$("#overlay-wrap-inner").fadeIn(500);
				});
			});
		},
		image_sorting : function() {			
			$("#image-sorting").dragsort({ dragSelector: "div.image-container", dragEnd: saveOrder, placeHolderTemplate: "<li class='placeHolder'><div></div></li>" });		
            
            function saveOrder() {
                var data = $("#image-sorting li").map(function() { 
                    return $(this).attr("id"); 
                }).get();
              
                $.post(lemon.get_link(lemon.module+",moveimg,"+lemon.id), { "ids[]": data });  
            }
		},
	
		/*
		 *	object_delete - sets up a message for object deletion and directs to relevant action
		 *		@param element - element class/id for the delete action to be activated
		 *		@param message - message to display once delete icon is clicked
		 *		@param attr    - attribute of the element that holds ID of the object
		 *		@param action  - action to be performed, "del" is default
		 */
		object_delete : function(element, message, attr, action) { 
			$(element).each(function() {
				var act = action || "del";
				$(this).click(function() {
					var value = $(this).attr(attr);
					if(confirm(message)) {
						window.location = lemon.get_link(lemon.module+","+action+","+value);
					}
				});
			});
		},
		
		/*
		 *	link_preview - attaches an onchange event to a select dropdown and displays full link for a selected page
		 */
		link_preview : function() {
			var status = true;
			$(document).on("change", "#linkPreview", function() {
				var v = $(this).val();
				if(status == true) {
					status = false;
					if(v != "") {
						$.post("/ajax/linkpreview",{id:v,lang:lemon.lang},function(data) {
							$("#linkPreviewText").text(data);
							status = true;
						});
					}
				}
			});
		},
		
		
		// Cropping images
		crop : {
			
			/*
			 * Set crop wrap inner dimensions , max 700x500
			 */
			 
			crop_height : $(window).height() > 595 ? 500 : $(window).height() - 59,
			crop_width : $(window).width() > 800 ? 800 : $(window).width(),
			msgs : {},
			
			/*
			 * Set up cropping structure
			 */
			construct : function(mode) {
				 
				// Building and appending crop structure
				$('<div id="crop-image"><img src="" id="crop-img" /></div><form onsubmit="return false;" class="coords"><input type="hidden" size="4" id="x1" name="x1" /><input type="hidden" size="4" id="y1" name="y1" /><input type="hidden" size="4" id="x2" name="x2" /><input type="hidden" size="4" id="y2" name="y2" /><input type="hidden" size="4" id="w" name="w" /><input type="hidden" size="4" id="h" name="h" /></form>').appendTo("#overlay-body");
				
				// Center #overlay-wrap-inner vertically if window height greater than 595
				if(lemon.window_height > 595) {
					$("#overlay-wrap-inner").css("margin-top",(lemon.window_height - 595) / 2);
				}	
				
				// Set up JCrop
				$("#crop-img").Jcrop({
					onChange: lemon.crop.coords,
					onSelect: lemon.crop.coords,
					boxWidth: lemon.crop.crop_width,
					boxHeight:lemon.crop.crop_height
				},function(){
					jcrop_api = this; 
				});   				 
				
				lemon.crop.init(mode);	
			},
			
			offset_top:0,
			
			/*
			 * Initialise event
			 */
			 init : function(mode) {
				 
				 /* Open Overlay */
				 $(document).on("click", ".crop-image", function() {
					 lemon.crop.offset_top = $(this).offset().top;
					 lemon.crop.start($(this));
				 }); 
				 
				 /*$(document).on("click", "#overlay-close", function() {
					 $("body, html").animate({scrollTop:lemon.crop.offset_top});
				 });*/
				 
				/* Do the cropping */
				$(document).on("click", "#overlay-action", function() {
					/* Get coordinates */
					var x = $("#x1").val(), 
						y = $("#y1").val(), 
						w  = $("#w").val(), 
						h  = $("#h").val(), 
						wt = w, 
						ht = h, 
					/* Crop Image */
						file = $("#crop-img").prop("src"), 
						id = $("#crop-img").data("id"), 
						self = this;
						
					/* No selection made */
					if(x == "" || y == "") {
						displayInfo("error", lemon.crop.msgs.erro1);
					}
					else {
						$.post("/ajax/crop",{"x":x,"y":y,"w":w,"h":h,"file":file.substring(file.lastIndexOf("/")+1, file.lastIndexOf("?")),"m":lemon.module},function(data) {
							var d = data.split("-");
							if(d[0] == "1") {
								
								/* Image cropped successfully */
								displayInfo("ok",lemon.crop.msgs.ok);
								
								/* Get new image dimensions */
								var ww = d[1];
								var hh = d[2];
								
								/* Update data */
								$("#"+id).find(".crop-image").first().data("size",wt+'-'+ht); 
								
								/* Close Overlay */
								closeOverlay(); 
								
								/* Replace the image */  
								$("#"+id).find("img").first().fadeOut(500, function() {
									$(this).remove();
									$("#crop-img").remove();
									var mt = ((mode == "single" ? hh : 100) - hh)/ 2;
									var ml = ((mode == "single" ? ww : (lemon.module == "slideshow" ? 182 : 143)) - ww) / 2;
									var f = file.substring(0,file.lastIndexOf("?"));
									$('<img src="'+file.replace("_cropped","_lemon")+'" style="margin-top:'+mt+'px;margin-left:'+ml+'px;" />').appendTo($("#"+id).find("div.image-container").first());
								}); 
								
								$("body, html").animate({scrollTop:lemon.crop.offset_top});
							}
							else if(d == "3") {
								displayInfo("error",lemon.crop.msgs.error2);
							}
							else {
								displayInfo("error",lemon.crop.msgs.error3);
							}			
						});
					}
				});
				
				/* Restoring the image */
				$(document).on("click", ".restore-image", function() {
					var id = $(this).prop("name");
					var src = $("#"+id+".image-wrap").find("img").first().prop("src");
					var file = src.substring(src.lastIndexOf("/")+1, (src.lastIndexOf("?") == -1 ? src.length : src.lastIndexOf("?")));
					$.post("/ajax/restore",{f:file,m:lemon.module},function(data) {
						var d = data.split("-");
						if(d[0] == "1") {
							displayInfo("ok", lemon.crop.msgs.restoreok);						
							var ww = d[1];
							var hh = d[2];
							$("#"+id).find(".crop-image").first().data("size",d[3]+'-'+d[4]); 
							$("#"+id).find("img").first().fadeOut(500, function() {
								$(this).remove();
								$("#crop-img").remove();
								var mt = ((mode == "single" ? hh : 100) - hh)/ 2;
								var ml = ((mode == "single" ? ww : (lemon.module == "slideshow" ? 182 : 143)) - ww) / 2;
								var rand = Math.floor((Math.random()*1000) + (Math.random()*100) + (Math.random()*10) + Math.random());
								$('<img src="/_images_content/'+lemon.module+'/_lemon/'+file+'?'+rand+'" style="margin-top:'+mt+'px;margin-left:'+ml+'px;" />').appendTo($("#"+id).find("div.image-container").first());
							}); 
						}
						else if(d[0] == "2") {
							displayInfo("error", lemon.crop.msgs.restoreerror1);
						}
						else if(d[0] == "3") {
							displayInfo("error", lemon.crop.msgs.restoreerror2);
						}
					});
				});
			 },
			
			/*
			 * Update input fields with coordinates
			 */
			coords : function(a) {
				$('#x1').val(a.x);
				$('#y1').val(a.y);
				$('#x2').val(a.x2);
				$('#y2').val(a.y2);
				$('#w').val(a.w);
				$('#h').val(a.h);
			},
			
			/*
			 * Show Crop Wrap
			 */
			start : function(element) {
				// Add overlay:hidden to body element
				$("body").css("overflow","hidden"); 
				
				// Remove previous image
				$("#crop-img").remove();
				
				// Get image path 
				var img = $("#"+element.prop("name")+".image-wrap").find("img").first(); 
				
				// Create random number
				var rand = Math.floor((Math.random()*1000) + (Math.random()*100) + (Math.random()*10) + Math.random());
				
				// Append the image
				var f = img.prop("src").replace("_lemon","_cropped");
				var file = f.lastIndexOf("?") > 0 ? f.substring(0,f.lastIndexOf("?")) : f;
				$('<img src="'+file+"?"+rand+'" id="crop-img" />').appendTo($("#crop-image")); 
				
				// Set new id
				$("#crop-img").data("id",element.prop("name"));
				
				// Get image dimensions
				var width = element.data("size").split("-")[0]*1;
				var height = element.data("size").split("-")[1]*1;
				
				// Destroy current JCrop
                if(!!jcrop_api) {
				    jcrop_api.destroy(); 
                }
				
				// Show overlay wrap
				$("#overlay-wrap").fadeIn(300); 
				
				
				// Wait until image is loaded and then create JCrop
				$("#crop-img").on("load", function() {
					$("#overlay-load").css("display","none");
					$("#crop-img").Jcrop({
						onChange: lemon.crop.coords,
						onSelect: lemon.crop.coords,
						boxWidth: lemon.crop.crop_width,
						boxHeight:lemon.crop.crop_height,
						trueSize:[width,height]
					},function(){
						jcrop_api = this; 
						//$("body, html").animate({scrollTop:0});
					});   
					
					// Re-enable JCrop
					jcrop_api.enable();  
					
					// Get JCrop image after resizing
					var jcrop_img = $(".jcrop-holder").first().find("img").first();
					
					// Vertically center overlay-wrap-inner if image height + 59 is less than window height
					if($(window).height() > jcrop_img.height() + 59) {
						$("#overlay-wrap-inner").css("margin-top",($(window).height() - (jcrop_img.height() + 59)) / 2);
					}
					
					// If image width is smaller then 700, set overlay-wrap-inner to image height but only to min 500px  
					if(jcrop_img.width() < 500) {
						$("#overlay-wrap-inner").css("width",500);
					}
					else if(jcrop_img.width() > 500 && jcrop_img.width() < 800) {
						$("#overlay-wrap-inner").css("width",jcrop_img.width());
					}
					else {
						$("#overlay-wrap-inner").css("width",800);
					}
					
					// Show overlay inner
					$("#overlay-wrap-inner").fadeIn(500);
				});
			}
		},
		
		newsletter : {
			template_loader : function() {
				$(document).on("change", "#load-template", function() {
					lemon.newsletter.tload($(this).val());
				});
			},
			tload : function (template_id) {				
				if(confirm(lemon.newsletter.load_template_msg)) { 
					$.post("/ajax/load",{id:template_id,type:"newsletter_template"}, function(data) {
						CKEDITOR.instances["content"].setData(data);
					});
				}
				else {
					return false;
				}
			},
			load_template_msg : "",
		}
	}
	window.lemon = window.LA = lemon; 
})(window)