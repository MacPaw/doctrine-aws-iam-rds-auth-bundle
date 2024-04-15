# doctrine-aws-iam-rds-auth-bundle
Doctrine AWS IAM RDS Auth Bundle provides a Doctrine DBAL driver decorator that supports AWS IAM RDS authentication.

## Installation
Use Composer to install the bundle:
```
composer require macpaw/doctrine-aws-iam-rds-auth-bundle
```

## Applications that don't use Symfony Flex
Enable the bundle by adding it to the list of registered bundles in ```config/bundles.php```

```php
// config/bundles.php
<?php

return [
            Macpaw\DoctrineAwsIamRdsAuthBundle\DoctrineAwsIamRdsAuthBundle::class => ['all' => true],
        // ...
    ];
```

## Configuration
Set the following environment variables in your ```.env``` file:
```
AWS_REGION # AWS region
USE_IAM # Set to 1 to enable AWS IAM RDS authentication
RDS_TOKEN_LIFETIME_MINUTES # RDS token lifetime in minutes
RDS_TOKEN_CACHE_LIFETIME_SECONDS # RDS token cache lifetime in seconds
SSL_MODE # SSL mode
SSL_ROOT_CERT_PATH # Path to the root certificate
```
Add sslmode and sslrootcert to your database configuration in ```config/packages/doctrine.yaml```:
```yaml
#example
doctrine:
  dbal:
    url: 'postgresql://%env(DB_USER)%:%env(DB_PASSWORD)%@%env(DB_HOST)%:%env(DB_PORT)%/%env(DB_NAME)%?serverVersion=%env(DB_SERVER_VERSION)%&charset=%env(DB_CHARSET)%&sslmode=%env(SSL_MODE)%&sslrootcert=%env(SSL_ROOT_CERT_PATH)%'
```

Specify CacheStorageInterface in ```config/services.yaml```:
```yaml
services:
    Macpaw\DoctrineAwsIamRdsAuthBundle\Cache\CacheStorageInterface:
      class: Macpaw\DoctrineAwsIamRdsAuthBundle\Cache\CacheStorage
      arguments:
        $cacheAdapter: '@redis_cache_adapter' # Implementation of Symfony\Component\Cache\Adapter\AdapterInterface
```
## Links
[RDS SSL Certificates](https://docs.aws.amazon.com/AmazonRDS/latest/UserGuide/UsingWithRDS.SSL.html#UsingWithRDS.SSL.CertificatesAllRegions)
