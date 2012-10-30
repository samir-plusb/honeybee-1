

/* /home/vagrant/projects/honeybee/app/modules/Events/resources/scripts/events.js */

var midas=midas||{};midas.events=midas.events||{};

/* /home/vagrant/projects/honeybee/app/modules/Events/resources/scripts/EventsListController.js */

midas.events.EventsListController=midas.list.ListController.extend({log_prefix:"EventsListController",init:function(e){this.parent(e)}});

/* /home/vagrant/projects/honeybee/app/modules/Events/resources/scripts/init.js */

(function(e){var t=$(".controller-edit"),n=$(".container-list-data");1===t.length?midas.core.EditController.factory(".controller-edit"):1===n.length&&midas.list.ListController.create(".container-list-data",e).attach()})(midas.events);