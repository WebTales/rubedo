function changePage(pageNumber, itemCount, itemsPerPage, maxPage,prefix,width,height,query,url) {
	if(jQuery('#'+prefix+' > #page'+pageNumber).length == 0){
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
			'query':query
		},
		dataType : "json"
	});

	request.done(function(data) {
		var newHtml = data.html;
		jQuery('#'+prefix).append(newHtml);
	});

	request.fail(function(jqXHR, textStatus) {
	});
	}
	jQuery('#'+prefix+' > .active').hide();
	jQuery('#'+prefix+' > .active').removeClass('active');
	jQuery('#'+prefix+' > #page'+pageNumber).show();
	jQuery('#'+prefix+' > #page'+pageNumber).addClass('active');
	return false;
}
	
function callModal(src, title){
	jQuery('#myModal').modal();
	jQuery('#fullScreenPicture').attr('src', src);
	jQuery('#myModalLabel').html(title);
	return false;		
}