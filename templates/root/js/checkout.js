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
    if (step==6){
        getCheckoutSummary();
    }
}

jQuery(".checkout-holder").collapse({toggle:false});
if (jQuery("#accordioncheckout").attr("data-currentstep")>1){
    setCheckoutStep(adaptToUserData(JSON.parse(jQuery("#accordioncheckout").attr("data-current-user"))));
} else {
    setCheckoutStep(jQuery("#accordioncheckout").attr("data-currentstep"));
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
                    window.location.reload();
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

function checkoutUpdateBillingAddress (){
    var canContinue=checkoutCheckFormValid("checkoutBillingAddressFrom");
    if (canContinue){
        var data=checkoutGetFormData(jQuery("#checkoutBillingAddressFrom"));

            var request = jQuery.ajax({
                url : "/blocks/"+jQuery('html').attr('lang')+"/checkout/xhr-update-billing",
                type : "POST",
                data : {
                    'data':JSON.stringify(data),
                    'current-page':jQuery('body').attr('data-current-page')
                },
                dataType : "json"
            });

            request.done(function(data) {
                if(data['success']) {
                    jQuery("#chk3Alert").addClass("hidden");
                    adaptToUserData(data['data']);
                    setCheckoutStep(4);
                } else {
                    jQuery("#chk3-message").html("<strong>"+data['msg']+"</strong>");
                    jQuery("#chk3Alert").removeClass("hidden");
                    jQuery("#chk3Alert").removeClass("alert-success");
                    jQuery("#chk3Alert").addClass("alert-danger");
                }
            });

            request.fail(function(jqXHR, textStatus, errorThrown) {
                try {
                    var responseText = jQuery.parseJSON(jqXHR.responseText);
                } catch(err) {
                    var responseText = jqXHR.responseText;
                }
                jQuery("#chk3-message").html("<strong>"+responseText['msg']+"</strong>");
                jQuery("#chk3Alert").removeClass("hidden");
                jQuery("#chk3Alert").removeClass("alert-success");
                jQuery("#chk3Alert").addClass("alert-danger");
            });



    } else {
        jQuery("#chk3-message").html("<strong>Please fill in all required fields</strong>");
        jQuery("#chk3Alert").removeClass("hidden");
        jQuery("#chk3Alert").removeClass("alert-success");
        jQuery("#chk3Alert").addClass("alert-danger");
    }
}

function checkoutUpdateShippingAddress (){
    var canContinue=checkoutCheckFormValid("checkoutShippingAddressFrom");
    if (canContinue){
        var data=checkoutGetFormData(jQuery("#checkoutShippingAddressFrom"));

        var request = jQuery.ajax({
            url : "/blocks/"+jQuery('html').attr('lang')+"/checkout/xhr-update-shipping",
            type : "POST",
            data : {
                'data':JSON.stringify(data),
                'current-page':jQuery('body').attr('data-current-page')
            },
            dataType : "json"
        });

        request.done(function(data) {
            if(data['success']) {
                jQuery("#chk4Alert").addClass("hidden");
                adaptToUserData(data['data']);
                setCheckoutStep(5);
            } else {
                jQuery("#chk4-message").html("<strong>"+data['msg']+"</strong>");
                jQuery("#chk4Alert").removeClass("hidden");
                jQuery("#chk4Alert").removeClass("alert-success");
                jQuery("#chk4Alert").addClass("alert-danger");
            }
        });

        request.fail(function(jqXHR, textStatus, errorThrown) {
            try {
                var responseText = jQuery.parseJSON(jqXHR.responseText);
            } catch(err) {
                var responseText = jqXHR.responseText;
            }
            jQuery("#chk4-message").html("<strong>"+responseText['msg']+"</strong>");
            jQuery("#chk4Alert").removeClass("hidden");
            jQuery("#chk4Alert").removeClass("alert-success");
            jQuery("#chk4Alert").addClass("alert-danger");
        });



    } else {
        jQuery("#chk4-message").html("<strong>Please fill in all required fields</strong>");
        jQuery("#chk4Alert").removeClass("hidden");
        jQuery("#chk4Alert").removeClass("alert-success");
        jQuery("#chk4Alert").addClass("alert-danger");
    }
}

function checkoutUpdateAccountData (){
    var canContinue=checkoutCheckFormValid("checkoutEditUserForm");
    if (canContinue){
        var data=checkoutGetFormData(jQuery("#checkoutEditUserForm"));

        var request = jQuery.ajax({
            url : "/blocks/"+jQuery('html').attr('lang')+"/checkout/xhr-update-account-data",
            type : "POST",
            data : {
                'data':JSON.stringify(data),
                'current-page':jQuery('body').attr('data-current-page')
            },
            dataType : "json"
        });

        request.done(function(data) {
            if(data['success']) {
                jQuery("#chk5Alert").addClass("hidden");
                adaptToUserData(data['data']);
                setCheckoutStep(3);
            } else {
                jQuery("#chk5-message").html("<strong>"+data['msg']+"</strong>");
                jQuery("#chk5Alert").removeClass("hidden");
                jQuery("#chk5Alert").removeClass("alert-success");
                jQuery("#chk5Alert").addClass("alert-danger");
            }
        });

        request.fail(function(jqXHR, textStatus, errorThrown) {
            try {
                var responseText = jQuery.parseJSON(jqXHR.responseText);
            } catch(err) {
                var responseText = jqXHR.responseText;
            }
            jQuery("#chk5-message").html("<strong>"+responseText['msg']+"</strong>");
            jQuery("#chk5Alert").removeClass("hidden");
            jQuery("#chk5Alert").removeClass("alert-success");
            jQuery("#chk5Alert").addClass("alert-danger");
        });



    } else {
        jQuery("#chk5-message").html("<strong>Please fill in all required fields</strong>");
        jQuery("#chk5Alert").removeClass("hidden");
        jQuery("#chk5Alert").removeClass("alert-success");
        jQuery("#chk5Alert").addClass("alert-danger");
    }
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
    var canContinue = true;
    jQuery("#"+formId+" input").each(function(){
        jQuery(this).closest('.error').removeClass('error');
        jQuery(this).siblings(".help-inline").remove();
        if (jQuery(this).is(":required") && jQuery.isEmptyObject(jQuery(this).val())){
            canContinue = false;
            jQuery(this).closest('.control-group').addClass('error');
            jQuery(this).after('<span class="help-inline">This field is required.</span>');
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
    var goodstep=2;
    var step2Data=userData;
    for (var attrname in step2Data.fields) {
        step2Data[attrname] = step2Data['fields'][attrname];
    }
    if (jQuery.isEmptyObject(step2Data.address)){
        step2Data.address={ };
    }
    if (jQuery.isEmptyObject(step2Data.billingAddress)){
        step2Data.billingAddress={ };
    }
    if (jQuery.isEmptyObject(step2Data.shippingAddress)){
        step2Data.shippingAddress={ };
    }
    for (var attrname in step2Data.address) {
        step2Data["address_"+attrname] = step2Data['address'][attrname];
    }
    checkoutSetFormData("checkoutEditUserForm",step2Data);
    if (checkoutCheckFormValid("checkoutEditUserForm")){
        jQuery("#chkStep2Continue").removeAttr("disabled");
        goodstep=goodstep+1;
    } else {
        jQuery("#chkStep2Continue").attr("disabled","disabled");
    }
    checkoutSetFormData("checkoutBillingAddressFrom",step2Data.billingAddress);
    if (checkoutCheckFormValid("checkoutBillingAddressFrom")){
        if (goodstep==3){
            goodstep=goodstep+1;
        }
        jQuery("#chkStep3Continue").removeAttr("disabled");
    } else {
        jQuery("#chkStep3Continue").attr("disabled","disabled");
    }
    checkoutSetFormData("checkoutShippingAddressFrom",step2Data.shippingAddress);
    if (checkoutCheckFormValid("checkoutShippingAddressFrom")){
        if (goodstep==4){
            goodstep=goodstep+1;
        }
        jQuery("#chkStep4Continue").removeAttr("disabled");
    } else {
        jQuery("#chkStep4Continue").attr("disabled","disabled");
    }
    var request = jQuery.ajax({
        url : "/blocks/"+jQuery('html').attr('lang')+"/checkout/xhr-get-shipping-options",
        type : "POST",
        data : {
            'current-choice': checkoutGetFormData(jQuery("#checkoutShippingMethodForm")).shipper,
            'current-page':jQuery('body').attr('data-current-page')
        },
        dataType : "json"
    });

    request.done(function(data) {
        if(data['success']) {
            jQuery("#shippingMethodsHolder").empty();
            jQuery("#shippingMethodsHolder").append(data.html);
            checkShipperStatus();
        } else {
            console.log("error in shippers retrieval");
        }
    });

    request.fail(function(jqXHR, textStatus, errorThrown) {
        console.log("error in shippers retrieval");
    });

    return(goodstep);
}

function checkShipperStatus(){
    if (checkoutGetFormData(jQuery("#checkoutShippingMethodForm")).shipper){
        jQuery("#chkStep5Continue").removeAttr("disabled");
    } else {
        jQuery("#chkStep5Continue").attr("disabled","disabled");

    }
}

jQuery("#shippingMethodsHolder").click(function(){
    checkShipperStatus();
})

function getCheckoutSummary(){
    var request = jQuery.ajax({
        url : "/blocks/"+jQuery('html').attr('lang')+"/checkout/xhr-get-summary",
        type : "POST",
        data : {
            'current-choice': checkoutGetFormData(jQuery("#checkoutShippingMethodForm")).shipper,
            'current-page':jQuery('body').attr('data-current-page')
        },
        dataType : "json"
    });

    request.done(function(data) {
        if(data['success']) {
            jQuery("#checkoutSummaryHolder").empty();
            jQuery("#checkoutSummaryHolder").append(data.html);
        } else {
            console.log("error in summary retrieval");
        }
    });

    request.fail(function(jqXHR, textStatus, errorThrown) {
        console.log("error in summary retrieval");
    });
}