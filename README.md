# Rocket Propelled Tortoise CMS - Core

[![Latest Version](https://img.shields.io/github/release/RocketPropelledTortoise/Core.svg?style=flat-square)](https://github.com/RocketPropelledTortoise/Core/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://github.com/RocketPropelledTortoise/Core/blob/master/LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/rocket/core.svg?style=flat-square)](https://packagist.org/packages/rocket/core)
[![Sonar Quality Gate](https://img.shields.io/sonar/alert_status/RocketPropelledTortoise_Core?server=https%3A%2F%2Fsonarcloud.io&style=flat-square)](https://sonarcloud.io/dashboard?id=RocketPropelledTortoise_Core)
[![Sonar Coverage](https://img.shields.io/sonar/coverage/RocketPropelledTortoise_Core?server=https%3A%2F%2Fsonarcloud.io&style=flat-square)](https://sonarcloud.io/dashboard?id=RocketPropelledTortoise_Core)
[![GitHub Workflow Status](https://img.shields.io/github/workflow/status/RocketPropelledTortoise/Core/PHP?style=flat-square)](https://github.com/RocketPropelledTortoise/Core/actions)

Core Components for Rocket Propelled Tortoise CMS

## What is it ?

Rocket Propelled Tortoise "Rocket" is a new generation of CMS. I'll explain why :

These last years the hype has been "Let's give the final user more power over his website give him a backend where he can tweak every small parameter of the CMS."
We ended up with CMS's with a huge admin area, dozens of options you won't need in your use case but will still be processed on each page load to trigger a part of code or an other.
Even worse, that didn't help the end user at all. On each small change he wants to implement, he has to dig to find what he wants or installs a new plugin that will slow down all the application or he calls his Web Agency to ask them to do it for him and a developer has to do it in the interface.

Rocket's approach is totally different : The final user wants to write content, the developer wants to write code.
Let's make both happy !

The only admin area you will find in Rocket is a Content Management Area. There is also no default front-end by default, it is yours to create.
Everything else is in the code !

To achieve this, _Rocket Propelled Tortoise CMS Core_ is a set of Components that interact with the content. The code defines your content types.
A second package: _Rocket Propelled Tortoise CMS UI_ is the administrative area on top of these other modules.

To create a front end you only need the Core part to use your models to query data and display it. With this kind of separation you may even deploy the front and back end on separate servers; perfect for Enterprise Content Management.

## What is it not ?

Rocket is not a "social" CMS, it is more a "presentation" CMS.

By default, Rocket doesn't come with facilities to connect users, interact with them. But you can certainly build it by yourself if you want to.

## Testing

``` bash
composer test
```

### Testing MySQL locally

```bash
docker run --rm -it --name mysql-test -e MYSQL_ROOT_PASSWORD=root -e MYSQL_DATABASE=test_db -p 3307:3306 mysql:5.7

DB_CONNECTION=mysql DB_DATABASE=test_db DB_USERNAME=root DB_PASSWORD=root DB_PORT=3307 composer test

docker stop mysql-test
```

## Contributing

Please see [CONTRIBUTING](https://github.com/RocketPropelledTortoise/Core/blob/master/CONTRIBUTING.md) for details.

## Credits

- [Stéphane Goetz](https://github.com/onigoetz)
- [All Contributors](https://github.com/RocketPropelledTortoise/Core/contributors)

## License

The MIT License (MIT). Please see [License File](https://github.com/RocketPropelledTortoise/Core/blob/master/LICENCE.md) for more information.
