<?php
/**
 * Náhľad pre detailné zobrazenie záznamu o tréningu
 *
 * @author Matus Macak < matus.macak@folcon.sk > 
 * @version 2.0
 * @since Subor je súčasťou aplikácie od verzie 2.0
 * @package views.workout
 * 
 * @var WorkoutModel $data->workout_basics
 * @var Array $data->workout_data
 */
?><div class="col-xs-12">
    <div id="map-canvas" style="height:500px"></div>
</div><?php

    //** Priprava jednotlivych bodov pre vykreslenie na mape
    $deg_to_semic = 180 / pow(2, 31);
    $coordinates  = '';
    $bounds       = '';
    
    foreach ( $data->workout_data as $r ){
        if( !empty($r['position_lat']) && !empty($r['position_long']) ) {
            $latlng       = 'new google.maps.LatLng(' . ($r['position_lat'] * $deg_to_semic) . ',' . ($r['position_long'] * $deg_to_semic) . ')';
            $bounds      .= 'bounds.extend(' . $latlng . '); ';
            $coordinates .= $latlng . ', ';
        }
    }

    ob_start();
    
?><script type="text/javascript">
    function initialize() {
               
        var styles = [{
            "featureType": "all",
            "elementType": "all",
            "stylers": [
                {"saturation": -100},
                {"gamma": 0.9}
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
                
        styledMap = new google.maps.StyledMapType(styles,{name: "Workout map"}),      
        map       = new google.maps.Map(document.getElementById('map-canvas'),mapOptions);

        var bounds = new google.maps.LatLngBounds();
        <?php echo $bounds; ?>
            
        map.mapTypes.set('map_style', styledMap);
        map.setMapTypeId('map_style');        
        map.fitBounds(bounds);   
        routePath.setMap(map);
    }

    google.maps.event.addDomListener(window, 'load', initialize);
</script><?php

    $map_create = ob_get_clean();

    Mcore::base()->cachescript->registerCodeSnippet('<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3.exp&signed_in=true"></script>', 'map-api', 2);
    Mcore::base()->cachescript->registerCodeSnippet($map_create, 'map-create', 2);
