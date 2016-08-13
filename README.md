# Jane OpenAPI Autogenerate

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/PurpleBooth/jane-open-api-autogenerate/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/PurpleBooth/jane-open-api-autogenerate/?branch=master)
[![Build Status](https://travis-ci.org/PurpleBooth/jane-open-api-autogenerate.svg?branch=master)](https://travis-ci.org/PurpleBooth/jane-open-api-autogenerate)
[![Dependency Status](https://www.versioneye.com/user/projects/57ad0ad6cb5df20031a64a5c/badge.svg?style=flat-square)](https://www.versioneye.com/user/projects/57ad0ad6cb5df20031a64a5c)
[![Latest Stable Version](https://poser.pugx.org/purplebooth/jane-open-api-autogenerate/v/stable)](https://packagist.org/packages/purplebooth/jane-open-api-autogenerate)
[![License](https://poser.pugx.org/purplebooth/jane-open-api-autogenerate/license)](https://packagist.org/packages/purplebooth/jane-open-api-autogenerate)

This project is designed to allow you to add an automatically generated
swagger as a dependency via a composer dependency.

## Getting Started

### Prerequisities

You'll need to install:

 * PHP (Minimum 5.6)

## Usage

You have a few options, you could add to the repository you keep your
swagger definition define a composer.json that looks something like
this.

```json
{
    "name": "swagger/petstore",
    "type": "swagger-api",
    "extra": {
        "namespace": "Swagger\\Petstore",
        "schema-file": "http://petstore.swagger.io/v2/swagger.json"
    },
    "autoload": {
        "psr-4": {
            "Swagger\\Petstore\\": "generated/"
        }
    },
    "require": {
        "purplebooth/jane-open-api-autogenerate": ""
    }
}
```

and then run

```
$ composer require swagger/petstore
```

alternatively you could add the package manually in the composer.json

```json
{
    "require": {
        "swagger/petstore": "0.1.0",
        "purplebooth/jane-open-api-autogenerate": ""
    },
    "repositories": [{
        "type": "package",
        "package": {
            "autoload": {
                "psr-4": {
                    "Swagger\\Petstore\\": "generated/"
                }
            },
            "type": "swagger-api",
            "name": "swagger/petstore",
            "version": "0.1.0",
            "extra": {
                "namespace": "Swagger\\Petstore",
                "schema-file": "http://petstore.swagger.io/v2/swagger.json"
            },
            "require": {
                "purplebooth/jane-open-api-autogenerate": ""
            }
        }
    }
}
```

Another feature of this library is the ability to override the swagger
file from a location defined by an environment variable. To do this you
simply define an additional key in the package specifies in which
environment variable.

```json
"extra": {
    "namespace": "Swagger\\Petstore\\",
    "schema-file": "http://petstore.swagger.io/v2/swagger.json",
    "environment-variable": "PETS_SWAGGER_YAML"
},
```

If it's not set it'll fall back to the value defined in the schema-file
attribute.

### Coding Style

We follow PSR2, and also enforce PHPDocs on all functions. To run the tests for coding style violations

```bash
vendor/bin/phpcs -p --standard=psr2 src/
```

## Contributing

Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details on our code
of conduct, and the process for submitting pull requests to us.

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions
available, see the [tags on this repository](https://github.com/purplebooth/jane-open-api-autogenerate/tags).

## Authors

See the list of [contributors](https://github.com/purplebooth/jane-open-api-autogenerate/contributors) who participated in this project.

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.
