midas.widgets.LocationWidget = midas.widgets.Widget.extend({

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
        return 'js/midas/templates/LocationWidget.html';
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
        this.location = new midas.widgets.LocationWidget.Location(
            this.options.location || {}
        );
    },

    localize: function()
    {
        var req = midas.core.Request.curry(
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
                        midas.widgets.LocationWidget.Location.createFromServiceResp(loc)
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
        this.location.postal_code(location.postal_code());
        this.location.neighborhood(location.neighborhood());
        this.location.district(location.district());
        this.location.administrative_district(
            location.administrative_district()
        );
        if (! this.location.street())
        {
            this.location.street(location.street());
        }
        if (! this.location.city())
        {
            this.location.city(location.city());
        }
        if (! this.location.housenumber())
        {
            this.location.housenumber(location.housenumber());
        }
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
        var myOptions = {
            zoom: 16,
            center: latlng,
            sensor:false,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        var map = new google.maps.Map(this.map_canvas[0], myOptions);
        marker.setMap(map);
    }
});

// #####################
// #     constants     #
// #####################
midas.widgets.LocationWidget.DEFAULT_OPTIONS = {
    autobind: true,
    localize_url: '',
    location: null
};

midas.widgets.LocationWidget.Location = midas.core.BaseObject.extend({

    name: null,

    street: null,

    housenumber: null,

    details: null,

    postal_code: null,

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
        this.postal_code = ko.observable(l.postalCode || '');
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
            this.longitude = ko.observable('');
            this.latitude = ko.observable('');
        }
    },

    values: function()
    {
        var fields = ['street', 'housenumber', 'city', 'postal_code'];
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

midas.widgets.LocationWidget.Location.createFromServiceResp = function(location)
{
    var data = {
        district: location.district || null,
        administrativeDistrict: location['administrative district'] || null,
        neighborhood: location.neighborhood || null,
        postalCode: location.uzip || null,
        street: location.street || null
    };
    if (location.latitude && location.longitude)
    {
        data.coordinates = {
            lon: location.longitude,
            lat: location.latitude
        };
    }
    return new midas.widgets.LocationWidget.Location(data);
};
