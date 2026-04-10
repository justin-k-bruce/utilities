<?php

use Utilities\EmailFlag;

/**
 * Set flags to a message in the mailbox folder
 * Use the EmailFlag enum: EmailFlag::Seen, EmailFlag::Flagged, EmailFlag::Deleted, EmailFlag::Draft or EmailFlag::Answered
 */

$sequence = "2,5"; //message numbers 2 to 5

$emailReader->setMessageStatus($sequence, EmailFlag::Seen->value);

/**
 * If you want to set a flag of a message in a different mailbox folder
 * Note: If you do this you might have to close the newly opened IMAP connection
 */

$otherMailBoxFolder = $emailReader->openMailBoxFolder($folders[2]);

$messageHeader2 = $emailReader->setMessageStatus($sequence, EmailFlag::Answered->value, $otherMailBoxFolder);
//$emailReader->close($otherMailBoxFolder);
