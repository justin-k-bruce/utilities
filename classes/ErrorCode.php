<?php

declare(strict_types=1);

namespace Utilities;

/**
 * Backed enum for EmailReader error codes and their corresponding messages
 */
enum ErrorCode: string
{
    case ImapError = '80001';
    case ImapStream = '80002';
    case ImapList = '80003';
    case MailboxFolder = '80004';
    case MailboxHeaders = '80005';
    case MessageHeader = '80006';
    case MessageData = '80007';
    case EditMessageFlags = '80008';
    case SearchHeaders = '80009';
    case MessageNumber = '80010';
    case ImapCloseFailure = '80011';
    case EditMessageSetFlagsAndClearFlags = '80012';
    case DumpAttachmentsData = '80013';
    case DumpAttachmentsDirectory = '80014';
    case DumpAttachmentsNotExist = '80015';
    case EditMessageStatusSequence = '80016';
    case EditMessageStatusNewMessageStatus = '80017';
    case EditMessageSetFlag = '80018';
    case EditMessageClearFlag = '80019';
    case DeleteMessage = '80020';
    case DestinationFolder = '80021';
    case MessageMove = '80022';
    case SearchFail = '80023';
    case SearchHeadersFail = '80024';
    case SearchHeadersResult = '80025';
    case SearchCriteria = '80026';
    case NoError = '00000';

    public function message(): string
    {
        return match ($this) {
            self::ImapError => 'Imap is not installed',
            self::ImapStream => 'Failed to open imap stream',
            self::ImapList => 'Failed to get a list of mailbox folders',
            self::MailboxFolder => 'Failed to open specific folder in mailbox',
            self::MailboxHeaders => 'Failed to get mailbox headers',
            self::MessageHeader => 'Failed to get message header',
            self::MessageData => 'Failed to get message data',
            self::EditMessageFlags => 'Failed to edit message flags',
            self::SearchHeaders => 'No search headers parsed',
            self::MessageNumber => 'No message number parsed',
            self::ImapCloseFailure => 'Failed to close imap stream',
            self::EditMessageSetFlagsAndClearFlags => 'Both setFlags() and clearFlags() have the same parameters',
            self::DumpAttachmentsData => 'No parsed message data',
            self::DumpAttachmentsDirectory => 'No parsed directory',
            self::DumpAttachmentsNotExist => 'No attachments found for parsed message data',
            self::EditMessageStatusSequence => 'No valid sequence number(s) parsed',
            self::EditMessageStatusNewMessageStatus => 'No valid new message statuses parsed',
            self::EditMessageSetFlag => 'Failed to set flags',
            self::EditMessageClearFlag => 'Failed to clear flags',
            self::DeleteMessage => 'Failed to delete message',
            self::DestinationFolder => 'No valid message folder parsed',
            self::MessageMove => 'Failed to move message',
            self::SearchFail => 'Failed to find messages of parsed search criteria',
            self::SearchHeadersFail => 'No headers found from parsed searched message number(s)',
            self::SearchHeadersResult => 'No search results parsed',
            self::SearchCriteria => 'Invalid search criteria',
            self::NoError => 'No Error',
        };
    }
}
