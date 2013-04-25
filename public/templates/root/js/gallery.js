function changePage(pageNumber, itemCount, itemsPerPage, maxPage, prefix,
		width, height, query, url, user, tags, tagMode) {
	if (jQuery('#' + prefix + ' > #' + prefix + '-page' + pageNumber).length == 0) {
		if (jQuery('body').attr('data-is-draft')) {
			var isDraft = true;
		} else {
			var isDraft = false;
		}
		jQuery('#' + prefix + ' > .progress-gallery').removeClass('hide');
		jQuery('#' + prefix + ' > .active-items').hide();
		var request = jQuery.ajax({
			url : url,
			type : "POST",
			data : {
				'page' : pageNumber,
				'itemCount' : itemCount,
				'itemsPerPage' : itemsPerPage,
				'maxPage' : maxPage,
				'width' : (width) ? width : null,
				'height' : (height) ? height : null,
				'prefix' : prefix,
				'query' : (query) ? query : null,
				'user' : (user) ? user : null,
				'tags' : (tags) ? tags : null,
				'tagMode' : (tagMode) ? tagMode : null,
				'is-draft' : isDraft,
				'current-page' : jQuery('body').attr('data-current-page')
			},
			dataType : "json"
		});

		request.done(function(data) {
			jQuery('#' + prefix + ' > .progress-gallery').addClass('hide');
			var newHtml = data.html;
			jQuery('#' + prefix).append(newHtml);
				jQuery('#' + prefix + ' > .active-items').removeClass(
				'active-items');
				jQuery('#' + prefix + ' > #' + prefix + '-page' + pageNumber)
				.show();
		jQuery('#' + prefix + ' > #' + prefix + '-page' + pageNumber)
				.addClass('active-items');	
		centerAll();
		});

		request
				.fail(function(jqXHR, textStatus) {
					jQuery('#' + prefix + ' > .progress-gallery').addClass(
							'hide');
					var errorHtml = '<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">&times;</button><h4>Erreur !</h4>Impossible de charger les images</div>';
					jQuery('#' + prefix).prepend(errorHtml);
					jQuery('#' + prefix + ' > .active-items').show();
					console.log(jqXHR);
				});
	} else {
		jQuery('#' + prefix + ' > .active-items').hide();
		jQuery('#' + prefix + ' > .active-items').removeClass('active-items');
		jQuery('#' + prefix + ' > #' + prefix + '-page' + pageNumber).show();
		jQuery('#' + prefix + ' > #' + prefix + '-page' + pageNumber).addClass(
				'active-items');
	}

	return false;
}

function callModal(src, title) {
	jQuery('#myModal').imageCenter(src,title);
	return false;
}
/**
 * Center gallery images and gallery pager
 * 
 */
function centerAll()
{
	nbItems=parseInt(jQuery(".active-items").attr("data-items"));
	if(nbItems==1)
		{
		nbItems++;
		}
	var galleryWidth=jQuery(".active-items").width();
	var width=jQuery(".active-items img").css("max-width").split("px");
	var img=parseInt(width[0]);
	var r=parseInt(galleryWidth)-(parseInt(img)*nbItems);
	var m=(r/nbItems)-(10*nbItems);
	if(m>20)
		{
	jQuery(".active-items .thumbnail").css({
		"margin-right":m+"px"
	});
		}
	var pagerWidth=jQuery(".active-items .pagination ul").width();
	jQuery(".active-items .pagination ul").css({
		"margin-left":+(galleryWidth/2)-pagerWidth+"px"
	});
	
	}
$(document).ready(function(){
centerAll();
});
/**
 * Require bootstrap modal
 * load wait img load and set calculate margin to center modal
 */
(function($)
		{
		    $.fn.imageCenter=function(src,title)
		    {
		      var self=this;
		      var id=jQuery(self).attr("id");
		      /**
		       * set image src and modal header text
		       */
		      jQuery(self).css({
		    	  "max-height":window.innerHeight*(80/100)+"px",
		      });
		      jQuery("#"+id+" .modal-body").css({
		    	  "overflow":"hidden"
		      });
		      //jQuery("#"+id+" .modal-body").height(jQuery(self).height()*(90/100));
		      jQuery('#fullScreenPicture').attr('src', src);
		  	  jQuery('#myModalLabel').html(title);
		  	jQuery("#"+id+" .modal-footer").hide();
		  	  /**
		  	   * Modal center after loading image
		  	   */
		      if(jQuery("#"+id+" .modal-body").find("img").length===1)
		       	{
		    	  jQuery("#"+id+" .modal-body img").load(function(){
		    		  jQuery(self).css({
				       		"margin-left":"-"+jQuery(self).width()/2+"px"
				       	});
		    		    jQuery("#"+id+" .modal-body img").css({
					    	   "max-height":(parseFloat(jQuery(self).css("max-height"))*(90/100))+"px"
					       });
		       	  });
		    	  }
		        jQuery(self).modal();
		    };
		})(jQuery);
