honeybee.list.WorkflowHandler = honeybee.core.BaseObject.extend({

    log_prefix: "WorkflowHandler",

    urls: null,

    module_prefix: null,

    init: function(module_prefix, urls)
    {
        this.parent();
        this.urls = urls || {};
        this.module_prefix = module_prefix;
    },

    proceed: function(resource, gate)
    {
        var that = this;
        var post_url = this.urls.execute + '?' + this.module_prefix + '=' + resource.data.identifier;
        var post_data = { gate: gate };

        return function(ok_callback, err_callback)
        {
            // @todo checkout ticket and take ownership
            honeybee.core.Request.curry(
                post_url,
                post_data,
                "POST"
            )(
                function(data) { ok_callback(data); },
                function(err) { err_callback(err); }
            );
        };
    },

    run: function(resource, err_callback)
    {
        var url = this.urls.checkin + '?id=' + resource.data.identifier + '&rev=' + resource.data.revision;
        var checkin_request = honeybee.core.Request.curry(url, null, 'post');
        var that = this;
        checkin_request(function(data)
        {
            if ('ok' === data.state)
            {
                window.location.href = that.urls.execute + '?' + this.module_prefix + '=' + resource.data.identifier;
            }
            else
            {
                err_callback(data);
            }
        }, function(data)
        {
            if (err_callback)
            {
                err_callback(data);
            }
        });
    },

    checkout: function(resource)
    {
        var url = this.urls.checkout + '?id=' + resource.data.identifier;
        var req = honeybee.core.Request.curry(url, null, 'post');
        var that = this;

        return function(ok_callback, err_callback)
        {
            req(function(data)
            {
                if ('ok' === data.state)
                {
                    ok_callback(data);
                }
                else
                {
                    err_callback(data);
                }
            }, function(data)
            {
                if (err_callback)
                {
                    err_callback(data);
                }
            });
        };
    }
});
