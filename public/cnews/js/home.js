jQuery(document).ready(function() {

	jQuery('#sliders').nivoSlider({
		effect : 'random',
		slices : 5,
		animSpeed : 500,
		pauseTime : 4000,
		startSlide : 0,
		directionNav : true,
		directionNavHide : true,
		controlNav : true,
		controlNavThumbs : true,
		pauseOnHover : true,
		loadprocess : true

	});

	jQuery('.nivo-control').each(function() {
		jQuery(this).append('<div class="tslide"></div>');
	});

	jQuery('.theme-default').hover(function(e) {
		jQuery('.nivo-caption  p').stop().fadeIn(500);
	}, function() {
		jQuery('.nivo-caption  p').stop().fadeOut(500);
	});

	jQuery(".nav-tabs li:first").addClass('active');
	jQuery(".tab-content .tab-pane:first").addClass('in active');

	jQuery('.counter-widget a.twitter > span').empty().html('14.5 M');											
	jQuery('.counter-widget a.facebook > span').html('14.2 K');
});