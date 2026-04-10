<?php

require "./vendor/autoload.php";

use Utilities\EmailReader;

/**
 * Instantiate the constructor and open the IMAP connection
 */
$host = "";
$username = "";
$password = "";
$port = 993; // (optional: default is 993)
$flags = ""; // (optional: default is "/imap/ssl")

$emailReader = new EmailReader($host, $username, $password, $port);

$mailBox = $emailReader->openMailBox($flags);

/**
 * If your port number is 993 and your flags are "/imap/ssl"
 */

$emailReader = new EmailReader($host, $username, $password);

$mailBox = $emailReader->openMailBox();
