// DOCUMENT READY
//////////////////////////////////////////////////////////////////////////////////
$(document).ready(function() {

    // Copy to cliboard
    $('a.copy-to-clipboard').clipboard({
        path: general.base.url + 'assets/js/clipboard/jquery.clipboard.swf',
        copy: function() {
            insertNotification('success', 'The link was successfully copied to clipboard.');
            return $(this).data('link');
        }
    });

    // Show Wizard
    $(document).on('click', '#show-share', function(){
        $('#wizard').slideToggle();
    });

    // Service choose
    $(document).on('click', '.select-one-service', function(){

        var id = $(this).data('id');

        // Deselect All
        $('.select-one-service').each(function(){
            $(this).removeClass('selected');
            $(this).parent().parent().removeClass('selected');
            $(this).find('.list-group-item-text').text('select');
        });


        // Select
        $(this).addClass('selected');
        $(this).parent().parent().addClass('selected');
        $(this).find('.list-group-item-text').text('selected');

        $('.next').addClass('blocked disabled');

        // Add selected services to session
        $.ajax({
            url: general.base.url + 'share/selectService',
            type: "POST",
            data: {
                'id': id
            },
            complete: function(){
                $('.next').removeClass('blocked disabled');
            }
        });

        return false;
    });

    // Data choose
    $(document).on('click', '.select-data', function(){

        var id   = $(this).data('id'),
            type = $(this).data('type');

        // Deselect
        if($(this).hasClass('selected')) {
            $(this).removeClass('selected');
            $(this).parent().parent().removeClass('selected');

            if($('.services-little li.selected').length == 1) {
                $('.next').addClass('disabled blocked');
            }

            // Add selected services to session
            $.ajax({
                url: general.base.url + 'share/selectData',
                type: "POST",
                data: {
                    'id': id,
                    'action': 'deselect',
                    'type': type
                },
                complete: function() {
                    if(!$('.services-little li.selected').length) {
                        $('.next').addClass('disabled blocked');
                    }
                }
            });

        // Select
        } else {
            $(this).addClass('selected');
            $(this).parent().parent().addClass('selected');

            // Add selected services to session
            $.ajax({
                url: general.base.url + 'share/selectData',
                type: "POST",
                data: {
                    'id': id,
                    'action': 'select',
                    'type': type
                },
                complete: function(){
                    $('.next').removeClass('blocked disabled');
                }
            });
        }

        return false;
    });

    // Start action
    $(document).on('click', '.start', function(){

        // Add selected services to session
        $.ajax({
            url: general.base.url + 'share/start',
            type: "GET",
            success: function() {
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

            $('#tab2').html('<section class="loaderush main"><div class="wrapper"><div class="inner"></div></div></section>');
            $('#tab3').html('<section class="loaderush main"><div class="wrapper"><div class="inner"></div></div></section>');

            $('.start').hide();

            switch($current) {

                // Action
                case 1:
                    $('.head-tab-2').addClass('blocked');
                    $('.head-tab-3').addClass('blocked');

                    $('.next').show();
                    $('.next').removeClass('blocked disabled');
                    break;

                // Data
                case 2:
                    $('.head-tab-1').removeClass('blocked');
                    $('.head-tab-2').removeClass('blocked');
                    $('.head-tab-3').addClass('blocked');

                    $('.next').show();
                    $('.next').addClass('blocked disabled');

                    $.ajax({
                        type: 'GET',
                        url: general.base.url + 'share/data',
                        dataType: 'html',
                        success: function(data){
                            $('#tab2').html(data);

                            if($('.services-little li.selected').length) {
                                $('.next').removeClass('blocked disabled');
                            }

                        }
                    });

                    break;


                // Go!
                case 3:
                    $('.head-tab-1').removeClass('blocked');
                    $('.head-tab-2').removeClass('blocked');
                    $('.head-tab-3').removeClass('blocked');

                    $('.next').hide();
                    $('.start').show();

                    $.ajax({
                        type: 'GET',
                        url: general.base.url + 'share/finish',
                        dataType: 'html',
                        success: function(data){
                            $('#tab3').html(data);
                            if($('#wizard .alert').length) {
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
                return false;
            }
        },

        onTabClick: function(tab, navigation, index, clicked) {
            if($('#wizard').hasClass('disabled')) {
                return false;
            }

            if($('.head-tab-' + (clicked + 1)).hasClass('blocked')) {
                return false;
            }
        }
    });

    function polling(){

        var pollings = $('#results').data('polling');
        if(pollings == "yes") {
            $('#results').css({opacity: 0.3});

            $.ajax({
                type: 'POST',
                url: general.base.url + 'share/feed',
                dataType: 'html',
                success: function(result) {
                    $('#results').html(result);

                    // Button
                    var inProgress = false;
                    $('table tbody tr').each(function(){

                        if($(this).data('status') == 'warning') {
                            inProgress = true;
                        }
                    });

                    if(inProgress) {
                        $('#results').data('polling', 'yes');
                    } else {
                        $('#results').data('polling', 'no');
                    }

                    $('#results').css({opacity: 1});

                    rebindCommon();

                    // Copy to cliboard
                    $('a.copy-to-clipboard').clipboard({
                        path: general.base.url + 'assets/js/clipboard/jquery.clipboard.swf',
                        copy: function() {
                            insertNotification('success', 'The link was successfully copied to clipboard.');
                            return $(this).data('link');
                        }
                    });

                },
                complete: function() {
                    setTimeout(polling, 10 * 1000);
                }
            });
        }
    }

    polling();
});