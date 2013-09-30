function calendarChangeDate(date, prefix, query, url, singlePage,dateField) {
	if (jQuery('#calendar-' + prefix + ' > #calendar-' + date).length == 0) {
		if (jQuery('body').attr('data-is-draft')){
			var isDraft = true;
		}else{
			var isDraft = false;
		}
		var request = jQuery.ajax({
			url : url + '/blocks/'+jQuery('html').attr('lang')+'/calendar/xhr-get-calendar',
			type : "POST",
			data : {
				'cal-date' : date,
				'prefix' : prefix,
				'query-id' : query,
				'single-page' : singlePage,
				'date-field' : dateField,
				'is-draft':isDraft,
				'current-page':jQuery('body').attr('data-current-page')
			},
			dataType : "json"
		});

		request.done(function(data) {
			var calHtml = data.calendarHtml;
			jQuery('#calendar-' + prefix).append(calHtml);
			if (jQuery('#calendar-items-' + prefix).length > 0) {
				var newHtml = data.html;
				jQuery('#calendar-items-' + prefix).append(newHtml);
			}
		});

		request.fail(function(jqXHR, textStatus) {
		});
	}
	jQuery('#calendar-' + prefix + ' > .active').hide();
	jQuery('#calendar-' + prefix + ' > .active').removeClass('active');
	jQuery('#calendar-' + prefix + ' > #calendar-' + date).show();
	jQuery('#calendar-' + prefix + ' > #calendar-' + date).addClass('active');
	if (jQuery('#calendar-items-' + prefix).length == 0) {
		return false;
	}
	jQuery('#calendar-items-' + prefix + ' > .active').hide();
	jQuery('#calendar-items-' + prefix + ' > .active').removeClass('active');
	jQuery('#calendar-items-' + prefix + ' > #calendar-items-' + date).show();
	jQuery('#calendar-items-' + prefix + ' > #calendar-items-' + date).addClass(
			'active');
	return false;
}