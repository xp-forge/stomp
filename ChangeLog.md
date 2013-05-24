stomp ChangeLog
========================================================================

## 6.0.0 / 2013-05-24

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