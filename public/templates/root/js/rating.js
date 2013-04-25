
jQuery(".star-edit").click(function(){
	if(starEdit==true)
		{
	if(jQuery(this).parent().attr("data-edit")=="true"){
		    jQuery(this).parent().attr("data-edit",false);
		}else{
			jQuery(this).parent().attr("data-edit",true);	
		}}

	var rateValue=null;
	var mother=jQuery(this).parent();
	var children=jQuery(mother[0]).children();
	jQuery(children).each(function(){
		rateValue+=parseFloat(jQuery(this).attr("data-value"));
	});
	jQuery(".star-edit").parent().attr("data-rate",rateValue);
});


jQuery(".star-edit").mousemove(function(e){
	var mother=jQuery(this).parent();
	var children=jQuery(mother[0]).children();
		var x = e.pageX - this.offsetLeft;
		var y = e.pageY - this.offsetTop;
		/*
		 * if cursor position on the star is < to the middle add half-star class
		 */
	if(jQuery(mother).attr("data-edit")=="true"){
			var current=this; 
		if((parseInt(jQuery(current).attr("data-index"))-1)+parseFloat(jQuery(current).attr("data-value"))>(parseFloat(jQuery(current).attr("data-min-value"))) &&(parseInt(jQuery(current).attr("data-index"))-1)+parseFloat(jQuery(current).attr("data-value"))<parseFloat(jQuery(current).attr("data-max-value"))){
		if(x<7){
			if(x<3){
				jQuery(this).attr("data-value","0");
				jQuery(this).addClass("empty-star").removeClass("full-star").removeClass("half-star");
			}else{
				jQuery(this).attr("data-value","0.5");
		    	 jQuery(this).addClass("half-star").removeClass("full-star").removeClass("empty-star");
			}
		    		jQuery(children).each(function(){
		    			/*
		    			 * check others stars and add them empty-star or full-star class
		    			 */
		    			if(parseInt(jQuery(this).attr("data-index"))>parseInt(jQuery(current).attr("data-index")))
		    				{
		    				jQuery(this).attr("data-value","0");
		    				jQuery(this).addClass("empty-star").removeClass("full-star").removeClass("half-star");
		    				 
		    				}
		    			else if(parseInt(jQuery(this).attr("data-index"))<parseInt(jQuery(current).attr("data-index"))){
		    				jQuery(this).attr("data-value","1");
		    				jQuery(this).addClass("full-star").removeClass("empty-star").removeClass("half-star");

		    				}
		    		});
		    }
		     /*
		      * else add full-star class
		      */
		     else if (x>=7 &&(parseFloat(jQuery(current).attr("data-index"))-1)+0.5<parseFloat(jQuery(current).attr("data-max-value"))){
		    	 jQuery(current).attr("data-value","1");
		    	 jQuery(current).addClass("full-star").removeClass("half-star").removeClass("empty-star");
		    }
		}
	}
});


