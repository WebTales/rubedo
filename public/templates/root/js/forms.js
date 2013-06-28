jQuery(".mother").each(addcheck);


jQuery(".dateField").each(switchToDateField);

function switchToDateField(){
	if(jQuery(this).prop('type')=='text'){
		jQuery(this).datepicker({ dateFormat: "yy-mm-dd"});
	}	
}

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
				//foreach parent fields if is checked and value is in array of values display children field
				if(jQuery(this).attr('type')=="checkbox"||jQuery(this).attr('type')=="radio")
				{
					if(jQuery(this).prop('checked'))
						{
					
							if(jQuery.inArray(jQuery(this).val(),value)!="-1"){
								jQuery(self).parentsUntil(jQuery(".rubedo-form"),".control-group").removeClass("hide").addClass("show");
							}else{
								if(jQuery.inArray(jQuery(this).val(),value)!="-1"){
								jQuery(self).parentsUntil(jQuery(".rubedo-form"),".control-group").removeClass("show").addClass("hide");
								//if child is also a mother (others conditionals depends of his value) set clear to true when his mother is hidden
								if(jQuery(self).hasClass("mother"))
								shoudClear = true;}
							}
						}
					if(!jQuery(this).prop('checked'))
						{
						//if field is not checked look if the child depends on several values and look if one of this values is checked 
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
				//if this shoudClear (is mother of other conditional) 
				if(jQuery(this).attr('type')=="checkbox"||jQuery(this).attr('type')=="radio")
				{
					//remove attribute checked for each field 
				jQuery(this).each(function(){
					jQuery(this).removeAttr("checked");
				});
				}
				else{
					//set value to null
					jQuery(this).val(null);
				}
			}
			
	});
}

jQuery(".lineExlusiveRadio").click( function () {
	if ((jQuery(this).is(":checked"))){
		jQuery(this).parent().parent().find("input").prop("checked",false);
		jQuery(this).prop("checked",true);
	}
	
});