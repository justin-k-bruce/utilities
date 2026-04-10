## Requirements

- PHP 8.2+
- ext-imap

## Installation

```sh
composer install
```

## Unit Testing

### How to create a unit test
```sh
vendor/bin/codecept g:test unit <testName>
```

### Run tests
```sh
vendor/bin/codecept run
```

## How to use

### Open IMAP connection
```php
use Utilities\EmailReader;

$emailReader = new EmailReader("host", "username", "password", 993);

$mailBox = $emailReader->openMailBox("/imap/ssl");
```
Both port (default: `993`) and flags (default: `"/imap/ssl"`) are optional.

### Get an array of mailbox folders to choose from
```php
$folders = $emailReader->getMailBoxFolders();
```

### Open the desired mailbox folder from the returned array
Use the returned folders array to select which mailbox folder you want to open:
```php
$mailBoxFolder = $emailReader->openMailBoxFolder($folders[0]);
```

### Get the headers of all the messages in the selected mailbox folder
```php
$headers = $emailReader->getMailBoxHeaders($mailBoxFolder);
```

### Read a selected message in the mailbox folder
Select which message number you want to read from the mailbox folder:
```php
$messageNumber = 418;

$email = $emailReader->getMessageData($messageNumber, $mailBoxFolder);
```

### Dump the attached files to a specified directory location
If there is an attachment on the email you want to download, supply the email data and a valid directory path:
```php
$directory = "C:\\Users\\user\\Downloads\\";

$emailReader->dumpAttachments($email, $directory);
```

### Set message status
Set flags on messages using the `EmailFlag` enum:
```php
use Utilities\EmailFlag;

$messageNumberSequence = 2;

$emailReader->setMessageStatus($messageNumberSequence, EmailFlag::Seen->value);
```

Available flags: `EmailFlag::Seen`, `EmailFlag::Flagged`, `EmailFlag::Deleted`, `EmailFlag::Draft`, `EmailFlag::Answered`.

### Clear message status
Clear flags from messages using the `EmailFlag` enum:
```php
use Utilities\EmailFlag;

$messageNumberSequence = 2;

$emailReader->clearMessageStatus($messageNumberSequence, EmailFlag::Seen->value);
```

### Move or copy messages to a different mailbox folder
**Important:** Some email servers require specific flags to be set before moving messages to a specific folder. For example, on a Gmail server, in order for messages to be moved to the Drafts folder (`[Gmail]/Drafts`) the message first needs to be flagged as `EmailFlag::Draft`.

#### Move:
```php
$messageNumberSequence = 2;

$emailReader->messageMove($messageNumberSequence, $folders[2]);
```

#### Copy:
```php
$messageNumberSequence = 2;

$emailReader->messageCopy($messageNumberSequence, $folders[2]);
```

### Delete a message in the mailbox folder
```php
$messageNumber = 2;

$emailReader->messageDelete($messageNumber);
```

### Search for messages

#### Search for message numbers containing specific criteria
Returns an array of message numbers containing the search criteria. A list of search criteria can be found here: https://www.php.net/manual/en/function.imap-search.php
```php
$searchCriteria = "SUBJECT \"test\"";

$searchResults = $emailReader->search($searchCriteria);
```

#### Get the headers for the search results
Requires the search results from the initial search and returns the headers of all the searched emails:
```php
$searchResultHeaders = $emailReader->getSearchResultHeaders($searchResults);
```

#### Extract message numbers from search result headers
```php
$messageNumbers = $emailReader->getMessageNumbersForSearch($searchResultHeaders);
```

### Error handling
All methods return an `EmailReaderError` instance on failure. Error codes are defined in the `ErrorCode` enum:
```php
use Utilities\EmailReaderError;
use Utilities\ErrorCode;

$result = $emailReader->openMailBox();

if ($result instanceof EmailReaderError) {
    $error = $result->getError();
    echo $error->errorCode;    // e.g. "80002"
    echo $error->errorMessage; // e.g. "Failed to open imap stream"
}
```

### Close the IMAP connection
```php
$emailReader->close();
```
