/*
        LEMON ART FUNCTIONS 1.0
        
        1. resizeImages     - resizes images or elements to fit/fill the parent's container
        2. localNavSystem   - when items in navigations are supposed to scroll to an element
        3. formEvents       - automatic focus/blur on form elements
        4. subMenu          - attaches mouseover and out events to menu elements
        5. centerElement    - centers element within its parent without changing its dimensions
        6. equalHeights     - resizes elements to the same height, either by specific value, parent's height or the tallest element in the collection
        7. createCarousel   - creates a horizontal carousel of elements
        8. assess_css3      - assesses abilities of browser in terms of CSS3 and attaches appropriate classes to the <html> element
        9. promobox         - promobox using magnific popup
       10. loadImages       - dynamic loading of images, after the initial HTML load, with optional callback
       11. lightboxCK       - ckeditor lightbox set up
       12. lightbox         - setup lightbox for all elements with classes
       13. cookie           - setup cookie 
       14  videoResize      - resize video according to given ratio
*/


/*
    Function to center images within the parameters of its container
    
    @param width    (int)   - min/max width
    @param height   (int)   - min/max height
    @param parent   (bool)  - indicates whether to take width & height from element's parent or the settings
    @param fit      (bool)  - indicates whether image should cover the area or be contained in it
    @param abs      (bool)  - apply absolute positioning to the image element
    @param margin   (bool)  - apply top & left values, not applicable for relative content
    @param resize   (bool)  - attach resize event
*/
$.fn.resizeImages = function(options) {
    var settings = $.extend({
        width   : 0,
        height  : 0,
        parent  : false,
        fit     : false,
        abs     : true,
        margin  : true,
        resize  : true
    }, options);
    
    var $els = this;
    
    /* Loop through each image */
    this.each(function() {
    
        /* Local element */
        var $el = $(this);
        
        /* Get element's parent */
        var $par = $el.parent();
        
        /* Before any calculation we need to remove style or width/height properties */
        $el.removeAttr("style");

        /* If parent setting is true, use its dimensions */
        if(settings.parent === true) {
            settings.width = $par.width();
            settings.height = parseInt($par.height()) > 0 ? $par.height() : parseFloat($par.css("padding-bottom"));
        }
        
        /* Calculate new width */
        var nw = settings.width;
        
        /* Calculate new height */
        var nh = ($el.height() * nw) / $el.width();
        
        /* Calculate whether current image size conforms to the settings, if not recalculate starting from the height */
        if((nh < settings.height && settings.fit == false) || (nh > settings.height && settings.fit == true)) {
            nh = settings.height;
            nw = ($el.width() * nh) / $el.height();
        }  
        
        /* Apply new dimensions */
        $el.css({
            width       :	nw,
            height      :	nh
        });
        
        /* Apply margin if necessary */
        if(settings.margin === true) {
            $el.css({
                top         :	(settings.height - nh) / 2, 
                left        :	(settings.width - nw) / 2
            });
        }
        
        /* Apply absolute posiitioning */
        if(settings.abs === true) {
            $el.css({
                position    :   "absolute"
            });
        }
    });
        
    /* Attach resize event if necessary */
    if(settings.resize === true) {
    
        /* Attach the event */
        $(window).on("resize", function() {
            $els.resizeImages({
                parent  : settings.parent,
                height  : settings.height,
                width   : settings.width,
                fit     : settings.fit,
                margin  : settings.margin,
                abs     : settings.abs,
                resize  : false
            });
        });
    }
}; 



/* Global variable to decide whether it's manual scrolling or automatic
   With automatic scrolling we don't want the URL to change (even though it will change all the way to the right one
   We want the URL to stay as to what it is */
var $allowUrlChange = true;

/* 
    Function to attach events for scrolling instead of clicking to new page 
    
    @param prefix   (string)    - prefix of the container elements
    @parem top      (int)       - additional value to scroll in case of fixed elements like docking menu
    @urlchange      (bool)      - should we change the url while scrolling?
*/
    
