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
if (jQuery("#accordioncheckout").attr("data-currentstep")>1){
    adaptToUserData(JSON.parse(jQuery("#accordioncheckout").attr("data-current-user")));
}

function checkoutSignup() {
    var canContinue=checkoutCheckFormValid("checkoutSignupForm");
    if (canContinue){
        var data=checkoutGetFormData(jQuery("#checkoutSignupForm"));
        if (!data.readTermsAndConds){
            jQuery("#chk2-message").html("<strong>Please accept the Terms and Conditions</strong>");
            jQuery("#chk2Alert").removeClass("hidden");
            jQuery("#chk2Alert").removeClass("alert-success");
            jQuery("#chk2Alert").addClass("alert-danger");
        } else if (data.password!=data.confirmPassword){
            jQuery("#chk2-message").html("<strong>Passwords don't match</strong>");
            jQuery("#chk2Alert").removeClass("hidden");
            jQuery("#chk2Alert").removeClass("alert-success");
            jQuery("#chk2Alert").addClass("alert-danger");
        } else {
            var request = jQuery.ajax({
                url : "/blocks/"+jQuery('html').attr('lang')+"/checkout/xhr-create-account",
                type : "POST",
                data : {
                    'data':JSON.stringify(data),
                    'current-page':jQuery('body').attr('data-current-page')
                },
                dataType : "json"
            });

            request.done(function(data) {
                if(data['success']) {
                    setCheckoutStep(1);
                } else {
                    jQuery("#chk2-message").html("<strong>"+data['msg']+"</strong>");
                    jQuery("#chk2Alert").removeClass("hidden");
                    jQuery("#chk2Alert").removeClass("alert-success");
                    jQuery("#chk2Alert").addClass("alert-danger");
                }
            });

            request.fail(function(jqXHR, textStatus, errorThrown) {
                try {
                    var responseText = jQuery.parseJSON(jqXHR.responseText);
                } catch(err) {
                    var responseText = jqXHR.responseText;
                }
                jQuery("#chk2-message").html("<strong>"+responseText['msg']+"</strong>");
                jQuery("#chk2Alert").removeClass("hidden");
                jQuery("#chk2Alert").removeClass("alert-success");
                jQuery("#chk2Alert").addClass("alert-danger");
            });
        }


    } else {
        jQuery("#chk2-message").html("<strong>Please fill in all required fields</strong>");
        jQuery("#chk2Alert").removeClass("hidden");
        jQuery("#chk2Alert").removeClass("alert-success");
        jQuery("#chk2Alert").addClass("alert-danger");
    }

    return false;
}

function checkoutGetFormData(form){
    var formData=form.serializeArray();
    var params={};
    formData.forEach(function(item){
        if (!jQuery.isEmptyObject(item.value)){
            params[item.name]=item.value;
        }
    });
    return(params);
}

function checkoutCheckFormValid(formId){
    var canContinue=true;
    jQuery("#"+formId+" input").each(function(){
        if ((jQuery(this).is(":required"))&&(jQuery.isEmptyObject(jQuery(this).val()))){
            canContinue=false;
        }
    });
    return canContinue;
}

function checkoutSetFormData(formId, data){
    jQuery("#"+formId+" input").each(function(){
        jQuery(this).val(data[jQuery(this).attr("name")]);
    });
}

function adaptToUserData(userData){
    var step2Data=userData;
    for (var attrname in step2Data.fields) {
        step2Data[attrname] = step2Data['fields'][attrname];
    }
    if (jQuery.isEmptyObject(step2Data).address){
        jQuery.isEmptyObject(step2Data).address={ };
    }
    for (var attrname in step2Data.address) {
        step2Data["address_"+attrname] = step2Data['address'][attrname];
    }
    checkoutSetFormData("checkoutEditUserForm",step2Data);
}