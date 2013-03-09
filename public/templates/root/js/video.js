jQuery(".videoPlaceholder").each(loadVideo);

function loadVideo(index){
	var id =jQuery(this).attr('id');
	var dataFile =jQuery(this).attr('data-videoFile');
	var config = new Object();
	config.file= dataFile;
	if(jQuery(this).attr('data-image')){
		config.image=jQuery(this).attr('data-image');
	}
	if(jQuery(this).attr('data-width')){
		config.width=jQuery(this).attr('data-width');
	}
	if(jQuery(this).attr('data-height')){
		config.height=jQuery(this).attr('data-height');
	}
	jwplayer(id).setup(config);
}