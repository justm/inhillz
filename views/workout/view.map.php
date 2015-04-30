<?php
/**
 * Mapa - Časť náhľadu pre detailné zobrazenie tréningu
 *
 * @author Matus Macak < matus.macak@folcon.sk > 
 * @version 2.0
 * @since Subor je súčasťou aplikácie od verzie 2.0
 * @package views.workout
 * 
 * @var WorkoutModel $data->workout_basics
 * @var Array $data->workout_data
 */
?><div class="col-xs-12 col-md-6">
    <div id="map-canvas" style="height:300px"></div>
</div>
<div class="clearfix push visible-xs visible-sm"></div>
<?php

    //** Priprava javascript kódu, ktorý sa vloží na koniec <body>
    ob_start();
    
?><script type="text/javascript">
    var movement_marker = {};
    
    function initialize() {
               
        var styles = [{
            "featureType": "all",
            "elementType": "all",
            "stylers": [
                {"saturation": -100},
                {"gamma": 1.0}
            ]
        }],
            
        mapOptions = {
            mapTypeControlOptions: {
                mapTypeIds: [google.maps.MapTypeId.TERRAIN, 'map_style']
            }
        },
        
        routeCoordinates = [<?php echo $coordinates ?>],
                        
        routePath = new google.maps.Polyline({
            path: routeCoordinates,
            geodesic: true,
            strokeColor: '#33CC99',
            strokeOpacity: 1.0,
            strokeWeight: 3
        }),
               
        style = new google.maps.StyledMapType(styles,{name: "Workout map"}),      
        map   = new google.maps.Map(document.getElementById('map-canvas'),mapOptions);

        window['movement_marker'] = new google.maps.Marker({
            icon: {
                path: google.maps.SymbolPath.CIRCLE,
                scale: 4
            },
            map: map,
            visible: false
        });
        
        var bounds = new google.maps.LatLngBounds();
        <?php echo $bounds; ?>
            
        map.mapTypes.set('map_style', style);
        map.setMapTypeId('map_style');        
        map.fitBounds(bounds);   
        routePath.setMap(map);
    }

    google.maps.event.addDomListener(window, 'load', initialize);
</script><?php

    $map_create = ob_get_clean();

    Mcore::base()->cachescript->registerCodeSnippet('<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3.exp"></script>', 'map-api', 2);
    Mcore::base()->cachescript->registerCodeSnippet($map_create, 'map-create', 2);

