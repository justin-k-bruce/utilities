<?php
/**
 * Close the IMAP connection
 */

$emailReader->close(); //Closes current IMAP connection

/**
 * If you want to close a different IMAP connection you can pass it as a parameter
 */
$otherMailBoxFolder = $emailReader->openMailBoxFolder($folders[2]);

$emailReader->close($otherMailBoxFolder); //Closes a different IMAP connection
