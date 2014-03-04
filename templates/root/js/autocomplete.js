$(function(){
	var searchItems = {};
    var searchLabels = [];
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
				
				searchItems = {};
	            searchLabels = [];

	            $.each(data.terms, function(index, value) {
	            	searchLabels.push( value.text );
	            	searchItems[ value.text ] = value.payload;
	            });

				return process(searchLabels);
			});
			
	    },
	    matcher: function(selectedName) {
	    	
	    	return selectedName;
	    },
	    highlighter: function( item ){
	    	var suggest = searchItems[ item ];
	        switch (suggest.type) {
	        case 'content':
	        	result = '<h4 class="media-heading">' + item + '</h4>';
	        	break;
	        case 'dam':
	        case 'user':
	        	result = '<div class="media">'
	                +'<a class="pull-left" href="' + suggest.id + '" />'
	                +'<img class="media-object" src="' + suggest.thumbnail + '" width="40px" />'
	                +'</a>'
	                +'<div class="media-body">'
	                +'<h4 class="media-heading">' + item + '</h4>'
	                +'</div>'
	                +'</div>';
	        	break;
	        }
	        return result;
	    },
	    items:10,
	    minLength:3
	})
});
