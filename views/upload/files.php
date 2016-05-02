<?php

use orchidphp\HTMLhelper;
use orchidphp\Orchid;

/**
 * View pre upload súboru s údajmi o tréningu 
 *
 * @package    inhillz\views
 * @author     Matus Macak <matus.macak@orchidsphere.com>
 * @link       http://ride.inhillz.com/
 * @version    2.0
 * 
 */
?>
<div class="col-xs-12 center-block">
    <?php
        echo HTMLhelper::displayFlash();
        //echo HTMLhelper::displayErrors($data->workout);
    ?>
    <form method="POST" action="<?php echo ENTRY_SCRIPT_URL . 'upload/files/'?>" class="ajaxForm">
        <div class="well">
            <input type="file" multiple="multiple" name="workout_files"/>
            <div class="progress hidden">
                <div class="progress-bar progress-bar-info progress-bar-striped" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="min-width: 2em;">0%</div>
            </div>
        </div>      
        <p><?php echo Orchid::t('Works for multiple .fit, .gpx files {SIZE} or smaller. Choose up to {COUNT} files.', 'global', ['{SIZE}' => get_cfg_var('upload_max_filesize'), '{COUNT}' => get_cfg_var('max_file_uploads')])?></p>
    </form>
</div><?php

    //** Priprava javascript kódu pre mapy, ktorý sa vloží na koniec <body>
    ob_start();
    
?><script type="text/javascript">
    
    function map_draw(mapcanvas_id, coordinates) {
              
        var map,
            route_path,
            map_bns,
            style;
    
        var map_styles = [{
            "featureType": "all",
            "elementType": "all",
            "stylers": [
                {"saturation": -100},
                {"gamma": 1.0}
            ]
        }],
            
        map_options = {
            scrollwheel:      false,
            disableDefaultUI: true,
            mapTypeControlOptions: {
                mapTypeIds: [google.maps.MapTypeId.TERRAIN, 'map_style']
            }
        };
                
        style = new google.maps.StyledMapType(map_styles,{name: "Workout map"});
        
        route_path = new google.maps.Polyline({
            path: coordinates,
            geodesic: true,
            strokeColor: '#33CC99',
            strokeOpacity: 1.0,
            strokeWeight: 3
        });
                 
        map = new google.maps.Map(document.getElementById(mapcanvas_id),map_options);
        
        map_bns = new google.maps.LatLngBounds();
        for(var i = 0; i < coordinates.length; i++){
            map_bns.extend(coordinates[i]);
        }
            
        map.mapTypes.set('map_style', style);
        map.setMapTypeId('map_style');        
        map.fitBounds(map_bns);   
        route_path.setMap(map);
    }
    
</script><?php

    $map_script = ob_get_clean();

    Orchid::base()->cachescript->registerCodeSnippet('<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3.exp"></script>', 'map-api', 2);
    Orchid::base()->cachescript->registerCodeSnippet($map_script, 'map-script', 2);