<?php 
// MyPastStatues (tentative name) FB Application
// 8/16/2011 

	
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
  

?>