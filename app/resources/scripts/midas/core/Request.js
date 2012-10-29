midas.core.Request = midas.core.BaseObject.extend({
   
    log_prefix: 'Request',

    init: function()
    {
        this.parent();
    }
});

midas.core.Request.curry = function(url, data, method, type)
{
    return function(ok_call, err_call)
    {
        $.ajax({
            url: url,
            dataType: type || 'json',
            data: data || {},
            type: method || 'get',
            success: ok_call,
            error: err_call || function() { if(console && console.log)console.log("request failed and no error callback was provided."); }
        });
    };
};