/* Create array of all scrollable elements */
var scel = [];
$.fn.localNavSystem = function(options) {
    var settings = $.extend({ 
        prefix  :   "section", 
        top     :   0, 
        urlchange : true
    }, options);
    
    /* Original URL */
    var org_url = window.location.pathname; 
    
    /* Original collection */
    var $els = this;
    
    /* Loop through all elements and attach an animation event if necessary */
    this.each(function() {
    
        /* The link inside li element */
        var $a = $("a", this).first();

        /* Check if link has exceptionn or module connection applied */
        var h = $(this).attr('class').match(/(exc|mod)\-[0-9]{1,2}/); 

        /* If connection was found and the element exists we turn the scrolling on */
        if(h != null && $("." + settings.prefix + "-" + h[0]).length > 0) {
                
            /* Get the href of the link */
            var hr = "/" + $a.prop("href").split("/").splice(3).join("/");

            /* Overwrite default click behaviour */
            $a.on("click", function(e) {

                /* Prevent default behaviour */
                e.preventDefault();

                /* Prevent the URL to change since this is automatic scrolling */
                $allowUrlChange = false;
                
                function thetop() {
                    return $("."+settings.prefix + "-" + h[0]).offset().top - (
                                    /* Only add the top if it's "fixed" */
                                    (settings.top.css("position") == "fixed" ? settings.top.height() : 0)) + 2
                }
                
                /* Animate the window to scroll to the element's position */
                $("body, html").stop().animate({
                    scrollTop : thetop()
                }, 750, function() {

                    /* Change the URL if necessary */
                    if(settings.urlchange === true) {
                        window.history.pushState("", $a.text(), hr);
                    }
                    
                    /* Re-enable URL change */
                    $allowUrlChange = true;
                    
                    /* Add active class where necessary */
                    $els.removeClass("active");
                    $a.parent().addClass("active");
                    
                    /* It could be that the "top" element changes its height during scrolling
                       We neeed to add the remaining gap */
                    $("body, html   ").stop().animate({
                        scrollTop : thetop()
                    });
                });
            });
                            
            /* Add the element to the array */
            scel.push({
                href : hr,
                el : $a,
                top : $("."+settings.prefix + "-" + h[0]).offset().top
            });
        }
    });
    
    /* On load event, check if the URL is to be scrolled and perform an action */
    if(window.location.pathname != "") {
        /* Loop through local array */
        for(var i=0; i < scel.length; i++) {
            if(window.location.pathname == scel[i].href && window.location.pathname != "/") {
                $allowUrlChange = false;
                
                scel[i].el.trigger("click");
            }
        }
    }
        
    /* Add current url as 0, to always revert to the original */
    /* Has to be added after the onload event, let it scroll first and then add it because it will most likely be duplicate */
    scel.push({
        href : org_url,
        top : 0,
        el : $els.filter(".active").find("a") || $("#logo")
    });
    
    /* Sort scel array from the element that's furthest down */
    scel.sort(function(a, b) { 
        return b.top - a.top;
    });
    
    /* Add event to the scroll */
    $(window).on("scroll", function(e) {
        
        /* Get current scroll */
        var cs = $("body").scrollTop() || $("html").scrollTop();
        
        /* Loop through the array but only if url change is enabled */
        if($allowUrlChange === true) {
            
            for(var i=0; i < scel.length; i++) {
                /* If scroll is over the "top" value of an element - call it and break the loop */
                if(cs > (scel[i].top - (0.33 * $(window).outerHeight()))) {
                    /* Only change if current URL is different - performance */
                    if(scel[i].href != window.location.pathname) {
                        window.history.pushState("", scel[i].el.text() || "", scel[i].href);
                    
                        /* Navigation active class */
                        $els.removeClass("active");

                        /* Add class the the right element */
                        scel[i].el.parent().addClass("active");
                    } 
                    
                    break;
                } 
            }   
        }
    });
};



