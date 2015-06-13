<img src="logo.png">

Fast web applications development framework for PHP

* [Testing](#testing)
* [Working on](#working-on)
* [Requirements](#requirements)

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

## Working on

* Package system
* Testing framework for php

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
