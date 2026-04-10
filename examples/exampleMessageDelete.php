<?php
/**
 * Delete a specific message number from the mailbox folder
 */

$emailReader->messageDelete(2); //Deletes message number 2 in the mailbox folder

/**
 * If you want to delete a message in a different mailbox folder
 * Note: If you do this you might have to close the newly opened IMAP connection
 */

$otherMailBoxFolder = $emailReader->openMailBoxFolder($folders[2]);

$emailReader->messageDelete(2, $otherMailBoxFolder);
//$emailReader->close($otherMailBoxFolder);