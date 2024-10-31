(function($) {
    $(document).ready(function() {
        $(document).on('heartbeat-tick', function(event, data) {
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'scwriter_heartbeat',
                },
                success: function(response) {
                },
                error: function(error) {
                    console.log('API call failed:', error);
                }
            });
        });
    });
})(jQuery);