jQuery(document).ready(function(){
    jQuery('.rubedo-login').bind('submit', function () {
        var form = this;
        jQuery.ajax({
            type: 'POST',
            url: '/xhr-authentication/login',
            async: true,
            dataType: 'json',
            data: jQuery(this).serialize(),
            success: function(msg){
                if(msg.success == false) {
                    jQuery('#error-msg', form).show();
                    jQuery('#error-msg', form).html(msg.msg);
                } else {
                    jQuery('#password', form).val('');
                    jQuery('#login', form).val('');
                    jQuery('#auth-modal', form).modal('hide');
                    window.location.replace(window.location.protocol+'//'+window.location.host+window.location.pathname);
                }
            }
        });
        return false;
    });

    jQuery('#recoverpwd-modal form').bind('submit', function() {
        var form = this;
        jQuery.ajax({
            type: 'POST',
            url: '/xhr-authentication/send-token',
            async: true,
            dataType: 'json',
            data: jQuery(this).serialize(),
            success: function(msg) {
                if(msg.success == false) {
                    jQuery('.xhr-return', form)
                        .removeClass('hidden alert alert-success')
                        .html(msg.msg)
                        .addClass('alert alert-error');
                } else {
                    jQuery('.xhr-return', form)
                        .removeClass('hidden alert alert-error')
                        .html(msg.msg)
                        .addClass('alert alert-success');
                }
            }
        });
        return false;
    });
    jQuery('#changepassword-modal').modal('toggle');
    jQuery('#changepassword-modal form').bind('submit', function() {
        var form = this;
        jQuery.ajax({
            type: 'POST',
            url: '/xhr-authentication/change-password',
            async: true,
            dataType: 'json',
            data: jQuery(this).serialize(),
            success: function(msg) {
                if(msg.success == false) {
                    jQuery('.xhr-return', form)
                        .removeClass('hidden alert alert-success')
                        .html(msg.msg)
                        .addClass('alert alert-error');
                } else {
                    jQuery('.xhr-return', form)
                        .removeClass('hidden alert alert-error')
                        .html(msg.msg)
                        .addClass('alert alert-success');
                }
            }
        });
        return false;
    });
});

function loggin() {/*Legacy, may be removed in future release*/}

function logout()
{	 
		jQuery.ajax({
		   type: "POST",
		   async:false, 
		   url: "/xhr-authentication/logout",
		   success: function(){
               window.location.replace(window.location.protocol+'//'+window.location.host+window.location.pathname);
		   }
		});
}