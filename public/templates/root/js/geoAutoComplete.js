$(function(){
	$('.typeahead').typeahead({
	    source: function (query, process) {
			var request = jQuery.ajax({
				url : '/blocks/geosearch/xhr-get-suggests',
				type : "POST",
				data : {
					'query': query,
					'searchParams': jQuery('#searchpage').attr('data-searchparams')
				},
				dataType : "json"
			});
			request.done(function(data) {
				return process(data.terms);
			});
			
	    },
	    matcher: function() {
	    	return true;
	    },
	    items:10,
	    minLength:3
	})
});
