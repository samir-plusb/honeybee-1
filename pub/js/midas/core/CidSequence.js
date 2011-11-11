/**
 * @class
 * @augments midas.core.BaseObject
 * @description <p>The CidSequence module provides a forthrunning sequence of ids.</p>
 * @author <a href="mailto:tschmittrink@gmail.com">Thorsten Schmit-Rink</a>
 * @version $Id:$
 */
midas.core.CidSequence = midas.core.BaseObject.extend(
/** @lends midas.core.CidSequence.prototype */
{
    /**
     * The prefix to use when logging messages from this class.
     * @type String
     */
    log_prefix: 'CidSequence',
    
    /**
     * The current id in our sequence.
     * @type Number
     */
    current_id: null,
    
    /**
     * Holds all ids that have been used so far.
     * @type Object
     */
    sequence: null,
    
    /**
     * @description 'Magic' method called during our prototype's constructor execution.
     * @param {midas.items.edit.EditController} controller The view's (parent) controller.
     * @param {Object} options An optional object containing options that are used to configure runtime behaviour.
     */
    init: function(options)
    {
        this.parent(options);
        this.current_id = 0;
        this.sequence = {};
    },
    
    /**
     * @description 'Magic' method called during our prototype's constructor execution.
     * @param {String} receiver_type The entity type we are providing the id to.
     * @return {Number}
     */
    nextCid: function(receiver_type)
    {
        this.current_id++;
        this.sequence[this.current_id] = receiver_type;
        return this.current_id;
    }
});

/**
 * @static
 * @function
 * @description Returns the next id form the sequence.
 * @param {String} receiver_type
 */
midas.core.CidSequence.nextCid = function(receiver_type)
{
    if (! midas.core.CidSequence._instance)
    {
        midas.core.CidSequence._instance = new midas.core.CidSequence();
    }

    return midas.core.CidSequence._instance.nextCid(receiver_type);
}