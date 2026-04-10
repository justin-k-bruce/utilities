<?php
/**
 * Get the header of a specific message number in the mailbox folder
 */

$messageHeader = $emailReader->getMessageHeader(2);

/**
 * If you want to get a message header of a message in a different mailbox folder
 * Note: If you do this you might have to close the newly opened IMAP connection
 */

$otherMailBoxFolder = $emailReader->openMailBoxFolder($folders[2]);

$messageHeader2 = $emailReader->getMessageHeader(2, $otherMailBoxFolder);
//$emailReader->close($otherMailBoxFolder);