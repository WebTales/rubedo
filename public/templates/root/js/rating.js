
/*
jQuery(".star-edit").click(function(){
	starEdit=(starEdit==true)?false:true;
});

jQuery(".star-edit").hover(function(){
	var self=this;
	jQuery(".star-edit").each(function(){
		//console.log(jQuery(this).attr("data-value")+"_____"+jQuery(self).attr("data-value"));
		if(parseInt(jQuery(this).attr("data-value"))>=parseInt(jQuery(self).attr("data-value"))&& starEdit==true)
			{
				if(parseInt(jQuery(this).attr("data-value"))>=parseInt(jQuery(this).attr("data-min-value"))){
				jQuery(this).removeClass("icon-star").addClass("icon-star-empty");}
			}
		if(parseInt(jQuery(this).attr("data-value"))<=parseInt(jQuery(self).attr("data-value"))&&starEdit==true)
			{
				if(parseInt(jQuery(this).attr("data-value"))<=parseInt(jQuery(this).attr("data-max-value"))){
					jQuery(this).removeClass("icon-star-empty").addClass("icon-star");
					}
			}
	})
});*/

jQuery(".star-edit").hover(function(e){
	var self=this;
		var x = e.pageX - this.offsetLeft;
		var y = e.pageY - this.offsetTop;
	     var maxPerCent=jQuery("#test").width();
	     jQuery("#infos").width(x);
	     var rate=Math.round((x*100)/maxPerCent);
	     jQuery("#infos").html(rate+"%");
	     var max=parseInt(jQuery(this).attr("data-max-value"));
	     console.log(rate);
});
