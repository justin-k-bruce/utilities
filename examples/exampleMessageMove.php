<?php

use Utilities\EmailFlag;

/**
 * Move a message to another mailbox folder
 * Note: Some email servers require certain flags to be set to messages before being able to move them to their respective mailbox folders
 */

$emailReader->setMessageStatus(2, EmailFlag::Draft->value);

$emailReader->messageMove(2, $folders[2]); // Example: Destination folder = $folders[2] ([Gmail]/Drafts)
