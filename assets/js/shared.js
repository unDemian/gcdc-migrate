// Sign In button
//////////////////////////////////////////////////////////////////////////////////
(function () {
    var po = document.createElement('script');
    po.type = 'text/javascript';
    po.src = 'https://plus.google.com/js/client:plusone.js?onload=render';
    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(po, s);
})();

// G+ Sign in button callback
function signInCallback(authResult) {

    // Send the code to the server
    authResult.task = document.getElementById("the-task").getAttribute("data-id");
    console.log(authResult)

    var request = new base.utils.Request({
        method: 'POST',
        url: general.base.url + 'shared/save',
        data: authResult,
        success: function(result) {
            location.reload();
        }
    });
    request.send();
}

function _getScopes() {
    var scopes   = '';
    scopes = $('.the-service').data('scopes');
    return scopes;
}

function render() {

    // Clear button
    $('#gSignInWrapper').empty();
    $('#gSignInWrapper').html('<div id="customBtn" class="customGPlusSignIn" data-gapiattached="true"><span class="icon"></span><span class="buttonText">Import data to my Google account</span></div>');

    var scopes = _getScopes();

    gapi.signin.render('customBtn', {
        'clientid':              general.oauth.clientId,
        'state':                 general.oauth.state,
        'redirecturi':           'postmessage',
        'accesstype':            'online',
        'approvalprompt':        'force',
        'scope':                 scopes,
        'callback':              'signInCallback',
        'cookiepolicy':          'single_host_origin',
        'width':                 'wide',
        'height':                'tall'
    });
}