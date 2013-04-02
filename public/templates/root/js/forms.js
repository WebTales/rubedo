jQuery(".mother").each(addcheck);

function addcheck() {
	jQuery(this).attr('onchange',"checkField()");
}

function checkField(){
	jQuery(".child").each(function(){
		var self=this;
		var value=jQuery(this).attr('data-value').split(";");
		var target=jQuery(this).attr('data-target');
		var shoudClear = false;
		//check children
		var multi=value.length>1?true:false;
			var checked;
			jQuery("."+target).each(function(){
				if(jQuery(this).attr('type')=="checkbox"||jQuery(this).attr('type')=="radio")
				{
					if(jQuery(this).prop('checked'))
						{
						//foreach parent fields if is checked and value is in array of values display children field
							if(jQuery.inArray(jQuery(this).val(),value)!="-1"){
								jQuery(self).parentsUntil(jQuery(".rubedo-form"),".control-group").removeClass("hide").addClass("show");
							}else{
								if(jQuery.inArray(jQuery(this).val(),value)!="-1"){
								jQuery(self).parentsUntil(jQuery(".rubedo-form"),".control-group").removeClass("show").addClass("hide");
								if(jQuery(self).hasClass("mother"))
								shoudClear = true;}
							}
						}
					if(!jQuery(this).prop('checked'))
						{
							if(multi==true)
								{
								var notAlone=false;
										jQuery("."+target).each(function(){
											if(jQuery(this).attr('type')=="checkbox"||jQuery(this).attr('type')=="radio")
											{
												if(jQuery(this).prop('checked'))
													{
													notAlone=true;
													}
											}
										});
								if(notAlone==false)
									{
									if(jQuery.inArray(jQuery(this).val(),value)!="-1"){
									jQuery(self).parentsUntil(jQuery(".rubedo-form"),".control-group").removeClass("show").addClass("hide");
									if(jQuery(self).hasClass("mother"))
									shoudClear = true;}
									}	
								}else{
									if(jQuery.inArray(jQuery(this).val(),value)!="-1"){
									jQuery(self).parentsUntil(jQuery(".rubedo-form"),".control-group").removeClass("show").addClass("hide");
									if(jQuery(self).hasClass("mother"))
									shoudClear = true;}
								}
						}
				}else{
					if(jQuery.inArray(jQuery(this).val(),value)!="-1"){
						jQuery(self).parentsUntil(jQuery(".rubedo-form"),".control-group").removeClass("hide").addClass("show");
					}else{
						jQuery(self).parentsUntil(jQuery(".rubedo-form"),".control-group").removeClass("show").addClass("hide");
						shoudClear = true;
					}
				}
			});
			if(shoudClear){
				if(jQuery(this).attr('type')=="checkbox"||jQuery(this).attr('type')=="radio")
				{
				jQuery(this).each(function(){
					jQuery(this).removeAttr("checked");
				});
				}
				else{
					jQuery(this).val(null);
				}
			}
			
	});
}