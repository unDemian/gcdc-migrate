// DOCUMENT READY
//////////////////////////////////////////////////////////////////////////////////

// hey man, just curios :D how did our guys' demo look from the other part of the ocean
// also if you get today to the assignment issue between discovery codes and aircraft types and need extra info or something I'll stick around for a couple of hours more

$(document).ready(function() {

    // Show Wizard
    $(document).on('click', '#show-backup', function(){
        $('#wizard').slideToggle();
    });

    // Choose another account
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

            $('.modal-footer .btn-success').removeClass('disabled');
        }
    });

    $(document).on('click', '.js-do-it', function(){

        var services = [];
        $('.js-select-service.little.selected').each(function(){
            services.push($(this).data('id'));
        });

        $.ajax({
            type: 'POST',
            url: general.base.url + 'backup/start',
            data: {services: services},
            success: function(){
                location.reload();
            }
        });
    });

    $('#wizard').bootstrapWizard({
        'tabClass': 'nav nav-tabs',
        'nextSelector': '.next',
        'previousSelector': '.previous',
        onTabShow: function(tab, navigation, index) {
            var $total = navigation.find('li').length;
            var $current = index+1;
            var $percent = ($current/$total) * 100;
            var $bar = $('#bar').find('.progress-bar');

            $bar.css({width: $percent + '%'});
            $bar.attr('aria-valuenow', $percent);

            $('#tab3').html('<h4>Loading...</h4><br />');
            $('#tab4').html('<h4>Loading...</h4><br />');

            $('.start').hide();

            switch($current) {
                // Services
                case 3:
                    $('.next').show();

                    $.ajax({
                        type: 'GET',
                        url: general.base.url + 'migrate/services',
                        dataType: 'html',
                        success: function(data){
                            if(data.length > 1) {
                                $('#tab3').html(data);
                                $('.next').show();
                                $('.head-tab-4').show();
                            } else {
                                $('.next').hide();
                                $('.head-tab-4').hide();
                            }

                        }
                    });
                    break;

                // Finish
                case 4:
                    $('.next').hide();
                    $('.start').show();

                    $.ajax({
                        type: 'GET',
                        url: general.base.url + 'migrate/finish',
                        dataType: 'html',
                        success: function(data){
                            $('#tab4').html(data);
                            if($('.alert').length) {
                                $('.start').addClass('disabled');
                            } else {
                                $('.start').removeClass('disabled');
                            }
                        }
                    });
                    break;

                default:
                    $('.next').show();
                    break;
            }
        },

        onNext: function(tab, navigation, index) {
            if($('#wizard').hasClass('disabled')) {
//                return false;
            }
        },

        onTabClick: function(tab, navigation, index) {
            var tabIndex = tab.find('a').attr('data-id');

            if($('#wizard').hasClass('disabled')) {
                return false;
            }

            if(tabIndex > index) {
                return false;
            } else {
                tab.click();
            }
        }
    });

    function polling(){

        $('#results').css({opacity: 0.3});

        $.ajax({
            type: 'POST',
            url: general.base.url + 'backup/feed',
            dataType: 'html',
            success: function(result) {
                $('#results').html(result);
                $('#results').css({opacity: 1});

                $('.js-tooltip').tooltip();
            },
            complete: function() {
                setTimeout(polling, 30 * 1000);
            }
        });
    }

    polling();
});

The application helps you migrate google data to between accounts. There is an introductory tour on the site, also almost everything comes with an explanation or an "What's this?" button. enjoy.

    Application Description
Currently it supports the following operations:
    - Migrate - Copy your data from one account to another without affecting existing data. This is a one way operation from Source to Destination.
    - Move - Copy your data between two accounts, until everything is matched. This is a two way operation from Source to Destination and vice-versa.
    - Sync - Move your data from one account to another. After the data is copied it will be deleted from the source account.
    - Clean - Now you can bulk delete data from your Google account.
    - Share - This is kind of unique. You can share your data with your friends. Select which services and exactly what data you want to share and then we will create a link for you. By
    accessing that link, your friends can import your data to their accounts.
    You can quickly check out your activity in the activity tab. You can verify the details of the operation and if something went wrong revert it. There is no limit you can add as many accounts as we want. For profile and accounts management click on your email address in the header.

    Technology stack
AppEngine PHP (created a lightweight mini framework with basic ORM, routing and templating functions)
CloudSQL MySQL
Login with Google+ SignIn
    Background tasks with Google Tasks API Queue
Integration with Youtube, Google Calendar, Google Tasks and Google Contacts APIs
Custom Bootstrap 3 + Bootstrap for HTML elements
Animations with jQuery + jQuery UI
Icons with Font Awesome
Wizard with http://github.com/VinceG/twitter-bootstrap-wizard
Intro.js for the Tour
Preloader with pace.js
    Notifications with favico.js


    The interface suffered a major revamp.
    We updated the way you create a new operation, we designed a wizard like interface.
We added move, sync, clean and share operations.
    We added the revert button, which makes all the operations reversible.
    There is a new notification system and activity log.
    A brand new detail page for operations with awesome graph data.
    Creation process and history work now on the fly via ajax async requests.