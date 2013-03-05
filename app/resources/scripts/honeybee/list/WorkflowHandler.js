honeybee.list.WorkflowHandler = honeybee.core.BaseObject.extend({

    log_prefix: "WorkflowHandler",

    urls: null,

    init: function(urls)
    {
        this.parent();
        this.urls = urls || {};
    },

    proceed: function(resource, gate)
    {
        var that = this;
        var post_url = this.urls.execute + '?id=' + resource.data.identifier;
        console.log('post_url',post_url);
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

    checkout: function(resource)
    {
        var url = this.urls.release + '?id=' + resource.data.identifier + '&rev=' + resource.data.revision;
        var req = honeybee.core.Request.curry(url);
        var that = this;

        return function(ok_callback, err_callback)
        {
			ok_callback();
			/*
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
            });*/
        };
    },

    release: function(resource)
    {
        var url = this.urls.release + '?id=' + resource.data.identifier;
        var req = honeybee.core.Request.curry(url);
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
    },

    run: function(resource)
    {
        // @todo checkout ticket and take ownership
        window.location.href = this.urls.execute + '?id=' + resource.data.identifier;
    }
});
