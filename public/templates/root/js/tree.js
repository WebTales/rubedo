jQuery(document).ready(function() {
	if(jQuery("#siteMap").length > 0){
		var displayLevel = jQuery("#displayLevel").val();
		
		if(displayLevel > 0){
			var i = 0;
			var lists = "";
			
			for (i = 0 ; i < displayLevel-1 ; i++){
				lists = lists + " > ul";
			}
			
			jQuery("#siteMap"+ lists +" ul").hide();
			jQuery("#siteMap"+ lists +" .expend").html("<i class=\"icon-plus min\"></i>");
		}
	}
});

function editList(element, elementId) {
	if(jQuery(element).html() === "<i class=\"icon-minus min\"></i>"){
		jQuery(element).html("<i class=\"icon-plus min\"></i>");
	} else {
		jQuery(element).html("<i class=\"icon-minus min\"></i>");
	}
	
	jQuery("#"+elementId).toggle(250);
	return false;
}