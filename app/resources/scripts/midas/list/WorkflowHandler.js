midas.list.WorkflowHandler = midas.core.BaseObject.extend({

    log_prefix: "WorkflowHandler",

    urls: null,

    init: function(urls)
    {
        this.parent();
        this.urls = urls || {};
    },

    proceed: function(ticket, gate)
    {
        var that = this;
        var post_url = this.urls.proceed.replace('{TICKET}', ticket.id);
        var post_data = { gate: gate };
        
        return function(ok_callback, err_callback)
        {
            // @todo checkout ticket and take ownership
            //console.log(this.urls.proceed.replace('{TICKET}', ticket.id));
            var req = midas.core.Request.curry(
                post_url, 
                post_data,
                "POST"
            );
            req(function(data){
                ok_callback(data);
            },
            function(data)
            {
                err_callback(data);
            });
        };
    },

    checkout: function(ticket)
    {
        var url = this.urls.checkout.replace("{TICKET_ID}", ticket.id).replace("{TICKET_REV}", ticket.rev);
        var req = midas.core.Request.curry(url);
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

    release: function(ticket)
    {
        var url = this.urls.release.replace("{TICKET}", ticket.id);
        var req = midas.core.Request.curry(url);
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

    run: function(ticket)
    {
        // @todo checkout ticket and take ownership
        window.location.href = this.urls.run.replace('{TICKET}', ticket.id);
    }
});
