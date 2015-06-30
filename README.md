<img src="logo.png">

<img src="https://travis-ci.org/GerardoOscarJT/TreeWeb.svg?branch=master"> Ready for PHP 5.5, 5.6

TreeWeb is a *fast web applications development framework* for PHP.

All developing decisions taken in the TreeWeb follows two key principles: fast to develop and fast to execute.

These are the reasons of several layers of caching, integrated online code editor, automatic code generation, component system, precompilation, convention instead of perfect theorical software architecture, hierarchical routing, etc.

<!-- MarkdownTOC autolink=true bracket=round depth=4 -->

- [Testing](#testing)
- [STORM](#storm)
    - [Database](#database)
- [Working on...](#working-on)
- [Requirements](#requirements)
    - [Tier 0](#tier-0)
    - [Tier 1](#tier-1)
    - [Tier 2](#tier-2)
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
* Index
* Main class

## Requirements

### Tier 0

* Package system
* STORM integration + data origins
* Main class
* Index
* Component system
* Template system
* Cache system

### Tier 1
* Image transcoding
* Profiling tool
* Trunk framework migration

### Tier 2
* Documentation site
* Code documentation system
* Testing framework for javascript
* Code minification: javascript and css
* Third party libs integration

## Milestones

* Testing framework for php (very simple but functional version)
* Keep (old named FileStore)
* TreeScript
* URL rewritting
* Routing system
* Nodes: page, php, reference and directory mapping
* Configuration (two levels: global and by domain)
