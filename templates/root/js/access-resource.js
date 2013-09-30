function submitEmail(prefix) {
	var request = jQuery.ajax({
		url : "/blocks/"+jQuery('html').attr('lang')+"/protected-resource/xhr-submit-email",
		type : "POST",
		data : {
			'mailing-list-id' : jQuery('#mailingListId-'+prefix).val(),
			'email' : jQuery('#email-'+prefix).val(),
			'dam-id' : jQuery('#damId-'+prefix).val(),
			'site-id' : jQuery("body").attr('data-site-id'),
			'current-page':jQuery('body').attr('data-current-page')
		},
		dataType : "json"
	});

	request.done(function(data) {
		if(data['success']) {
			jQuery("#message-"+prefix).html("<strong>"+data['msg']+"</strong>");
		} else {
			jQuery("#message-"+prefix).html("<strong>"+data['msg']+"</strong>");
		}
	});

	request.fail(function(jqXHR, textStatus, errorThrown) {
		var responseText = jQuery.parseJSON(jqXHR.responseText);
		jQuery("#message-"+prefix).html("<strong>"+responseText['msg']+"</strong>");
	});
	
	return false;
}

function toggleDownloadForm(prefix){
	var currentDiv = jQuery('#download-form-'+prefix);
	if(currentDiv.hasClass('hide')){
		jQuery('#download-form-'+prefix).removeClass('hide');
	}else{
		jQuery('#download-form-'+prefix).addClass('hide');
	}
	
}