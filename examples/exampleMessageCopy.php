<?php

use Utilities\EmailFlag;

/**
 * Copy a message to another mailbox folder
 * Note: Some email servers require certain flags to be set to messages before being able to copy them to their respective mailbox folders
 */

$emailReader->setMessageStatus(2, EmailFlag::Draft->value);

$emailReader->messageCopy(2, $folders[2]); // Example: Destination folder = $folders[2] ([Gmail]/Drafts)
