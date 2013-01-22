(function($) {
    $.fn.slideDownOnly = function() { 
    var self=$(this);  
	if(self.css("display")=="block")
	{
		self.slideUp("slow");
		
	}
	else
	{
		self.slideDown("slow");
	}
	$(".info").each(function(){
		if($(this).css("display")=="block" && $(this).attr("id")!=self.attr("id"))
		{
			$(this).slideUp("slow");
		}
	});
    };
})(jQuery);  