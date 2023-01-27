const home = !!$("body").hasClass("home");
$(document).ready(function() {
    // Contents table
    let count = $("h2.contents-table").length;
    if(count > 5) {
        let list = '';

        let i = 1;
        $("h2.contents-table").each(function() {
            let el = $(this);
            let id = "ct-" + i;

            el.prop("id", id);

            list += '<h3 class="ct-item" data-id="' + id + '">'+ i + '. '  + el.text() + '<h3>';
            i++;
        });

        let html = '<div id="contents-table"><div id="contents-table-title">Spis Treści</div><div id="ct-items">' + list + '</div></div>';
        $(html).insertBefore($("#page-body"));

        $(".ct-item").on("click", function() {
            $("body,html").animate({
                scrollTop: $("#" + $(this).data("id")).offset().top
            });
        });
    }

	$("#cookie-info-accept").click(function() {
		$.post("/ajax/cookie",function(data) {
			if(data == 1) {
				$("#cookie-info-wrap").animate({height:0},600, function() {
					$(this).remove();
				});
				$("body").animate({paddingTop:0},600);
			}
		});
	});

    $(".form-event").formEvents();

    $("#menu").subMenu();

     $("span.lightbox").each(function() {
        var s = $(this).find("img").attr("src");
        var s2 = $(this).find("img").attr("style");
        var a = $(this).find("img").attr("alt");

        $(this).replaceWith('<a href="'+s+'" class="lightbox"><img src="'+s+'" style="'+s2+'" alt="'+a+'" /></a>');
    });

    $('a.lightbox, a.gallery-image, .gallery-link').magnificPopup({
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
});

$(window).on("load", function() {
    $(".gallery-link").equalHeights();

    $("[data-src]").loadImages({
        callback : function() {$(this).resizeImages({parent:true,resize:true})}
    });

    $(".slide img, .g-linkimg img, .g-image img").resizeImages({parent:true});

    $("#slideshow").cycle({
        slides              :   '> div',
        swipe               :   true,
        fx                  :   'fade',
        paused              :   false
    });

    $(".slide img").css({opacity:1});/* Make sure first slide's text is also centered */
    $(".slide-text-in:first").css("margin-top", (($("#slideshow").height() * 0.7) - $(".slide-text-in:first").height()) / 2);
    $(".slide-text:first").css({
        display: "block",
        opacity: 1
    });

    $("#slideshow").on("cycle-before", function(event, options, out, inc) {
        $(".slide-text", out).fadeOut();
        $(".slide-text", inc).css({
            display : "block",
            opacity : 0
        });
        $(".slide-text-in", inc).css("margin-top", (($("#slideshow").height() * 0.7) - $(".slide-text-in", inc).height()) / 2);
        $(".slide-text", inc).css({
            display : "none",
            opacity : 1
        });
    });
    $("#slideshow").on("cycle-after", function(event, options, out, inc) {
        $(".slide-text", inc).fadeIn();
    });
});

$(window).resize(function() {
    $(".slide img, .g-linkimg img, .g-image img").resizeImages({parent:true});
});

$(window).scroll(function() {

});