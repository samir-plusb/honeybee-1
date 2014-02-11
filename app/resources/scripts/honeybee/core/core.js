/**
 * Globally (project wide) configure the copy&paste flash widget
 */
ZeroClipboard.setMoviePath( 'flash/ZeroClipboard.swf' );

/**
 * Define custom ko-bindings.
 */
ko.bindingHandlers.sortable = {
    init: function (element, valueAccessor) {
        // cached vars for sorting events
        var drag_start_idx = null;
        var drag_end_idx = null;
        var ko_array = valueAccessor();
        var $element = $(element);
        var sortable_setup = {
            onDrop: function  ($item, target_container, _super) {
                var $cloned_item = $('<li/>').css({height: 3});
                $item.before($cloned_item);
                $cloned_item.animate({'height': $item.height()});
                $item.animate($cloned_item.position(), function () {
                    $cloned_item.detach();
                    _super($item);

                    drag_end_idx = $element.find('li').index($item);
                    if (null !== drag_start_idx && null !== drag_end_idx) {
                        var moved_data_item = ko_array()[drag_start_idx];
                        ko_array.remove(moved_data_item);
                        ko_array.splice(drag_end_idx, 0, moved_data_item);
                        $item.remove();
                    }
                    drag_start_idx = null;
                    drag_end_idx = null;
                });
            },
            onDragStart: function ($item, container, _super) {
                var offset = $item.offset();
                var pointer = container.rootGroup.pointer;
                adjustment = {
                    left: pointer.left - offset.left,
                    top: pointer.top - offset.top
                };
                _super($item, container);
                drag_start_idx = $element.find('li').index($item);
            },
            onDrag: function ($item, position) {
                $item.css({
                    left: position.left - adjustment.left,
                    top: position.top - adjustment.top
                });
            }
        };

        $element.sortable(sortable_setup);
    }
};

/**
 * @namespace The honeybee namespace holds all honeybee related stuff.
 * @name honeybee
 */
var honeybee = {
    /**
     * @namespace The honeybee.core namespace holds all core base stuff.
     * @name honeybee.core
     */
    core: {}
};
