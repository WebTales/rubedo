$(document).ready(function() {
    $('#contentType a').click(function(event) {
        $('#addContentForm').modal();
        <!-- todo : put real url for editing contents -->
        var
        url = 'http://rubedo-bo.local/test.html';
        $('#addContentForm .modal-body').html('<iframe width="100%" height="100%" frameborder="0" scrolling="no" allowtransparency="true" src="' + url + '"></iframe>');
        event.preventDefault();
    });
    $('#themeChooser a').click(function(event) {
        $.getJSON('/xhr-theme/define-theme?theme=' + $(this).attr('href'), function(data) {
            if (data.success)
                location.reload();
        });
        event.preventDefault();
    });
    $('#langChooser a').click(function(event) {

        $.getJSON('/xhr-language/define-language?language=' + $(this).attr('href'), function(data) {
            if (data.success)
                location.reload();
        });
        event.preventDefault();
    });
    $('#connect').submit(function() {
        $.post('/xhr-authentication/login', {
            "login" : $('#inputEmail').val(),
            "password" : $('#inputPassword').val()
        }, function(data) {
            if (data.success) {
                $(this).modal('hide');
                location.reload();
            } else {
                $("#connect-msg").html('Identifiants incorrects');
            }
        }, "json");
        return false;
    });
    $.getJSON('/xhr-authentication/is-logged-in', function(data) {
        if (data.loggedIn) {
            $('#connect-btn').html('<a class="btn dropdown-toggle" data-toggle="dropdown" href="#"><i class="icon-user"></i> ' + data.username + ' <span class="caret"></span></a><ul class="dropdown-menu"><li><a href="#">Mon compte</a></li><li class="divider"></li><li><a href="/backoffice/" >Accès Back Office</a></li><li class="divider"></li><li><a href="#" id="logout">Se déconnecter</a></li></ul>');
            $.getScript('/xhr-javascript/get-script?script=rubedo-edit', function() {

            });
            $('#logout').click(function() {
                $.getJSON('/xhr-authentication/logout/', function(data) {
                    if (data.success) {
                        location.reload();
                    }
                });
                return false;
            });
        } else {
            $('#connect-btn').html('<a class="btn" data-toggle="modal" href="#connect"><i class="icon-user"></i> Connexion</a>');
        }
    });
}); 