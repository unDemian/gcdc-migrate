// DOCUMENT READY
//////////////////////////////////////////////////////////////////////////////////
$(document).ready(function() {

    // Resize the dropdown to match the email length
    $(document).on('click', '.js-resize-dropdown', function(){
        width = $(this).parent().width();
        $(this).next().css('min-width', width);
        return false;
    });

    // Show info
    $(document).on('click', '.js-info-sign', function(){
        var src = $(this).data('src');
        $('.info[data-id="' + src + '"]').slideToggle();
        return false;
    });

    rebindCommon();

    showNotifications();

    $(document).on('click', '.notifications .close', function(){
        hideNotifications(0);
    });

    // Favicon
    var favicon = new Favico({
        animation : 'popFade',
        type : 'circle',
        fontStyle : 'normal',
        fontFamily: 'Arial',
        position : 'down'
    });


    // Intro
    introJs().start();

    function getNotifications()
    {
        $.ajax({
            type: 'POST',
            url: general.base.url + 'activity/notifications',
            dataType: 'html',
            success: function(result) {

                if( !general.login && !$('body#tasks').length) {

                    favicon.badge(result);

                    if(result != 0) {
                        $('.badge').text(result);
                        $('.badge').show('slow');
                    } else {
                        $('.badge').hide();
                    }
                }
            },
            complete: function() {
                setTimeout(getNotifications, 10 * 1000);
            }
        });
    }

    getNotifications();
});

function rebindCommon() {

    // Tooltip
    $('.js-tooltip').tooltip();
}

// Notifications
function showNotifications(persistent) {
    if($('.notifications .notification').length) {

        $('.navbar').animate({top: '-54'}, 500);
        $('.notifications').animate({top: "0"}, 500);

        if(typeof persistent === 'undefined') {
            hideNotifications();
        }
    }
}

function hideNotifications(time) {

    if(typeof time === 'undefined') {
        time = 7000;
    }

    if(time === 0){
        $('.navbar').animate({top: '0'}, 500);
        $('.notifications').animate({top: "-54"}, 500);
    }

    if( $('.notifications .notification').length) {
        setTimeout(function(){
            $('.navbar').animate({top: '0'}, 500);
            $('.notifications').animate({top: "-54"}, 500);
        }, time);
    }
}

function insertNotification(type, message, persistent) {
    var html = '<div class="notification notification-' + type + ' text-center alert-dismissable">';

        html += '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';

    html += message;
    html += '</div>';

    $('.notifications').html(html);
    showNotifications(persistent);
}