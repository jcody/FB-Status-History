/**
 * JS support for graph
 *
 * Global variables passed from PHP
 *    statuses
 *    graph_data
 */

/**
 * Toggle button active (blue) and inactive states
 */
$(document).ready(function() {
  $(".timeframe #button-2").addClass("confirm");
	
  $(".timeframe .uibutton").click(function(){
	$(".timeframe .confirm").toggleClass("confirm");
	$(this).next().slideToggle();
	$(this).toggleClass("confirm");
  });
  
  $(".timeright .uibutton:last").addClass("confirm");
	
  $(".timeright .uibutton").click(function(){
	$(".timeright .confirm").toggleClass("confirm");
	$(this).next().slideToggle();
	$(this).toggleClass("confirm");
  });
});

$(document).bind('loaded', function() {
  // Toggle button active (blue) and inactive states	
  $(".timeframe #button-2").addClass("confirm");	
  
  $(".timeframe .uibutton").click(function(){
	$(".timeframe .confirm").toggleClass("confirm");
	$(this).next().slideToggle();
	$(this).toggleClass("confirm");
  });
    
  $(".timeright .uibutton:last").addClass("confirm");
  	
  $(".timeright .uibutton").click(function(){
	$(".timeright .confirm").toggleClass("confirm");
	$(this).next().slideToggle();
	$(this).toggleClass("confirm");
  });
  
  // Specify the Graph options	   
  var options = {
      lines: { show: true },
      points: { show: true },
      xaxis: { mode: "time", timeformat: "%b %y", tickSize: [3, "month"], 
       	       monthNames: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"]},
      yaxis: { tickDecimals: 0, tickSize: 10 }        
  };
  
  // Change date to JS timestamp          
  for (var key in graph_data) {
    if(graph_data.hasOwnProperty(key)) {      	
      for (var i = 0; i < graph_data[key].length; i++) {         	   
        graph_data[key][i][0] = Date.parse(graph_data[key][i][0]);	
      }
    }
  }    
  
  // Plot the graph for 6 months initially  
  var data = [];	
  data.push(graph_data.period_6);         
  var placeholder = $("#placeholder");        
  $.plot(placeholder, data, options);        
  
  // Redraw the graph when clicking buttons   
  $("input.fetchSeries").click(function () {
    var button = $(this);
	var graph_period = button.attr('graph_period');
    
    // Reset data 
    data = [];
    switch (graph_period) {
      case '3':            
        data.push(graph_data.period_3);
		break;
	  case '6':
		data.push(graph_data.period_6);
		break;
	  case '12':
		data.push(graph_data.period_12);
		break;              
    }
    $.plot(placeholder, data, options);
  });

  // Initiate a recurring data update
  $("input.dataUpdate").click(function () {
    // reset data
    data = [];
    alreadyFetched = {};
        
    $.plot(placeholder, data, options);

    var iteration = 0;
        
    function fetchData() {
        ++iteration;

        function onDataReceived(series) {
          	alert("asdf");
            // we get all the data in one go, if we only got partial
            // data, we could merge it with what we already got
            data = [ series ];
            console.log(data);
            $.plot($("#placeholder"), data, options);
        }
        
        $.ajax({
            // usually, we'll just call the same URL, a script
            // connected to a database, but in this case we only
            // have static example files so we need to modify the
            // URL
            url: "data-eu-gdp-growth-" + iteration + ".json",
            method: 'GET',
            dataType: 'json',
            success: onDataReceived
        });
           
        if (iteration < 5)
            setTimeout(fetchData, 1000);
        else {
            data = [];
            alreadyFetched = {};
        }
    }

    setTimeout(fetchData, 1000);
  });
});