<?php
/**
 * Created by PhpStorm.
 * User: andrevanzuydam
 * Date: 2019-08-19
 * Time: 09:30
 */

require_once  "vendor/autoload.php";


$emailReader = new \Utilities\EmailReader("oxyros.co.za", "glocell.oxyros", "jct1969", 143);

$mailBox = $emailReader->openMailBox();

$folders = $emailReader->getMailBoxFolders($mailBox);


print_r ($folders);
//Open a connection to server
//Get a list of folders from the server
//Get a list of email headers from one of the folders - how do I know which folder ?
//Choose an email header - read the message from the server
//Save any attachments into attachment folder


//Manual: Check that the above things have worked.





