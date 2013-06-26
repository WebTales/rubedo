function loggin()
{
		jQuery.ajax({
		   type: "POST", 
		   url: "/xhr-authentication/login",
		   async:true,
		   dataType: "json",
		   data: { login: jQuery('#login').val(), password: jQuery('#password').val() },
		   success: function(msg){
		   if(msg.success==false){
		   jQuery("#error-msg").show();
		   jQuery("#error-msg").html(msg.message);
		   }else{
		   	jQuery('#password').val("");
		    jQuery('#login').val("");
		    jQuery('#auth-modal').modal("hide");
		    window.location.reload(); 
			}
		   }
		});
		
		return false;
}
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