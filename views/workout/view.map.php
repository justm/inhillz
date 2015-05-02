<?php
/**
 * Mapa - Časť náhľadu pre detailné zobrazenie tréningu
 *
 * @author Matus Macak < matus.macak@folcon.sk > 
 * @version 2.0
 * @since Subor je súčasťou aplikácie od verzie 2.0
 * @package views.workout
 * 
 * @var WorkoutModel $data->workout_summary
 * @var Array $data->workout_data
 */
?><div class="col-xs-12 col-md-7">
    <div id="map-canvas" style="height:400px"></div>
</div>
<div class="clearfix push visible-xs visible-sm"></div>
<?php

    //** Priprava javascript kódu, ktorý sa vloží na koniec <body>
    ob_start();
    
?><script type="text/javascript">
    var map_marker_mov = {},
        map = {},
        map_bns = {},
        route_coordinates = [],
        route_path = {},
        selection_path;
    
    function map_initialize() {
               
        var map_styles = [{
            "featureType": "all",
            "elementType": "all",
            "stylers": [
                {"saturation": -100},
                {"gamma": 1.0}
            ]
        }],
            
        map_options = {
            mapTypeControlOptions: {
                mapTypeIds: [google.maps.MapTypeId.TERRAIN, 'map_style']
            }
        },
                
        style = new google.maps.StyledMapType(map_styles,{name: "Workout map"})
        
        route_coordinates = [<?php echo $coordinates ?>];
                        
        route_path = new google.maps.Polyline({
            path: route_coordinates,
            geodesic: true,
            strokeColor: '#33CC99',
            strokeOpacity: 1.0,
            strokeWeight: 3
        });
                 
        map = new google.maps.Map(document.getElementById('map-canvas'),map_options);

        map_marker_mov = new google.maps.Marker({
            icon: {
                path: google.maps.SymbolPath.CIRCLE,
                scale: 4
            },
            map: map,
            visible: false
        });
        
        map_bns = new google.maps.LatLngBounds();
        <?php echo $bounds; ?>
            
        map.mapTypes.set('map_style', style);
        map.setMapTypeId('map_style');        
        map.fitBounds(map_bns);   
        route_path.setMap(map);
    }
    
    function map_selection(data){
        
        var selection_coordinates = [];
        var selection_bounds = new google.maps.LatLngBounds();
        
        for(var i = 0; i < data.length; i++){
            selection_coordinates.push(
                new google.maps.LatLng( data[i].position_lat, data[i].position_long )
            );
            selection_bounds.extend(
                new google.maps.LatLng( data[i].position_lat, data[i].position_long )
            );
        }
        
        try {
            selection_path.setMap(null);
        }catch(e){}
        
        selection_path = new google.maps.Polyline({
            path: selection_coordinates,
            geodesic: true,
            strokeColor: '#9933cc',
            strokeOpacity: 1.0,
            strokeWeight: 3
        });
                
        map.fitBounds(selection_bounds);
        selection_path.setMap(map);
    }
    
    function map_reset(){
        map.fitBounds(map_bns);
        selection_path.setMap(null);
    }

    google.maps.event.addDomListener(window, 'load', map_initialize);
</script><?php

    $map_create = ob_get_clean();

    Mcore::base()->cachescript->registerCodeSnippet('<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3.exp"></script>', 'map-api', 2);
    Mcore::base()->cachescript->registerCodeSnippet($map_create, 'map-create', 2);

