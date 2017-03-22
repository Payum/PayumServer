if("undefined"==typeof jQuery) {
    throw new Error("Payum's JavaScript requires jQuery");
}

Payum = function(serverUrl) {
    var payum = this;

    payum.serverUrl = serverUrl;

    payum.payment = {
        create: function(payment, callback) {
            $.ajax(payum.serverUrl + '/payments', {
                'data': JSON.stringify(payment),
                'type': 'POST',
                'processData': false,
                'contentType': 'application/json',
                success: function(data) {
                    callback(data.payment);
                }
            });
        },
        get: function(id, callback) {
            $.ajax(payum.serverUrl + '/payments/'+id, {
                'type': 'GET',
                success: function(data) {
                    callback(data.payment);
                }
            });
        }
    };

    payum.token = {
        create: function(token, callback) {
            $.ajax(payum.serverUrl + '/tokens', {
                'data': JSON.stringify(token),
                'type': 'POST',
                'processData': false,
                'contentType': 'application/json',
                success: function(data) {
                    callback(data.token);
                }
            });
        }
    };

    payum.execute = function(url, container) {
        $.ajax(url, {
            type: "GET",
            async: true,
            headers: {
                Accept: 'application/vnd.payum+json'
            },
            complete: function(data) {
                payum.updateContainer(data, container);

                $(container + ' form').on('submit', function (e) {
                    e.preventDefault();

                    var form = $(this);

                    var values = {};
                    $.each(form.serializeArray(), function (i, field) {
                        values[field.name] = field.value;
                    });

                    jQuery.ajax(form.attr('action'), {
                        type: "POST",
                        headers: {
                            Accept: 'application/vnd.payum+json'
                        },
                        data: values,
                        success: function(data) {
                        },
                        complete: function(data) {
                            payum.updateContainer(data, container);
                        },
                        error: function(data) {
                            payum.updateContainer(data, container);
                        }
                    });
                });
            },
            error: function(data) {
                payum.updateContainer(data, container);
            }
        });
    };

    payum.updateContainer = function updateContainer(data, container) {
        if (data.status == 302) {
            window.location.replace(data.responseJSON.headers.Location);
        }
        if (data.status >= 200 && data.status < 300) {
            $(container).html(data.responseJSON.content);
        }
        if (data.status >= 400 && data.status < 500) {
            $(container).html('<div class="alert alert-warning">Bad request error</div>');
        }
        if (data.status >= 500 && data.status < 600) {
            $(container).html('<div class="alert alert-warning">Internal server error</div>');
        }
    };
};