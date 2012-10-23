midas.widgets.AssetList = midas.widgets.Widget.extend({
    
    // #########################
    // #     property defs     #
    // #########################
    log_prefix: "AssetList",

    assets: null,

    fieldname: null,

    uploader: null,

    ichie: null,

    area_of_interest: null,

    aoi_enabled: null,

    start_active: null,

    init: function(element, options)
    {
        this.parent(element, options);
    },

    // #################################
    // #     widget implementation     #
    // #################################
    getTemplate: function()
    {
        return 'js/midas/templates/AssetList.html';
    },

    initGui: function()
    {
        this.parent();
        var that = this;
        this.ichie = window.IchieJs.create({
            main_container: this.element.find('.ichiejs-main-stage')[0],
            width: 500,
            height: 385,
            onSelectionChanged: function(selection)
            {
                var asset;
                if (that.dialog && (asset = that.dialog.data('cur_asset')))
                {
                    that.applyAoiSelection(selection);
                }
            }
        });
        this.element.find('ul').sortable().bind('sortupdate', function() {
            console.log('Yay, we\'ve a new sorting!');
        });;
    },

    initKnockoutProperties: function()
    {
        this.fieldname = ko.observable(this.options.fieldname);
        this.aoi_enabled = ko.observable(false);
        this.start_active = ko.observable(false);
        this.area_of_interest = ko.observableArray([0, 0, 0, 0]);
        var assets = this.options.assets || [];
        this.assets = ko.observableArray([]);
        for (var i = 0; i < assets.length; i++)
        {
            this.assets.push(
                new midas.widgets.AssetList.Asset(assets[i])
            );
        }

        this.initUploader();
        this.initDialog();
    },

    initUploader: function()
    {
        var that = this;
        this.uploader = new midas.widgets.AssetList.FileUploader({
            drop_target: this.element.find('.asset-list').first(),
            put_url: this.options.put_url
        });
        this.uploader.on('upload::start', function(asset)
        {
            that.assets.push(asset);
            if (that.options.max <= that.assets().length)
            {
                that.uploader.enabled = false;
            }
        });
        this.uploader.on('upload::progress', function(asset, progress)
        {
            asset.progress(progress * 100);
        });
        this.uploader.on('upload::complete', function(asset, data)
        {
            that.element.find('.asset-list').sortable();
            asset.id(data.identifier);
            asset.url(data.url);
        });
        if (that.options.max <= that.assets().length)
        {
            that.uploader.enabled = false;
        }
    },

    initDialog: function()
    {
        var that = this;
        this.dialog = $('.modal-edit-asset').first().twodal({
            backdrop: true,
            show: false,
            events: {
                onstoredata: function()
                {
                    var asset = that.dialog.data('cur_asset');
                    if (! asset)
                    {
                        alert("AssetList widget state is invalid, no current asset found!");
                    }
                    that.storeMetaData(
                        asset, {
                            'caption': that.dialog.twodal('promptVal', '.input-caption'),
                            'copyright': that.dialog.twodal('promptVal', '.input-copyright'),
                            'copyright_url': that.dialog.twodal('promptVal', '.input-copyright-url'),
                            'aoi': that.aoi_enabled() ? that.area_of_interest() : null
                        }
                    );
                    that.dialog.twodal('hide');
                }
            }
        });
    },

    onEditClicked: function(asset)
    {
        this.dialog.data('cur_asset', asset);
        this.dialog.twodal('promptVal', '.input-caption', asset.caption());
        this.dialog.twodal('promptVal', '.input-copyright', asset.copyright());
        this.dialog.twodal('promptVal', '.input-copyright-url', asset.copyright_url());

        var that = this;
        this.ichie.launch(asset.url(), function(){
            if (asset.aoi)
            {
                that.ichie.setSelection({
                    left: asset.aoi[0],
                    top: asset.aoi[1],
                    right: asset.aoi[0] + asset.aoi[2],
                    bottom: asset.aoi[1] + asset.aoi[3]
                });
                that.aoi_enabled(true);
                that.start_active(false); // hack, make sure we trigger a value -change
                that.start_active(true); // which would not be the case if start_active was true.
                that.ichie.showSelection();
            }
            else
            {
                that.ichie.setSelection({
                    left: 60,
                    top: 60,
                    right: 200,
                    bottom: 200
                });
                that.aoi_enabled(false);
                that.start_active(true); // hack, make sure we trigger a value -change
                that.start_active(false); // which would not be the case if start_active was false.
                that.ichie.hideSelection();
            }
        });

        this.dialog.twodal('show');
    },

    onDeleteClicked: function(asset)
    {
        this.assets.remove(asset);
        if (this.options.max > this.assets().length)
        {
            this.uploader.enabled = true;
        }
    },

    storeMetaData: function(asset, meta_data)
    {
        var updateData = {
            aid: asset.id(),
            metaData: meta_data
        };
        var req = midas.core.Request.curry(this.options.post_url, updateData, 'post');
        asset.is_loading(true);
        req(function(resp)
        {
            var newData = resp.data.asset.metaData;
            asset.caption(newData.caption || '');
            asset.copyright_url(newData.copyright_url || '');
            asset.copyright(newData.copyright || '');
            asset.setAoi(newData.aoi);
            asset.is_loading(false);
        },
        function(resp)
        {
            asset.is_loading(false);
        });
    },

    toggleAoiSelection: function()
    {
        this.aoi_enabled(! this.aoi_enabled());
        this.ichie[this.aoi_enabled() ? 'showSelection' : 'hideSelection']();
    },

    applyAoiSelection: function(selection)
    {
        this.area_of_interest.splice(
            0, 4,
            Math.floor(selection.left),
            Math.ceil(selection.top),
            Math.ceil(selection.right - selection.left),
            Math.ceil(selection.bottom - selection.top)
        );
    }
});

