stomp ChangeLog
========================================================================

## ?.?.? / ????-??-??

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