<?php

declare(strict_types=1);

use Utilities\EmailReader;
use Utilities\EmailReaderError;
use Utilities\ErrorCode;
use Utilities\EmailFlag;

class TestEmailReaderTest extends \Codeception\Test\Unit
{
    protected \UnitTester $tester;
    protected EmailReader $emailReader;

    protected function _before(): void
    {
        $this->emailReader = new EmailReader("imap.gmail.com", "username", "password");
    }

    protected function _after(): void
    {
        try {
            $this->emailReader->close();
        } catch (\Throwable) {
            // Ignore close errors in teardown
        }
    }

    public function testOpenMailBox(): \IMAP\Connection|EmailReaderError
    {
        $mailBox = $this->emailReader->openMailBox();

        $this->assertNotTrue($mailBox, "Returned FALSE, which means there is no imap stream");

        return $mailBox;
    }

    public function testGetMailBoxFolders(): array|EmailReaderError
    {
        $mailBoxArray = $this->emailReader->getMailBoxFolders($this->testOpenMailBox());

        $this->assertIsArray($mailBoxArray, "Return was not an array of mailbox folders");
        $this->assertNotEmpty($mailBoxArray, "Return has no mailbox folders");

        $errors2 = $this->emailReader->handleErrors();

        $mailBoxArray2 = $this->emailReader->getMailBoxFolders(null);

        $this->assertEquals(new EmailReaderError(ErrorCode::ImapStream, $errors2), $mailBoxArray2, "No or incorrect error returned");

        return $mailBoxArray;
    }

    public function testOpenMailBoxFolder(): \IMAP\Connection|EmailReaderError
    {
        $folderName = "INBOX";
        $mailBox = $this->emailReader->openMailBox();
        $mailBoxFolder = $this->emailReader->openMailBoxFolder($folderName);

        $this->assertNotEmpty($mailBoxFolder, "Inbox is empty");
        $this->assertNotFalse($mailBoxFolder, "No mailbox folder parsed");

        return $mailBoxFolder;
    }

    public function testOpenMailBoxFolderNegative(): void
    {
        $errors2 = $this->emailReader->handleErrors();
        $folderName2 = "";
        $mailBox2 = $this->emailReader->openMailBox();
        $mailBoxFolder2 = $this->emailReader->openMailBoxFolder($folderName2);

        $this->assertEquals(new EmailReaderError(ErrorCode::MailboxFolder, $errors2), $mailBoxFolder2, "No or incorrect error returned");
    }

    public function testSearch(): array|EmailReaderError
    {
        $searchCriteria = "SUBJECT \"test\"";

        $searchResult = $this->emailReader->search($searchCriteria, $this->testOpenMailBoxFolder());

        $searchArrayCount = count($searchResult);

        $this->assertNotFalse($searchResult, "Returned FALSE, which means either incorrect criteria or no messages have been found");
        $this->assertNotEmpty($searchResult, "Method failed");
        $this->assertEquals(2, $searchArrayCount, "Something went wrong with the method");

        $errors2 = $this->emailReader->handleErrors();
        $searchResult2 = $this->emailReader->search("", $this->testOpenMailBoxFolder());
        $this->assertEquals(new EmailReaderError(ErrorCode::SearchCriteria, $errors2), $searchResult2, "No or incorrect error returned");

        return $searchResult;
    }

    public function testGetSearchResultHeaders(): array|EmailReaderError
    {
        $messageHeaders = $this->emailReader->getSearchResultHeaders($this->testSearch(), $this->testOpenMailBoxFolder());

        $messageHeadersArrayCount = count($messageHeaders);

        $this->assertIsArray($messageHeaders, "Return was not an array");
        $this->assertNotEmpty($messageHeaders, "Array is empty");
        $this->assertEquals(2, $messageHeadersArrayCount, "Something went wrong in the method");

        $errors2 = $this->emailReader->handleErrors();
        $messageHeaders2 = $this->emailReader->getSearchResultHeaders([], $this->testOpenMailBoxFolder());
        $this->assertEquals(new EmailReaderError(ErrorCode::SearchHeadersResult, $errors2), $messageHeaders2, "No or incorrect error returned");

        return $messageHeaders;
    }

    public function testGetMailBoxHeaders(): array|EmailReaderError
    {
        $mailBoxHeaders = $this->emailReader->getMailBoxHeaders($this->testOpenMailBoxFolder());

        $this->assertIsArray($mailBoxHeaders, "Return was not an array");
        $this->assertNotEmpty($mailBoxHeaders, "There are no headers in this array");

        return $mailBoxHeaders;
    }

