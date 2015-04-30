<?php
/**
 * Graf - Časť náhľadu pre detailné zobrazenie tréningu
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
    <div class="panel panel-default">
        <div class="panel-heading"><h2 class="h5"><?php echo Mcore::t('Effort analysis')?></h2></div>
        <div class="panel-body"><div id="chart-canvas" class="chart"></div></div>
    </div>
</div><?php

    ob_start();
    
?><script type="text/javascript">

    /**
     * Množina dát s údajmi o tréningu
     * @type Array
     */
    var chart_data = [<?php echo $chart_data; ?>];
    
    /**
     * Funkcia pre vytvorenie X-ovej osi
     * @param {function} scale
     * @param {string} orientation
     * @returns {function}
     */
    function create_xaxis(scale, orientation){
        return d3.svg.axis().scale(scale).orient(orientation);
    }
    
    /**
     * Funkcia pre vytvorenie Y-ovej osi
     * @param {function} scale
     * @param {string} orientation
     * @returns {function}
     */
    function create_yaxis(scale, orientation){
        return d3.svg.axis().scale(scale).ticks(0).orient(orientation);
    }
   
    /**
     * Rozmery grafu
     */
    var MARGINS = {top: 20, right: 10, bottom: 20, left: 10},
        WIDTH   = $("#chart-canvas").width(),
        HEIGHT  = 100;

    /**
     * @type {function} Funkcia pre rozsah data v zavislosti na x-ovej osi (default: distance)
     */
    var x_range = d3.scale.linear()
                .range([MARGINS.left, WIDTH-MARGINS.right])
                .domain([
                        d3.min(chart_data, function(d) {return d.distance;}), 
                        d3.max(chart_data, function(d) {return d.distance;})
                ]);
    
    /**
     * @type {array} Pole funkcii pre rozsah data v zavislosti na y-ovej osi 
     */                
    var y_range = [];
    
    /**
     * @type {array} Pole minimalnych hodnôt, ktoré nadobúdajú jednotlivé veličiny 
     */                
    var min = [];
    
    /**
     * @type {array} Pole maximalnych hodnôt, ktoré nadobúdajú jednotlivé veličiny 
     */                
    var max = [];
    
    /**
     * Uchovava funkcie a rozsahy potrebne pre nanesenie jednotlivych veličín na graf
     * @type Array 
     */
    var chart_def = {cadence:{},heart_rate:{},speed:{},altitude:{}/*power:{}*/};
    
    var charts_count, i;
    charts_count = i = Object.keys(chart_def).length;
       
    //** ROOT SVG
    var svg = d3.select("#chart-canvas").append("svg")
        .attr("width", WIDTH + MARGINS.left + MARGINS.right)
        .attr("height", HEIGHT * i + MARGINS.top + MARGINS.bottom)
        .append("g")
        .attr("transform", "translate(" + MARGINS.left + "," + MARGINS.top + ")");

    //** Inicializacia funkcii
    for(var key in chart_def){
        try{
            min[key] = d3.min(chart_data, function(d) {return d[key];});
            max[key] = d3.max(chart_data, function(d) {return d[key];});
            
            y_range[key] = d3.scale.linear()
                        .range([HEIGHT*i, HEIGHT*(i-1)])
                        .domain([ min[key] - 0.05 * min[key], max[key] + 0.05 * max[key] ]);
                
            if( key === 'altitude' ){
                
                var draw_fn = d3.svg.area()
                            .x(function(d) {return x_range(d.distance);})
                            .y0(HEIGHT*i)
                            .y1(function(d) {return y_range[key](d[key]);});
            }
            else{
                
                var draw_fn = d3.svg.line()
                            .x(function(d) {return x_range(d.distance);})
                            .y(function(d) {return y_range[key](d[key]) || y_range[key](min[key]);});
            }
            chart_def[key] = {
                draw_fn: draw_fn,
                x_range: x_range,
                y_range: y_range[key],
            }
            i--;
        }
        catch(e){
            console.log("Data error in: " + key);
        };
    }
    
    i = 0;
    
    //** Vykreslenie grafu
    for(var key in chart_def){
        
        //** Path
        svg.append('svg:path')
            .attr('d', chart_def[key].draw_fn(chart_data))
            .attr("class","datapath " + key)
            .attr('shape-rendering','optimizeSpeed')
            .attr("transform", "translate(" +(-MARGINS.left)+ ",0)");

        svg.append("g")
            .attr("class", "y axis left")
            .attr('shape-rendering','crispEdges')
            .call(create_yaxis(y_range[key], "left").tickFormat(""));
    
        svg.append("g")
            .attr("class", "y axis right")
            .attr('shape-rendering','crispEdges')
            .attr("transform", "translate(" +(WIDTH - MARGINS.left - MARGINS.right)+ ",0)")
            .call(create_yaxis(y_range[key], "right").tickFormat(""));
    
           
        svg.append("g")
            .attr("class", "x grid")
            .append("line")
            .attr("x1", 0)
            .attr("x2", WIDTH - MARGINS.left - MARGINS.right) 
            .attr("y1", HEIGHT*i)
            .attr("y2", HEIGHT*i + 1);
   
        i++;
    }
    
    //** AXIS 
    svg.append("g")
        .attr("class", "x axis")
        .attr('shape-rendering','crispEdges')
        .attr("transform", "translate(" +(-MARGINS.left)+ "," + (HEIGHT*charts_count) + ")")
        .call(create_xaxis(x_range, "bottom").tickFormat(function(d) {var f = d3.format(".1f"); return f(d) + " km";}));
    
    svg.append("g")
        .attr("class", "x axis")
        .attr('shape-rendering','crispEdges')
        .attr("transform", "translate(" +(-MARGINS.left)+ ",0)")
        .call(create_xaxis(x_range, "top").tickFormat(function(d) {var f = d3.format(".1f"); return f(d) + " km";}));
    
    //** GRID
    svg.append("g")
        .attr("class", "x grid")
        .attr('shape-rendering','crispEdges')
        .attr("transform", "translate(" +(-MARGINS.left)+ ",0)")
        .call(create_xaxis(x_range, "bottom").tickSize(HEIGHT*charts_count, 0, 0).tickFormat(""));

    //** MOUSE EVENTS
    
    /**
     * Funkcia reagujúca na drag event
     * @returns {void}
     */
    function drag_select(){
        alert();
    }
    
    var bisect_fn     = d3.bisector(function(d) { return d.distance; }).right;
    var drag_listener = d3.behavior.drag().on("drag", drag_select);
    
    var select_lines_g = svg.append("g");
    var line_hover = 
            select_lines_g.append("line").style("opacity", 0).attr("class", "line-hover")
            .attr("x1", 0).attr("x2", 0).attr("y1", 0).attr("y2", HEIGHT*charts_count);
    var line_select_r = 
            select_lines_g.append("line").style("opacity", 0).attr("class", "line-select")
            .attr("x1", 0).attr("x2", 0).attr("y1", 0).attr("y2", HEIGHT*charts_count);
    var line_select_l = 
            select_lines_g.append("line").style("opacity", 0).attr("class", "line-select")
            .attr("x1", 0).attr("x2", 0).attr("y1", 0).attr("y2", HEIGHT*charts_count);

    d3.select("#chart-canvas").select("svg")
        .call(drag_listener)
        .on("click", function(){
            var x  = d3.mouse(this)[0];
            line_select_l.attr("x1", x - MARGINS.left).attr("x2", x - MARGINS.left).style("opacity", 1);
            /* @todo Disable overwriting displayed data with mousemove event*/
        })
        .on("mousemove", function() {
            var x  = d3.mouse(this)[0];
            var i  = bisect_fn( chart_data, x_range.invert(x) );
            
            if(i === 0){ return false; } //Pravy bisector, tj. 0 iba v pripade ak ide o poziciu mimo mnoziny zobrazovanych dat
            
            try{
                window['movement_marker'].setPosition(new google.maps.LatLng(chart_data[i]['position_lat'],chart_data[i]['position_long']));
                window['movement_marker'].setVisible(true);
                line_hover.attr("x1", x - MARGINS.left).attr("x2", x - MARGINS.left).style("opacity", 1);
            }
            catch(e){}
            console.log(chart_data[i]);
        })
        .on("mouseout", function() {
            window['movement_marker'].setVisible(false);
            line_hover.style("opacity", 0);
        });
</script><?php

    $chart_create = ob_get_clean();

    Mcore::base()->cachescript->registerCodeSnippet('<script src="' . ROOT_URL . 'libraries/d3.min.js"></script>', 'chart-api', 2);
    Mcore::base()->cachescript->registerCodeSnippet($chart_create, 'chart-create', 2);