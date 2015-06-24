# <img src="logo.png">

TreeWeb is a fast web applications development framework for PHP.

<!-- MarkdownTOC -->

- [Testing][testing]
- [Working on...][working-on]
- [Requirements][requirements]
- [Milestones][milestones]

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

## Working on...

* FileStore
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