/* Attaches focus/blur events to act as replacement for "placeholder" attribute */
$.fn.formEvents = function() {

    /* Loop through each element */
    this.each(function() {
    
        /* Get the desired placeholder value */
        var name = $(this).data("name");

        /* Continue if not empty */
        if(name != "") {
        
            /* Local element */
            var $el = $(this);
        
            /* If element is empty apply the value */
            if($el.val() == "")
                $el.val(name);

            /* On focus remove the value if it equals the placeholder value */
            $el.on("focus", function() {
                var val = $el.val();
                $el.val(val == name ? "" : val);
            });

            /* On blur apply the placeholder value if current value is empty */
            $el.on("blur", function() {
                var val = $el.val();
                $el.val(val == "" ? name : val);
            });
        }
    });
};



/* Function to attach mousenter and leave events between parent/child of a navigation */
$.fn.subMenu = function() {
    /* Loop through all elements that have submenu */
    this.find(".submenu-true").each(function() { 
    
        /* Attach both events */
        $(this).on("mouseenter mouseleave", function(e) { 
        
            /* If event is mousenter show the element */
            if(e.type == "mouseenter") {
                $(this).addClass("active");
                $(this).find("> ul").stop().fadeIn();
            }
            /* If it's leave hide it */
            else {
                $(this).removeClass("active");
                $(this).find("> ul").stop().fadeOut();
            }
        });
    });
};



/* 
    Centers any kind of element within its parent based on its size 
    
    @param parent       (bool)  - should we take parent's dimension
    @parem width        (int)   - override parent's setting, give the dimensions you want to use
    @param height       (int)   - same as above for heith
    @param vertical     (bool)  - only vertical centering
    @param horizontal   (bool)  - only horizontal centering
    @param resize       (bool)  - attach resize event to repeat the procedure when window size changes
*/
$.fn.centerElement = function(options) {
    var settings = $.extend({
        parent      :   true,
        width       :   0,
        height      :   0,
        vertical    :   true,
        horizontal  :   false,
        resize      :   true
    }, options);
    
    /* Elements */
    $els = this;
    
    /* Loop through each element */
    this.each(function() {
    
        /* Get element's parent */
        var par = $(this).parent();
    
        /* Take the outer dimensions, whether it's a parent or fixed dimensions */
        var o_w = !!settings.parent ? par.width() : settings.width;
        var o_h = !!settings.parent ? (parseInt(par.height()) == 0 ? parseFloat(par.css("padding-bottom")) : par.height()) : settings.height;
        
        /* Calculate top and left */
        var top = (o_h - ($(this).height() + parseFloat($(this).css("padding-top")) + parseFloat($(this).css("padding-bottom")))) / 2;
        var left = (o_w - ($(this).width() + parseFloat($(this).css("padding-left")) + parseFloat($(this).css("padding-right")))) / 2;
        
        /* Apply top if vertical centering enabled */
        if(settings.vertical == true)
            $(this).css("top", top);
            
        /* Apply left if horizontal centering enabled */
        if(settings.horizontal == true)
            $(this).css("left", left);
    });
            
    /* Attach resize event */
    if(settings.resize === true) {
        $(window).on("resize", function() {
            $els.centerElement({
                parent  :   settings.parent,
                width   :   settings.width,
                height  :   settings.height,
                vertical:   settings.vertical,
                horizontal: settings.horizontal,
                resize  :   false
            });
        });
    }
};



/*
    Function takes elements and resizes them to the height of the tallest one
    
    @param parent (bool) - should we take the height of a parent?
    @param height (int) - override parent's settings, decide what height to resize it to
*/
$.fn.equalHeights = function(options) {
    var settings = $.fn.extend({
        parent      :   false,
        height      :   0
    }, options);
    
    /* Set the height setting to the height of the parent */
    if(settings.parent == true && settings.height == 0) {
        settings.height = this.first().parent().height();
    }
    /* Loop through all the elements and get the heighest one */
    else if(settings.parent == false && settings.height == 0) {
        this.each(function() {
            settings.height = $(this).height() > settings.height ? $(this).height() : settings.height;
        });
    }
    
    /* Apply the highest value to all the elements */
    this.height(settings.height);
};
        
        
        
