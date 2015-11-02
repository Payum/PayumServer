if("undefined"==typeof jQuery) {
    throw new Error("Payum's JavaScript requires jQuery");
}

Payum = {
    render: function(url, container) {
        jQuery.ajax(url, {
            type: "GET",
            async: true,
            contentType: 'application/vnd.payum+json',
            success: function(data) {
            },
            complete: function(data) {
                console.log(data);
                if (data.status == 302) {
                    window.location = data.responseJSON.headers.location;
                }
                if (data.status >= 200 && data.status < 300) {
                    $(container).html(data.responseJSON.content);
                }
            },
            error: function() {
            }
        });
    }
};