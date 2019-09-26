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
// if user table does not exist creating
$users_tbl = PON_PREFIX."users";
$users_tbl_structure = "CREATE TABLE IF NOT EXISTS `$users_tbl` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `full_name` varchar(255) NOT NULL,
	  `email` varchar(255) NOT NULL,
	  `username` varchar(255) NOT NULL,
	  `phone_number` varchar(255) NOT NULL,
	  `password` varchar(255) NOT NULL,
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=latin1";
$conn->query($users_tbl_structure);
$con_string = $conn;//db_mysqli_connnection_hook_here
$tbl_users = PON_PREFIX."users";//users_table_name_here
$tbl_users_email_column_name = "email";//users_table_email_field_name_here
$tbl_conversations = PON_PREFIX."convo";//conversations_table_name_here
$tbl_conversations_id_name = "cid";//conversations_table id column name here
$tbl_conversations_email_column_name = "user_convo";//conversations_table email column name here
$tbl_conversations_conversation_column_name = "user_conversation";//conversations_table conversation column name here
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
    if($count < 1) // user doesn't exist in our external drive
    { 
    	return false;
    }else{
    	return true;
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
 if($_SERVER['REQUEST_METHOD']==='POST' && empty($_POST)) {
 $_POST = json_decode(file_get_contents('php://input'));
  $json_email = $_POST->email;
  $json_token = $_POST->token;
  $json_data = $_POST->message;
  }
//real
$url_email = $json_email;
// Save email in browser cookie for easy accessibility in dashboard
if(isset($url_email)
{
 // One hour cookie is ok
 setcookie("slack_email", $url_email, time() + 3600, "/");
}
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
}
if(isset($url_token) && $url_token != ''){
	$verifyUrl = verifyUrl($url_token);
}
// var_dump($verifyUrl);
// var_dump($verifyEmail);
// making sure we are processing from known source and if source returned a valid email in our server
if($verifyUrl === true AND $verifyEmail === true){
	if (isset($url_message) && $url_message != '') {
		$from_get = $url_message;
		$arr1 = explode(' ',trim($from_get));
		$first = $arr1[0];
		$first_1 = str_replace("<@", '', $first);
		$first_2 = str_replace(">", '', $first_1);
		$slack_user_id = $first_2;
		
		$email =  $url_email;
		$conversation_key = trim(str_replace($first, "", $from_get));
		$conversation = trim(str_replace("save my convo", "", $conversation_key));//obtained from bot once a message is made by slack user
		$theTableStructure = "CREATE TABLE `$tbl_conversations` (
		  `$tbl_conversations_id_name` int(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY  NOT NULL,
		  `$tbl_conversations_email_column_name` varchar(225) NOT NULL,
		  `$tbl_conversations_conversation_column_name` varchar(225) NOT NULL
		) ENGINE=InnoDB DEFAULT CHARSET=latin1";
		$queryTable = "INSERT INTO $tbl_conversations(`$tbl_conversations_email_column_name`,`$tbl_conversations_conversation_column_name`) VALUES('$email','$conversation')";
		conversation(
			$con_string,
			$tbl_conversations,
			$theTableStructure,
			$queryTable
		);
		echo $email;//bot should say -conversation saved
	}else{
	  echo "Nothing to save";//bot should say you gat nothing to save
       }	
}else{
	echo "error";//bot should say -conversation not saved
}

?>
