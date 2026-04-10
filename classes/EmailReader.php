<?php

declare(strict_types=1);

/**
 * EmailReader is used read and interact with emails from an email server
 */

namespace Utilities;

use IMAP\Connection;

/**
 * Class EmailReader Used to hold all the functions required for use
 * @package Utilities Namespace
 */
class EmailReader
{
    /**
     * @var Connection|null Keeps a handle to a mailbox
     */
    private Connection|null $mailBox = null;

    /**
     * @var string|null The hostname or address of the email server
     */
    private ?string $host;

    /**
     * @var string|null Flags passed for specific mail box
     *
     * Link to optional flags: [https://www.php.net/manual/en/function.imap-open.php]
     */
    private ?string $flags = null;

    /**
     * @var string|null $username Username
     */
    private ?string $username;

    /**
     * @var string|null $password Password, don't commit your passwords into your code repo
     */
    private ?string $password;

    /**
     * @var string|null Holds the HTML messages
     */
    private ?string $htmlMessage = null;

    /**
     * @var string|null Holds plain messages
     */
    private ?string $plainMessage = null;

    /**
     * @var string|null Holds charset
     */
    private ?string $charset = null;

    /**
     * @var array|null Holds attachments file names and data
     */
    private ?array $attachments = null;

    /**
     * @var int|null $port Port to connect to
     */
    public ?int $port = null;

