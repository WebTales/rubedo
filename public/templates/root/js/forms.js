jQuery(".formcheck").each(addcheck);

function addcheck() {
	jQuery(this).attr('onchange',"checkField()");
}

function checkField(){
	jQuery(".conditional").each(function(){
		var self=this;
		var value=jQuery(this).attr('data-value').split(";");
		var target=jQuery(this).attr('data-target');
		//check children
			var checked;
			jQuery("."+target).each(function(){
				if(jQuery(this).attr('type')=="checkbox"||jQuery(this).attr('type')=="radio")
				{
					if(jQuery(this).prop('checked'))
						{
						//foreach parent fields if is checked and value is in array of values display children field
						if(jQuery.inArray(jQuery(this).val(),value)!="-1"){
						jQuery(self).parentsUntil(jQuery(".rubedo-form"),".control-group").removeClass("hide").addClass("show");}
						}
					if(!jQuery(this).prop('checked'))
						{
						if(jQuery.inArray(jQuery(this).val(),value)!="-1")
							jQuery(self).parentsUntil(jQuery(".rubedo-form"),".control-group").removeClass("show").addClass("hide");
						}
				}else{
					if(jQuery.inArray(jQuery(this).val(),value)!="-1"){
						jQuery(self).parentsUntil(jQuery(".rubedo-form"),".control-group").removeClass("hide").addClass("show");
					}else{
						jQuery(self).parentsUntil(jQuery(".rubedo-form"),".control-group").removeClass("show").addClass("hide");
					}
				}
			});
	});
}