midas.widgets.AssetList.Asset = midas.core.BaseObject.extend({

    id: null,

    url: null,

    name: null,

    caption: null,

    caption_txt: null,

    copyright: null,

    copyright_txt: null,

    copyright_url: null,

    copyright_url_txt: null,

    aoi: null,

    aoi_enabled: null,

    has_id: null,

    progress: null,

    is_loading: null,

    init: function(data)
    {
        this.parent();
        this.initKnockoutProperties(data);
        this.setAoi(data.aoi);
    },

    setAoi: function(aoi)
    {
        if ($.isArray(aoi))
        {
            this.aoi = [];
            for (var i = 0; i < 4; i++)
            {
                this.aoi[i] = +aoi[i];
            }
        }
    },

    initKnockoutProperties: function(data)
    {
        this.id = ko.observable(data.id || '');
        this.is_loading = ko.observable(false);
        this.url = ko.observable(data.url);
        this.name = ko.observable(data.name);
        this.caption = ko.observable(data.caption || '');

        this.aoi_enabled = ko.observable(!!data.aoi);
        this.progress = ko.observable(0);

        var that = this;
        this.caption_txt = ko.computed(function()
        {
            return 0 === that.caption().length ? ' - ' : that.caption();
        });
        this.copyright = ko.observable(data.copyright || '');
        this.copyright_txt = ko.computed(function()
        {
            return 0 === that.copyright().length ? ' - ' : that.copyright();
        });
        this.copyright_url = ko.observable(data.copyright_url || '');
        this.copyright_url_txt = ko.computed(function()
        {
            return 0 === that.copyright_url().length ? ' - ' : that.copyright_url();
        });
        this.has_id = ko.computed(function()
        {
            return !! that.id();
        });
    }
});

