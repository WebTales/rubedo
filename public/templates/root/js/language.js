function chooseLanguage(locale) {
	jQuery.ajax({
		type : "GET",
		url : "/xhr-language/define-language",
		async : true,
		dataType : "json",
		data : {
			"current-page" : jQuery('body').attr('data-current-page'),
			locale : locale
		},
		success : function(msg) {
			if (msg.success == false) {
				jQuery("#error-msg").show();
				jQuery("#error-msg").html(msg.message);
			} else {
				window.location.reload();
			}
		}
	});
}