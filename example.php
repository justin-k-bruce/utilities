<?php

declare(strict_types=1);

require_once "vendor/autoload.php";

use Utilities\EmailReader;
use Utilities\EmailReaderError;
use Utilities\EmailFlag;

// Open a connection to server
$emailReader = new EmailReader("imap.gmail.com", "username", "password");

$mailBox = $emailReader->openMailBox();

// Get a list of folders from the server
$folders = $emailReader->getMailBoxFolders();

// Open a specific folder
$mailBoxFolder = $emailReader->openMailBoxFolder($folders[0]);

// Get all headers in the folder
$headers = $emailReader->getMailBoxHeaders();

// Read a specific message
$email = $emailReader->getMessageData(7);

if ($email instanceof EmailReaderError) {
    print_r($email->getError());
} else {
    print_r($email);
}

// Move a message to another folder
$sequence = 3;
$destination = "[Gmail]/Drafts";
$moveResult = $emailReader->messageMove($sequence, $destination, $mailBoxFolder);

// Copy a message to another folder
$copyMessage = $emailReader->messageCopy(1, $destination);

// Set a message flag
$emailReader->setMessageStatus(2, EmailFlag::Seen->value);

// Close the connection
$emailReader->close();
