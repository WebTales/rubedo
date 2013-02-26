function changePage(pageNumber, itemCount, itemsPerPage, maxPage,galleryid,width,height,query,url) {
		if(jQuery('#'+galleryid+' > .carousel-inner > #page'+pageNumber).length == 0){
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
			jQuery('#'+galleryid+' > .carousel-inner').append(newHtml);
		});

		request.fail(function(jqXHR, textStatus) {
		});
		}
		jQuery('#'+galleryid+' > .carousel-inner > .active').hide();
		jQuery('#'+galleryid+' > .carousel-inner > .active').removeClass('active');
		jQuery('#'+galleryid+' > .carousel-inner > #page'+pageNumber).show();
		jQuery('#'+galleryid+' > .carousel-inner > #page'+pageNumber).addClass('active');
		return false;
	}