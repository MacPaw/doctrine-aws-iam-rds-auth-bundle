#!/usr/bin/env bash

#!/usr/bin/env bash

rm -rf tests/App/var/cache/*
symfonyVersion=${1:-6.4.*}
dbalVersion=${2:-3.0}

rm -rf vendor composer.lock;
composer require symfony/config:$symfonyVersion --no-update --no-scripts
composer require symfony/dependency-injection:$symfonyVersion --no-update --no-scripts
composer require symfony/http-kernel:$symfonyVersion --no-update --no-scripts
composer require symfony/cache:$symfonyVersion --no-update --no-scripts
composer require doctrine/dbal:$dbalVersion --no-update --no-scripts -W
composer require --dev symfony/yaml:$symfonyVersion --no-update --no-scripts
composer require --dev symfony/phpunit-bridge:$symfonyVersion --no-update --no-scripts
composer require --dev symfony/framework-bundle:$symfonyVersion --no-update --no-scripts

composer install

