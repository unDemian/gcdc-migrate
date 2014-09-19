// DOCUMENT READY
//////////////////////////////////////////////////////////////////////////////////
$(document).ready(function() {



    $('#wizard').bootstrapWizard({
        'tabClass': 'nav nav-tabs',
        'nextSelector': '.next',
        'previousSelector': '.previous',

        onTabShow: function(tab, navigation, index) {
            var name = $(tab).data('id'),
                id = $('#wizard').data('id');

            $('#tab' + index).html('<section class="loaderush main"><div class="wrapper"><div class="inner"></div></div></section>');

            $.ajax({
                type: 'POST',
                url: general.base.url + 'migrate/detail',
                data: {
                    id: id,
                    type: name
                },
                dataType: 'html',
                success: function(data){
                    $('#tab' + index).html(data);

                    // Graph link positioning
                    $('.linking, .linking-cut, .linking-inverse').each(function(index){
                        var id = $(this).data('id'),
                            height = $('.from[data-id="' + id + '"], .cut[data-id="' + id + '"], .to[data-id="' + id + '"').height(),
                            prevHeight = $('.from[data-id="' + (id - 1)  + '"], .cut[data-id="' + (id - 1) + '", .to[data-id="' + (id - 1) + '"]').height();

                        if(height < 30) {
                            if(id == 2 || id == 4) {
                                $(this).css({'top': 18 + prevHeight + height / 2 + 'px' });
                            } else {
                                $(this).css({'top': 28 + height / 10 + 'px'});
                            }
                        } else {
                            if(id == 2 || id == 4) {
                                $(this).css({'top': 18 + prevHeight + height / 2 + 'px' });
                            } else {
                                $(this).css({'top': (18 + (height / 2)) + 'px' });
                            }

                        }
                    });
                }
            });
        }
    });
});