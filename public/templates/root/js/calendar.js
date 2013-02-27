function calendarChangeDate(date,prefix,query,url) {
		if(jQuery('#calendar-'+prefix+' > #calendar-'+date).length == 0){
		var request = jQuery.ajax({
			url : url,
			type : "POST",
			data : {
				'cal-date' : date,
				'prefix':prefix,
				'query-id':query
			},
			dataType : "json"
		});

		request.done(function(data) {
			var newHtml = data.html;
			jQuery('#calendar-'+prefix).append(newHtml);
		});

		request.fail(function(jqXHR, textStatus) {
		});
		}
		jQuery('#calendar-'+prefix+' > .active').hide();
		jQuery('#calendar-'+prefix+' > .active').removeClass('active');
		jQuery('#calendar-'+prefix+' > #calendar-'+date).show();
		jQuery('#calendar-'+prefix+' > #calendar-'+date).addClass('active');
		return false;
	}