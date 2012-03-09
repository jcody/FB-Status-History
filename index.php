<?php 
// MyPastStatues (tentative name) FB Application
// 8/16/2011 

// Include the main Facebook PHP library
require_once 'php-sdk/facebook.php';
// Include the component files
require_once 'includes/status.php';
require_once 'includes/graph.php';
require_once 'includes/feed.php';
//require_once 'includes/pics.php';

// FB App details	
$app_id = "233868763323548";
$app_secret = "9fa776021a4760d8946d74a9dbeac900"; 
$canvas_page = "https://apps.facebook.com/status-timecapsule";//"http://hollow-snow-2685.heroku.com/";	

// Create the facebook API object
$facebook = new Facebook(array(
	'appId' => $app_id,
	'secret' => $app_secret,
));
  	

// Check if the user id is present
// If it is present, the user has used the application before
// If it isn't, this is the user's first time on the application


$user = $facebook->getUser();

if ($user) {
  try {
    $user_profile = $facebook->api('/me');
  } catch (FacebookApiException $e) {
    $user = null;
  }
}

if (!$user) {//empty($data["user_id"])) {

	// If the user id is not present, redirect the user to the 
	// page to allow the application access to his data
	
	// Permissions that the application requires
	$fb_perms_required = "user_status, read_stream,publish_stream"; 

	$auth_url2 = "http://www.facebook.com/dialog/oauth?client_id=" 
		. $app_id
 . "&redirect_uri=" . urlencode($canvas_page)
		. "&scope=" . $fb_perms_required;
	$fb_root = 'http://apps.facebook.com/the_time_capsule';
	$auth_url = $facebook->getLoginUrl(array('scope'=>$fb_perms_required,'redirect_uri'=>$canvas_page));//'canvas'=>1,'fbconnect'=>0,'next'=>$fb_root));//,'display'=>'iframe'));//'grant_type'=>'client_credentials'));//'type'=>'user_agent'));
	// send a script tag to
	echo "<script>top.location.href='" . $auth_url . "';
	// Make Google Analytics happy
	var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-803292-17']);
  _gaq.push(['_setDomainName', '.compute-1.amazonaws.com']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
	
	</script>";
	// type='text/javascript'
	// Stop script execution. Any code below exit() will not be executed.
	exit(); 
	
}

//authentication with user_status, read_stream permission
if(isset($_POST["signed_request"])) {
	$signed_request = $_POST["signed_request"];
  // The portion of signed request before the first period contains the signature
  // The portion after contains the encoded data
  // See: http://developers.facebook.com/docs/authentication/signed_request/
  list($encoded_sig, $payload) = explode('.', $signed_request, 2); 

  // The data is encoded by base 64. We have to do some decoding to get the 
  // actual piece of data. For explanation on the details, see the signed_request link above
  $data = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);

  // Get the user information and store it in a global variable
  $user = $facebook->api('me/');
}




/***************************************************************************
 ******************************** Main Page ********************************
 ***************************************************************************/


function index_page() {
  global $user;
  print theme_header();
  // Print a welcome message 
  // $user['name']
  print ("<h1>Status Time Capsule</h1><div id='fb-like-button'><div id='fb-root'></div></div><script src='http://connect.facebook.net/en_US/all.js#appId=251997751507458&amp;xfbml=1'></script><fb:like href='http://apps.facebook.com/status-timecapsule/' send='true' layout='button_count' width='200' show_faces='false' font=''></fb:like><div class='clearfloat'></div>");
  print theme_footer();
}


function ajax_content_page() {
  global $user;
  $statuses = statuses_retrieve();
  
  // Display graph
  print_graph();
  // Display karma index
  print_karma($statuses);
	// Display the most popular status
  print_most_popular($statuses);
  // Display the oldest status
  print_oldest($statuses);
  // Display all statuses
  print_statuses($statuses);
  
  
  //print '<iframe src="http://www.facebook.com/plugins/like.php?app_id=141366179290507&amp;href=http%3A%2F%2Fwww.facebook.com%2Fapps%2Fapplication.php%3Fid%3D233868763323548&amp;send=false&amp;layout=standard&amp;width=450&amp;show_faces=true&amp;action=like&amp;colorscheme=light&amp;font&amp;height=80" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:450px; height:80px;" allowTransparency="true"></iframe>';
  
  post_feed();
  
  print '<a href="/?q=privacy">Privacy Policy</a>';
  //$pics = pics_retrieve();
  //print_pics($pics);
  if (!empty($user)) {
    log_user($user);
  }
}


