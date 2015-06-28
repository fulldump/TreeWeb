<img src="logo.png">

TreeWeb is a *fast web applications development framework* for PHP.

All developing decisions taken in the TreeWeb follows two key principles: fast to develop and fast to execute.

These are the reasons of several layers of caching, integrated online code editor, automatic code generation, component system, precompilation, convention instead of perfect theorical software architecture, hierarchical routing, etc.

<!-- MarkdownTOC autolink=true bracket=round depth=4 -->

- [Testing](#testing)
- [STORM](#storm)
    - [Database](#database)
- [Working on...](#working-on)
- [Requirements](#requirements)
- [Milestones](#milestones)

<!-- /MarkdownTOC -->

## Testing

```sh
php test file/you/want/to/test/file.test.php
```

By convention, all test files has the extension `.test.php`.

Sample test:

```php
Tests::add('Test one', function($t) {
	$t->log('You can log things');
});

Tests::add('Test two', function($t) {
	throw new Exception('accidental exception');
});

Tests::add('Test three', function($t) {
	$_is_going_to_fail = true;
	if ($_is_going_to_fail) {
		$t->error('this should not fail');
	}
});
```

Useful way of developing with tests:

```sh
watch -n 1 php test file/you/want/to/test/file.test.php
```

Will execute tests each second:

```text
Every 1,0s: php test packages/core/php/Tests.test.php   Wed Jun 24 22:25:20 2015

Create a row...OK
Modify a row...OK
Delete all rows...OK
PASS
```

## STORM

STORM is the acronym for **S**imple **T**reeWeb **ORM**

### Database

Connect to a database. Typical usage:

```php
import('storm.Database');

// Only the first time:
Database::configure('localhost', 'my_db', 'root', '123456');

// Perform a query:
$cursor = Database::sql("SELECT * FROM Users");

// Perform several queries (return the result for the last one):
$cursor = Database::sql(array(
    "INSERT INTO Users (Name) VALUES ('Fulanitez')",
    "INSERT INTO Users (Name) VALUES ('Fulanitez')",
    "SELECT * FROM Users",
));

// Escape values
$search = Database::escape($_POST['search']);
$cursor = Database::sql("SELECT * FROM Users WHERE Name Like '%$search%'");
```

## Working on...

* Package system

## Requirements

* Package system
* Configuration (two levels: global and by domain)
* STORM integration + data origins
* Main class
* Index
* URL rewritting
* Routing system
* Nodes: page, php, reference and directory mapping
* Component system
* Documentation site
* Code documentation system
* Profiling tool
* Template system
* Image transcoding
* Cache system
* Trunk framework migration
* Testing framework for javascript
* Code minification: javascript and css
* Third party libs integration

## Milestones

* Testing framework for php (very simple but functional version)
* Keep (old named FileStore)
* TreeScript
