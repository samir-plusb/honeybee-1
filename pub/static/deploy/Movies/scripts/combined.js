

/* /home/vagrant/projects/honeybee/app/modules/Movies/resources/scripts/movies.js */

var midas=midas||{};midas.movies=midas.movies||{};

/* /home/vagrant/projects/honeybee/app/modules/Movies/resources/scripts/MoviesListController.js */

midas.movies.MoviesListController=midas.list.ListController.extend({log_prefix:"MoviesListController",init:function(e){this.parent(e)}});

/* /home/vagrant/projects/honeybee/app/modules/Movies/resources/scripts/init.js */

(function(e){var t=$(".controller-edit"),n=$(".container-list-data");1===t.length?midas.core.EditController.factory(".controller-edit"):1===n.length&&midas.list.ListController.create(".container-list-data",e).attach()})(midas.movies);