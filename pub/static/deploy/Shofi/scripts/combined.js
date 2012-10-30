

/* /home/vagrant/projects/honeybee/app/modules/Shofi/resources/scripts/shofi.js */

var midas=midas||{};midas.shofi=midas.shofi||{};

/* /home/vagrant/projects/honeybee/app/modules/Shofi/resources/scripts/CategoriesListController.js */

midas.shofi.CategoriesListController=midas.list.ListController.extend({log_prefix:"CategoriesListController",vertical_dialog:null,init:function(e){this.parent(e),this.vertical_dialog=new midas.shofi.SelectVerticalDialog($(".modal-vertical-select").first(),this.options.vertical_batch.autocomplete_url)},assignVertical:function(e,t){var n=this,r=function(i){n.vertical_dialog.hide().reset().removeListener("vertical::selected",r);var s=!0===e?n.getSelectedItems():[t];n.createVerticalBatch(s,i).run()};this.vertical_dialog.on("vertical::selected",r).show()},createVerticalBatch:function(e,t){var n=new midas.list.ActionBatch;for(var r=0;r<e.length;r++)n.addAction(new midas.list.Action(this.createVerticalBatchPayload(e[r],t)));return n},createVerticalBatchPayload:function(e,t){var n=this.options.vertical_batch.post_url.replace("{TICKET}",e.ticket.id),r={vertical:t.id};return this.ajaxCurry(n,{detailItem:r},"post")},findDuplicates:function(e){var t=this.options.find_duplicates_url.replace(":CATEGORY:",e.data.identifier);window.location.href=t}});

/* /home/vagrant/projects/honeybee/app/modules/Shofi/resources/scripts/CategoryMatchingWidget.js */

midas.shofi.CategoryMatchingWidget=midas.core.BaseObject.extend({element:null,rows:null,autocomplete_options:null,init:function(e){this.element=e;var t=this.element.attr("data-category-options"),n=JSON.parse(t);this.rows=ko.observableArray([]),this.autocomplete_options=n.autocomplete;var r=this;$.each(n.mappings,function(t,n){r.rows.push(new midas.shofi.CategoryMatchingWidget.Row(e,t,n,r.autocomplete_options))}),ko.applyBindings(this,this.element[0]);for(var i=0;i<this.rows().length;i++)this.rows()[i].activate()}}),midas.shofi.CategoryMatchingWidget.Row=midas.core.BaseObject.extend({element:null,ext_category:null,ext_category_display:null,categories:null,selector:null,busy:null,init:function(e,t,n,r){this.element=e,n=n||[],r.tags=n,r.fieldname=t.toLowerCase().replace(/[\/\s:]/g,"-").replace("&","and"),r.tpl=midas.widgets.TagsList.TPL.INLINE,this.autocomplete_options=$.extend({},midas.widgets.TagsList.DEFAULT_OPTIONS,r),this.ext_category=ko.observable(t),this.busy=ko.observable(!1),this.categories=ko.observableArray(n||[]);var i=this;this.selector=ko.computed(function(){return r.fieldname+"-matches-row"}),this.ext_category_display=ko.computed(function(){var e=t.split(":");return e[0]+":<b>"+e[1]+"</b>"})},activate:function(){var e=this,t=$("."+this.selector()).first(),n=new midas.widgets.TagsList(t,this.autocomplete_options,function(){t.find(".tagslist-tag").each(function(e,t){0<e&&$(t).removeClass("btn-info")})});n.on("tagschanged",function(r,i){var s=[];for(var o=0;o<i.length;o++)s.push(i[o].value);var u={category:e.ext_category(),mappings:s};n.busyStart("Speichere Änderungen ...");var a=function(){n.busyEnd()};midas.core.Request.curry(location.href,u,"post")(a,a),t.find(".tagslist-tag").each(function(e,t){0<e?$(t).removeClass("btn-info"):$(t).addClass("btn-info")})})}});

/* /home/vagrant/projects/honeybee/app/modules/Shofi/resources/scripts/PlacesListController.js */