midas.widgets.AssetList.FileUploader = midas.core.BaseObject.extend({

    log_prefix: 'FileUploader',

    put_url: null,

    enabled: null,

    drop_target: null,

    queue: null,

    is_running: null,

    init: function(options)
    {
        this.parent();

        this.queue = [];
        this.enabled = true;
        this.put_url = options.put_url;
        this.is_running = false;
        this.drop_target = $(options.drop_target);
        this.initDragEvents();
    },

    initDragEvents: function()
    {
        var that = this;
        var dropbox = this.drop_target[0];
        var body = $(document.body);
        document.addEventListener('dragenter', function(evt) {
            evt.stopPropagation();                                                                                                                                                          
            evt.preventDefault();
            if (that.enabled)
            {
                body.addClass('drag-enabled');
            }
        }, false);
        document.addEventListener('dragexit', function(evt) {
            evt.stopPropagation();                                                                                                                                                          
            evt.preventDefault();    
            body.removeClass('drag-enabled');
        }, false);
        document.addEventListener('drop', function(evt) {
            evt.stopPropagation();                                                                                                                                                          
            evt.preventDefault();
            body.removeClass('drag-enabled');
        }, false);
        document.addEventListener('dragover', function(evt) {
            evt.stopPropagation();                                                                                                                                                          
            evt.preventDefault();    
        }, false);
        dropbox.addEventListener('dragover', this.dragHover.bind(this), false);
        dropbox.addEventListener('drop', this.dragDropped.bind(this), false);
    },

    dragHover: function(evt) 
    {                         
        evt.stopPropagation();                                                                                                                                                          
        evt.preventDefault();
    }, 
                                                                                                                                                                       
    dragDropped: function(evt) 
    {                             
        $(document.body).removeClass('drag-enabled');
        if (this.enabled)
        {
            evt.stopPropagation();                                       
            evt.preventDefault();
            var files = evt.dataTransfer.files;                                                                                                                                 
            if (0 < files.length) 
            {                                                                                                                                                          
                this.handleFiles(files);                                                                                                                                                    
            }   
        }
    },

    handleFiles: function(files) 
    {
        var that = this;
        for (i = 0; i < files.length; i++) 
        {               
            this.queue.push(files[i]);                                                                                                                                     
        }
        if (! this.is_running)
        {
            this.shiftQueue();
        }                                                                                                                  
    },                                                                                                                                                                              
                                                                                                                                                                                   
    handleReaderProgress: function(evt) 
    {                         
        if (evt.lengthComputable) 
        {                   
            this.fire('readfile::progress', [ (evt.loaded / evt.total) ]);                                                                                                                                  
        }
    },                                                                                                                                                                              
                                                                                                                                                                                  
    handleReaderLoadComplete: function(evt, file)
    {            
        this.fire('readfile::complete', [ file ]);
        this.uploadFile(file, evt.target.result);
    },

    shiftQueue: function()
    {
        var that = this;

        if (! this.enabled)
        {
            this.queue = [];
            this.is_running = false;
            return;
        }
        
        var file = this.queue.shift();
        if (file)
        {
            this.is_running = true;
            var uploadReader = new FileReader();
            uploadReader.onloadend = function(evt)
            {
                that.handleReaderLoadComplete(evt, file);
            };
            uploadReader.onprogress = function(evt)
            {
                that.handleReaderProgress(evt, file);
            };
            uploadReader.readAsDataURL(file);
        }
        else
        {
            this.is_running = false;
        }
    },

    uploadFile: function(file, result)
    {
        var that = this;

        var fd = new FormData();
        fd.append("asset", file);
        var asset = new midas.widgets.AssetList.Asset({
            name: file.name,
            url: result
        });

        xhr = new XMLHttpRequest();
        xhr.onload = function(evt)
        {
            if (xhr.status == 200) 
            {  
                var json = JSON.parse(xhr.responseText);
                that.fire('upload::complete', [ asset, json.data ]);
                that.shiftQueue();
            }
        };
        xhr.upload.addEventListener("progress", function(evt)
        {
            if (evt.lengthComputable)
            {                                                                                                                                                 
                that.fire('upload::progress', [ asset, (evt.loaded / evt.total) ]);                                                                                                
            }
        }, false)

        this.fire('upload::start', [ asset ]);  

        xhr.open('post', this.put_url, true);
        xhr.setRequestHeader("Accept", "application/json");                                                                                                 
        xhr.send(fd);
    }
});


// #####################
// #     constants     #
// #####################
midas.widgets.AssetList.DEFAULT_OPTIONS = {
    autobind: true,
    fieldname: '',
    assets: null,
    post_url: '',
    put_url: '',
    max: 50
};