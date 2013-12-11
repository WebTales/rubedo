function addEmail() {
	
	
	var email=jQuery("#mailingListEmail").val();
	var mailingLists= [ ];
	var canContinue=false;
	jQuery(".mailingListCheck").each(function(thing, thing2){
		if (jQuery(this).is(":checked")) {
			mailingLists.push(jQuery(this).attr("name"));
			canContinue=true;
		}
	});
	if (canContinue){
		
	
	var request = jQuery.ajax({
		url : "/blocks/"+jQuery('html').attr('lang')+"/mailing-list/xhr-add-email",
		type : "POST",
		data : {
			'mailing-list-id' : JSON.stringify(mailingLists),
			'email' : email,
			'current-page':jQuery('body').attr('data-current-page')
		},
		dataType : "json"
	});

	request.done(function(data) {
		if(data['success']) {
			jQuery("#mailinglist-message").html("<strong>"+data['msg']+"</strong>");
			jQuery("#mailingListAlert").removeClass("hidden");
			jQuery("#mailingListAlert").removeClass("alert-danger");
			jQuery("#mailingListAlert").addClass("alert-success");
		} else {
			jQuery("#mailinglist-message").html("<strong>"+data['msg']+"</strong>");
			jQuery("#mailingListAlert").removeClass("hidden");
			jQuery("#mailingListAlert").removeClass("alert-success");
			jQuery("#mailingListAlert").addClass("alert-danger");
		}
	});

	request.fail(function(jqXHR, textStatus, errorThrown) {
		try {
			var responseText = jQuery.parseJSON(jqXHR.responseText);
		} catch(err) {
			var responseText = jqXHR.responseText;
		}
		jQuery("#mailinglist-message").html("<strong>"+responseText['msg']+"</strong>");
		jQuery("#mailingListAlert").removeClass("hidden");
		jQuery("#mailingListAlert").removeClass("alert-success");
		jQuery("#mailingListAlert").addClass("alert-danger");
	});
	
	}
}