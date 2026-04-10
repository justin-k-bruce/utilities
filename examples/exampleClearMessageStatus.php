<?php

use Utilities\EmailFlag;

/**
 * Clear flags from a message in the mailbox folder
 * Use the EmailFlag enum: EmailFlag::Seen, EmailFlag::Flagged, EmailFlag::Deleted, EmailFlag::Draft or EmailFlag::Answered
 */

$sequence = "2,5"; //message numbers 2 to 5

$emailReader->clearMessageStatus($sequence, EmailFlag::Seen->value); //Message now becomes flagged as "\\Unseen"

/**
 * If you want to clear a flag of a message in a different mailbox folder
 * Note: If you do this you might have to close the newly opened IMAP connection
 */

$otherMailBoxFolder = $emailReader->openMailBoxFolder($folders[2]);

$messageHeader2 = $emailReader->clearMessageStatus($sequence, EmailFlag::Answered->value, $otherMailBoxFolder);
//$emailReader->close($otherMailBoxFolder);
