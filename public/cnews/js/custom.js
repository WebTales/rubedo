// JavaScript Document 

jQuery(document).ready(function($) { 
        $('#menu-top ul.menu').superfish({ 
            delay: 100,  
            animation:{height: 'show', opacity: 'show'},  
            speed:'normal',                         
            autoArrows:  false, 
            dropShadows: false,
			disableHI:true                         
        });
		
	});

jQuery(function(){
  
jQuery(".menu-csc-side-navigation-container .menu > li > a,li.cat-item > a,.widget_archive li > a").each(function(index, element) {
	   jQuery(this).append('<i class="icon-chevron-right"></i>');

  });

  
});
jQuery(document).ready(function($) {
$.noConflict();
jQuery.noConflict();


  // Menu Setting
  
  jQuery("ul.w-recentpost > li").each(function() {
    if (jQuery(this).has('iframe').length) {
      jQuery(this).find('div').hide();
    }
  });
  
  jQuery("ul.menu > li > ul > li > a").each(function(index, element) {
	   
	var desclink = jQuery(this).attr('title');
	jQuery(this).append('<em></em>');
	jQuery(this).find('em').text(desclink);
	

  });
  
  jQuery("ul.menu > li > ul > li > ul > li > a").each(function(index, element) {
	   
	var desclink = jQuery(this).attr('title');
	jQuery(this).append('<em></em>');
	jQuery(this).find('em').text(desclink);
	

  });


  // Topbar open / close & hover

  jQuery('.pagenavi').find('a,.current').addClass('button small');
  jQuery('.pagenavi').find('.current').addClass('button small blue');

  // Testimonials cycle 								

    $('#testimonials').cycle({
        fx: 'scrollLeft',
        speed: 500,
        timeout: 3500,
        pause: 1,
        cleartypeNoBg: true,
		next:'.next-l', 
	    prev:'.prev-l'
    });
  

  //Carousel images

  jQuery('#Carousel1,#Carousel').carousel({
    interval: 2500
  });

  //Media element player

  jQuery('audio,video').mediaelementplayer({
    audioWidth: '100%',
    audioHeight: '30px',
    videoWidth: '100%',
    videoHeight: '100%'
  });

  jQuery('.video-port').mediaelementplayer();

   
   
  //prettyPhoto

  jQuery("a[rel^='prettyPhoto']").prettyPhoto();


  //Toggle

  jQuery(".toggle-box").hide();
  jQuery(".open-block").toggle(function() {
    jQuery(this).addClass("active");
  },
  function() {
    jQuery(this).removeClass("active");
  });
  jQuery(".open-block").click(function() {
    jQuery(this).next(".toggle-box").slideToggle();
  });

  //Accordion

  jQuery('.accordion-box').hide();
  jQuery('.open-block-acc').click(function() {
    jQuery(".open-block-acc").removeClass("active");
    jQuery('.accordion-box').slideUp('normal');
    if (jQuery(this).next().is(':hidden') == true) {
      jQuery(this).next().slideDown('normal');
      jQuery(this).addClass("active");
    }
  });

  //Message box

  jQuery('.message-box').find('.closemsg').click(function() {
    jQuery(this).parent('.message-box').slideUp(500);
  });

 // Mobi Navigation

  jQuery("ul.menu-primary-navigation").find('li').hover(function() {
    jQuery(this).children("ul").stop(true, true).fadeIn(300);
  },
  function() {
    jQuery(this).children("ul").stop(true, true).fadeOut(200);
  });

  (function() {

    var $navResp = jQuery('nav').children('ul'),
    optionsList = '<option value="" selected>SITE MENU</option>';

    $navResp.find('li').each(function() {
      var $this = jQuery(this),
      $anchor = $this.children('a'),
      depth = $this.parents('ul').length - 1,
      indent = '';

      if (depth) {
        while (depth > 0) {
          indent += '--';
          depth--;
        }
      }

      optionsList += '<option value="' + $anchor.attr('href') + '">' + indent + ' ' + $anchor.text() + '</option>';
    }).end().after('<select class="menuselect">' + optionsList + '</select>');

    jQuery('.menuselect').on('change',
    function() {
      window.location = jQuery(this).find("option:selected").val();

    });

  })();


  jQuery('#submit').click(function() {
    jQuery('input.error-input, textarea.error-input').delay(300).animate({
      marginLeft: 0
    },
    100).animate({
      marginLeft: 10
    },
    100).animate({
      marginLeft: 0
    },
    100).animate({
      marginLeft: 10
    },
    100).animate({
      marginLeft: 0
    },
    100);
  });
  

	
	
	jQuery().UItoTop({ easingType: 'easeOutQuart' });
  

});

jQuery(document).ready(function($) {
jQuery.noConflict();

jQuery('.blog-masonry').masonry({
  itemSelector: 'article',
  columnWidth: 460,
  gutterWidth: 20,
  isAnimated: true
});



jQuery(function() {  
  // Isotope
	  
	  var $container_two = jQuery('ul.portfolio');
	  $container_two.imagesLoaded( function(){
		$container_two.isotope({
			itemSelector : '.item-block'
		});	
	});
      
	  // onclick reinitialise the isotope script
	jQuery('#filters a').click(function(){
		
		jQuery('#filters a').removeClass('selected');
		jQuery(this).addClass('selected');
		
		var selector = $(this).attr('data-option-value');
		$container_two.isotope({ filter: selector });
		
		return(false);
	});
	
	});
});


