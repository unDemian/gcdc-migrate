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
    authResult.referrer = 'permissions';

    if($('body').attr('id') == 'accounts-add') {
        authResult.referrer = 'add';
    }

    var request = new base.utils.Request({
        method: 'POST',
        url: general.base.url + 'oauth/login',
        data: authResult,
        success: function(result) {
            result = JSON.parse(result);

            if(result.success) {
                window.location = result.redirectUrl;
            } else {
                if(result.error != 'immediate_failed') {
                    location.reload();
                }
            }
        }
    });
    request.send();
}

function _getScopes() {
    var scopes   = '',
        services = [];

    $('.js-select-service.selected').each(function(key, value){
        scopes += $(value).data('scopes') + ' ';
        services.push($(value).data('id'));
    });

    // Add selected services to session
    $.ajax({
        url: general.base.url + 'accounts/services',
        type: "POST",
        data: { 'ids': services }
    });

    return scopes;
}

function render() {

    // Clear button
    $('#gSignInWrapper').empty();
    $('#gSignInWrapper').html('<div id="customBtn" class="customGPlusSignIn" data-gapiattached="true"><span class="icon"></span><span class="buttonText">Authorize Selected Services with Google</span></div>');

    var scopes = _getScopes();

    gapi.signin.render('customBtn', {
        'clientid':              general.oauth.clientId,
        'state':                 general.oauth.state,
        'redirecturi':           'postmessage',
        'accesstype':            'offline',
        'approvalprompt':        'force',
        'scope':                 scopes,
        'callback':              'signInCallback',
        'cookiepolicy':          'single_host_origin',
        'width':                 'wide',
        'height':                'tall',
        'requestvisibleactions': 'http://schemas.google.com/AddActivity'
    });
}

// DOCUMENT READY
//////////////////////////////////////////////////////////////////////////////////
$(document).ready(function() {

    // Popover
    $('.js-popoverish').popover({
        html : true,
        template: '<div class="popover auto clearfix"><div class="arrow"></div><div class="popover-inner auto clearfix"><h3 class="popover-title"></h3><div class="popover-content auto clearfix"><p></p></div></div></div>',
        content: function() {
            return $('#service-' + $(this).data('id')).html();
        }
    });

    $('.popover').css({width: 'auto'});

    // Select service
    $(document).on('click', '.js-select-service', function(){

        // Deselect
        if($(this).hasClass('selected')) {
            if( ! $(this).parent().parent().hasClass('mandatory')) {
                $(this).removeClass('selected');
                $(this).parent().parent().removeClass('selected');
                $(this).find('.list-group-item-text').text('select');
            }

        // Select
        } else {
            $(this).addClass('selected');
            $(this).parent().parent().addClass('selected');
            $(this).find('.list-group-item-text').text('selected');
        }

        render();
        return false;
    });

    // Select all services
    $(document).on('click', '.js-services-select-all', function(){

        // Deselect
        if( $(this).text() == 'DESELECT ALL' ) {
            $('.js-select-service').removeClass('selected');
            $('.js-select-service').parent().parent().removeClass('selected');
            $('.js-select-service').find('.list-group-item-text').text('select');
            $(this).text('SELECT ALL');

            $('.mandatory').find('.js-select-service').addClass('selected');
            $('.mandatory').find('.js-select-service').parent().parent().addClass('selected');
            $('.mandatory').find('.js-select-service').find('.list-group-item-text').text('selected');

        // Select
        } else {
            $('.js-select-service').addClass('selected');
            $('.js-select-service').parent().parent().addClass('selected');
            $('.js-select-service').find('.list-group-item-text').text('selected');
            $(this).text('DESELECT ALL');
        }

        render();
    });
});
