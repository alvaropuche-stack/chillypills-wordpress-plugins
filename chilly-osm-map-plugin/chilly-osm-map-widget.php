<?php
if (!defined('ABSPATH')) {
    exit;
}

class Elementor_Chillypills_OSM_Map_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'chillypills_osm_map_widget';
    }

    public function get_title() {
        return 'Chillypills Open Street Map Widget';
    }

    public function get_icon() {
        return 'https://chillypills.com/dist/app/images/favicon/favicon-196x196.png';
    }

    public function get_categories() {
        return ['general'];
    }

    protected function _register_controls() {
        // Pestaña de Ubicaciones
        $this->start_controls_section(
            'locations_section',
            [
                'label' => __('Ubicaciones', 'elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $repeater = new \Elementor\Repeater();

        $repeater->add_control(
            'title',
            [
                'label' => __('Título', 'elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '',
                'placeholder' => __('Introduce un título', 'elementor'),
            ]
        );

        $repeater->add_control(
            'address',
            [
                'label' => __('Dirección', 'elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '',
                'placeholder' => __('Introduce una dirección', 'elementor'),
            ]
        );

        $repeater->add_control(
            'manual_coordinates',
            [
                'label' => __('Introducir coordenadas manualmente', 'elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Sí', 'elementor'),
                'label_off' => __('No', 'elementor'),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );

        $repeater->add_control(
            'latitude',
            [
                'label' => __('Latitud', 'elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'condition' => [
                    'manual_coordinates' => 'yes',
                ],
                'default' => '',
                'placeholder' => __('Introduce la latitud', 'elementor'),
            ]
        );

        $repeater->add_control(
            'longitude',
            [
                'label' => __('Longitud', 'elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'condition' => [
                    'manual_coordinates' => 'yes',
                ],
                'default' => '',
                'placeholder' => __('Introduce la longitud', 'elementor'),
            ]
        );

        $repeater->add_control(
            'google_maps_link',
            [
                'label' => __('Enlace de Google Maps', 'elementor'),
                'type' => \Elementor\Controls_Manager::URL,
                'placeholder' => __('https://www.google.com/maps', 'elementor'),
            ]
        );

        $this->add_control(
            'locations',
            [
                'label' => __('Ubicaciones', 'elementor'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [],
                'title_field' => '{{{ title }}} - {{{ address }}}',
            ]
        );

        $this->end_controls_section();

        // Pestaña de Configuración de Mapa
        $this->start_controls_section(
            'map_settings_section',
            [
                'label' => __('Configuración del Mapa', 'elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'map_style',
            [
                'label' => __('Estilo de Mapbox', 'elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'streets-v11' => 'Streets',
                    'outdoors-v11' => 'Outdoors',
                    'light-v10' => 'Light',
                    'dark-v10' => 'Dark',
                    'satellite-v9' => 'Satellite',
                    'satellite-streets-v11' => 'Satellite Streets',
                ],
                'default' => 'streets-v11',
            ]
        );

        $this->add_control(
            'map_zoom',
            [
                'label' => __('Zoom del Mapa', 'elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 13,
                'min' => 0,
                'max' => 22,
            ]
        );

        $this->end_controls_section();

        // Pestaña de Token Mapbox
        $this->start_controls_section(
            'mapbox_token_section',
            [
                'label' => __('Token Mapbox', 'elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'mapbox_token',
            [
                'label' => __('Token de Mapbox', 'elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'pk.eyJ1IjoiYWx2YXJvcHVjaGUiLCJhIjoiY2xjcTlqa3ZtMDFnNzNwbnB5ejR6NzQ4bCJ9.j2hplYkteh3PWHeDXxbs_Q',
                'description' => __('Introduce tu token de Mapbox.', 'elementor'),
            ]
        );

        $this->add_control(
            'mapbox_token_status',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '<div id="mapbox-token-status"></div>',
                'content_classes' => 'elementor-control-field',
            ]
        );

        $this->add_control(
            'disclaimer',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '<p>Este plugin es propiedad única y exclusiva de Chillypills Comunicación S.L., y su uso está restringido a proyectos aprobados por Chillypills.</p>',
                'content_classes' => 'elementor-control-field',
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $map_style = $settings['map_style'];
        $map_zoom = $settings['map_zoom'];
        $locations = $settings['locations'];
        $mapbox_token = $settings['mapbox_token'];

        $locations_js = json_encode($locations);
        ?>
        <div id="osm-map" style="width: 100%; height: 500px; position: relative;"></div>
        <div id="osm-map-buttons" style="position: absolute; top: 10px; right: 10px; z-index: 400;"></div>
        <script>
            (function($) {
                function initMap() {
                    var settings = {
                        locations: <?php echo $locations_js; ?>,
                        map_style: '<?php echo esc_js($map_style); ?>',
                        map_zoom: <?php echo esc_js($map_zoom); ?>,
                        mapbox_token: '<?php echo esc_js($mapbox_token); ?>'
                    };

                    var mapContainer = document.getElementById('osm-map');
                    if (mapContainer._leaflet_id) {
                        mapContainer._leaflet_id = null;
                    }

                    var mapCenter = [51.505, -0.09]; // Default center
                    if (settings.locations.length > 0 && settings.locations[0].manual_coordinates === 'yes') {
                        mapCenter = [settings.locations[0].latitude, settings.locations[0].longitude];
                    } else if (settings.locations.length > 0) {
                        // Geocode the first address to get the map center
                        var geocoder = L.Control.Geocoder.nominatim();
                        geocoder.geocode(settings.locations[0].address, function(results) {
                            if (results.length) {
                                mapCenter = results[0].center;
                            }
                        });
                    }

                    var map = L.map('osm-map').setView(mapCenter, settings.map_zoom);

                    L.tileLayer('https://api.mapbox.com/styles/v1/mapbox/' + settings.map_style + '/tiles/{z}/{x}/{y}?access_token=' + settings.mapbox_token, {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://www.mapbox.com/about/maps/">Mapbox</a>',
                        tileSize: 512,
                        zoomOffset: -1,
                    }).addTo(map);

                    var geocoder = L.Control.Geocoder.nominatim();

                    settings.locations.forEach(function(location) {
                        var popup_content = '<strong>' + location.title + '</strong><br>' + location.address;
                        if (location.manual_coordinates === 'yes') {
                            var marker = L.marker([location.latitude, location.longitude]).addTo(map)
                                .bindPopup(popup_content)
                                .openPopup();
                        } else {
                            geocoder.geocode(location.address, function(results) {
                                if (results.length) {
                                    var marker = L.marker(results[0].center).addTo(map)
                                        .bindPopup(popup_content)
                                        .openPopup();
                                }
                            });
                        }

                        if (location.google_maps_link.url) {
                            var button = document.createElement('a');
                            button.href = location.google_maps_link.url;
                            button.target = '_blank';
                            button.style.cssText = 'display: block; background: white; color: #006790; padding: 5px 10px; margin-top: 5px; font-family: "Lexend Light"; text-align: center; text-decoration: none; border-radius: 4px; border: 1px solid #006790;';
                            button.textContent = 'Ver en Google Maps';
                            button.onmouseover = function() {
                                button.style.backgroundColor = '#006790';
                                button.style.color = 'white';
                            };
                            button.onmouseout = function() {
                                button.style.backgroundColor = 'white';
                                button.style.color = '#006790';
                            };
                            document.getElementById('osm-map-buttons').appendChild(button);
                        }
                    });
                }

                $(document).ready(function() {
                    initMap();
                });

                $(window).on('elementor/frontend/init', function() {
                    elementorFrontend.hooks.addAction('frontend/element_ready/global', initMap);
                });
            })(jQuery);
        </script>
        <?php
    }

    protected function _content_template() {
        ?>
        <#
        var settings = {
            map_style: settings.map_style,
            map_zoom: settings.map_zoom,
            locations: settings.locations,
            mapbox_token: settings.mapbox_token
        };
        var mapCenter = [51.505, -0.09]; // Default center
        if (settings.locations.length > 0 && settings.locations[0].manual_coordinates === 'yes') {
            mapCenter = [settings.locations[0].latitude, settings.locations[0].longitude];
        } else if (settings.locations.length > 0) {
            // Geocode the first address to get the map center
            var geocoder = L.Control.Geocoder.nominatim();
            geocoder.geocode(settings.locations[0].address, function(results) {
                if (results.length) {
                    mapCenter = results[0].center;
                }
            });
        }
        #>
        <div id="osm-map" style="width: 100%; height: 500px; position: relative;"></div>
        <div id="osm-map-buttons" style="position: absolute; top: 10px; right: 10px; z-index: 400;"></div>
        <script>
            (function($) {
                function initMap() {
                    var map = L.map('osm-map').setView(mapCenter, settings.map_zoom);

                    L.tileLayer('https://api.mapbox.com/styles/v1/mapbox/' + settings.map_style + '/tiles/{z}/{x}/{y}?access_token=' + settings.mapbox_token, {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://www.mapbox.com/about/maps/">Mapbox</a>',
                        tileSize: 512,
                        zoomOffset: -1,
                    }).addTo(map);

                    var geocoder = L.Control.Geocoder.nominatim();

                    settings.locations.forEach(function(location) {
                        var popup_content = '<strong>' + location.title + '</strong><br>' + location.address;
                        if (location.manual_coordinates === 'yes') {
                            var marker = L.marker([location.latitude, location.longitude]).addTo(map)
                                .bindPopup(popup_content)
                                .openPopup();
                        } else {
                            geocoder.geocode(location.address, function(results) {
                                if (results.length) {
                                    var marker = L.marker(results[0].center).addTo(map)
                                        .bindPopup(popup_content)
                                        .openPopup();
                                }
                            });
                        }

                        if (location.google_maps_link.url) {
                            var button = document.createElement('a');
                            button.href = location.google_maps_link.url;
                            button.target = '_blank';
                            button.style.cssText = 'display: block; background: white; color: #006790; padding: 5px 10px; margin-top: 5px; font-family: "Lexend Light"; text-align: center; text-decoration: none; border-radius: 4px; border: 1px solid #006790;';
                            button.textContent = 'Ver en Google Maps';
                            button.onmouseover = function() {
                                button.style.backgroundColor = '#006790';
                                button.style.color = 'white';
                            };
                            button.onmouseout = function() {
                                button.style.backgroundColor = 'white';
                                button.style.color = '#006790';
                            };
                            document.getElementById('osm-map-buttons').appendChild(button);
                        }
                    });
                }

                $(document).ready(function() {
                    initMap();
                });

                $(window).on('elementor/frontend/init', function() {
                    elementorFrontend.hooks.addAction('frontend/element_ready/global', initMap);
                });
            })(jQuery);
        </script>
        <?php
    }
}
?>
