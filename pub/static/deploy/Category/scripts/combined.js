

/* /home/vagrant/projects/honeybee-showcase/app/modules/Category/resources/scripts/category.namespace.js */

var midas=midas||{};midas.category=midas.category||{};

/* /home/vagrant/projects/honeybee-showcase/app/modules/Category/resources/scripts/category.ListController.js */

midas.category.CategoryListController=midas.list.ListController.extend({log_prefix:"CategoryListController",init:function(e){this.parent(e)}});

/* /home/vagrant/projects/honeybee-showcase/app/modules/Category/resources/scripts/category.init.js */

(function(e){var t=function(){var e=$(".controller-edit .form-actions"),t=$(".footer"),n=e.height(),r=$(document).height(),i=function(){var r=t.offset().top,i=e.offset(),s=i.top+n,o=$(document).scrollTop()+$(window).height(),u=o-r;u>=0?(e.removeClass("overlayed"),e.css("bottom",u-t.height()+3)):(e.addClass("overlayed"),e.css("bottom","-20px"))};$(document).scroll(i),$('a[data-toggle="tab"]').on("shown",i),$(window).resize(i)},n=$(".controller-edit"),r=$(".container-list-data");1===n.length?(midas.core.EditController.factory(".controller-edit"),t()):1===r.length&&midas.list.ListController.create(".container-list-data",e).attach()})(midas.category);