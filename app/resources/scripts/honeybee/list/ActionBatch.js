honeybee.list.ActionBatch = honeybee.core.BaseObject.extend({

    log_prefix: "ActionBatch",
    
    actions: null,

    queue: null,

    progress_dialog_tpl: null,

    progress_dialog: null,

    init: function(actions)
    {
        this.parent();
        this.actions = actions || [];
        this.queue = [];
        this.progress_dialog_tpl = $('.modal-batch-progress').first()[0].outerHTML;
    },

    addAction: function(action)
    {
        this.actions.push(action);
    },

    run: function()
    {
        var that = this;
        var failHandler = function(error) { that.onActionFailure(error); };

        for (var i = 0; i < this.actions.length; i++)
        {
            var action = this.actions[i];
            this.queue.push(
                action.on('start', this.onActionStarted.bind(this))
                .on('success', this.onActionSuccess.bind(this))
                .on('failure',  failHandler)
                .execute.bind(action)
            );
        }
        this.dequeue();
        this.progress_dialog = $(this.progress_dialog_tpl).twodal({backdrop: 'static'});
        this.progress_dialog.twodal('show');
        return this;
    },

    dequeue: function()
    {
        if (0 === this.queue.length)
        {
            var that = this;
            setTimeout(function()
            {
                that.fire('complete');
                that.progress_dialog.twodal('hide');
                that.progress_dialog.remove();
                that.progress_dialog = null;
            }, 1500);
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
