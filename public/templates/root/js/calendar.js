function calendarChangeDate(date, prefix, query, url, singlePage) {
	if (jQuery('#calendar-' + prefix + ' > #calendar-' + date).length == 0) {
		var request = jQuery.ajax({
			url : url + '/blocks/calendar/xhr-get-calendar',
			type : "POST",
			data : {
				'cal-date' : date,
				'prefix' : prefix,
				'query-id' : query,
				'single-page' : singlePage
			},
			dataType : "json"
		});

		request.done(function(data) {
			var newHtml = data.html;
			jQuery('#calendar-' + prefix).append(newHtml);
		});

		request.fail(function(jqXHR, textStatus) {
		});
	}
	jQuery('#calendar-' + prefix + ' > .active').hide();
	jQuery('#calendar-' + prefix + ' > .active').removeClass('active');
	jQuery('#calendar-' + prefix + ' > #calendar-' + date).show();
	jQuery('#calendar-' + prefix + ' > #calendar-' + date).addClass('active');
	calendarChangeDateForList(date, prefix, query, url, singlePage);
	return false;
}

function calendarChangeDateForList(date, prefix, query, url, singlePage) {
	if (jQuery('#calendar-items-' + prefix + ' > #calendar-' + date).length == 0) {
		var request = jQuery.ajax({
			url : url + '/blocks/calendar/xhr-get-items',
			type : "POST",
			data : {
				'cal-date' : date,
				'prefix' : prefix,
				'query-id' : query,
				'single-page' : singlePage
			},
			dataType : "json"
		});

		request.done(function(data) {
			var newHtml = data.html;
			jQuery('#calendar-items-' + prefix).append(newHtml);
		});

		request.fail(function(jqXHR, textStatus) {
		});
	}
	jQuery('#calendar-items-' + prefix + ' > .active').hide();
	jQuery('#calendar-items-' + prefix + ' > .active').removeClass('active');
	jQuery('#calendar-items-' + prefix + ' > #calendar-' + date).show();
	jQuery('#calendar-items-' + prefix + ' > #calendar-' + date).addClass(
			'active');
}