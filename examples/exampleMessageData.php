<?php
/**
 * Get an object containing the message's data (email data)
 */

$messageData = $emailReader->getMessageData(2);

/**
 * If you want to get message data of a message in a different mailbox folder
 * Note: If you do this you might have to close the newly opened IMAP connection
 */

$otherMailBoxFolder = $emailReader->openMailBoxFolder($folders[2]);

$messageData = $emailReader->getMessageData(2, $otherMailBoxFolder);
//$emailReader->close($otherMailBoxFolder);