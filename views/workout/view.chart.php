<?php
/**
 * Graf - Časť náhľadu pre detailné zobrazenie tréningu
 *
 * @author Matus Macak < matus.macak@folcon.sk > 
 * @version 2.0
 * @since Subor je súčasťou aplikácie od verzie 2.0
 * @package views.workout
 * 
 * @var WorkoutModel $data->workout_summary
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
     * Množina dát s údajmi o tréningu, pôvodné dáta, ktoré sa neupravujú
     * @type Array
     */
    var chart_data_original = [<?php echo $chart_data; ?>];
    
    /**
     * Množina dát s údajmi o tréningu, upravované selekciou
     * @type Array
     */
    var chart_data = chart_data_original;
    
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
     * Upravena array.slice metoda
     * @param {int} start
     * @param {int} end
     * @returns {Array.prototype._slice@call;slice}
     */
    Array.prototype._slice = function(start,end){
        
        start = Math.max(0,start);
        end   = Math.min(end,this.length);
        if(start > end){
            var t = start;
            start = end;
            end   = t; 
        }
        var r = this.slice(start,end);
        return r;
    }
   
    /**
     * Rozmery grafu
     */
    var MARGINS = {top: 20, right: 70, bottom: 20, left: 70},
        WIDTH   = $("#chart-canvas").width(),
        HEIGHT  = 100;

    /**
     * @type {function} Funkcia pre rozsah data v zavislosti na x-ovej osi (default: distance)
     */
    var x_range;
    
    /**
     * @type {array} Pole funkcii pre rozsah data v zavislosti na y-ovej osi 
     */                
    var y_range = [];
    
    /**
     * @type {array} Pole minimalnych/maximálnych a priemerných hodnôt, 
     * ktoré nadobúdajú jednotlivé veličiny 
     */                
    var min = [], max = [], avg = [];
    
    /**
     * Uchovava funkcie a rozsahy potrebne pre nanesenie jednotlivych veličín na graf
     * @type Array 
     */
    var chart_def = {<?php echo $chart_def; ?>};
    
    var charts_count, i;
    charts_count = i = Object.keys(chart_def).length;
       
    var root = d3.select("#chart-canvas").append("svg")
            .attr("width", WIDTH )
            .attr("height", HEIGHT * i + MARGINS.top + MARGINS.bottom);
    
    chart_draw(chart_data);
    
    /**
     * Funkcia na vykreslenie grafu
     * @param {array} chart_data
     * @returns {void}
     */
    function chart_draw(chart_data){

        if(chart_data.length <= 0){
            console.log("No data");
            return;
        }
        window['chart_data'] = chart_data;
        i = Object.keys(chart_def).length;
        
        //** ROOT SVG
        root.select("g").remove();
        var svg = root.append("g").attr("transform", "translate(" + MARGINS.left + "," + MARGINS.top + ")");
    
        x_range = d3.scale.linear()
                .range([MARGINS.left, WIDTH-MARGINS.right])
                .domain([
                        d3.min(chart_data, function(d) {return d.distance;}), 
                        d3.max(chart_data, function(d) {return d.distance;})
                ]);
                
        //** Inicializacia funkcii
        for(var key in chart_def){
            try{    
                var p = Math.pow(10, chart_def[key].precision);
                min[key] = d3.min(chart_data, function(d) {return d[key];});
                max[key] = d3.max(chart_data, function(d) {return d[key];});
                avg[key] = Math.round( d3.mean(chart_data,function(d) {return d[key];}) * p) / p ;

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
                chart_def[key].draw_fn = draw_fn;
                chart_def[key].x_range = x_range;
                chart_def[key].y_range = y_range[key];
                i--;
            }
            catch(e){
                console.log("Data error in: " + key);
            };
        }

        i = charts_count;

        //** Vykreslenie grafu
        for(var key in chart_def){

            //** Path
            svg.append('svg:path')
                .attr('d', chart_def[key].draw_fn(chart_data))
                .attr("class","datapath " + key)
                .attr("transform", "translate(" +(-MARGINS.left)+ ",0)");

            //** Axis
            svg.append("g")
                .attr("class", "y axis left")
                .call(create_yaxis(chart_def[key].y_range, "left").tickFormat(""));

            svg.append("g")
                .attr("class", "y axis right")
                .attr("transform", "translate(" +(WIDTH - MARGINS.left - MARGINS.right)+ ",0)")
                .call(create_yaxis(chart_def[key].y_range, "right").tickFormat(""));

            //** Grid
            svg.append("g").attr("class", "x grid").append("line")
                .attr("x1", 0).attr("x2", WIDTH - MARGINS.left - MARGINS.right).attr("y1", HEIGHT*i).attr("y2", HEIGHT*i + 1);

            //** Min, Max, Avg
            var g_d = svg.append("g")
                .attr("class","val val-current " + key)
                .attr("transform", "translate(" +(-MARGINS.left/1.5)+ ","+ ((HEIGHT*(i-1))) +")");

            g_d.append("text").attr("class", "val-name").text(chart_def[key].name).attr("x",10).attr("y",35);
            g_d.append("text").attr("class", "val-value").text("--").attr("x",10).attr("y",60);
            g_d.append("text").attr("class", "val-unit").text(chart_def[key].unit).attr("x",10).attr("y",75);

            //** Min, Max, Avg
            var g_d = svg.append("g")
                .attr("class","val val-detail " + key)
                .attr("transform", "translate(" +(WIDTH-MARGINS.left-(MARGINS.right/1.5))+ ","+ ((HEIGHT*(i-1))) +")");

            g_d.append("text").attr("class", "max").text("Max: " + max[key]).attr("x",10).attr("y",35);
            g_d.append("text").attr("class", "avg").text("Avg: " + avg[key]).attr("x",10).attr("y",55);
            g_d.append("text").attr("class", "min").text("Min: " + min[key]).attr("x",10).attr("y",75);

            i--;
        }
        
        //** AXIS 
        svg.append("g")
            .attr("class", "x axis")
            .attr("transform", "translate(" +(-MARGINS.left)+ "," + (HEIGHT*charts_count) + ")")
            .call(create_xaxis(x_range, "bottom").tickFormat(function(d) {var f = d3.format(".1f"); return f(d) + " km";}));

        svg.append("g")
            .attr("class", "x axis")
            .attr("transform", "translate(" +(-MARGINS.left)+ ",0)")
            .call(create_xaxis(x_range, "top").tickFormat(function(d) {var f = d3.format(".1f"); return f(d) + " km";}));

        //** GRID
        svg.append("g")
            .attr("class", "y grid")
            .attr("transform", "translate(" +(-MARGINS.left)+ ",0)")
            .call(create_xaxis(x_range, "bottom").tickSize(HEIGHT*charts_count, 0, 0).tickFormat(""));
            
        initMouseHandles();
    }
    
    //** MOUSE EVENTS
    var isSelected, isFocused, isDrag;
    var select_g, hover_line, select_line_r, select_line_l;
    var selection_data_x1, selection_data_x2, selection_start, selection_rect;
    
    var bisect_fn = d3.bisector(function(d) { return d.distance; }).right;
    
    isSelected = isFocused = isDrag = false;
    /**
     * Inicializuje čiary a rozsahy pre ovládanie myšou
     * @returns {void}
     */
    function initMouseHandles(){
        
        select_g = d3.select("#chart-canvas").select("svg").select("g").append("g");
        
        hover_line = select_g.append("line").style("opacity", 0).attr("class", "hover-line")
                            .attr("x1", 0).attr("x2", 0).attr("y1", 0).attr("y2", HEIGHT*charts_count);
        select_line_r = select_g.append("line").style("opacity", 0).attr("class", "select-line")
                            .attr("x1", 0).attr("x2", 0).attr("y1", 0).attr("y2", HEIGHT*charts_count);
        select_line_l = select_g.append("line").style("opacity", 0).attr("class", "select-line")
                            .attr("x1", 0).attr("x2", 0).attr("y1", 0).attr("y2", HEIGHT*charts_count);
    
    }
    
    /**
     * Zobrazenie markera na mape a hodnôt v grafe
     * @param {Object} data
     * @param {int} x
     * @returns {void}
     */
    function onFocus(data,x){
        try{
            map_marker_mov.setPosition(new google.maps.LatLng(data['position_lat'],data['position_long']));
            map_marker_mov.setVisible(true);
            hover_line.attr("x1", x).attr("x2", x).style("opacity", 1);
            for(key in data){
                d3.select(".val-current."+key+" .val-value").text(data[key]);
            }
        }
        catch(e){}
    }
    
    /**
     * Funkcia reagujúca na click event
     * @returns {Boolean}
     */
    function onClick(){
        
        if(isFocused){
            isFocused = false;
            select_line_l.style("opacity", 0);
            return false;
        }
        isFocused = true;

        var x  = d3.mouse(this)[0];
        var i  = bisect_fn( chart_data, x_range.invert(x) );

        if(i === 0){ return; } //Pravy bisector, tj. 0 iba v pripade ak ide o poziciu mimo mnoziny zobrazovanych dat
        
        selection_data_x1 = i;
        select_line_l.attr("x1", x- MARGINS.left).attr("x2", x- MARGINS.left).style("opacity", 1);

        onFocus(chart_data[i],x - MARGINS.left);
    }
    
    /**
     * Funkcia reagujúca na mousemove event
     * @returns {void}
     */
    function onMousemove() {
            
        if(isFocused){ return; };

        var x  = d3.mouse(this)[0];
        var i  = bisect_fn( chart_data, x_range.invert(x) );

        if(i === 0){ return false; } //Pravy bisector, ...

        onFocus(chart_data[i],x - MARGINS.left);
    }
    
    /**
     * Funkcia reagujúca na mouseout event
     * @returns {void}
     */
    function onMouseout() {
        hover_line.style("opacity", 0);
        
        if(!isFocused){
            map_marker_mov.setVisible(false);
            d3.selectAll(".val-current .val-value").text("--");
        }
    }
    
    /**
     * Funkcia reagujúca na drag event
     * @returns {void}
     */
    function onDrag(){
        
        var x = d3.mouse(this)[0];
        var i  = bisect_fn( chart_data, x_range.invert(x) );
        x -= MARGINS.left;
        
        if(i === 0 || typeof chart_data[i+1] === "undefined"){ return; } //Index out of bounds, ...
        
        //** Drag start
        if(!isDrag){
            isDrag = true;
            select_line_l.attr("x1", x).attr("x2", x).style("opacity", 1);
            selection_start = x;
            selection_rect  = select_g.append("rect")
                            .attr("class","select-rect")
                            .attr("x", selection_start).attr("width", x-selection_start)
                            .attr("y",0).attr("height",HEIGHT*charts_count);
            
        }
        
        select_line_r.attr("x1", x).attr("x2", x).style("opacity", 1);
        onFocus(chart_data[i],x);
        
        if( x-selection_start > 0 ){
            selection_rect.attr("x",selection_start).attr("width",x-selection_start);
        }
        else{
            selection_rect.attr("x",x).attr("width",selection_start-x);
        }
    }
    
    /**
     * Funkcia reagujúca na dragend event
     * @returns {void}
     */
    function onDragend(){
        
        if(isDrag){
            var x  = d3.mouse(this)[0];
            selection_data_x2 = bisect_fn( chart_data, x_range.invert(x) );
            
            var dt = chart_data._slice(selection_data_x1,selection_data_x2);
            
            chart_draw(dt);
            map_selection(dt);
            
            isDrag     = false;
            isSelected = true;
            isFocused = false;
        }
        else if(isSelected){
            //** On click only, reset selection
            isSelected = false;
            isFocused = false;
            chart_draw(chart_data_original);
            map_reset();
        }
    }
    
    var drag_listener = d3.behavior.drag().on("dragstart",onClick).on("drag", onDrag).on("dragend", onDragend);
    
    d3.select("#chart-canvas").select("svg")
        .on("mousemove", onMousemove)
        .on("mouseout", onMouseout)
        .call(drag_listener);
        
</script><?php

    $chart_create = ob_get_clean();

    Mcore::base()->cachescript->registerCodeSnippet('<script src="' . ROOT_URL . 'libraries/d3.min.js"></script>', 'chart-api', 2);
    Mcore::base()->cachescript->registerCodeSnippet($chart_create, 'chart-create', 2);