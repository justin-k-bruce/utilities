<?php

/**
 * Using the returned array of mailbox folders, open an IMAP connection to the selected mailbox folder to specify where you are getting your messages from
 */
$mailBoxFolder = $emailReader->openMailBoxFolder($folders[0]);