#Teknoo Software - East Foundation - Change Log

##[0.0.4-alpha4] - 2016-11-19
###Updated
- Rename the project "East Framework" to "East Foundation"
- Transform the bundle to universal package, usable with any PSR11 Framework, a Symfony Bundle is already provided.
- Behavior of Router : Must return a ResultInterface instance (a value object) with the callable to use as controller
- Behavior of Processor : Independent of Symfony, to use only Router's result.

###Added
- Support PSR-11 to be independent with Symfony
 
###Removed
- Base Symfony Controller

##[0.0.4-alpha3] - 2016-10-11
###Fix
- Remove dead code, useless with States 3.0
- Fix bad namespace in manager

##[0.0.4-alpha2] - 2016-10-11
###Fix
- Travis configuration

##[0.0.4-alpha1] - 2016-10-11
###Updated
- Migrate to States 3.0

##[0.0.3] - 2016-08-04
###Fixed
- Improve optimization on call to native function and optimized

##[0.0.2] - 2016-08-01
###Fixed
- Error when the framework is used with a lower configurations

##[0.0.1] - 2016-07-26
###Updated
- First stable release
- Improve documentation and add API Doc
- Fix CS Style

##[0.0.1-alpha3] - 2016-04-16
###Updated
    - Update Manager to use Teknoo / States to implement a defective behavior to avoid multiple requests by instance
    - Update Client to use Teknoo / States to implement a defective behavior to avoid multiple requests by instance

##[0.0.1-alpha2] - 2016-04-16
###Updated
    - update processor to ignore non available controller and log info

##[0.0.1-alpha1] - 2016-04-11
###Added
    - First release
    - Add manager to manager routers and processing request
    - Add router to analyze requests and find controller
    - Add processor to instantiate controller and execute request
    - Add client to manage and send response to client
    - Add a kernel listener to intercept response from
    - Add a abstract controller to replace symfony base controller for developpers.
