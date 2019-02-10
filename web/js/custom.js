customJs = function() {

    $('.ajax-request a.ajax-button').click(function(e) {
        var href = $(this).attr('href');
        if (href) {
            e.preventDefault();
            $.get(href, null, function(data) {
                var contentWrap = $('body').find('#page-content');
                if (contentWrap) {
                    contentWrap.html(data);
                    window.history.pushState("object or string", "Title", href);
                    customJs();
                }
            }).fail(function(data) {
                console.log('error', data);
            });
        }
    });

};

customJs();
