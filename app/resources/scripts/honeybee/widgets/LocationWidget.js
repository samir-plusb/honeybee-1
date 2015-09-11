honeybee.widgets.LocationAggregate = honeybee.widgets.Aggregate.extend({

    log_prefix: "LocationAggregate",

    initializing: null,

    init: function(element, options, ready_callback)
    {
        this.initializing = true;
        this.parent(element, options, ready_callback);
        this.initializing = false;
    },

    initGui: function()
    {
        this.parent();
        if (this.options.single_entry === true)
        {
            var controls_to_hide = [
                '.aggregate-selector',
                '.collapse-actions',
                '.aggregate .actions',
                '.aggregate .position'
            ];
            this.element.find(controls_to_hide.join(',')).hide();
            this.element.find('.aggregate').removeClass('collapsed');
        }

        this.bindMapResetToParentTabClick();
    },

    bindMapResetToParentTabClick: function()
    {
        var that = this;
        var $parent_tab = that.aggregate_list.parents('.tab-pane');
        var tab_id = $parent_tab.attr('id');
        $('.nav-tabs li a, .nav-list li a').each(function(idx, tab_item)
        {
            tab_item = $(tab_item);
            if (tab_item.attr('href').match('#'+tab_id))
            {
                tab_item.click(function()
                {
                    setTimeout(function()
                    {
                        that.aggregate_list.find('> .aggregate').each(function(idx, aggregate)
                        {
                            var widget;
                            if (aggregate.widgets.length > 0 && aggregate.widgets[0].map)
                            {
                                widget = aggregate.widgets[0];
                                widget.map.setCenter(new google.maps.LatLng(
                                    widget.location.lat(),
                                    widget.location.lon()
                                ));
                            }
                        });
                    }, 500);
                });
            }
        });
    },

    initAggregateListItem: function(aggregate_element)
    {
        this.registerAggregateEvents(aggregate_element);

        var that = this;
        var type = aggregate_element.find('.honeybee-js-type').val();
        var position = this.aggregate_list.find('> .aggregate').index(aggregate_element);
        var fields_section = aggregate_element.find('section.aggregate-fields');
        var location = {};

        if (!type.match(this.options.location_type))
        {
            return;
        }

        fields_section.find('input, textarea').each(function(idx, input)
        {
            input = $(input);
            location[input.attr('name').match(/\[([\w\d\-_]+)\](?!\[)/)[1]] = input.val();
        });
        var ready_callback = function()
        {
            that.registerDisplayedTextInputs(aggregate_element);
        };
        if (!this.initializing)
        {
            ready_callback = function()
            {
                var first_input = aggregate_element.find('input').first();
                first_input.focus();
                that.registerDisplayedTextInputs(aggregate_element);
                $('html, body').animate({scrollTop: first_input.offset().top}, 350);
            };
        }
        aggregate_element[0].widgets = [];

        aggregate_element[0].widgets.push(
            new honeybee.widgets.LocationWidget(
                fields_section, {
                    autobind: true,
                    localize_url: this.options.localize_url,
                    reverse_geocode: (this.options.reverse_geocode || false),
                    visible_coordinates: (this.options.visible_coordinates || false),
                    fieldname: this.options.fieldname + '[' + position + ']',
                    location: location
                },
                ready_callback
            )
        );

        aggregate_element.find('.actions').css({
            'margin-right': '70px',
            'z-index': 200
        });
    },

    addAggregate: function(module_item_markup)
    {
        var that = this;
        this.parent(module_item_markup);
    }
});

honeybee.widgets.LocationWidget = honeybee.widgets.Widget.extend({

    // #########################
    // #     property defs     #
    // #########################
    log_prefix: "LocationWidget",

    select_dialog: null,

    map_canvas: null,

    // <knockout_props>
    found_locations: null,

    fields: null,

    location: null,

    is_processing: null,

    fieldname: null,
    // </knockout_props>

    init: function(element, options, ready_callback)
    {
        this.parent(element, options, ready_callback);
    },

    // #################################
    // #     widget implementation     #
    // #################################
    getTemplate: function()
    {
        return 'static/widgets/LocationWidget.html';
    },

    initGui: function()
    {
        this.map_canvas = this.element.find('.map-canvas').first();
        this.parent();

        if (this.location && this.location.lon() && this.location.lat())
        {
            this.renderLocationOnMap();
        }
        else
        {
            this.setDefaultMap();
        }
    },

    initKnockoutProperties: function()
    {
        this.found_locations = ko.observableArray([]);
        this.is_processing = ko.observable(false);
        this.fieldname = ko.observable(this.options.fieldname);
        this.location = new honeybee.widgets.LocationWidget.Location(
            this.options.location || {}
        );
    },

    localize: function()
    {
        if (this.options.readonly)
        {
            return;
        }

        var req = honeybee.core.Request.curry(
            this.options.localize_url,
            this.location.values(), 'get', 'json'
        );
        this.is_processing(true);
        var that = this;
        req(function(data)
        {
            var location_count = data.location.items_count;
            that.found_locations([]);
            for (var i = 0; i < location_count; i++)
            {
                var loc = data.location[i];
                if (loc && "Außerhalb Berlins" != loc['administrative district'])
                {
                    loc.type = that.options.implementor;
                    that.found_locations.push(
                        honeybee.widgets.LocationWidget.Location.createFromServiceResp(loc)
                    );
                }
            }
            if (1 < that.found_locations().length)
            {
                that.showSelectLocationDialog();
            }
            else
            {
                that.onLocationSelected(that.found_locations()[0]);
            }
            that.is_processing(false);
        }, function()
        {
            that.is_processing(false);
        });
    },

    onLocationSelected: function(location)
    {
        if (! location)
        {
            return;
        }

        this.hideSelectLocationDialog();
        this.location.lon(location.lon());
        this.location.lat(location.lat());
        this.location.zipcode(location.zipcode());
        this.location.neighborhood(location.neighborhood());
        this.location.district(location.district());
        this.location.administrative_district(
            location.administrative_district()
        );

        if (location.street())
        {
           // this.location.street(location.street());
        }
        if (location.housenumber())
        {
            //this.location.housenumber(location.housenumber());
        }

        this.location.city(location.city());

        this.renderLocationOnMap();
    },

    showSelectLocationDialog: function()
    {
        if (! this.select_dialog)
        {
            this.select_dialog = this.element.find('.dialog-select-location').twodal({
                show: true,
                backdrop: true
            });
        }
        else
        {
            this.select_dialog.twodal('show');
        }
    },

    hideSelectLocationDialog: function()
    {
        if (this.select_dialog)
        {
            this.select_dialog.twodal('hide');
        }
    },

    setDefaultMap: function()
    {
        this.map = new google.maps.Map(this.map_canvas[0], {
            zoom: 6,
            center: new google.maps.LatLng(52.519564, 13.408813),
            sensor:false,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        });

        if (! this.options.readonly)
        {
            this.registerGeoCoder();
        }
    },

    renderLocationOnMap: function()
    {
        this.marker = new google.maps.Marker({
            position: new google.maps.LatLng(
                this.location.lat(),
                this.location.lon()
            ),
            title: this.location.name()
        });
        this.map = new google.maps.Map(this.map_canvas[0], {
            zoom: 16,
            center: this.marker.getPosition(),
            sensor:false,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        });
        this.marker.setMap(this.map);

        if (!this.options.readonly)
        {
            this.registerGeoCoder();
        }
    },

    registerGeoCoder: function()
    {
        if (this.options.reverse_geocode === true) {
            var geocoder = new google.maps.Geocoder();
        }

        var that = this;
        google.maps.event.addListener(this.map, 'click', function(event)
        {
            that.marker.setPosition(event.latLng);
            that.location.setLat(event.latLng.lat());
            that.location.setLon(event.latLng.lng());

            // only do reverse geocoding if appropriate
            if (that.options.reverse_geocode === true)
            {
                that.is_processing(true);
                geocoder.geocode({'latLng': event.latLng}, function(results, status)
                {
                    if (status == google.maps.GeocoderStatus.OK)
                    {
                        if (results[0])
                        {
                            var components = results[0]['address_components'];
                            var data = {};
                            for (var i = 0; i < components.length; i++)
                            {
                                var type = components[i].types[0];
                                data[type] = components[i].long_name;
                            }

                            that.onLocationSelected(
                                honeybee.widgets.LocationWidget.Location.createFromServiceResp({
                                    street: data.route,
                                    housenumber: data.street_name,
                                    uzip: data.postal_code,
                                    city: data.locality,
                                    lat: event.latLng.lat(),
                                    lon: event.latLng.lng()
                                })
                            );
                            that.is_processing(false);
                        }
                    }
                    else
                    {
                        alert("Geocoder failed due to: " + status);
                    }
                });
            }
        });
    }
});

// #####################
// #     constants     #
// #####################
honeybee.widgets.LocationWidget.DEFAULT_OPTIONS = {
    autobind: true,
    localize_url: '',
    reverse_geocode: false,
    location: null
};

honeybee.widgets.LocationWidget.Location = honeybee.core.BaseObject.extend({

    name: null,

    street: null,

    housenumber: null,

    zipcode: null,

    name_detail: null,

    description: null,

    city: null,

    homepage: null,

    mail: null,

    openinghours: null,

    administrative_district: null,

    district: null,

    neighborhood: null,

    lon: null,

    lat: null,

    init: function(l)
    {
        this.name = ko.observable(l.name || '');
        this.street = ko.observable(l.street || '');
        this.housenumber = ko.observable(l.housenumber || '');
        this.name_detail = ko.observable(l.name_detail || '');
        this.description = ko.observable(l.description || '');
        this.mail = ko.observable(l.mail || '');
        this.homepage = ko.observable(l.homepage || '');
        this.openinghours = ko.observable(l.openinghours || '');
        this.city = ko.observable(l.city || '');
        this.zipcode = ko.observable(l.postalCode || l.zipcode || '');
        this.administrative_district = ko.observable(l.administrativeDistrict || '');
        this.district = ko.observable(l.district || '');
        this.neighborhood = ko.observable(l.neighborhood || '');
        if (l.coordinates)
        {
            this.lon = ko.observable(l.coordinates.lon || '');
            this.lat = ko.observable(l.coordinates.lat || '');
        }
        else
        {
            this.lon = ko.observable(l.lon || '');
            this.lat = ko.observable(l.lat || '');
        }
    },

    values: function()
    {
        var fields = ['street', 'housenumber', 'city', 'zipcode'];
        var values = {};
        for (var i = 0; i < fields.length; i++)
        {
            var val = this[fields[i]]().trim();
            if (val)
            {
                values[fields[i]] = val;
            }
        }
        return values;
    },

    /**
     * @param google.maps.LatLng
     */
    setCoordinates: function(coord)
    {
        this.setLat(coord.lat);
        this.setLon(coord.lng);
    },

    setLat: function(lat)
    {
        var latitude = lat || '';
        this.lat(+latitude.toFixed(6));
    },

    setLon: function(lon)
    {
        var longitude = lon || '';
        this.lon(+longitude.toFixed(6));
    }
});

honeybee.widgets.LocationWidget.Location.createFromServiceResp = function(location)
{
    var data = {
        district: location.district || null,
        administrativeDistrict: location['administrative district'] || null,
        neighborhood: location.neighborhood || null,
        postalCode: location.uzip || null,
        street: location.street || null,
        city: location.city || null,
        housenumber: location.housenumber || null,
        type: location.type || null
    };

    if (location.latitude && location.longitude)
    {
        data.lon = location.longitude;
        data.lat = location.latitude;
    }
    else
    {
        data.lon = location.lon;
        data.lat = location.lat;
    }
    return new honeybee.widgets.LocationWidget.Location(data);
};
