jQuery(".formcheck").each(addcheck);

function addcheck() {
	jQuery(this).attr('onchange',"checkField()");
}

function checkField(obj){
	jQuery(".formcheck").each(addcheck);
	console.log(obj.value);
	var target=jQuery(obj).attr('data-target');
	var value=jQuery(obj).attr('data-value');
	if(obj.value==value)
		{
		jQuery("#"+target).parentsUntil(jQuery(".control-group"),".control-group").removeClass("hide").addClass("show");
		}else
			{
			if(obj.value!=value)
			{
			jQuery("#"+target).parentsUntil(jQuery(".control-group"),".control-group").removeClass("show").addClass("hide");
			}
			}
}