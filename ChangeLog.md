stomp ChangeLog
========================================================================

## ?.?.? / ????-??-??

## 10.0.1 / 2020-04-04

* Made compatible with XP 10 - @thekid

## 10.0.0 / 2018-08-24

* Made compatible with `xp-framework/logging` version 9.0.0 - @thekid
* **Heads up: Dropped PHP 5.5 support** - @thekid
* Added compatibility with XP9 - @thekid

## 9.3.0 / 2017-09-11

* Made connection timeout configurable both via connection URL *and/or* by
  passing it to `connect()`. See pull request #9
  (@treuter, @thekid)

## 9.2.2 / 2016-11-02

* Made compatible w/ PHP < 5.5
  (@kiesel)

## 9.2.1 / 2016-10-31

* Made `toString()` output not leak credentials, indicate elected endpoint
  (@kiesel)

## 9.2.0 / 2016-10-27

* Merged pull request #7: Implement failover connections / HA; introduces
  `peer.stomp.Failover` class
  (@kiesel, @thekid)

## 9.1.1 / 2016-09-20

* Merged pull request #6: Prevent endless loop when server disconnects
  (@kiesel, @thekid)

## 9.1.0 / 2016-08-29

* Added version compatibility with XP 8 - @thekid

## 9.0.0 / 2016-02-21

* Added version compatibility with XP 7 - @thekid

## 8.0.2 / 2016-01-24

* Fix pushing back newlines - @thekid

## 8.0.1 / 2016-01-24

* Fix code to use `nameof()` instead of the deprecated `getClassName()`
  method from lang.Generic. See xp-framework/core#120
  (@thekid)

## 8.0.0 / 2015-12-20

* **Heads up: Dropped PHP 5.4 support**. *Note: As the main source is not
  touched, unofficial PHP 5.4 support is still available though not tested
  with Travis-CI*.
  (@thekid)

## 7.1.0 / 2015-09-26

* Added PHP 7 support - @thekid
* Changed code to use PHP 5.4 short array syntax - @thekid
* Use `::class` inside annotations - @thekid

## 7.0.2 / 2015-07-12

* Added forward compatibility with XP 6.4.0 - @thekid

## 7.0.1 / 2015-02-12

* Changed dependency to use XP ~6.0 (instead of dev-master) - @thekid

## 7.0.0 / 2015-01-11

* Heads up: Changed Stomp to depend on XP6 core (@thekid)
* Made xp-forge/stomp available via Composer (@thekid)

## 6.0.2 / 2014-05-25

* Changed constructor to accept strings and peer.URL instances (@thekid)
* Adjusted to new coding standards (@thekid)
* Moved all tests to peer.stomp namespace (@thekid)

## 6.0.1 / 2013-10-23

* Fixed header literal (`persistence` -> `persistent`) - (@iigorr)

## 6.0.0 / 2013-05-24

* Bump major version to 6 - see pull request #5 (@thekid, @kiesel, @mrosoiu)
* Support STOMP 1.1 features: virtual hosts, protocol version negotiation,
  message NACK - (@kiesel)
* Implement PHP namespaces - (@kiesel)
* Added high-level OO STOMP API: New Connection, Subscription, Message,
  Transaction, Destination classes - (@kiesel)
* Hide low-level functionality - (@kiesel)
* Require XP >= 5.9.0 - (@kiesel)
* Complete overhaul of class structure, no BC(!) - (@kiesel)
* added examples, README - (@kiesel)

## 1.2.3 / 2013-05-15

* Drop ANT support - (@kiesel)

## 1.2.2 / 2013-03-18
This release makes the Stomp API work with [RabbitMQ](http://www.rabbitmq.com/)

* Fix issue #2 - Class 'StringReader' not found (@thekid)
* Fix issue #1 - No line delimiter (@thekid)

## 1.2.1 / 2010-09-18

* Code QA - (@thekid)

## 1.2.0 / 2010-05-14

* Support selectors in subscribe() - (@thekid)

## 1.1.1 / 2010-05-12 

* Fix payload reading (@thekid)

## 1.1.0 / 2010-04-07

* Timeouts for receive() - (@thekid)

## 1.0.0 / 2010-04-01

* Initial release - (@kiesel)
* Protocol support - (@kiesel)