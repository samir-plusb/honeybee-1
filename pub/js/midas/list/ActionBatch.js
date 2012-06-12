midas.list.ActionBatch = midas.core.BaseObject.extend({

    log_prefix: "ActionBatch",
    
    actions: null,

    queue: null,

    progress_dialog: null,

    init: function(actions)
    {
        this.parent();
        this.actions = actions || [];
        this.queue = [];
        this.progress_dialog = $('.modal-batch-progress').clone().twodal({ // @todo make selector configurable
            events: {
                keyboard: false,
                show: false,
                cancelbatch: function()
                {
                    // @todo implement cancel.
                    console.log("cancel batch clicked!");
                }
            }
        });
    },

    addAction: function(action)
    {
        this.actions.push(action);
    },

    run: function()
    {
        for (var i = 0; i < this.actions.length; i++)
        {
            var action = this.actions[i];
            this.queue.push(
                action.on('start', this.onActionStarted.bind(this))
                .on('success', this.onActionSuccess.bind(this))
                .on('failure', function(error) { this.onActionFailure(error); }.bind(this))
                .execute.bind(action)
            );
        }
        this.dequeue();
        this.progress_dialog.twodal('show');
        return this;
    },

    dequeue: function()
    {
        if (0 === this.queue.length)
        {
            this.progress_dialog.twodal('hide');
            this.fire('complete');
            return;
        }
        this.queue.shift()();
    },

    onActionStarted: function()
    {
        this.fire('started');
    },

    onActionSuccess: function()
    {
        // @todo make the progress bar selector configurable or so.
        var progress = (100 / this.actions.length) * (this.actions.length - this.queue.length);
        this.progress_dialog.find('.progress .bar').css('width', Math.round(progress)+'%');
        this.fire('progress');
        this.dequeue();
    },

    onActionFailure: function(error)
    {
        // @todo log/display error?
        this.fire('error', [ error ]);
        this.dequeue();
    }
});