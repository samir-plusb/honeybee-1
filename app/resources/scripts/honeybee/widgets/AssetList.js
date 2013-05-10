/**
 * honeybee.widgets
 **/
honeybee.widgets.AssetList = honeybee.widgets.Widget.extend({
    
    log_prefix: "AssetList",

    assets: null,

    fieldname: null,

    uploader: null,

    ichie: null,

    area_of_interest: null,

    aoi_enabled: null,

    start_active: null,

    aoi_scan_active: null,

    cur_aoi_request: null,

    popover_pos: null,

    dropzone: null,

    cur_file_is_image: null,

    face_detect_feat_on: null,

    init: function(element, options)
    {
        this.parent(element, options);
    },

    getTemplate: function()
    {
        return 'static/widgets/AssetList.html';
    },

    initGui: function()
    {
        this.parent();

        this.initUploader();
        this.initDialog();

        var that = this;
        this.ichie = window.IchieJs.create({
            main_container: this.element.find('.ichiejs-main-stage')[0],
            width: 600,
            height: 338,
            onSelectionChanged: function(selection)
            {
                var asset;
                if (that.dialog && (asset = that.dialog.data('cur_asset')))
                {
                    that.applyAoiSelection(selection);
                }
            }
        });
        
        /*this.element.find('ul').sortable().bind('sortupdate', function() {
            console.log('Yay, we\'ve a new sorting!');
        });*/

        this.element.find('.asset-item').popover();
    },

    initKnockoutProperties: function()
    {
        this.fieldname = ko.observable(this.options.fieldname);
        this.aoi_enabled = ko.observable(false);
        this.start_active = ko.observable(false);
        this.cur_file_is_image = ko.observable(false);
        this.aoi_scan_active = ko.observable(false);
        this.face_detect_feat_on = ko.observable(false);
        this.area_of_interest = ko.observableArray([]);
        this.popover_pos = ko.observable(this.options.popover_pos || 'top');
        var assets = this.options.assets || [];
        this.assets = ko.observableArray([]);
        for (var i = 0; i < assets.length; i++)
        {
            this.assets.push(
                new honeybee.widgets.AssetList.Asset(assets[i])
            );
        }
        this.dropzone = {
            dropzone: true, 
            show: ko.observable(true), 
            label: ko.observable('Datei(en) hier ablegen') 
        };

        this.assets.push(this.dropzone);
    },

    initUploader: function()
    {
        var that = this;
        this.uploader = new honeybee.widgets.AssetList.FileUploader({
            drop_target: this.element.find('.dropzone').first(),
            put_url: this.options.put_url,
            allowed_types: this.options.allowed_types
        });

        this.uploader.on('upload::start', function(asset)
        {
            that.element.find('.asset-item').popover('destroy');
            that.assets.splice(that.assets().length - 1, 0, asset);
            that.element.find('.asset-item').popover();
            if (that.options.max <= that.assets().length - 1)
            {
                that.uploader.enabled = false;
                that.dropzone.show(false);
            }
        }).on('upload::progress', function(asset, progress)
        {
            asset.progress(progress * 100);
        }).on('upload::complete', function(asset, data)
        {
            that.element.find('.asset-list').sortable();
            asset.id(data.identifier);
            asset.url(data.url);
            asset.mime_type = data.mimeType;
        });

        if (that.options.max <= that.assets().length - 1)
        {
            that.uploader.enabled = false;
            that.dropzone.show(false);
        }
    },

    initDialog: function()
    {
        var that = this;
        this.dialog = $('.modal-edit-asset').first().twodal({
            backdrop: true,
            show: false,
            onhidden: function()
            {

            },
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
        this.area_of_interest(asset.aoi || [0, 0, 20, 20]);

        var that = this;

        this.cur_file_is_image(false);
        var supported_img_types = ['jpg', 'png', 'gif', 'jpeg'];
        for (var i = 0; i < supported_img_types.length; i++)
        {
            if (-1 !== asset.mime_type.indexOf(supported_img_types[i]))
            {
                this.cur_file_is_image(true);
                break;
            }
        }

        this.aoi_enabled(false);
        this.ichie.launch(asset.url(), function(){
            if (asset.aoi)
            {
                that.ichie.setSelection({
                    left: asset.aoi[0],
                    top: asset.aoi[1],
                    right: asset.aoi[0] + asset.aoi[2],
                    bottom: asset.aoi[1] + asset.aoi[3]
                });
                that.aoi_enabled(that.cur_file_is_image());
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
        this.element.find('.asset-item').popover('destroy');
        this.assets.remove(asset);
        this.element.find('.asset-item').popover();

        if (this.options.max > this.assets().length - 1)
        {
            this.uploader.enabled = true;
            this.dropzone.show(true);
        }
    },

    runFaceDetection: function()
    {
        asset = this.dialog.data('cur_asset') || asset;
        var that = this;
        this.aoi_scan_active(true);

        var req = honeybee.core.Request.curry(this.options.aoi_url+'?aid='+asset.id());
        this.cur_aoi_request = req(
            function(resp)
            {
                that.aoi_scan_active(false);
                var aoi = resp.aoi;
                that.ichie.setSelection({
                    left: aoi[0],
                    top: aoi[1],
                    right: aoi[2],
                    bottom: aoi[3]
                });
            }, function(err)
            {
                that.aoi_scan_active(false);
            }
        );
    },

    abortFaceDetection: function()
    {
        if (this.cur_aoi_request)
        {
            this.cur_aoi_request.abort();
        }
    },

    storeMetaData: function(asset, meta_data)
    {
        var that = this;
        var updateData = {
            aid: asset.id(),
            metaData: meta_data
        };
        var req = honeybee.core.Request.curry(this.options.post_url, updateData, 'post');
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
        if (true === this.aoi_enabled())
        {
            this.area_of_interest.splice(
                0, 4,
                Math.floor(selection.left),
                Math.ceil(selection.top),
                Math.ceil(selection.right - selection.left),
                Math.ceil(selection.bottom - selection.top)
            );
        }
    }
});

honeybee.widgets.AssetList.Asset = honeybee.core.BaseObject.extend({

    id: null,

    mime_type: null,

    url: null,

    name: null,

    caption: null,

    caption_txt: null,

    copyright: null,

    copyright_txt: null,

    copyright_url: null,

    copyright_url_txt: null,

    popover_content: null,

    width: null,

    height: null,

    x: null,

    y: null,

    aoi: null,

    aoi_enabled: null,

    has_id: null,

    progress: null,

    is_loading: null,

    init: function(data)
    {
        this.parent();
        this.initKnockoutProperties(data);
        this.mime_type = data.mimeType;
        this.setAoi(data.aoi);
    },

    setAoi: function(aoi)
    {
        this.aoi = null;
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
        var that = this;

        this.id = ko.observable(data.id || '');
        this.is_loading = ko.observable(false);

        this.url = ko.observable(data.url);
        this.name = ko.observable(data.name);
        this.caption = ko.observable(data.caption || '');

        this.aoi_enabled = ko.observable(!!data.aoi);
        this.progress = ko.observable(0);
        
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

        this.popover_content = ko.computed(function()
        {
            return '<p><b>Titel</b> ' + that.caption_txt() + 
                '</p><p><b>Copyright</b> ' + that.copyright_txt() +
                '</p><p><b>Copyright Url</b> ' + that.copyright_url_txt()
        });

        var cell_width = 140;
        var cell_height = 130;
        var width = data.width || cell_width;
        var height = data.height || cell_height;
        var ratio = width / height;

        if (ratio > 1 && width > cell_width)
        {
            width = cell_width;
            height = width / ratio;
        }
        else if(ratio <= 1 && height > cell_height)
        {
            height = cell_height;
            width = ratio * height;
        }

        this.height = ko.observable(height);
        this.width = ko.observable(width);
        this.x = ko.observable(
            Math.floor((cell_width - width) / 2)
        );
        this.y = ko.observable(
            Math.floor((cell_height - height) / 2)
        );
    }
});

honeybee.widgets.AssetList.FileUploader = honeybee.core.BaseObject.extend({

    log_prefix: 'FileUploader',

    put_url: null,

    enabled: null,

    drop_target: null,

    queue: null,

    is_running: null,

    supported_image_types: ['image/jpeg', 'image/png', 'image/gif'],

    supported_doc_types: ['application/pdf'],

    supported_audio_types: ['audio/mp2', 'audio/mp3', 'audio/mpg', 'audio/mpeg'],

    allowed_types: null,

    init: function(options)
    {
        this.parent();

        this.queue = [];
        this.enabled = true;
        this.put_url = options.put_url;
        this.allowed_types = options.allowed_types;
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
            var file = files[i];
            var is_allowed = (-1 !== this.allowed_types.indexOf(file.type));    

            if (is_allowed)
            {
                this.queue.push(file);
            }           
            else
            {
                alert("File type: '" + file.type + "'' not supported.");
            }
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
        var is_image = (-1 !== this.supported_image_types.indexOf(file.type));
        var is_doc = (-1 !== this.supported_doc_types.indexOf(file.type));
        var is_audio = (-1 !== this.supported_audio_types.indexOf(file.type));
        if (-1 === this.allowed_types.indexOf(file.type))
        {
            alert("File type not supported and should not have landed in the queue in the first place.");
            return;
        }

        var img = new Image;

        img.onload = function() 
        {
            var fd = new FormData();
            var asset = null;

            fd.append("asset", file);

            if (is_image)
            {
                asset = new honeybee.widgets.AssetList.Asset({
                    name: file.name,
                    url: result,
                    width: img.width,
                    height: img.height
                });
            }
            else
            {
                asset = new honeybee.widgets.AssetList.Asset({
                    name: file.name,
                    url: img.src,
                    width: img.width,
                    height: img.height
                });
            }

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
            }, false);

            that.fire('upload::start', [ asset ]);  

            xhr.open('post', that.put_url, true);
            xhr.setRequestHeader("Accept", "application/json");                                                                                              
            xhr.send(fd);
        };

        if (is_image)
        {
            img.src = result;
        }
        else if (is_doc)
        {
            img.src = 'static/deploy/_global/binaries/pdficon_large.png';
        }
        else if (is_audio)
        {
            console.log("waaat?!");
            img.src = 'static/deploy/_global/binaries/audio.png';
        }
    }
});


honeybee.widgets.AssetList.DEFAULT_OPTIONS = {
    autobind: true,
    fieldname: '',
    assets: null,
    post_url: '',
    put_url: '',
    max: 50,
    allowed_types: [ 'image/jpeg', 'image/png', 'image/gif' ]
};
