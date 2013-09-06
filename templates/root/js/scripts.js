jQuery(document).ready(function() {
    
    jQuery.getJSON('/xhr-authentication/is-logged-in', function(data) {
        if (data.loggedIn) {
            //jQuery('#connect-btn').html('<a class="btn dropdown-toggle" data-toggle="dropdown" href="#"><i class="icon-user"></i> ' + data.username + ' <span class="caret"></span></a><ul class="dropdown-menu"><li><a href="#">Mon compte</a></li><li class="divider"></li><li><a href="/backoffice/" >Accès Back Office</a></li><li class="divider"></li><li><a href="#" id="logout">Se déconnecter</a></li></ul>');
            jQuery.getScript('/xhr-javascript/get-script?script=rubedo-edit', function() {

            });
            /*jQuery('#logout').click(function() {
                $.getJSON('/xhr-authentication/logout/', function(data) {
                    if (data.success) {
                        location.reload();
                    }
                });
                return false;
            });*/
        } else {
            //jQuery('#connect-btn').html('<a class="btn" data-toggle="modal" href="#connect"><i class="icon-user"></i> Connexion</a>');
        }
    });
}); 