# Contributing

## Getting Started

If you've downloaded or cloned this repo from source, make sure you install dependencies before
you do anything else.

```bash
composer install
```

Unfortunately, Composer can't define conditional dependency versions contingent on system software versions.
So PHPUnit is not defined as a dev dependency because its version will depend on your PHP version. Instead,
Please use the provided shell script to install the appropriate verion based on your system.

This script will download PHPUnit to `./vendor/bin/phpunit`.

```bash
/bin/bash ./bin/install-phpunit.sh
```

## Pull Requests

Before making a Pull Request (PR), please ensure you have linted your code against the [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standard.

```bash
composer lint
```

You can also often auto-correct your code using the following command.

```bash
composer lint-autofix
```

You'll also need to ensure your changes don't cause any regression.

```bash
composer test
```

If you want to run only a specific category of tests, you can also use one of these commands:

```bash
composer test-unit
composer test-service
composer test-network
composer test-integration
```

Or, if you want to only run a specific test case, you can call the PHPUnit binary script with whatever arguments
you need.

For example:

```bash
./vendor/bin/phpunit --colors=always ./test --filter testNaRegionalApiEnvironmentSelection
```
