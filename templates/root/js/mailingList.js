function addEmail() {
	
	
	var email=jQuery("#mailingListEmail").val();
	var name=jQuery("#mailingListName").val();
	var mailingLists= [ ];
	var canContinue=false;
	jQuery(".mailingListCheck").each(function(){
		if (jQuery(this).is(":checked")) {
			mailingLists.push(jQuery(this).attr("name"));
			canContinue=true;
		}
	});
	if ((jQuery.isEmptyObject(email))||(jQuery.isEmptyObject(name))){
		canContinue=false;
	}
	var fields={ };
	jQuery("#malingListFields input").each(function(){
		fields[jQuery(this).attr("name")]=jQuery(this).val();
		if ((jQuery(this).is(":required"))&&(jQuery.isEmptyObject(jQuery(this).val()))){
			canContinue=false;
		}
	});
	
	if (canContinue){
		
	
		var request = jQuery.ajax({
			url : "/blocks/"+jQuery('html').attr('lang')+"/mailing-list/xhr-add-email",
			type : "POST",
			data : {
				'mailing-list-id' : JSON.stringify(mailingLists),
				'email' : email,
				'name': name,
				'fields':JSON.stringify(fields),
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
	
	} else {
		jQuery("#mailinglist-message").html("<strong>Veuillez renseigner tous les champs obligatoires</strong>");
		jQuery("#mailingListAlert").removeClass("hidden");
		jQuery("#mailingListAlert").removeClass("alert-success");
		jQuery("#mailingListAlert").addClass("alert-danger");
	}
}