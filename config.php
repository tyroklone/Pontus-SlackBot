<?php

/**
 * Pontus Slackbot
 * 
 * Details: This file is part of the pontus slackbot file
 * Author: Josef
 * Modified By: titaro
 *
 */

// Database information
$dbname = "ikhgynl5yi6ppldx";
$user = "jse831u4uvmhykrg";
$host = "s9xpbd61ok2i7drv.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$password = "ji7u73u42vplhnzb";

$conn = mysqli_connect("$host", "$user", "$password", "$dbname");

// Database prefix
define("PON_PREFIX","PON_");

?>
