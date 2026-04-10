<?php

declare(strict_types=1);

namespace Utilities;

/**
 * Backed enum for email message flags used with IMAP
 */
enum EmailFlag: string
{
    case Seen = "\\Seen";
    case Flagged = "\\Flagged";
    case Deleted = "\\Deleted";
    case Draft = "\\Draft";
    case Answered = "\\Answered";
}
