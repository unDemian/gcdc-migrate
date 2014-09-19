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
                url: general.base.url + 'clean/detail',
                data: {
                    id: id,
                    type: name
                },
                dataType: 'html',
                success: function(data){
                    $('#tab' + index).html(data);
                }
            });
        }
    });
});