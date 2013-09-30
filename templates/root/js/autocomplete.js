$(function(){
	$('.typeahead').typeahead({
	    source: function (query, process) {
			var request = jQuery.ajax({
				url : '/blocks/'+jQuery('html').attr('lang')+'/search/xhr-get-suggests',
				type : "POST",
				data : {
					'query' : query,
					'current-page':jQuery('body').attr('data-current-page'),
					'searchParams':jQuery('#searchpage').attr('data-searchparams')
				},
				dataType : "json"
			});
			request.done(function(data) {
				return process(data.terms);
			});
			
	    },
	    matcher: function(item) {
	    	var strlength = this.query.length;
	    	return this.query.toLowerCase() == item.toLowerCase().substring(0,strlength);
	    },
	    items:10,
	    minLength:3
	})
});