    public function testGetMessageNumbersForSearch(): array|EmailReaderError
    {
        $messageNumbers = $this->emailReader->getMessageNumbersForSearch($this->testGetSearchResultHeaders());

        $this->assertIsArray($messageNumbers, "Returned was not an array of message numbers");
        $this->assertNotEmpty($messageNumbers, "Returned was empty");

        $messageNumbers2 = $this->emailReader->getMessageNumbersForSearch([]);
        $this->assertEquals(new EmailReaderError(ErrorCode::SearchHeaders), $messageNumbers2, "No or incorrect error returned");

        return $messageNumbers;
    }

    public function testGetMessageHeader(): \stdClass|EmailReaderError
    {
        $messageNumber = 3;
        $messageHeader = $this->emailReader->getMessageHeader($messageNumber, $this->testOpenMailBoxFolder());

        $this->assertIsObject($messageHeader, "Return was not an object");

        $messageNumber2 = 0;

        $errors2 = $this->emailReader->handleErrors();
        $messageHeader2 = $this->emailReader->getMessageHeader($messageNumber2, $this->testOpenMailBoxFolder());
        $this->assertEquals(new EmailReaderError(ErrorCode::MessageNumber, $errors2), $messageHeader2, "No or incorrect error returned");

        $messageNumber3 = 3;

        $errors3 = $this->emailReader->handleErrors();
        $messageHeader3 = $this->emailReader->getMessageHeader($messageNumber3, null);
        $this->assertEquals(new EmailReaderError(ErrorCode::ImapStream, $errors3), $messageHeader3, "No or incorrect error returned");

        return $messageHeader;
    }

    public function testGetMessageData(): \stdClass|EmailReaderError
    {
        $messageNumber = 4;

        $messageData = $this->emailReader->getMessageData($messageNumber, $this->testOpenMailBoxFolder());

        $this->assertIsObject($messageData, "Returned data is not an object");
        $this->assertNotEmpty($messageData, "Returned array is empty");
        $this->assertTrue(property_exists($messageData, "htmlMessage"), "Returned object doesn't have the attribute");

        $errors2 = $this->emailReader->handleErrors();
        $messageNumber2 = 0;
        $messageData2 = $this->emailReader->getMessageData($messageNumber2, $this->testOpenMailBoxFolder());
        $this->assertEquals(new EmailReaderError(ErrorCode::MessageNumber, $errors2), $messageData2, "No or incorrect error returned");

        return $messageData;
    }

    public function testGetMessageDataNegative(): void
    {
        $messageNumber2 = 0;

        $errors2 = $this->emailReader->handleErrors();
        $messageData2 = $this->emailReader->getMessageData($messageNumber2, $this->testOpenMailBoxFolder());
        $this->assertEquals(new EmailReaderError(ErrorCode::MessageNumber, $errors2), $messageData2, "No or incorrect error returned");

        $messageNumber3 = 6;

        $errors3 = $this->emailReader->handleErrors();
        $messageData3 = $this->emailReader->getMessageData($messageNumber3, null);
        $this->assertEquals(new EmailReaderError(ErrorCode::ImapStream, $errors3), $messageData3, "No or incorrect error returned");
    }

    public function testSetMessageStatus(): bool|EmailReaderError
    {
        $messageNumberSequence = 5;

        $newFlags = $this->emailReader->setMessageStatus($messageNumberSequence, EmailFlag::Seen->value, $this->testOpenMailBoxFolder());

        $this->assertTrue($newFlags, "Returned was not TRUE, meaning it failed to set flags to the message");

        $messageNumberSequence2 = 0;
        $errors2 = $this->emailReader->handleErrors();
        $newFlags2 = $this->emailReader->setMessageStatus($messageNumberSequence2, EmailFlag::Seen->value, $this->testOpenMailBoxFolder());

        $this->assertEquals(new EmailReaderError(ErrorCode::EditMessageStatusSequence, $errors2), $newFlags2, "No or incorrect error returned");

        $messageNumberSequence3 = 4;
        $errors3 = $this->emailReader->handleErrors();
        $newFlags3 = $this->emailReader->setMessageStatus($messageNumberSequence3, "", $this->testOpenMailBoxFolder());

        $this->assertEquals(new EmailReaderError(ErrorCode::EditMessageStatusNewMessageStatus, $errors3), $newFlags3, "No or incorrect error returned");

        return $newFlags;
    }

    public function testClearMessageStatus(): bool|EmailReaderError
    {
        $messageNumberSequence = 4;

        $newFlags = $this->emailReader->clearMessageStatus($messageNumberSequence, EmailFlag::Seen->value, $this->testOpenMailBoxFolder());

        $this->assertTrue($newFlags, "Returned was not TRUE, meaning it failed to set flags to the message");

        $messageNumberSequence2 = 0;
        $errors2 = $this->emailReader->handleErrors();
        $newFlags2 = $this->emailReader->clearMessageStatus($messageNumberSequence2, EmailFlag::Seen->value, $this->testOpenMailBoxFolder());

        $this->assertEquals(new EmailReaderError(ErrorCode::EditMessageStatusSequence, $errors2), $newFlags2, "No or incorrect error returned");

        $messageNumberSequence3 = 4;
        $errors3 = $this->emailReader->handleErrors();
        $newFlags3 = $this->emailReader->clearMessageStatus($messageNumberSequence3, "", $this->testOpenMailBoxFolder());

        $this->assertEquals(new EmailReaderError(ErrorCode::EditMessageStatusNewMessageStatus, $errors3), $newFlags3, "No or incorrect error returned");

        return $newFlags;
    }

