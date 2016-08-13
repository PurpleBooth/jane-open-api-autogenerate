#!/usr/bin/env bash

set -euo pipefail

COMPOSER_FLAGS=${COMPOSER_FLAGS:-""}
TEST_DIR="$HOME/test"

cp -r test "$TEST_DIR"

cat > "$TEST_DIR/composer.json" << COMPOSER
{
  "name": "billie/jane-openapi-autogenerate-test-$TRAVIS_BRANCH-$TRAVIS_COMMIT",
  "authors": [
    {
      "name": "Billie Thompson",
      "email": "billie@purplebooth.co.uk"
    }
  ],
  "minimum-stability": "dev",
  "require": {
    "swagger/petstore": "0.1.0"
  },
  "repositories": [
    {
      "type": "package",
      "package": {
        "autoload": {
          "psr-4": {
            "Swagger\\\\Petstore\\\\": "generated/"
          }
        },
        "type": "swagger-api",
        "name": "swagger/petstore",
        "version": "0.1.0",
        "extra": {
          "namespace": "Swagger\\\\Petstore",
          "schema-file": "swagger.json"
        },
        "require": {
          "purplebooth/jane-open-api-autogenerate": "*"
        }
      }
    },
    {
      "type": "path",
      "url": "$TRAVIS_BUILD_DIR"
    }
  ]
}
COMPOSER

composer update -vvv $COMPOSER_FLAGS --working-dir="$TEST_DIR" --no-interaction --prefer-stable

exec php "$TEST_DIR/test.php"
