midas.core.CidSequence = midas.core.BaseObject.extend({

    log_prefix: 'CidSequence',

    current_id: null,

    sequence: null,

    init: function(options)
    {
        this.parent(options);
        this.current_id = 0;
        this.sequence = {};
    },

    nextCid: function(receiver_type)
    {
        this.current_id++;
        this.sequence[this.current_id] = receiver_type;
        return this.current_id;
    }
});

midas.core.CidSequence.nextCid = function(receiver_type)
{
    if (! midas.core.CidSequence._instance)
    {
        midas.core.CidSequence._instance = new midas.core.CidSequence();
    }

    return midas.core.CidSequence._instance.nextCid(receiver_type);
}