    public function testClose(): void
    {
        $resultBoolean = $this->emailReader->close($this->testOpenMailBox());

        $this->assertTrue($resultBoolean, "Returned FALSE, this means the close failed");

        $errors2 = $this->emailReader->handleErrors();
        $resultBoolean2 = $this->emailReader->close();

        $this->assertEquals(new EmailReaderError(ErrorCode::ImapStream, $errors2), $resultBoolean2, "No or incorrect error returned");
    }

    public function testDumpAttachments(): void
    {
        $messageData = $this->testGetMessageData();
        $directory = "C:\\Users\\user\\Downloads\\";

        $dumpAttachmentsResult = $this->emailReader->dumpAttachments($messageData, $directory);

        $this->assertTrue($dumpAttachmentsResult);

        $messageData2 = (object)[];
        $directory2 = "C:\\Users\\user\\Downloads\\";
        $dumpAttachmentsResult2 = $this->emailReader->dumpAttachments($messageData2, $directory2);

        $this->assertEquals(new EmailReaderError(ErrorCode::DumpAttachmentsNotExist), $dumpAttachmentsResult2, "No or incorrect error returned");

        $messageData3 = $this->testGetMessageData();
        $directory3 = "";
        $dumpAttachmentsResult3 = $this->emailReader->dumpAttachments($messageData3, $directory3);

        $this->assertEquals(new EmailReaderError(ErrorCode::DumpAttachmentsDirectory), $dumpAttachmentsResult3, "No or incorrect error returned");
    }

    public function testMessageMove(): void
    {
        $sequence = 12;
        $destination = "[Gmail]/Spam";
        $messageMoveResult = $this->emailReader->messageMove($sequence, $destination, $this->testOpenMailBoxFolder());
        $this->assertTrue($messageMoveResult, "Returned was not true");

        $errors2 = $this->emailReader->handleErrors();
        $sequence2 = 0;
        $destination2 = "";
        $messageMoveResult2 = $this->emailReader->messageMove($sequence2, $destination2, $this->testOpenMailBoxFolder());

        $this->assertEquals(new EmailReaderError(ErrorCode::EditMessageStatusSequence, $errors2), $messageMoveResult2, "No or incorrect error returned");

        $errors3 = $this->emailReader->handleErrors();
        $sequence3 = 12;
        $destination3 = "";
        $messageMoveResult3 = $this->emailReader->messageMove($sequence3, $destination3, $this->testOpenMailBoxFolder());

        $this->assertEquals(new EmailReaderError(ErrorCode::DestinationFolder, $errors3), $messageMoveResult3, "No or incorrect error returned");
    }

    public function testMessageCopy(): void
    {
        $sequence = 1;
        $destination = "[Gmail]/Spam";
        $messageCopyResult = $this->emailReader->messageCopy($sequence, $destination, $this->testOpenMailBoxFolder());
        $this->assertTrue($messageCopyResult);

        $errors2 = $this->emailReader->handleErrors();
        $sequence2 = 0;
        $destination2 = "";
        $messageCopyResult2 = $this->emailReader->messageCopy($sequence2, $destination2, $this->testOpenMailBoxFolder());
        $this->assertEquals(new EmailReaderError(ErrorCode::EditMessageStatusSequence, $errors2), $messageCopyResult2, "No or incorrect error returned");

        $errors3 = $this->emailReader->handleErrors();
        $sequence3 = 13;
        $destination3 = "";
        $messageCopyResult3 = $this->emailReader->messageCopy($sequence3, $destination3, $this->testOpenMailBoxFolder());
        $this->assertEquals(new EmailReaderError(ErrorCode::DestinationFolder, $errors3), $messageCopyResult3, "No or incorrect error returned");
    }

    public function testMessageDelete(): void
    {
        $messageNumber = 11;
        $mailBox = $this->testOpenMailBoxFolder();
        $messageDeleteResult = $this->emailReader->messageDelete($messageNumber, $mailBox);
        $this->assertTrue($messageDeleteResult);

        $messageNumber2 = 0;

        $errors2 = $this->emailReader->handleErrors();
        $messageDeleteResult2 = $this->emailReader->messageDelete($messageNumber2, $this->testOpenMailBoxFolder());
        $this->assertEquals(new EmailReaderError(ErrorCode::MessageNumber, $errors2), $messageDeleteResult2, "No or incorrect error returned");
    }
}