midas.shofi.PlacesListController=midas.list.ListController.extend({log_prefix:"PlacesListController",category_dialog:null,init:function(e){this.parent(e),this.category_dialog=new midas.shofi.SelectCategoryDialog(this.options.category_batch.autocomplete_url)},assignCategory:function(e,t){var n=this,r=function(t){n.category_dialog.hide().reset().removeListener("category::selected",r);var i=!0===e?n.getSelectedItems():[item];n.createCategoryBatch(i,t).run()};this.category_dialog.on("category::selected",r).show()},createCategoryBatch:function(e,t){var n=new midas.list.ActionBatch;for(var r=0;r<e.length;r++)n.addAction(new midas.list.Action(this.createCategoryBatchPayload(e[r],t)));return n},createCategoryBatchPayload:function(e,t){var n=this.options.category_batch.post_url.replace("{TICKET}",e.ticket.id),r={category:t.id};return this.ajaxCurry(n,{detailItem:r},"post")},createResolveConflictBatch:function(e,t){var n=new midas.list.ActionBatch;for(var r=0;r<e.length;r++)n.addAction(new midas.list.Action(this.createCategoryBatchPayload(e[r],t)));return n},createResolveConflictBatchPayload:function(e,t){var n=this.options.category_batch.post_url.replace("{TICKET}",e.ticket.id),r={category:t.id};return this.ajaxCurry(n,{detailItem:r},"post")},markDeduplicated:function(e,t){if(!e){alert("Orte zu deduplizieren wird nur über die Stapelverarbeitung unterstützt.");return}var n=this.options.dedup_url,r=new midas.list.ActionBatch,s=this.getSelectedItems();for(i=0;i<s.length;i++)r.addAction(new midas.list.Action(midas.core.Request.curry(n,{item_id:s[i].data.identifier},"post")));r.run()}});

/* /home/vagrant/projects/honeybee/app/modules/Shofi/resources/scripts/SelectCategoryDialog.js */

midas.shofi.SelectCategoryDialog=midas.core.BaseObject.extend({log_prefix:"SelectCategoryDialog",category_prompt:null,categories_uri:null,currently_valid:null,init:function(e){this.parent();var t=this;this.categories_uri=e,this.category_prompt=$("#batchAssignNewCategoryModal").twodal({show:!1,backdrop:!0,events:{categoryselect:this.onCategorySelected.bind(this)}}),this.currently_valid={},this.category_prompt.find("input").typeahead({property:"name",items:50,source:function(e,n){if(0>=n.length){e.process([]),t.currently_valid={};return}var r=midas.core.Request.curry(t.categories_uri.replace("{PHRASE}",n));r(function(n){var r=n.data;t.currently_valid={};for(var i=0;i<r.length;i++)t.currently_valid[r[i].name]=r[i].identifier;e.process(r)})}})},show:function(){return this.category_prompt.twodal("show"),this},hide:function(){return this.category_prompt.twodal("hide"),this},onCategorySelected:function(){var e=this.category_prompt.twodal("promptVal","input");this.validate(e)&&this.fire("category::selected",[{id:this.currently_valid[e],name:e}])},validate:function(e){return"undefined"!=typeof this.currently_valid[e]},reset:function(){return this.category_prompt.twodal("promptVal","input",""),this}});

/* /home/vagrant/projects/honeybee/app/modules/Shofi/resources/scripts/SelectVerticalDialog.js */

midas.shofi.SelectVerticalDialog=midas.core.BaseObject.extend({log_prefix:"SelectVerticalDialog",vertical_prompt:null,verticals_uri:null,currently_valid:null,init:function(e,t){this.parent();var n=this;this.verticals_uri=t,this.vertical_prompt=e.twodal({show:!1,backdrop:!0,events:{verticalselect:this.onVerticalSelected.bind(this)}}),this.currently_valid={},this.vertical_prompt.find("input").typeahead({property:"name",items:50,source:function(e,t){if(0>=t.length){e.process([]),n.currently_valid={};return}var r=midas.core.Request.curry(n.verticals_uri.replace("{PHRASE}",t));r(function(t){var r=t.data;n.currently_valid={};for(var i=0;i<r.length;i++)n.currently_valid[r[i].name]=r[i].identifier;e.process(r)})}})},show:function(){return this.vertical_prompt.twodal("show"),this},hide:function(){return this.vertical_prompt.twodal("hide"),this},onVerticalSelected:function(){var e=this.vertical_prompt.twodal("promptVal","input");this.validate(e)&&this.fire("vertical::selected",[{id:this.currently_valid[e],name:e}])},validate:function(e){return"undefined"!=typeof this.currently_valid[e]},reset:function(){return this.vertical_prompt.twodal("promptVal","input",""),this}});

/* /home/vagrant/projects/honeybee/app/modules/Shofi/resources/scripts/VerticalsListController.js */

midas.shofi.VerticalsListController=midas.list.ListController.extend({log_prefix:"VerticalsListController",init:function(e){this.parent(e)}});

/* /home/vagrant/projects/honeybee/app/modules/Shofi/resources/scripts/init.js */

(function(e){var t=$(".controller-edit"),n=$(".container-list-data"),r=$(".category-matching-widget");1===t.length?midas.core.EditController.factory(".controller-edit"):1===n.length?midas.list.ListController.create(".container-list-data",e).attach():1===r.length&&new midas.shofi.CategoryMatchingWidget(r.first())})(midas.shofi);