<img align="right" src="https://raw.githubusercontent.com/BrightFlair/PHP.Gt/master/Logo.png" alt="PHP.Gt logo" />

Welcome to PHP.Gt — a lightweight **PHP7** application development toolkit aimed at streamlining development and respecting web technologies.

PHP frameworks offer many features, but often come with steep learning curves or imposing rules. The motivation behind PHP.Gt is the belief that what a framework can offer can be achieved by **eliminating code rather than adding more**.

[Head over to the Github Wiki for documentation](https://github.com/g105b/PHP.Gt/wiki)

***

[![Build Status](http://img.shields.io/circleci/project/BrightFlair/PHP.Gt.svg?style=flat-square)](https://circleci.com/gh/BrightFlair/PHP.Gt)
[![Coverage Status](http://img.shields.io/coveralls/BrightFlair/PHP.Gt.svg?style=flat-square)](https://coveralls.io/r/BrightFlair/PHP.Gt)
[![Code Quality](http://img.shields.io/scrutinizer/g/BrightFlair/PHP.Gt.svg?style=flat-square)](https://scrutinizer-ci.com/g/BrightFlair/PHP.Gt/)
[![Composer Version](http://img.shields.io/packagist/v/brightflair/php.gt.svg?style=flat-square)](https://packagist.org/packages/brightflair/php.gt)
[![Download Stats](http://img.shields.io/packagist/dm/BrightFlair/PHP.Gt.svg?style=flat-square)](https://packagist.org/packages/brightflair/php.gt)
[![PHP.Gt Website](http://img.shields.io/badge/web-www.php.gt-26a5e3.svg?style=flat-square)](http://www.php.gt)
[![PHP.Gt Roadmap](http://img.shields.io/badge/roadmap-public%20trello-26a5e3.svg?style=flat-square)](https://trello.com/b/zbfqGWbH/php-gt-public-roadmap)

Current project status
======================

View the detailed [project roadmap on Trello](https://trello.com/b/zbfqGWbH/php-gt-public-roadmap) to see what's coming up, and what's made its way into recent releases.

The most important changes are:

+ Extracting all modules of functionality into their own Composer packages.
+ Gaining full PHP7 support.
+ Full code coverage.

Features at a glance
====================

Static first
------------

To lower the barrier of entry to web development, the technique of developing a static prototype first is promoted, dropping in logic when and where necessary to turn prototypes into fully functional production code with as few steps as possible.

Build using tech you already know
---------------------------------

The main idea is to provide a platform where you can get as much done, using standard tech you've already learnt. Technologies that make up the [world wide web](https://en.wikipedia.org/wiki/World_Wide_Web), such as HTML and HTTP, are respected and enhanced by bringing useful tools and techniques to you, the developer.

Drop in tools without any fuss
------------------------------

There are a lot of useful tools included as standard, such as [SCSS parsing](https://github.com/BrightFlair/PHP.Gt/wiki/Client-side-files), [HTML templating](https://github.com/BrightFlair/PHP.Gt/wiki/Templating) and [CSRF handling](https://github.com/BrightFlair/PHP.Gt/wiki/CSRF), but the highly modularised architecture keeps compatibility high. Packages from [Packagist](https://packagist.org) can be installed and loaded with zero configuration.

Develop locally or virtually
----------------------------

Preconfigured scripts are available to automatically set up local servers or virtualisation environments to get you going as quickly as possible, without having to change existing computer configuration.