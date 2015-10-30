if("undefined"==typeof jQuery) {
    throw new Error("Payum's JavaScript requires jQuery");
}

Payum = {
    render: function(url, container) {
        jQuery.ajax(url, {
            type: "GET",
            async: true,
            contentType: 'application/json',
            success: function(data) {
                if (data.status < 400) {
                    $(container).html(data.content);
                }
            },
            complete: function() {
            },
            error: function() {
            }
        });
    }
};