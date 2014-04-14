jQuery(document).ready(function(){
    jQuery('.rubedo-login').bind('submit', function () {
        jQuery.ajax({
            type: 'POST',
            url: '/xhr-authentication/login',
            async:true,
            dataType: 'json',
            data: jQuery(this).serialize(),
            success: function(msg){
                if(msg.success == false) {
                    jQuery('#error-msg', this).show();
                    jQuery('#error-msg', this).html(msg.msg);
                } else {
                    jQuery('#password', this).val('');
                    jQuery('#login', this).val('');
                    jQuery('#auth-modal', this).modal('hide');
                    window.location.reload();
                }
            }
        });
        return false;
    });
});

function logout()
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