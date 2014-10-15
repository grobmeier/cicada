Cicada changelog
================

0.4.9 (2014-10-15)
------------------

Features:

* Route can now accept OPTIONS requests
* Added an event emitter `$app['emitter']`
* Added two new events:
    * `router.match`, when a route is matched by a request
    * `router.nomatch`, when none of the routes are matched by a request

0.4.8 (2014-08-25)
------------------

Bugfixes:

* Fixed invalid access to Pimple container (v.3 removed the \Pimple alias)

0.4.7 (2014-08-25)
------------------

Bugfixes:

* BREAKING CHANGE: The Pimple dependency changed a signature to code, which caused a conflict with
  Application:register. The register method was renamed to addRouteCollection.

Misc:

* Updated Pimple to 3.0

0.4.6 (2014-06-04)
------------------

Features:

* Routes now accept [$class, $method] style callbacks

0.4.5 (2014-06-03)
------------------

Features:

* Implemented named routes

0.4.4 (2014-06-01)
------------------

Features:

* Added `$app->before()` and `$app->after`

0.4.3 (2014-05-27)
------------------

Bugfixes:

* return $this in RouteCollection builder methods

Features:

* started keeping a changelog

