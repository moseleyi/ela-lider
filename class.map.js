(function(w, d, s, u) {
    var a = d.createElement(s);
    var m = d.getElementsByTagName("script")[0];
    a.src = u;
    m.parentNode.insertBefore(a, m);
})(window, document, 'script', 'https://maps.googleapis.com/maps/api/js?key='+map_key+'&libraries=geometry,places&language=en&region=UK')

/* 
    STYLING REFERENCE

[
    {
        featureType : "value", 
        elementType : "value", 
        stylers : [
            {visibility : "value"},
            {color : "value"}
        ]
    }
]

featureType:
    all
    administrative
        administrative.country
        administrative.land_parcel
        administrative.locality
        administrative.neighborhood
        administrative.province
    landscape
        landscape.man_made
        landscape.natural
        landscape.natural.landcover
        landscape.natural.terrain
    poi
        poi.attraction
        poi.business
        poi.government
        poi.medical
        poi.park
        poi.place_of_worship
        poi.school
        poi.sports_complex
    road
        road.arterial
        road.highway
        road.highway.controlled_access
        road.local
    transit
        transit.line
        transit.station
        transit.station.airport
        transit.station.bus
        transit.station.rail
    water
    
elementType:
    all
    geometry
        geometry.fill
        geometry.stroke
    labels
        labels.icon
        labels.text
        labels.text.fill
        labels.text.stroke
stylers:
    hue
    lightness
    saturation
    gamma
    invert_lightness
    visibility
    color
    weight
*/
  
class Map {
    constructor(options) {   
        /**
         *  @property map - main map object
         */
        this.map;

        /**
         *  @property options - initializations options 
         * 
         */
        this.options = options;

        /**
         *  @property mapcenter - the center of the map
         */
        this.mapcenter  = this.getCoordinates(options.lat, options.lng);

        /**
         *  @property infowindow - infobox object
         */
        this.infowindow = new google.maps.InfoWindow(options.infowindow || {});

        /**
         *  @property infoHtml - html for inside the box
         */
        this.infoHtml = '<div class="marker-info"><div class="marker-name">{content}</div></div>'

        /* Initialize the map */
        this.initialize();

        /**
         *  @property service - places service
         */
        this.service = new google.maps.places.PlacesService(this.map);

        /**
         *  @property geocoder
         */
        this.geocoder = new google.maps.Geocoder;
        
        /**
         *  @property markers - global marker array
         */
        this.markers = [];

        /**
         *  @property lines - global lines array
         */
        this.lines = [];
        
        /**
         *  @property bounds
         */
        this.bounds = new google.maps.LatLngBounds();
    }

    initialize() {    

        /* Create the main map object */
        this.map = new google.maps.Map(document.getElementById("map"), {
            zoom            : this.options.zoom,
            center          : this.mapcenter,
            scaleControl    : true,
            draggable       : true,
            scrollwheel     : true,
            mapTypeId       : this.options.type,
            styles          : this.options.styles || [],
            zoomAfterMarker : this.options.zoomAfterMarker || 15,
            styles          : this.options.styles || []
        });

        /* Attach resize event if needed */
        if(this.options.centerOnResize === true) {
            this.resizeEvent(this);
        } 
    }            

    /* Map Events */                
    resizeEvent() {     

        /* Create local variables */
        let map = this.map;
        let mapcenter = this.mapcenter;
        let bounds = this.fitBounds;
        let ma = this;

        /* Add resize event */
        google.maps.event.addDomListener(
            window, 
            'resize', 
            function() {
                ma.fitBounds();
            }
        );
    }
    
    fitBounds() { 
        let ma = this;
        $(this.markers).each(function() {
            var m = $(this);                    
            ma.bounds.extend(m[0].getPosition());
        });
        $(this.lines).each(function() {
            var l = $(this);
            ma.bounds.extend(l[0].getPath().getAt(0));
            ma.bounds.extend(l[0].getPath().getAt(1));
        });
        
        this.map.fitBounds(this.bounds);
    }

    /* Markers */
    createMarker(options) {     
        /* Create the marker object */ 
        let marker = new google.maps.Marker({
            map       : this.map,
            position  : options.coord,
            icon      : options.icon || this.options.defaulticon,
            draggable : options.draggable || false ,
            zoom      : options.zoom || options.zoomAfterMarker || 10
        });  

        let m = this;

        this.markers.push(marker); 

        /* Add click event */
        if(!!options.onclick) {
            google.maps.event.addListener(marker, 'click', function() { 
                if(typeof options.onclick === "function") {
                    options.onclick(marker, options, this);
                }
                else {
                    m.markerClickDefault(marker, options, this);
                }
            }); 
        }

        /* Add dragend event */
        if(!!options.ondragend && !!options.draggable) {                        
            google.maps.event.addListener(marker, 'dragend', function() {
                if(typeof options.ondragend === "function") {
                    options.ondragend(marker, options, this);
                }
                else {
                    m.markerDragEndDefault(marker, options, this);
                } 
            });
        }
        
        /* Extend the bounds */
        if(!!options.fitBounds) {
            this.fitBounds();
        }
        
        google.maps.event.addListenerOnce(m.map, 'bounds_changed', function(event) {
            if (this.getZoom() > options.zoom) {
                this.setZoom(options.zoom);
            }
        });
    }