    /**
     * EmailReader constructor.
     * @param string $host Hostname of the email server
     * @param string $username Username
     * @param string $password Password, don't commit your passwords into your code repo
     * @param int $port Port to connect to
     * @example examples/exampleOpen.php
     */
    public function __construct(string $host, string $username, string $password, int $port = 993)
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->port = $port;
    }

    /**
     * Checks if the given value is a valid IMAP connection
     */
    private function isValidConnection(mixed $connection): bool
    {
        return $connection instanceof Connection;
    }

    /**
     * Handles any IMAP errors or alerts
     * @return array ["errors" => , "alerts" => ]
     */
    public function handleErrors(): array
    {
        $errors = imap_errors();
        $alerts = imap_alerts();

        return ["errors" => $errors, "alerts" => $alerts];
    }

    /**
     * Gets back a mail box handle for all future processing
     * @param string $flags Use URL to see available flags
     * @param string|null $folderName Folder name of opened mailbox folder when called by openMailBoxFolder()
     * @return Connection|EmailReaderError IMAP Connection or error message on failure
     * @example examples/exampleOpen.php
     */
    public function openMailBox(string $flags = "/imap/ssl", ?string $folderName = null): Connection|EmailReaderError
    {
        if (!empty($flags)) {
            $this->flags = $flags;
        }
        if (function_exists("imap_open")) {
            $this->mailBox = imap_open(
                "{{$this->host}:{$this->port}{$this->flags}}{$folderName}",
                $this->username,
                $this->password
            );

            $errors = $this->handleErrors();

            if ($this->mailBox) {
                return $this->mailBox;
            } else {
                return new EmailReaderError(ErrorCode::ImapStream, $errors);
            }
        } else {
            return new EmailReaderError(ErrorCode::ImapError);
        }
    }

    /**
     * Gets a list of mailbox folders
     * @param Connection|null $mailBox IMAP Connection
     * @return array|EmailReaderError array of folder names or error message on failure
     * @example examples/exampleFolders.php
     */
    public function getMailBoxFolders(Connection|null $mailBox = null): array|EmailReaderError
    {
        if (empty($mailBox) && !empty($this->mailBox)) {
            $mailBox = $this->mailBox;
        }
        $errors = $this->handleErrors();
        if ($this->isValidConnection($mailBox)) {
            $folders = imap_list($mailBox, "{{$this->host}}", "*");
            $parsedFolders = [];
            foreach ($folders as $folder) {
                $tempName = explode("}", $folder);
                if (isset($tempName[1])) {
                    $parsedFolders[] = $tempName[1];
                } else {
                    $parsedFolders[] = $folder;
                }
            }

            return $parsedFolders;
        } else {
            return new EmailReaderError(ErrorCode::ImapStream, $errors);
        }
    }

    /**
     * Opens a mailbox stream in a specific mailbox folder
     * @param string|null $folderName Folder name
     * @return Connection|EmailReaderError IMAP Connection in the opened mailbox folder or error message on failure
     * @example examples/exampleOpenFolder.php
     */
    public function openMailBoxFolder(?string $folderName = null): Connection|EmailReaderError
    {
        $errors = $this->handleErrors();
        if (!empty($folderName)) {
            $result = $this->openMailBox($this->flags ?? "/imap/ssl", $folderName);

            if ($result instanceof EmailReaderError) {
                return $result;
            }

            $this->mailBox = $result;

            return $this->mailBox;
        } else {
            return new EmailReaderError(ErrorCode::MailboxFolder, $errors);
        }
    }

    /**
     * Gets the message numbers of all messages that contain the parsed criteria
     * @param string $searchCriteria Search criteria. List of search criteria: https://www.php.net/manual/en/function.imap-search.php
     * @param Connection|null $mailBox IMAP Connection
     * @return array|EmailReaderError array of message numbers or error message on failure
     * @example examples/exampleSearch
     */
    public function search(string $searchCriteria, Connection|null $mailBox = null): array|EmailReaderError
    {
        if (empty($mailBox) && !empty($this->mailBox)) {
            $mailBox = $this->mailBox;
        }
        $errors = $this->handleErrors();

        if (!empty($searchCriteria)) {
            if ($this->isValidConnection($mailBox)) {
                $searchResult = imap_search($mailBox, $searchCriteria);

                if ($searchResult !== false) {
                    return $searchResult;
                } else {
                    return new EmailReaderError(ErrorCode::SearchFail, $errors);
                }
            } else {
                return new EmailReaderError(ErrorCode::ImapStream, $errors);
            }
        } else {
            return new EmailReaderError(ErrorCode::SearchCriteria, $errors);
        }
    }

    /**
     * Uses the message numbers to get and put all the message headers into an array
     * @param array $searchResult Array of search results from search()
     * @param Connection|null $mailBox IMAP Connection
     * @return array|EmailReaderError array of headers or error message on failure
     * @example examples/exampleSearchResultHeaders.php
     */
    public function getSearchResultHeaders(array $searchResult, Connection|null $mailBox = null): array|EmailReaderError
    {
        if (empty($mailBox) && !empty($this->mailBox)) {
            $mailBox = $this->mailBox;
        }
        $errors = $this->handleErrors();
        if (!empty($searchResult)) {
            if ($this->isValidConnection($mailBox)) {
                $searchResultHeaders = [];

                foreach ($searchResult as $messageNumber) {
                    $searchResultHeaders[] = imap_headerinfo($mailBox, $messageNumber);
                }
                if (!empty($searchResultHeaders)) {
                    return $searchResultHeaders;
                } else {
                    return new EmailReaderError(ErrorCode::SearchHeadersFail, $errors);
                }
            } else {
                return new EmailReaderError(ErrorCode::ImapStream, $errors);
            }
        } else {
            return new EmailReaderError(ErrorCode::SearchHeadersResult, $errors);
        }
    }

    /**
     * Gets all the headers in the mailbox folder
     * @param Connection|null $mailBox IMAP Connection
     * @return array|EmailReaderError array of headers or error message on failure
     * @example examples/exampleMailBoxHeaders.php
     */
    public function getMailBoxHeaders(Connection|null $mailBox = null): array|EmailReaderError
    {
        if (empty($mailBox) && !empty($this->mailBox)) {
            $mailBox = $this->mailBox;
        }
        $errors = $this->handleErrors();
        if ($this->isValidConnection($mailBox)) {
            return imap_headers($mailBox);
        } else {
            return new EmailReaderError(ErrorCode::ImapStream, $errors);
        }
    }

    /**
     * Gets the headers of a specific message
     * @param int $messageNumber Message number
     * @param Connection|null $mailBox IMAP Connection
     * @return object|EmailReaderError
     * @example examples/exampleMessageHeader.php
     */
    public function getMessageHeader(int $messageNumber, Connection|null $mailBox = null): \stdClass|EmailReaderError
    {
        if (empty($mailBox) && !empty($this->mailBox)) {
            $mailBox = $this->mailBox;
        }
        $errors = $this->handleErrors();
        if (!empty($messageNumber)) {
            if ($this->isValidConnection($mailBox)) {
                return imap_headerinfo($mailBox, $messageNumber);
            } else {
                return new EmailReaderError(ErrorCode::ImapStream, $errors);
            }
        } else {
            return new EmailReaderError(ErrorCode::MessageNumber, $errors);
        }
    }

    /**
     * Returns the ending result data array containing all the message's data
     * @param int $messageNumber Message number
     * @param Connection|null $mailBox IMAP Connection
     * @return object|EmailReaderError message data object or error on failure
     * @example examples/exampleMessageData.php
     */
    public function getMessageData(int $messageNumber, Connection|null $mailBox = null): \stdClass|EmailReaderError
    {
        if (empty($mailBox) && !empty($this->mailBox)) {
            $mailBox = $this->mailBox;
        }
        $errors = $this->handleErrors();
        if (!empty($messageNumber)) {
            if ($this->isValidConnection($mailBox)) {
                $emailMessage = (object)[];

                $structure = imap_fetchstructure($mailBox, $messageNumber);

                if (empty($structure->parts)) {
                    $emailMessage = $this->addMessageDataToArray($messageNumber, $structure, 0, $mailBox);
                } else {
                    foreach ($structure->parts as $partNumber0 => $p) {
                        $emailMessage = $this->addMessageDataToArray($messageNumber, $p, $partNumber0 + 1, $mailBox);
                    }
                }

                // Clear email message and attachments from email class object, to be empty for when next message is read
                $this->plainMessage = null;
                $this->htmlMessage = null;
                $this->attachments = null;

                return $emailMessage;
            } else {
                return new EmailReaderError(ErrorCode::ImapStream, $errors);
            }
        } else {
            return new EmailReaderError(ErrorCode::MessageNumber, $errors);
        }
    }

    /**
     * Returns the object containing the accumulated message parts
     * @param int $messageNumber Message number of specified message
     * @param object $part Object of message part
     * @param int|string $partNumber Part number
     * @param Connection|null $mailBox IMAP Connection
     * @return object|EmailReaderError
     */
    private function addMessageDataToArray(
        int $messageNumber,
        object $part,
        int|string $partNumber,
        Connection|null $mailBox = null
    ): \stdClass|EmailReaderError {
        if (empty($mailBox) && !empty($this->mailBox)) {
            $mailBox = $this->mailBox;
        }
        $errors = $this->handleErrors();
        if (!empty($messageNumber)) {
            if ($this->isValidConnection($mailBox)) {
                $data = ($partNumber) ? imap_fetchbody($mailBox, $messageNumber, (string)$partNumber) : imap_body(
                    $mailBox,
                    $messageNumber
                );

                $params = [];
                if (isset($part->parameters) && $part->parameters) {
                    foreach ($part->parameters as $parameters) {
                        $params[strtolower($parameters->attribute)] = $parameters->value;
                    }
                }
                if (isset($part->ifdparameters) && $part->ifdparameters == 1) {
                    foreach ($part->dparameters as $dparameter) {
                        $params[strtolower($dparameter->attribute)] = $dparameter->value;
                    }

                    if (isset($params["filename"]) || isset($params["name"])) {
                        $filename = ($params["filename"] ?? null) ?: ($params["name"] ?? '');

                        $this->attachments[] = (object)[
                            "encoding" => $part->encoding,
                            "fileName" => $filename,
                            "data" => $data
                        ];
                    }
                }

                if ($part->encoding == ENCQUOTEDPRINTABLE) {
                    $data = quoted_printable_decode($data);
                } elseif ($part->encoding == ENCBASE64) {
                    $data = base64_decode($data);
                }

                if ($part->type == 0 && $data) {
                    if (strtolower($part->subtype) == "plain") {
                        $this->plainMessage .= trim($data) . "\n\n";
                    } else {
                        $this->htmlMessage .= $data;
                        $this->charset = $params["charset"] ?? null;
                    }
                }

                if (isset($part->parts)) {
                    foreach ($part->parts as $partNo0 => $p2) {
                        $this->addMessageDataToArray($messageNumber, $p2, $partNumber . "." . ($partNo0 + 1), $mailBox);
                    }
                }

                return (object)[
                    "htmlMessage" => $this->htmlMessage,
                    "plainMessage" => $this->plainMessage,
                    "charset" => $this->charset,
                    "attachments" => $this->attachments
                ];
            } else {
                return new EmailReaderError(ErrorCode::ImapStream, $errors);
            }
        } else {
            return new EmailReaderError(ErrorCode::MessageNumber, $errors);
        }
    }

    /**
     * Dumps the parsed message data's attachments to a directory location
     * @param object $messageData message data object from getMessageData()
     * @param string $directory Directory destination
     * @return bool|EmailReaderError True on success or error message on failure
     * @example examples/exampleDumpAttachments.php
     */
    public function dumpAttachments(object $messageData, string $directory): bool|EmailReaderError
    {
        if (!empty($directory)) {
            if (!empty($messageData->attachments)) {
                foreach ($messageData->attachments as $attachment) {
                    $fp = fopen($directory . $attachment->fileName, "w+");

                    if ($attachment->encoding == ENCQUOTEDPRINTABLE) {
                        fwrite($fp, quoted_printable_decode($attachment->data));
                    } elseif ($attachment->encoding == ENCBASE64) {
                        fwrite($fp, base64_decode($attachment->data));
                    }
                    fclose($fp);
                }

                return true;
            } else {
                return new EmailReaderError(ErrorCode::DumpAttachmentsNotExist);
            }
        } else {
            return new EmailReaderError(ErrorCode::DumpAttachmentsDirectory);
        }
    }

    /**
     * Sets the message status by setting message flags
     * @param int|string $sequence Message number(s) for the flags to be set on. Example: "2,5" - message numbers 2 to 5
     * @param string $newMessageStatus Message of parsed EmailFlag value
     * @param Connection|null $mailBox IMAP Connection
     * @return bool|EmailReaderError True on success or error message on failure
     * @example examples/exampleSetMessageStatus.php
     */
    public function setMessageStatus(
        int|string $sequence,
        string $newMessageStatus,
        Connection|null $mailBox = null
    ): bool|EmailReaderError {
        if (empty($mailBox) && !empty($this->mailBox)) {
            $mailBox = $this->mailBox;
        }
        $errors = $this->handleErrors();
        if (!empty($sequence)) {
            if (!empty($newMessageStatus)) {
                if ($this->isValidConnection($mailBox)) {
                    $setFlagResult = imap_setflag_full($mailBox, (string)$sequence, $newMessageStatus);

                    if ($setFlagResult) {
                        imap_expunge($mailBox);

                        return true;
                    } else {
                        return new EmailReaderError(ErrorCode::EditMessageSetFlag, $errors);
                    }
                } else {
                    return new EmailReaderError(ErrorCode::ImapStream, $errors);
                }
            } else {
                return new EmailReaderError(ErrorCode::EditMessageStatusNewMessageStatus, $errors);
            }
        } else {
            return new EmailReaderError(ErrorCode::EditMessageStatusSequence, $errors);
        }
    }

    /**
     * Sets the message status by clearing message flags
     * @param int|string $sequence Message number(s) for the flags to be cleared on. Example: "2,5" - message numbers 2 to 5
     * @param string $clearedMessageStatus Message of parsed EmailFlag value
     * @param Connection|null $mailBox IMAP Connection
     * @return bool|EmailReaderError True on success or error message on failure
     * @example examples/exampleClearMessageStatus.php
     */
    public function clearMessageStatus(
        int|string $sequence,
        string $clearedMessageStatus,
        Connection|null $mailBox = null
    ): bool|EmailReaderError {
        if (empty($mailBox) && !empty($this->mailBox)) {
            $mailBox = $this->mailBox;
        }
        $errors = $this->handleErrors();
        if (!empty($sequence)) {
            if (!empty($clearedMessageStatus)) {
                if ($this->isValidConnection($mailBox)) {
                    $clearFlagResult = imap_clearflag_full($mailBox, (string)$sequence, $clearedMessageStatus);

                    imap_expunge($mailBox);

                    if ($clearFlagResult) {
                        return true;
                    } else {
                        return new EmailReaderError(ErrorCode::EditMessageClearFlag, $errors);
                    }
                } else {
                    return new EmailReaderError(ErrorCode::ImapStream, $errors);
                }
            } else {
                return new EmailReaderError(ErrorCode::EditMessageStatusNewMessageStatus, $errors);
            }
        } else {
            return new EmailReaderError(ErrorCode::EditMessageStatusSequence, $errors);
        }
    }

    /**
     * Moves message(s) to a specified folder
     * @param int|string $sequence Message number(s) for the flags to be set on. Example: "2,5" - message numbers 2 to 5
     * @param string $destination Destination mailbox folder name
     * @param Connection|null $mailBox IMAP Connection
     * @return bool|EmailReaderError True on success or error message on failure
     * @example examples/exampleMessageMove.php
     */
    public function messageMove(
        int|string $sequence,
        string $destination,
        Connection|null $mailBox = null
    ): bool|EmailReaderError {
        if (empty($mailBox) && !empty($this->mailBox)) {
            $mailBox = $this->mailBox;
        }
        $errors = $this->handleErrors();

        if (!empty($sequence)) {
            if (!empty($destination)) {
                if ($this->isValidConnection($mailBox)) {
                    $moveResult = imap_mail_move($mailBox, (string)$sequence, $destination);

                    if ($moveResult) {
                        imap_expunge($mailBox);

                        return true;
                    } else {
                        return new EmailReaderError(ErrorCode::MessageMove, $errors);
                    }
                } else {
                    return new EmailReaderError(ErrorCode::ImapStream, $errors);
                }
            } else {
                return new EmailReaderError(ErrorCode::DestinationFolder, $errors);
            }
        } else {
            return new EmailReaderError(ErrorCode::EditMessageStatusSequence, $errors);
        }
    }

    /**
     * Copies message(s) and pastes it in a specified folder
     * @param int|string $sequence Message number(s) for the flags to be set on. Example: "2,5" - message numbers 2 to 5
     * @param string $destination Destination mailbox folder name
     * @param Connection|null $mailBox IMAP Connection
     * @return bool|EmailReaderError True on success or error message on failure
     * @example examples/exampleMessageCopy.php
     */
    public function messageCopy(
        int|string $sequence,
        string $destination,
        Connection|null $mailBox = null
    ): bool|EmailReaderError {
        if (empty($mailBox) && !empty($this->mailBox)) {
            $mailBox = $this->mailBox;
        }
        $errors = $this->handleErrors();

        if (!empty($sequence)) {
            if (!empty($destination)) {
                if ($this->isValidConnection($mailBox)) {
                    $copyResult = imap_mail_copy($mailBox, (string)$sequence, $destination);

                    if ($copyResult) {
                        return true;
                    } else {
                        return new EmailReaderError(ErrorCode::MessageMove, $errors);
                    }
                } else {
                    return new EmailReaderError(ErrorCode::ImapStream, $errors);
                }
            } else {
                return new EmailReaderError(ErrorCode::DestinationFolder, $errors);
            }
        } else {
            return new EmailReaderError(ErrorCode::EditMessageStatusSequence, $errors);
        }
    }

    /**
     * Deletes a specific message
     * @param int $messageNumber The message number
     * @param Connection|null $mailBox IMAP Connection
     * @return bool|EmailReaderError True for success or error on failure
     * @example examples/exampleMessageDelete.php
     */
    public function messageDelete(int $messageNumber, Connection|null $mailBox = null): bool|EmailReaderError
    {
        if (empty($mailBox) && !empty($this->mailBox)) {
            $mailBox = $this->mailBox;
        }
        $errors = $this->handleErrors();
        if (!empty($messageNumber)) {
            if ($this->isValidConnection($mailBox)) {
                $setFlagResult = imap_setflag_full($mailBox, (string)$messageNumber, EmailFlag::Deleted->value);
                if ($setFlagResult) {
                    imap_expunge($mailBox);

                    return true;
                } else {
                    return new EmailReaderError(ErrorCode::DeleteMessage, $errors);
                }
            } else {
                return new EmailReaderError(ErrorCode::ImapStream, $errors);
            }
        } else {
            return new EmailReaderError(ErrorCode::MessageNumber, $errors);
        }
    }

    /**
     * Extracts message numbers from search result headers
     * @param array $searchResultHeaders Array of header objects from getSearchResultHeaders()
     * @return array|EmailReaderError Array of message numbers or error on failure
     */
    public function getMessageNumbersForSearch(array $searchResultHeaders): array|EmailReaderError
    {
        if (empty($searchResultHeaders)) {
            return new EmailReaderError(ErrorCode::SearchHeaders);
        }

        $messageNumbers = [];
        foreach ($searchResultHeaders as $header) {
            if (isset($header->Msgno)) {
                $messageNumbers[] = (int)trim($header->Msgno);
            }
        }

        if (empty($messageNumbers)) {
            return new EmailReaderError(ErrorCode::SearchHeaders);
        }

        return $messageNumbers;
    }

    /**
     * Closes the mailbox stream
     * @param Connection|null $mailBox IMAP Connection
     * @return bool|EmailReaderError True for success or error message on failure
     * @example examples/exampleClose.php
     */
    public function close(Connection|null $mailBox = null): bool|EmailReaderError
    {
        if (empty($mailBox) && !empty($this->mailBox)) {
            $mailBox = $this->mailBox;
        }
        $errors = $this->handleErrors();
        if ($this->isValidConnection($mailBox)) {
            $closeResult = imap_close($mailBox, CL_EXPUNGE);

            if ($closeResult) {
                return true;
            } else {
                return new EmailReaderError(ErrorCode::ImapCloseFailure, $errors);
            }
        } else {
            return new EmailReaderError(ErrorCode::ImapStream, $errors);
        }
    }
}
