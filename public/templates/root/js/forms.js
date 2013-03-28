jQuery(".formcheck").each(addcheck);

function addcheck() {
	jQuery(this).attr('onchange',"checkField()");
}

function checkField(){
	jQuery(".conditional").each(function(){
		var value=jQuery(this).attr('data-value').split(";");
		var target=jQuery(this).attr('data-target');
		if(value.length>1)
			{
			var checked;
			jQuery("."+target).each(function(){
				if(jQuery(this).is(":checked"))
					{
					checked=jQuery.inArray(jQuery(this).val(),value)!="-1"?true:false;
					}
			})
			}
		else{
			checked=jQuery.inArray(jQuery("#"+target).val(),value)!="-1"?true:false;
		}
		if(checked==true)
			jQuery(this).parentsUntil(jQuery(".rubedo-form"),".control-group").removeClass("hide").addClass("show");
		else{
			jQuery(this).parentsUntil(jQuery(".rubedo-form"),".control-group").removeClass("show").addClass("hide");
		}
		
		
	});
}