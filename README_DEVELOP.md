## Development

### Prerequisites

Install `composer` using these guidelines: https://getcomposer.org/doc/00-intro.md.

After installation proceed to the project root directory and run

```
composer install
```  

### Tests

From the project root run

```
composer install
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

### Release new version
create a branch
update version semver in /src/Rox/Core/Client/DeviceProperties.php
merge to master (make sure unit tests and E2E tests are passing)
create a new github release, use branch `master` and add a `tag` with the version name (same version from DeviceProperties.php)
go to packagist https://packagist.org/packages/rollout/rox, and click `update` (you will only see action buttons if you are logged in, and a maintainer of this package, if you are not, please ask from one of the maintainers on the right panel to add you)
make sure the new version shows with the right version name
(run E2E tests using this new version)
