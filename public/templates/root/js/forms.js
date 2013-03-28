jQuery(".formcheck").each(addcheck);

function addcheck() {
	jQuery(this).attr('onchange',"checkField()");
}

function checkField(){
	self=this;
	jQuery(".conditional").each(function(){
		var target=jQuery(this).attr('data-target');
		var value=jQuery(this).attr('data-value');
console.log(self);
		if(self.value==value)
			jQuery(this).parentsUntil(jQuery(".rubedo-form"),".control-group").removeClass("hide").addClass("show");
		else{
			jQuery(this).parentsUntil(jQuery(".rubedo-form"),".control-group").removeClass("show").addClass("hide");
		}
		
		
	});
	/*jQuery(".formcheck").each(addcheck);
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
			}*/
}