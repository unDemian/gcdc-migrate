// DOCUMENT READY
//////////////////////////////////////////////////////////////////////////////////
$(document).ready(function() {

    // Show Wizard
    $(document).on('click', '#show-migrate', function(){
        $('#wizard').slideToggle();
    });

    // Action Chooser
    $(document).on('click', '.action', function(){

        var id = $(this).data('id');

        $('.action').removeClass('selected');
        $(this).addClass('selected');

        $('.action .checkmark').find('i').removeClass('fa-check-square-o').addClass('fa-square-o');
        $(' .checkmark', this).find('i').removeClass('fa-square-o').addClass('fa-check-square-o');

        $('.next').addClass('disabled blocked');

        $.ajax({
            type: 'POST',
            url: general.base.url + 'migrate/action',
            data: {id: id},
            complete: function() {
                $('.next').removeClass('disabled blocked');
            }
        });

        // Step two select action
        $('.le-action i').removeClass('hide').addClass('hide');
        $('.le-action i[data-id="' + id + '"]').removeClass('hide');
    });

    // User Chooser
    $(document).on('click', '.account', function(){

        var isSource = $(this).parent('.accounts').hasClass('source'),
            selectedId = $(this).data('id'),
            selectedSource = $('.accounts.source .account.selected').data('id'),
            selectedDestination = $('.accounts.destination .account.selected').data('id'),
            sourceId,
            destinationId,
            selected = false;

        if(isSource) {
            $('.accounts.source .account').removeClass('selected');
            $(this).addClass('selected');

            sourceId = selectedId;

            // Select a destination Id
            if(sourceId == selectedDestination) {
                $('.accounts.destination .account').removeClass('selected');
                $('.accounts.destination .account').each(function(){
                    if($(this).data('id') != sourceId && !selected) {
                        $(this).addClass('selected');
                        destinationId = $(this).data('id');
                        selected = true;
                    }
                });
            } else {
                destinationId = selectedDestination;
            }

        } else {
            $('.accounts.destination .account').removeClass('selected');
            $(this).addClass('selected');

            destinationId = selectedId;

            // Select a destination Id
            if(destinationId == selectedSource) {
                $('.accounts.source .account').removeClass('selected');
                $('.accounts.source .account').each(function(){
                    if($(this).data('id') != destinationId && !selected) {
                        $(this).addClass('selected');
                        sourceId = $(this).data('id');
                        selected = true;
                    }
                });
            } else {
                sourceId = selectedSource;
            }
        }

        $.ajax({
            type: 'POST',
            url: general.base.url + 'migrate/users',
            data: {
                sourceId: sourceId,
                destinationId: destinationId
            },
            dataType: 'json',
            success: function(data){
                if(data.count <= 1) {
                    insertNotification('warning', 'The selected accounts have no services in common, please refine your permissions.', 'persistent');
                    $('.next').addClass('blocked disabled');
                } else {
                    hideNotifications(0);
                    $('.next').removeClass('blocked disabled');
                }

            }
        });
    });

    // Service choose
    $(document).on('click', '.select-service', function(){

        var id = $(this).data('id');

        // Deselect
        if($(this).hasClass('selected')) {
            $(this).removeClass('selected');
            $(this).parent().parent().removeClass('selected');
            $(this).find('.list-group-item-text').text('select');

            if(!$('.services .selected').length) {
                $('.next').addClass('disabled blocked');
            }

            // Add selected services to session
            $.ajax({
                url: general.base.url + 'migrate/selectService',
                type: "POST",
                data: {
                    'id': id,
                    'action': 'deselect'
                },
                complete: function(){
                    if(!$('.services .selected').length) {
                        $('.next').addClass('disabled blocked');
                    }
                }
            });

        // Select
        } else {
            $(this).addClass('selected');
            $(this).parent().parent().addClass('selected');
            $(this).find('.list-group-item-text').text('selected');

            // Add selected services to session
            $.ajax({
                url: general.base.url + 'migrate/selectService',
                type: "POST",
                data: {
                    'id': id,
                    'action': 'select'
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
            url: general.base.url + 'migrate/start',
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

            $('#tab3').html('<section class="loaderush main"><div class="wrapper"><div class="inner"></div></div></section>');
            $('#tab4').html('<section class="loaderush main"><div class="wrapper"><div class="inner"></div></div></section>');

            $('.start').hide();
            switch($current) {
                // Action
                case 1:
                    $('.head-tab-2').addClass('blocked');
                    $('.head-tab-3').addClass('blocked');
                    $('.head-tab-4').addClass('blocked');

                    $('.next').show();
                    $('.next').removeClass('blocked disabled');
                    break;

                // Accounts
                case 2:
                    $('.head-tab-1').removeClass('blocked');
                    $('.head-tab-2').removeClass('blocked');
                    $('.head-tab-3').addClass('blocked');
                    $('.head-tab-4').addClass('blocked');

                    $('.next').show();
                    $('.next').removeClass('blocked disabled');


                    var sourceId      = $('.accounts.source .account.selected').data('id'),
                        destinationId = $('.accounts.destination .account.selected').data('id');
                    $.ajax({
                        type: 'POST',
                        url: general.base.url + 'migrate/users',
                        data: {
                            sourceId: sourceId,
                            destinationId: destinationId
                        },
                        dataType: 'json',
                        success: function(data){
                            if(data.count <= 1) {
                                insertNotification('warning', 'The selected accounts have no services in common, please refine your permissions.', 'persistent');
                                $('.next').addClass('blocked disabled');
                            } else {
                                hideNotifications(0);
                                $('.next').removeClass('blocked disabled');
                            }

                        }
                    });

                    break;


                // Services
                case 3:
                    $('.head-tab-1').removeClass('blocked');
                    $('.head-tab-2').removeClass('blocked');
                    $('.head-tab-3').removeClass('blocked');

                    $('.next').show();
                    $('.head-tab-4').addClass('blocked');

                    $('.next').removeClass('blocked disabled');
                    
                    $.ajax({
                        type: 'GET',
                        url: general.base.url + 'migrate/services',
                        dataType: 'html',
                        success: function(data){
                            if(data.length > 1) {
                                $('#tab3').html(data);
                                $('.next').removeClass('blocked disabled');

                                if($('.services .selected').length) {
                                    $('.next').removeClass('blocked disabled');
                                } else {
                                    $('.next').addClass('blocked disabled');
                                }

                            } else {
                                $('.next').addClass('blocked disabled');
                            }
                        }
                    });
                    break;

                // Finish
                case 4:
                    $('.head-tab-1').removeClass('blocked');
                    $('.head-tab-2').removeClass('blocked');
                    $('.head-tab-3').removeClass('blocked');
                    $('.head-tab-4').removeClass('blocked');

                    $('.next').hide();
                    $('.start').show();
                    
                    $.ajax({
                        type: 'GET',
                        url: general.base.url + 'migrate/finish',
                        dataType: 'html',
                        success: function(data){
                            $('#tab4').html(data);
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
                url: general.base.url + 'migrate/feed',
                dataType: 'html',
                success: function(result) {
                    $('#results').html(result);

                    // Button
                    var inProgress = false;
                    $('table tbody tr').each(function(){

                        if($(this).data('status') == 'warning' || $(this).data('status') == 'default') {
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

                },
                complete: function() {
                    setTimeout(polling, 10 * 1000);
                }
            });
        }
    }

    polling();
});