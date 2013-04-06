function addEmail(email, mailingListId) {
	var request = jQuery.ajax({
		url : "/blocks/mailing-list/xhr-add-email",
		type : "POST",
		data : {
			'mailing-list-id' : mailingListId,
			'email' : email
		},
		dataType : "json"
	});

	request.done(function(data) {
		if(data['success']) {
			jQuery("#mailinglist-message").html("<strong>"+data['msg']+"</strong>");
			jQuery(".mailinglist-modal").css("display", "block");
			jQuery(".mailinglist-modal").css("background", "green");
		} else {
			jQuery("#mailinglist-message").html("<strong>"+data['msg']+"</strong>");
			jQuery(".mailinglist-modal").css("display", "block");
			jQuery(".mailinglist-modal").css("background", "red");
		}
	});

	request.fail(function(jqXHR, textStatus, errorThrown) {
		var responseText = jQuery.parseJSON(jqXHR.responseText);
		jQuery("#mailinglist-message").html("<strong>"+responseText['msg']+"</strong>");
		jQuery(".mailinglist-modal").css("display", "block");
		jQuery(".mailinglist-modal").css("background", "red");
	});
}