honeybee.widgets.LocationWidget = honeybee.widgets.Widget.extend({

    // #########################
    // #     property defs     #
    // #########################
    log_prefix: "LocationWidget",

    select_dialog:null,

    map_canvas: null,

    // <knockout_props>
    found_locations: null,

    fields: null,

    location: null,

    is_processing: null,

    fieldname: null,
    // </knockout_props>

    init: function(element, options)
    {
        this.parent(element, options);
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

        if (this.location)
        {
            this.renderLocationOnMap();
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
        this.location.longitude(location.longitude());
        this.location.latitude(location.latitude());
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

    renderLocationOnMap: function()
    {
        var latlng = new google.maps.LatLng(
            this.location.latitude(),
            this.location.longitude()
        );
        var marker = new google.maps.Marker({
            position: latlng,
            title: this.location.name()
        });

        var map = new google.maps.Map(this.map_canvas[0], {
            zoom: 16,
            center: latlng,
            sensor:false,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        });
        marker.setMap(map);

        var geocoder = new google.maps.Geocoder();

        var that = this;
        if (! this.options.readonly)
        {
            google.maps.event.addListener(map, 'click', function(event) {
                marker.setPosition(event.latLng);
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
                                    latitude: event.latLng.lat(),
                                    longitude: event.latLng.lng()
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
            });
        }
    }
});

// #####################
// #     constants     #
// #####################
honeybee.widgets.LocationWidget.DEFAULT_OPTIONS = {
    autobind: true,
    localize_url: '',
    location: null
};

honeybee.widgets.LocationWidget.Location = honeybee.core.BaseObject.extend({

    name: null,

    street: null,

    housenumber: null,

    details: null,

    zipcode: null,

    city: null,

    administrative_district: null,

    district: null,

    neighborhood: null,

    longitude: null,

    latitude: null,

    init: function(l)
    {
        this.name = ko.observable(l.name || '');
        this.street = ko.observable(l.street || '');
        this.housenumber = ko.observable(l.housenumber || '');
        this.details = ko.observable(l.details || '');
        this.city = ko.observable(l.city || '');
        this.zipcode = ko.observable(l.postalCode || l.zipcode || '');
        this.administrative_district = ko.observable(l.administrativeDistrict || '');
        this.district = ko.observable(l.district || '');
        this.neighborhood = ko.observable(l.neighborhood || '');
        if (l.coordinates)
        {
            this.longitude = ko.observable(l.coordinates.lon || '');
            this.latitude = ko.observable(l.coordinates.lat || '');
        }
        else
        {
            this.longitude = ko.observable(l.lon || '');
            this.latitude = ko.observable(l.lat || '');
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
        housenumber: location.housenumber || null
    };
    if (location.latitude && location.longitude)
    {
        data.coordinates = {
            lon: location.longitude,
            lat: location.latitude
        };
    }
    return new honeybee.widgets.LocationWidget.Location(data);
};