/***************************************************************************
 ***************************** Page dispatch *******************************
 ***************************************************************************/

function theme_header($really_load=TRUE) {
  //$statuses = statuses_retrieve();
	
  return <<<EOS
	<!DOCTYPE html>
	<html lang="en">
	<head>
		<title>Status Time Capsule</title>
	  	<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="styles/style.css" />
		<link href="http://fonts.googleapis.com/css?family=Lobster:regular" rel="stylesheet" type="text/css" >
		<link href="http://fonts.googleapis.com/css?family=Droid+Sans:400,700" rel="stylesheet" type="text/css">	
  <script language="javascript" type="text/javascript">
  
EOS
. 'var _really_load = ' . ($really_load ? 'true' : 'false') . ';'
. 'var _POST = ' . json_encode($_POST) . ';'
//. 'var graph_data = ' . generate_data($statuses) . ';'
. <<<EOS
  </script>
	<script language="javascript" type="text/javascript" src="scripts/jquery-1.6.2.min.js"></script>
	<script language="javascript" type="text/javascript" src="scripts/spin.min.js"></script>
    <script language="javascript" type="text/javascript" src="scripts/loading.js"></script>
    <script language="javascript" type="text/javascript" src="scripts/graph.js"></script>
  
  <script language="javascript" type="text/javascript" src="js/flot/jquery.flot.js"></script>
  <script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-803292-17']);
  _gaq.push(['_setDomainName', '.compute-1.amazonaws.com']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
</head>

<body>
<div id="wrapper">

EOS;
}


function theme_footer() {
  return <<<EOS
<div id="spinner" style="margin-top: 8em;"></div>
<div id="main"></div>
</div>
</body>
</html>
EOS;
}


function page_dispatch() {
  $module_whitelist = array('index', 'ajax_content', 'privacy');
  
  $module = 'index'; // default module
  if (isset($_GET['q']) && in_array($_GET['q'], $module_whitelist)) {
    $module = $_GET['q'];
  }

  // Optionally you may put the callbacks inside the following file, see json.php for an example
  $page_fn = dirname(__FILE__) . "/includes/$module.php";
  if (file_exists($page_fn)) {
    require_once $page_fn;
  }
  $page_callback = "{$module}_page";
  $page_callback();
}


page_dispatch();



	//Temporarily disabling cause I have no idea what the fuck this does
	/* 
	// FB App details
	include_once('settings.php');

  	
  	//authentication with user_status, read_stream permission
  	$code = $_REQUEST["code"];
  	if(empty($code)) {
  	  $dialog_url = "http://www.facebook.com/dialog/oauth?client_id=" 
  	  . $app_id . "&redirect_uri=" . urlencode($my_url) 
  	  . "&scope=user_status,read_stream";
    echo("<script>top.location.href='" . $dialog_url . "'</script>");
  	}

  	$token_url = "https://graph.facebook.com/oauth/access_token?client_id="
    	. $app_id . "&redirect_uri=" . urlencode($my_url) 
    	. "&client_secret=" . $app_secret 
    	. "&code=" . $code;
    	$response = file_get_contents($token_url);
    	$params = null;
    	parse_str($response, $params);
    	$access_token = $params['access_token'];
    
    
  
  	//get json feed of the user's statuses
  	$post_url = "https://graph.facebook.com/me/statuses?"
  	. "access_token=". $access_token;
  	$response = file_get_contents($post_url);
  	$decoded_response = json_decode($response);
  	//$photo_id = $decoded_response->data[0]->id;
  */

?>
