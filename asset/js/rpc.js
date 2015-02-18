$.oxygen = function(options) {
    var defaults = {
        component: 'oxygen\\page\\missing\\Missing',
        method: 'getData',
        args: {},
        callback: function (err,data) {}
    };
    options = _.extend(defaults, options);
    $.post({
        url: OXYGEN_ROOT,
        headers: {
            'X-Oxygen-Request': 'POST',
            'X-Oxygen-Class': ''
        }

    })
};