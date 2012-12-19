

/* /home/vagrant/projects/honeybee-showcase/app/modules/Person/resources/scripts/person.namespace.js */

var midas=midas||{};midas.person=midas.person||{};

/* /home/vagrant/projects/honeybee-showcase/app/modules/Person/resources/scripts/person.ListController.js */

midas.person.PersonListController=midas.list.ListController.extend({log_prefix:"PersonListController",init:function(e){this.parent(e)},assignCategory:function(e,t){var n=this,r=function(t){n.category_dialog.hide().reset().removeListener("category::selected",r);var i=!0===e?n.getSelectedItems():[item];n.createCategoryBatch(i,t).run()};this.category_dialog.on("category::selected",r).show()},createCategoryBatch:function(e,t){var n=new midas.list.ActionBatch;for(var r=0;r<e.length;r++)n.addAction(new midas.list.Action(this.createCategoryBatchPayload(e[r],t)));return n},createCategoryBatchPayload:function(e,t){var n=this.options.category_batch.post_url.replace("{TICKET}",e.ticket.id),r={category:t.id};return this.ajaxCurry(n,{detailItem:r},"post")}});

/* /home/vagrant/projects/honeybee-showcase/app/modules/Person/resources/scripts/person.init.js */

(function(e){var t=function(){var e=$(".controller-edit .form-actions"),t=$(".footer"),n=e.height(),r=$(document).height(),i=function(){var r=t.offset().top,i=e.offset(),s=i.top+n,o=$(document).scrollTop()+$(window).height(),u=o-r;u>=0?(e.removeClass("overlayed"),e.css("bottom",u-t.height()+3)):(e.addClass("overlayed"),e.css("bottom","-20px"))};$(document).scroll(i),$('a[data-toggle="tab"]').on("shown",i),$(window).resize(i)},n=$(".controller-edit"),r=$(".container-list-data");1===n.length?(midas.core.EditController.factory(".controller-edit"),t()):1===r.length&&midas.list.ListController.create(".container-list-data",e).attach()})(midas.person);