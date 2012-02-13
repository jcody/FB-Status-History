<?php
/**
 * @file
 *   Create a status graph based on likes/comments
 */

function print_graph() {
  $statuses = statuses_retrieve();
  $graph_data = new stdClass();
  $graph_data = array(
    'period_3' => generate_data($statuses, 3),
    'period_6' => generate_data($statuses, 6),
    'period_12' => generate_data($statuses, 12)
  ); 
  $statuses_json = graph_prepare_data($statuses);
?>
  <script type="text/javascript">
    var graph_data = <?php print json_encode($graph_data); ?>;
    var statuses = <?php print $statuses_json; ?>
  </script>
  <div id="placeholder" style="width:720px;height:250px;"></div>

  <br />

  <div class="timeframe"><input class="fetchSeries uibutton" id="button-1" graph_period="12" type="button"
	value="One year">
  <span></span></div>

  <div class="timeframe"><input class="fetchSeries uibutton" id="button-2" graph_period="6" type="button"
	value="6 months"> 
  <span></span></div>

  <div class="timeframe"><input class="fetchSeries uibutton" id="button-3" graph_period="3" type="button"
	value="3 Months">
  <span></span></div>
  
  <div class="clearfloat"></div>
  
<?php   
}

/**
 * Generate the json data to be plotted in the graph  
 * 
 * The data is an array of graph coordinates, which in our case is in this form
 * ([time], [number of likes/comments])  
 * 
 * @param $period
 *   The period to calculate the most popular status (by month, e.g. 6 or 12 months)
 */
function generate_data($statuses, $period = null) {
  $oldest_status = get_oldest($statuses);  
  $oldest_year = get_year($oldest_status['updated_time']); // year of the oldest status
  $oldest_status_month = get_month($oldest_status['updated_time']); // month of the oldest status  
  $newest_status = get_newest($statuses);  
  $newest_year = get_year($newest_status['updated_time']); // year of the most recent status
  $newest_status_month = get_month($newest_status['updated_time']); // month of the most recent status     
  $start_month = floor(($oldest_status_month - 1) / $period) * $period + 1; // when we start recording
  $stop_month = ceil(($newest_status_month - 1) / $period) * $period + 1; // when we stop recording
  // print("start: " . $start_month . " stop: " . $last_month);
   
      
  // Currently the graph can only plot time period of 2, 3, 4, 6
  // So make it 12 months if an invalid number is passed
  if (($period == null) || ($period > 12) || ((12 % $period) != 0)) {
  	$period = 12;
  }
  
  // Testing
  $start_month = 1;
  $from = "01-$start_month-$oldest_year";
  
  $data = array();  
  for ($year = $oldest_year; $year <= $newest_year; $year++) {  	
  	$to = "01-$start_month-$year";  	
  	for ($month = $start_month; $month <= 12 + 1; $month += $period) {
  	  // print("from " . $from . " to: " . $to);  	    	    	    	  
  	  $most_popular = get_most_popular($statuses, '', $from, $to);
  	  if ($most_popular) {
  	    $popularity = $most_popular['like_count'] + $most_popular['comment_count']; 
   	  }
  	  else {
  	    $popularity = 0;
  	  }    	    	    	 
  	  // Notes: The Flot API can only accept time data as Javascript timestamp
  	  // We need to pass a valid date string and generate timestamp at front end
  	  // If it's the first loop, don't record
  	  if ($to != $from) {
  	    $data[] = array(date('M j, Y', strtotime("$to")), $popularity);
  	  }  	    	    	 
  	  $from = $to;
  	  $to = "01-$month-$year";

  	  // if ($year == $newest_year && $month == $stop_month) {
  	  //	break;
  	  // }
  	}  	
  	
  	$start_month = 1;
  }	  
     
  return $data;
}

/**
 * Extract the year from a given date string
 */
function get_year($date) {
  return date('Y', strtotime($date));
}

/**
 * Extract the month from a given date string
 */
function get_month($date) {
  return date('n', strtotime($date));
}

/**
 * Prepare the data to pass to JS. JS will further process the data and plot the graph
 * 
 * JS only need the following data: 
 *   - Status id
 *   - HTML representation of the status
 *   - Post date
 *   - Like and comment count
 */
function graph_prepare_data($statuses) {
  $data = array();
  foreach ($statuses as $status) {
  	$data[] = array(
  	  'id' => $status['id'],
  	  'html' => '', // for now
  	  'post_date' => date('M j, Y', strtotime($status['updated_time'])),
  	  'like_count' => $status['like_count'],
  	  'comment_count' => $status['comment_count'],
  	);
  }	
  
  return json_encode($data);
}

?>
