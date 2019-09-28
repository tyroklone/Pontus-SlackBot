<?php

/**
 * Pontus Slackbot
 * 
 * Details: This file is part of the pontus slackbot file
 * Author: Ugbogu Justice
 * Modified By: titaro
 *
 */
 
 ini_set("allow_url_fopen", 1);

session_start();

// Lets include some important files here
$dbname = "";
$user = "";
$host = "";
$password = "";

$conn = mysqli_connect("$host", "$user", "$password", "$dbname");

// Database prefix
define("PON_PREFIX","pon_");



$con_string = $conn;//db_mysqli_connnection_hook_here

$tbl_users = PON_PREFIX."user";//users_table_name_here
$tbl_users_email_column_name = "email";//users_table_email_field_name_here

$tbl_conversations = PON_PREFIX."convo";//conversations_table_name_here
$tbl_conversations_id_name = "cid";//conversations_table id column name here
$tbl_conversations_email_column_name = "user_convo";//conversations_table email column name here
$tbl_conversations_conversation_column_name = "user_conversation";//conversations_table conversation column name here
$tbl_conversations_timestamp_column_name = "convo_time";

// if user table does not exist creating
$users_tbl_structure = "CREATE TABLE IF NOT EXISTS `$tbl_users` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `full_name` varchar(255) NOT NULL,
	  `email` varchar(255) NOT NULL,
	  `username` varchar(255) NOT NULL,
	  `phone_number` varchar(255) NOT NULL,
	  `password` varchar(255) NOT NULL,
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=latin1";
$conn->query($users_tbl_structure);

function verifyUrl($token){

	$static = "0d609cb8-30f4-4329-9e5d-7b5c554163fc";
	$floating = $token;

	if ($static != $floating) {
		return false;
	}else{
		return true;
	}
}


function verifyEmail($email,$tbl,$con_string,$tbl_column_name){
	$result = $con_string->query("SELECT * FROM $tbl WHERE `$tbl_column_name` = '$email' ");
        $count=$result->num_rows;
        if($count == 0) // user doesn't exist in our external drive
        { 
    	  return false;
        }else{
           
            $fetch_user_data = $result->fetch_assoc();
            $myObj = new stdClass();
            $myObj->username = $fetch_user_data['username'];
            $myObj->email = $fetch_user_data['email'];
            $myObj->password = $fetch_user_data['password'];
    
            $myJSON = json_encode($myObj);
            return $myJSON;
        
        }
       
}


function conversation($con_string,$tbl,$tbl_structure,$queryTable){
    // Checking if conversation has been made
	$sql_check_tbl_existance = $con_string->query("SELECT 1 FROM $tbl Limit 1");
	if ($sql_check_tbl_existance === FALSE) {
		// making table if not exist
		$con_string->query($tbl_structure);   
	}
	// inserting conversation
	$con_string->query($queryTable);
}


//Get the request method.
$requestType = $_SERVER['REQUEST_METHOD'];

