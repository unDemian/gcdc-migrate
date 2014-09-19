// SignIn Button
(function () {
    var po = document.createElement('script');
    po.type = 'text/javascript';
    po.src = 'https://plus.google.com/js/client:plusone.js?onload=start';
    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(po, s);
})();

// Screenshots
(function () {
    $('.screen-center').animate({ bottom : "0"}, 2000);
    $('.screen-left').animate({ bottom : "0"}, 2000).animate({ left: "-154"}, 1000);
    $('.screen-right').animate({ bottom : "0"}, 2000).animate({ right: "-67" }, 1000);
})();

// G+ Sign in button callback
function signInCallback(authResult) {

    authResult.referrer = 'login';

    var request = new base.utils.Request({
        method: 'POST',
        url: 'oauth/login',
        data: authResult,
        success: function(result) {
            result = JSON.parse(result);

            if(result.success) {
                // Redirect
                if(general.redirect.logout === 'dashboard') {
                    window.location = '/dashboard';
                } else {
                    general.redirect.logout = 'dashboard';
                    $('#signinButton').removeClass('blurred');
                }

            } else {
                if(result.error != 'immediate_failed') {
                    insertNotification('danger', 'Sorry, operation failed with the following error: ' + result.error);
                }
                general.redirect.logout = 'dashboard';
                $('#signinButton').removeClass('blurred');
            }
        }
    });

    request.send();
}