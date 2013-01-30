honeybee.core.Request = honeybee.core.BaseObject.extend({
   
    log_prefix: 'Request',

    init: function()
    {
        this.parent();
    }
});

honeybee.core.Request.curry = function(url, data, method, type)
{
    return function(ok_call, err_call)
    {
        return $.ajax({
            url: url,
            dataType: type || 'json',
            data: data || {},
            type: method || 'get',
            success: ok_call,
            error: err_call || function() {
                if(console && console.log) console.log("request failed and no error callback was provided.");
            }
        });
    };
};