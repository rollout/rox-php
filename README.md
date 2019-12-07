# rox-php

Rox SDK for PHP

## Requirements

PHP 5.5 or later.

## Development

### Prerequisites

Install `composer` using these guidelines: https://getcomposer.org/doc/00-intro.md.

After installation proceed to the project root directory and run

```
composer update
```  

### Tests

From the project root run

```
composer update
composer test
```

### Running Demo Server

Demo uses the following environment variables defined in the system:

 - `ROLLOUT_MODE` - can be `LOCAL`, `QA` (default), or `PROD`
 - `ROLLOUT_API_KEY`
 - `ROLLOUT_DEV_MOD_KEY`
 
These variables can be set before run. To start demo server at `localhost:8080` run

```
composer run demo
```