/* 
    Function to create horizontal carousel
    
    @parem ratio (int) - ratio of the images/containers
    @param visible (int) - how many elements are supposed to be visible
    @param zoom (bool) - (dis)allow zooming
    @param el (string) - type of elements to look for
*/
$.fn.createcarousel = function(options) {
    var settings = $.extend({
        ratio   :   1,
        visible :   4,
        zoom    :   false,
        el      :   "a"
    }, options);
    
    /* Get all the elements */
    var $els = $(this).find(">"+settings.el);

    /* If we have less items than the "visible" setting, give it the value of all elements */
    if(settings.visible > $els.length) {
        setings.visible = $els.length;
    }
    
    /* Calculate the size of one element */
    var slide = {
        width   :   $(this).width() / settings.visible,
        height  :   ($(this).width() / settings.visible) / settings.ratio
    };

    /* Apply the dimensions to the elements */
    $els.css({
        height  :   slide.height,
        width   :   slide.width
    }).find(".inner");

    /* Add zoom elements if setting enabled */
    if(settings.zoom == true)
        $(this).find("> a").append('<span class="zoom"><span class="img"></span></span>');

    /* Attach an event to be executed once the carousel is initialised */
    $(this).on("cycle-post-initialize", function() {

        /* Resize the images */
        $els.find(".inner img").resizeImages({
            parent  :   false,
            width   :   slide.width,
            height  :   slide.height
        });

        /* Center arrows */
        $(this).parents(".carousel-wrap").find('img[src*="arrow"]').css("top", (slide.height - $(this).parents(".carousel-wrap").find('img[src*="arrow"]').height()) / 2);

        /* Vertically center potential span within the element */
        $(this).on("cycle-update-view", function() {
            /* Center text content - repeat  just in case*/
            $(".content-info").each(function() {
                $(this).find("span").first().css("margin-top", ($(this).height() - $(this).find("span").first().height()) / 2);
            });
        });
    }); 

    /* Apply settings and create the carousel */
    $(this).cycle({
        slides              :   '>' + settings.el,
        swipe               :   true,
        fx                  :   'carousel',
        carouselFluid       :   'true',
        carouselVisible     :   settings.visible,
        next                :   $(this).parents('.carousel-wrap').find('img[src*="arrow_next.png"]'),
        prev                :   $(this).parents('.carousel-wrap').find('img[src*="arrow_prev.png"]'),
        paused              :   false
    });
}



/* Function to show and resize promobox */
$.fn.promobox = function () {

    /* Set the magnificPopup */
    $(this).magnificPopup({
      type:'inline'
    });
    
    /* Open lightbox */
    $(this).trigger("click");
    
    /* Get original dimensions of the picture */
    var org_w = $("#promobox-image").attr("width") * 1;
    var org_h = $("#promobox-image").attr("height") * 1;
    
    /* If picture is bigger then available space, resize it */
    function ri() {
        var cur_w = $("#promobox-image").width();
        var cur_h = $("#promobox-image").height();
        
        var w_w = $(window).width();
        var w_h = $(window).height();
        
        if(w_h < org_h || w_w < org_w || cur_w < w_w || cur_h < w_h * 0.9) {
            var pos_h = (w_h - (parseFloat($("#promobox-in").css("padding-top")) * 4) - $("#promobox-text").height());
            
            $("#promobox-image").resizeImages({
                parent  : false,
                width   : w_w * 0.94 > org_w ? org_w : w_w * 0.94,
                height  : pos_h > org_h ? org_h : pos_h,
                margin  : false,
                abs     : false,
                fit     : true
            }); 
        }
    }     
    ri();
    
    /* Move close button inside promobox */
    $(".mfp-close").appendTo($("#promobox-in"));
    
    /* Add resize event to keep resizing the image */
    $(window).on("resize", function() { 
        ri();
    });
} 



