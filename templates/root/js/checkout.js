function checkoutLoggin()
{
		jQuery.ajax({
		   type: "POST", 
		   url: "/xhr-authentication/login",
		   async:true,
		   dataType: "json",
		   data: { login: jQuery('#checkoutlogin').val(), password: jQuery('#checkoutpassword').val() },
		   success: function(msg){
		   if(msg.success==false){
               jQuery("#checkout-login-error-msg").show();
               jQuery("#checkout-login-error-msg").html(msg.msg);
		   }else{
                jQuery('#checkoutpassword').val("");
                jQuery('#checkoutlogin').val("");
                window.location.reload();
           }
		   }
		});
		
		return false;
}
function checkoutLoggout()
{	 
		jQuery.ajax({
		   type: "POST",
		   async:false, 
		   url: "/xhr-authentication/logout",
		   success: function(msg){
               window.location.reload();
           }
		});
}

function setCheckoutStep(step){
    jQuery(".checkout-holder").collapse('hide');
    jQuery("#checkoutstep"+step).collapse('show');
    jQuery("#checkoutmainprogress").css("width",step*17+"%");
    jQuery(".checkouteditlink").each(function(){
        if (jQuery(this).attr("data-targetstep")<step){
            jQuery(this).show();
        }
        else {
            jQuery(this).hide();
        }
    });
}

jQuery(".checkout-holder").collapse({toggle:false});
setCheckoutStep(jQuery("#accordioncheckout").attr("data-currentstep"));