function changePage(pageNumber, itemCount, itemsPerPage, maxPage,prefix,width,height,query,url,user,tags,tagMode) {
	if(jQuery('#'+prefix+' > #'+prefix+'-page'+pageNumber).length == 0){
		jQuery('#'+prefix+' > .progress-gallery').removeClass('hide');
		jQuery('#'+prefix+' > .active-items').hide();
	var request = jQuery.ajax({
		url : url,
		type : "POST",
		data : {
			'page' : pageNumber,
			'itemCount' : itemCount,
			'itemsPerPage' : itemsPerPage,
			'maxPage' : maxPage,
			'width': (width) ? width : null,
			'height': (height) ? height : null,
			'prefix':prefix,
			'query': (query) ? query : null,
			'user': (user) ? user : null,
			'tags': (tags) ? tags : null,
			'tagMode': (tagMode) ? tagMode : null
		},
		dataType : "json"
	});

	request.done(function(data) {
		jQuery('#'+prefix+' > .progress-gallery').addClass('hide');
		var newHtml = data.html;
		jQuery('#'+prefix).append(newHtml);
		jQuery('#'+prefix+' > .active-items').removeClass('active-items');
		jQuery('#'+prefix+' > #'+prefix+'-page'+pageNumber).show();
		jQuery('#'+prefix+' > #'+prefix+'-page'+pageNumber).addClass('active-items');
	});

	request.fail(function(jqXHR, textStatus) {
		jQuery('#'+prefix+' > .progress-gallery').addClass('hide');
		var errorHtml ='<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">&times;</button><h4>Erreur !</h4>Impossible de charger les images</div>';
		jQuery('#'+prefix).prepend(errorHtml);
		jQuery('#'+prefix+' > .active-items').show();
		console.log(jqXHR);
	});
	}else{
		jQuery('#'+prefix+' > .active-items').hide();
		jQuery('#'+prefix+' > .active-items').removeClass('active-items');
		jQuery('#'+prefix+' > #'+prefix+'-page'+pageNumber).show();
		jQuery('#'+prefix+' > #'+prefix+'-page'+pageNumber).addClass('active-items');
	}
	
	return false;
}
	
function callModal(src, title){
	jQuery('#myModal').modal();
	jQuery('#fullScreenPicture').attr('src', src);
	jQuery('#myModalLabel').html(title);
	return false;		
}