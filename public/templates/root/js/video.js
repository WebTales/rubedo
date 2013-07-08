jQuery(".videoPlaceholder").each(loadVideo);
jQuery(".audioPlaceholder").each(loadAudio);

function loadVideo() {
	var id = jQuery(this).attr('id');
	var dataFile = jQuery(this).attr('data-videoFile');
	var config = new Object();
	config.file = dataFile;
	
	console.log(dataFile);
	
	
	if (jQuery(this).attr('data-image')) {
		config.image = jQuery(this).attr('data-image');
	}
	if (jQuery(this).attr('data-width')) {
		config.width = jQuery(this).attr('data-width');
	}
	if (jQuery(this).attr('data-height')) {
		config.height = jQuery(this).attr('data-height');
	}
	if (jQuery(this).attr('data-controls')) {
		config.controls = jQuery(this).attr('data-controls');
	} else {
		config.controls = false;
	}
	if (jQuery(this).attr('data-repeat')) {
		config.repeat = jQuery(this).attr('data-repeat');
	}
	if (jQuery(this).attr('data-autostart')) {
		config.autostart = jQuery(this).attr('data-autostart');
	}
	config.analytics = {'enabled':false,'cookies': false};
	jwplayer(id).setup(config);
}

function loadAudio() {
	var id = jQuery(this).attr('id');
	var dataFile = jQuery(this).attr('data-audioFile');
	var config = new Object();
	config.file = dataFile;
	config.primary = 'flash';
	config.height = '40';
	
	
	console.log(dataFile);
	

	if (jQuery(this).attr('data-controls')) {
		config.controls = jQuery(this).attr('data-controls');
	} else {
		config.controls = false;
	}
	if (jQuery(this).attr('data-width')) {
		config.width = jQuery(this).attr('data-width');
	}
	if (jQuery(this).attr('data-repeat')) {
		config.repeat = jQuery(this).attr('data-repeat');
	}
	if (jQuery(this).attr('data-autostart')) {
		config.autostart = jQuery(this).attr('data-autostart');
	}
	//return false;
	jwplayer(id).setup(config);
}
