
var starEdit=true;
jQuery(".star-edit").click(function(){
	starEdit=(starEdit==true)?false:true;
});

jQuery(".star-edit").hover(function(){

	var self=this;
	jQuery(".star-edit").each(function(){
		//console.log(jQuery(this).attr("data-value")+"----------"+jQuery(self).attr("data-value"));
		if(parseInt(jQuery(this).attr("data-value"))>parseInt(jQuery(self).attr("data-value"))&& starEdit==true)
			{
				jQuery(this).removeClass("icon-star").addClass("icon-star-empty");
			}
		else if(parseInt(jQuery(this).attr("data-value"))<=parseInt(jQuery(self).attr("data-value"))&& starEdit==true)
			{
			jQuery(this).removeClass("icon-star-empty").addClass("icon-star");
			}
	})
});
