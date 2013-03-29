jQuery(".formcheck").each(addcheck);

function addcheck() {
	jQuery(this).attr('onchange',"checkField()");
}

function checkField(){
	jQuery(".conditional").each(function(){
		var self=this;
		var value=jQuery(this).attr('data-value').split(";");
		var target=jQuery(this).attr('data-target');
		console.log(value.length);
		//check children
		if(value.length>=1)
			{
			var checked;
			jQuery("."+target).each(function(){
				console.log(jQuery(this).prop('checked'));
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
				
			});
			}
		else{
			checked=jQuery.inArray(jQuery("#"+target).val(),value)!="-1"?true:false;
			if(checked==true)
				jQuery(this).parentsUntil(jQuery(".rubedo-form"),".control-group").removeClass("hide").addClass("show");
			else{
				jQuery(this).parentsUntil(jQuery(".rubedo-form"),".control-group").removeClass("show").addClass("hide");
			}
		}
	});
}