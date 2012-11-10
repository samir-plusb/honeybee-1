

/* /home/vagrant/projects/honeybee/app/modules/Article/resources/scripts/article.namespace.js */

var midas=midas||{};midas.article=midas.article||{};

/* /home/vagrant/projects/honeybee/app/modules/Article/resources/scripts/article.ListController.js */

midas.article.ArticleListController=midas.list.ListController.extend({log_prefix:"ArticleListController",init:function(e){this.parent(e)}});

/* /home/vagrant/projects/honeybee/app/modules/Article/resources/scripts/article.init.js */

(function(e){var t=$(".controller-edit"),n=$(".container-list-data");1===t.length?midas.core.EditController.factory(".controller-edit"):1===n.length&&midas.list.ListController.create(".container-list-data",e).attach()})(midas.article);