    markerSize(a, b) {
        return new google.maps.Size(a, b);
    }

    markerPoint(a, b) {
        return new google.maps.Point(a, b);
    }

    markerClickDefault(marker, options, event) {

        /* Create local variables */
        let info = this.infowindow;
        let html = this.infoHtml;
        let cont = options.content || "";

        info.setContent(html.replace('{content}', decodeURIComponent(cont).replace(/\+/gi, " ")));

        info.open(map, event);   
    } 

    markerDragEndDefault(marker, options, event) {
        console.log('default')
    }

    markerOnClick(options) {
        let m = this;  
        let icon = !!options && options.icon || this.options.defaulticon;

        this.map.addListener("click", function(e) { 

            let lat = e.latLng.lat();
            let lng = e.latLng.lng();

            m.createMarker({
                coord : e.latLng,
                icon  : icon
            });

            if(!!options && options.getaddress === true) {
                m.getPlaceByCoords(lat, lng, options)
            }
        }); 
    } 

    clearAllMarkers() {
        for(var i=0; i < this.markers.length; i++) {
            this.markers[i].setMap(null);
        }
        this.markers = [];
    }

    clearLastMarker() {
        if(this.markers.length > 0) {
            this.markers[this.markers.length - 1].setMap(null);
            this.markers.pop();
        }
    }

    /* Paths and Polylines */
    polyline(path, options) {
        let line = new google.maps.Polyline({
            path            : path,
            geodesic        : options.geodesic || true,
            strokeColor     : options.strokeColor || "black",
            strokeOpacity   : options.strokeOpacity || 1.0,
            strokeWeight    : options.strokeWeight || 2,
            icons           : options.icons || []
        })

        line.setMap(this.map); 
        this.lines.push(line);

        if(options.distance === true)
            this.pathDistance();
                        
        this.fitBounds();
    }

    pathDistance() {
        let dist = 0;
        for(var i=0; i < this.lines.length; i++) { 
            dist += this.pointDistance(this.lines[i].getPath().getAt(0), this.lines[i].getPath().getAt(1)); 
        }
        let u = this.options.distanceUnit;
        let el = this.options.distanceEl;

        dist = (u == "m" ? dist : (u == "km" ? (dist / 1000) : (dist / 1600))).toFixed(2); 
        this.distance = parseFloat(dist);
        
        if(el.length > 0) {
            if(el.is("input"))
                el.val(dist);
            else
                el.text(dist + ' ' + u);
        }
    }         

    clearLine() {
        for(var i=0; i < this.lines.length; i++) {
            this.lines[i].setMap(null);
        }
        this.lines = [];
    }

    clearLastLine() {
        if(this.lines.length > 0) {
            this.lines[this.lines.length - 1].setMap(null);
            this.lines.pop();
            this.clearLastMarker();
            this.pathDistance();
        }
    }

    /* Geocoding Service */
    getPlaceByCoords(lat, lng, options) {
        this.geocoder.geocode({
            location : {
                lat : lat, 
                lng : lng
            }
        }, function(results, status) {
            if(status === "OK") {
                if(results[0] && typeof options != "undefined" && typeof options.callback != "undefined") { 
                    options.callback(results);
                }
            }
        });
    }

    getCoordsByPlaceId(options) {
        this.geocoder.geocode({
            "placeId" : options.place_id
        }, function(results, status) {
            if(status === "OK") {
                if(results[0] && typeof options != "undefined" && typeof options.callback != "undefined") { 
                    options.callback(results);
                }
            }
        });
    }

    getPlaceByAddress(options) {
        let m = this;
        this.geocoder.geocode({
            "address" : options.address
        }, function(results, status) {
            if(status === "OK") {
                if(results[0] && typeof options != "undefined" && typeof options.callback != "undefined") { 
                    options.callback(results);
                }
            }
        });
    }

    /* Utilities */                
    pointDistance(a, b) {
        return google.maps.geometry.spherical.computeDistanceBetween(a,b);
    }

    getCoordinates(a, b) {
        return new google.maps.LatLng(a,b);
    }    
}