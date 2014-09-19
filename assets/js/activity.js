// DOCUMENT READY
//////////////////////////////////////////////////////////////////////////////////
$(document).ready(function() {

    $('.badge').hide('slow');

    function polling(){

        var pollings = $('#results').data('polling');
        if(pollings == "yes") {
            $('#results').css({opacity: 0.3});

            $.ajax({
                type: 'POST',
                url: general.base.url + 'activity/feed',
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

                },
                complete: function() {
                    setTimeout(polling, 10 * 1000);
                }
            });
        }
    }

    polling();
});