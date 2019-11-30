# rox-php

Rox SDK for PHP

## Requirements

PHP 5.5 or later.

## Development

### Composer


Install composer using these guidelines: https://getcomposer.org/doc/00-intro.md.

After installation proceed to the project root directory and run

```
composer update
```

NOTE: this command generates `vendor/autoload.php` script from the project sources. 
So each time some new class is added to the project you should run this command 
before executing tests (see below).  

### Tests

From the project root run

```
composer update
composer test
```