//Switch statement
switch ($requestType) {
    case 'POST':
      handle_post_request($tbl_users,$con_string,$tbl_users_email_column_name,$tbl_conversations,$tbl_conversations_id_name,$tbl_conversations_email_column_name,$tbl_conversations_conversation_column_name,$tbl_conversations_timestamp_column_name);  
      break;
    case 'GET':
      handle_get_request($tbl_users,$con_string,$tbl_users_email_column_name);  
      break;
    default:
      //request type that isn't being handled.
      handle_unknown_request();
      break;
}
function handle_unknown_request(){
    //who are you loading this api  (post/get  == false) method , oya start dey go our main branch
        header("location:../");
        return false;//do not process any other thing, kick them off this place
    
}
function handle_post_request($tbl_users,$con_string,$tbl_users_email_column_name,$tbl_conversations,$tbl_conversations_id_name,$tbl_conversations_email_column_name,$tbl_conversations_conversation_column_name,$tbl_conversations_timestamp_column_name){
    
    if($_SERVER['REQUEST_METHOD']==='POST' && empty($_POST)) {
        $_POST = json_decode(file_get_contents('php://input'));
        $json_email = $_POST->email;
        $json_token = $_POST->token;
        $json_data = $_POST->message;
    }else{
        $json_email = isset($_POST['email']) ? $_POST['email'] : '';
        $json_token =  isset($_POST['token']) ? $_POST['token'] : '';
        $json_data =  isset($_POST['message']) ? $_POST['message'] : '';
    }
    
    //real
    $url_email = $json_email;
    //test
    // $url_email =  'groundnut@fruit.lactose';
    //real
    $url_token = $json_token;
    //test
    // $url_token =  '0d609cb8-30f4-4329-9e5d-7b5c554163fc';
    //real
    $url_message = $json_data;
    //test
    // $url_message =  '<@UN9PP45GA> save my convo here';
    
    
    if(isset($url_email) && $url_email != ''){
    	$verifyEmail = verifyEmail($url_email,$tbl_users, $con_string, $tbl_users_email_column_name);
    }else{
     $verifyEmail = false;
    }
    
    if(isset($url_token) && $url_token != ''){
    	$verifyUrl = verifyUrl($url_token);
    }else{
     $verifyUrl = false;
    }
    
    // var_dump($verifyUrl);

    // var_dump($verifyEmail);
     
    
    // making sure we are processing from known source and if source returned a valid email in our server
    if($verifyUrl === true){ 
        if(!($verifyEmail === false)){
            if (isset($url_message) && $url_message != '') {
            	$from_get = $url_message;
            	$arr1 = explode(' ',trim($from_get));
            	$first = $arr1[0];
            	$first_1 = str_replace("<@", '', $first);
            	$first_2 = str_replace(">", '', $first_1);
            	$slack_user_id = $first_2;
            	
            	$conversation_key = trim(str_replace($first, "", $from_get));
            	$conversation = trim(str_replace("save my convo", "", $conversation_key));//obtained from bot once a message is made by slack user
            
            	$theTableStructure = "CREATE TABLE `$tbl_conversations` (
            	  `$tbl_conversations_id_name` int(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY  NOT NULL,
            	  `$tbl_conversations_email_column_name` varchar(225) NOT NULL,
            	  `$tbl_conversations_conversation_column_name` varchar(225) NOT NULL,
            	  `$tbl_conversations_timestamp_column_name` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            	) ENGINE=InnoDB DEFAULT CHARSET=latin1";
            
            	$queryTable = "INSERT INTO $tbl_conversations(`$tbl_conversations_email_column_name`,`$tbl_conversations_conversation_column_name`) VALUES('$url_email','$conversation')";
            
            	conversation(
            		$con_string,
            		$tbl_conversations,
            		$theTableStructure,
            		$queryTable
            	);
            	echo $verifyEmail;//bot should say -conversation saved
            }else{
              echo "Nothing to save";//bot should say you gat nothing to save
            }	
        }else{
            $myObj = new stdClass();
            $myObj->username = '';
            $myObj->email = '';
            $myObj->password = '';
    
            $myJSON = json_encode($myObj);
            echo $myJSON;
            return false;//bot should say -conversation not saved
        }
    }else{
       handle_unknown_request();
    }
}

function handle_get_request($tbl_users,$con_string,$tbl_users_email_column_name){
    $url_token = isset($_GET['token']) ? $_GET['token'] : '';
    $url_email = isset($_GET['email']) ? $_GET['email'] : '';
    
    $verifyUrl = verifyUrl($url_token);
    if($verifyUrl === true){
        $verifyEmail = verifyEmail($url_email,$tbl_users, $con_string, $tbl_users_email_column_name);
        
        if(!($verifyEmail === false)){
            echo $verifyEmail;
            return false;
        }else{
            $myObj = new stdClass();
            $myObj->username = '';
            $myObj->email = '';
            $myObj->password = '';
    
            $myJSON = json_encode($myObj);
            echo $myJSON;
            return false;
        }
    }else{
        handle_unknown_request();
    }
}

?>