/* 
    Function to defer loading of images, so that they are not downloaded with initial load 
    
    @param callback (function)  - optional callback, what to do after the image is fully loaded
*/
$.fn.loadImages = function(options) {
    var settings = $.extend({
        callback : null
    }, options);
    
    $(this).each(function() {
        var $el = $(this);
        
        /* Change CSS, make the image invisible but able to read its dimensions */
        $el.css("opacity", 0);
        
        /* Set parent with loading background */
        $el.parent().css("background", 'url("/_images/loading.gif") no-repeat center center');
        
        /* Change the source */
        $el.attr("src", $el.data("src"));
        
        /* Once the image is fully loaded */
        $el.on("load", function() {
        
            /* Callback if needed */
            if(!!settings.callback && typeof settings.callback === "function") {
                settings.callback.call(this);   
            } 
            
            /* Animate it */
            $(this).animate({
                opacity : 1
            });
        });
    });
    
    return this;
} 


/* Function to create "a" links from images that have lightbox class */
$.fn.lightboxCK = function() {
    
     this.each(function() {
    
        /* Local image element */
        var $img = $(this).find("img:first");
        
        /* Replace the image element with link and image inside it */
        $(this).replaceWith('<a href="' + $img.attr("src") + '" class="lightbox"><img src="' + $img.attr("src") + '" style="' + $img.attr("style") + '" alt="' + $img.attr("alt") + '" /></a>'); 
    });
}


/* Function to create lightbox with magnific popup for specific classes */
$.fn.lightbox = function() {
     
    this.magnificPopup({ 
        type        :   'image',
        removalDelay: 500, //delay removal by X to allow out-animation
        callbacks: {
            beforeOpen: function() {
              // just a hack that adds mfp-anim class to markup 
               this.st.image.markup = this.st.image.markup.replace('mfp-figure', 'mfp-figure mfp-with-anim');
               this.st.mainClass = this.st.el.attr('data-effect');
            }
        },
        gallery : {
            enabled : true
        },
        image: {
            titleSrc: function(item) { 
                return item.el.find("img").attr("alt") || item.el.prop("title")
            }
        },
        mainClass: 'mfp-fade'
    });
}


/* Cookie function */
$.fn.cookie = function() {
    
	$("#cookie-info-accept").click(function(e) {
        e.preventDefault();
		$.post("/ajax/cookie",function(data) {
			if(data == 1) {
				$("#cookie-info-wrap").stop().animate({height:0},600, function() {
					$(this).remove();
				});
			}
		});
	});
}

/* Function to resize video elements responsively, needs data-ratio attribute or width & height */
$.fn.videoResize = function() {
    this.each(function() {
    
        /* Local element */
        var $el = $(this);
        
        /* Get the ratio */
        var ratio = !!$el.data("ratio") ? $el.data("ratio") : ($el.width() / $el.height());
        
        /* Set the height */
        function rv() {
            $el.height($el.width() / ratio);
        }
        rv();
        
        /* Attach resize event */
        $(window).on("resize", function() {
            rv();
        });
    });
}



/* Asses if the browser supports certain CSS3 styles */
function asses_css3() { 
    var classes = [];
    for(var prop in document.documentElement.style) {
        if(!document.documentElement.style.hasOwnProperty(prop)) continue;         
        
        if(prop.indexOf("opacity") != -1)
            classes.push("css3-opacity")
        if(prop.indexOf("transform") != -1)
            classes.push("css3-transform")
        if(prop.indexOf("borderRadius") != -1)
            classes.push("css3-borderradius")
        if(prop.indexOf("textShadow") != -1)
            classes.push("css3-textshadow");
        if(prop.indexOf("boxShadow") != -1)
            classes.push("css3-boxshadow");
    }
    $("html").addClass(classes.join(" "));
}
asses_css3();