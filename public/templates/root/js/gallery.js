function changePage(pageNumber, itemCount, itemsPerPage, maxPage,galleryid,width,height,query,url) {
	if(jQuery('#'+galleryid+' > #page'+pageNumber).length == 0){
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
			'galleryid':galleryid,
			'query':query
		},
		dataType : "json"
	});

	request.done(function(data) {
		var newHtml = data.html;
		jQuery('#'+galleryid).append(newHtml);
	});

	request.fail(function(jqXHR, textStatus) {
	});
	}
	jQuery('#'+galleryid+' > .active').hide();
	jQuery('#'+galleryid+' > .active').removeClass('active');
	jQuery('#'+galleryid+' > #page'+pageNumber).show();
	jQuery('#'+galleryid+' > #page'+pageNumber).addClass('active');
	return false;
}
	
function callModal(src, title){
	jQuery('#myModal').modal();
	jQuery('#fullScreenPicture').attr('src', src);
	jQuery('#myModalLabel').html(title);
	return false;		
}