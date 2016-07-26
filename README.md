Teknoo Software - East Framework
================================

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/6d14de07-2c9e-4070-a044-c9362fe2dc08/mini.png)](https://insight.sensiolabs.com/projects/6d14de07-2c9e-4070-a044-c9362fe2dc08) [![Build Status](https://travis-ci.org/TeknooSoftware/east-framework.svg?branch=master)](https://travis-ci.org/TeknooSoftware/east-framework)

East framework is a bundle to implement the [#east](http://blog.est.voyage/phpTour2015/) philosophy in Symfony project :
All public method of objects must return $this or a new instance of $this.

This bundle uses PSR7 requests and responses and do automatically the conversion from Symfony's requests and responses.
So your controllers and services can be independent of Symfony. This bundle reuse internally Symfony's components
to manage routes and find controller to call.

It is a prototype, other adaptation for others framework may be released :).

Demo
----

Simple demo with Symfony 3.0 available [here](https://github.com/TeknooSoftware/east-framework-demo)

Installation & Requirements
---------------------------
To install this library with composer, run this command :

    composer require teknoo/east-framework

This library requires :

    * PHP 7+
    * Symfony 3+
    * Composer

Credits
-------
Richard Déloge - <richarddeloge@gmail.com> - Lead developer.
Teknoo Software - <http://teknoo.software>

About Teknoo Software
---------------------
**Teknoo Software** is a PHP software editor, founded by Richard Déloge. 
Teknoo Software's DNA is simple : Provide to our partners and to the community a set of high quality services or software,
 sharing knowledge and skills.

License
-------
East Framework is licensed under the MIT License - see the licenses folder for details

Contribute :)
-------------

You are welcome to contribute to this project. [Fork it on Github](CONTRIBUTING.md)
