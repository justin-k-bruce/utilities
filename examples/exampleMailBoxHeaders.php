<?php
/**
 * Get an array of all the headers in the mailbox folder
 */

$emailHeaders = $emailReader->getMailBoxHeaders();

/**
 * If you want to get the headers of a different mailbox folder
 * Note: If you do this you might have to close the newly opened IMAP connection
 */

$otherMailBoxFolder = $emailReader->openMailBoxFolder($folders[1]);

$emailHeaders2 = $emailReader->getMailBoxHeaders($otherMailBoxFolder);
//$emailReader->close($otherMailBoxFolder);
