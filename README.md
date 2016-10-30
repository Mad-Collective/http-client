# Pluggit Http

Small PSR7 compatible library to perform http requests based on pre-configured set of requests

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/CMProductions/http-client/badges/quality-score.png?b=draft&s=0c6f482c48d0759ef1f4b2c6015469be3984ea0b)](https://scrutinizer-ci.com/g/CMProductions/http-client/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/CMProductions/http-client/badges/build.png?b=draft&s=4980b96726a88a92c3a7355a270b1bc1148207ce)](https://scrutinizer-ci.com/g/CMProductions/http-client/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/CMProductions/http-client/badges/coverage.png?b=draft&s=81094879399190f548440b163be5b2fd9ec1b135)](https://scrutinizer-ci.com/g/CMProductions/http-client/?branch=master)

## TL;DR
```php
$config = [
    'my_cool_api' => [
        'endpoint' => 'http//my_cool_api.com/v2',
        'requests'  => [
           'get_user' => [
               'url' => '/user/${USER_ID}'
           ],
           'create_user' => [
               'url' => '/user/${USER_ID}',
               'method' => 'POST'
               'headers' => ['Content-Type' => 'application/json']
               'body' => ['name' => '${NAME}', 'email' => '${EMAIL}']
           ],
        ],
    ],
];

$client = ClientBuilder::create()
    ->withConfig($config)
    ->build('my_cool_api');

/** @var \stdClass $user */
$user = $client->execute('get_user', ['user_id' => $userId])->json();
```
## Table of contents
- [Installation](#installation)
- [Requirements](#requirements)
  - [Compatibility](#compatibility)
  - [Additional requirements](#additional-requirements)
- [Usage](#usage)
  - [Defining a configuration](#defining-a-configuration)
  - [Building the client](#building-the-client)
  - [Creating a request](#creating-a-request)
  - [Sending the request](#sending-the-request)
  - [Exceptions](#exceptions)
- [REST API integration example](#rest-api-integration-example)
  - [Building the API client](#building-the-api-client)
  - [Get a user](#get-a-user)
  - [Create a user](#create-a-user)
  - [Replace a user](#replace-a-user)
  - [Update a user](#update-a-user)
  - [Delete a user](#delete-a-user)
- [Annexes](#annexes)
  - [Pimple Service Provider](#pimple-service-provider)
  - [Development environment](#development-environment)  

## Installation
Add this repo to your composer.json
```javascript
"repositories": {
  "pluggit/http-client": {
    "type": "vcs",
    "url": "git@github.com:CMProductions/http-client.git"
  }
}
```
Then require it as usual
```bash
composer require pluggit/http-client
```
To use [Guzzle](http://http://docs.guzzlephp.org/en/latest/overview.html) as a sender (the default option), you have to require it manually 
```bash
composer require guzzlehttp/guzzle "^6.0"
```
### Requirements
* php >=5.5
* guzzlehttp/psr7 ^1.0
* psr/log: "^1.0"

__*Note:*__ This package relies on the implementation of guzzle for the [PSR-7: HTTP message interfaces](http://www.php-fig.org/psr/psr-7/)

### Compatibility
This library has been tested with the following configuration

PHP | guzzlehttp/psr7 | guzzlehttp/guzzle | result
--- | --------------- | ----------------- | ------
5.5.25 | 1.3.1 | 6.2.2 | :white_check_mark:
5.6.16 | 1.3.1 | 6.2.2 | :white_check_mark:
7.0.7 | 1.3.1 | 6.2.2 | :white_check_mark:
 hhvm | 1.3.1  | 6.2.2 | :white_check_mark:

### Additional requirements
#### Guzzle
The library allows to use different senders to execute requests, for this reason if you one you use the built-in sender for Guzzle *you must require* this dependency in your project's
```bash
composer require guzzlehttp/guzzle "^6.0"
```
#### Yaml parsing
Maintain a php configuration file can be cumbersome, that's why formats like Yaml arose, but don't worry, you can write down your requests configuration as a yaml file and the library will load it. To so so, *you must require* Symfony's Yaml package
```bash
composer require symfony/yaml "^3.1"
```
#### Logging
Do you want to know why your request is failing? No problem, the library accepts any [Psr-3 logger](http://www.php-fig.org/psr/psr-3/) implementation` (like [Monolog](https://github.com/Seldaek/monolog)) that will write debug and error messages.

For a quick start you can use the included library integration with Symfony's console package
```bash
composer require symfony/console "^3.1"
```
Then build the client with the console output enabled:
```php
$client = ClientBuilder::create()
    ->withConfig($config)
    ->withConsoleDebug()
    ->build();
```
#### Pimple
If you use pimple for building your dependencies you can use the provider `Cmp\Http\Provider\HttpClientServiceProvider` to register a client. 
```bash
composer require pimple/pimple "^3.0"
```
__*NOTE*:__ See the annex for how to register the provider
---
## Usage
The library is allows to build requests from a set configuration values, execute them trough the client and get a response
### Defining a configuration
The configuration is what tells the client how to build the request, let's see some examples of configuration file as yaml and talk about it:
```yaml
my_users_api:
  endpoint: https://api.mysite.com
  headers:
    api_version: 1.2
  options:
    timeout: 2
  requests:
    list_users:
      url: /users
      options:
        timeout: 15
    get_user:
      url: /users/${USER_ID}
    create_user:
      url: /users/${USER_ID}
      headers: 
        - Content-Type: application/json
      method: POST
      
# Another service
the_comments_api:
  endpoint: http://messaging_service.com/v2/
  ...
```
The first line defines a name for a service, in this case `my_users_api`

__NOTE:__ You can see a full-fledged sample configuration file in `config-sample/requests.yaml` for working with a REST API
#### Service configuration values
* `endpoint` (**required**): This is the endpoint for the all the request for this service
* `headers`: A key-value array with the headers to add to *all* the requests for this service
* `query`: A key-value array with the query parameters to append in the URI of *all* the requests for this service
* `body`: A key-value array with the post parameters to send in the body of *all* the requests for this service
* `version`: HTTP protocol version, default one `1.1``
* `options`: A key-value array with options to pass to the http client sender to modify it's behaviour
* `request`: A key-value array with the allowed requests for this service
#### Request configuration values
The request configuration allows the same options as the service, overwriting the values provided for the service, this allows to define a general behaviour for a service but tweak some options for specific requests, like for example, allowing a longer timeout
* `path` (**require**): Defines the path that follows the endpoint
* `method`: Defines the HTTP method for the request. `GET` by default.
#### Configuration options
The options configuration allows to customize the client behaviour without the library having to know specific details about how to handle them.
Taking the provided integration with [Guzzle](http://docs.guzzlephp.org/en/latest/request-options.html) as examples, we could have:
* Basic auth: `auth: ['myUser', 'myPass']`
* Timeout: `timeout: 2`
* Connect timeout: `timeout: 15`
* Add a certificate: `['cert' => ['/path/server.pem', 'password']]`
#### Configuration placeholders
The library allows to indicate the presence of dynamic values in this configuration parameters
##### Placeholder rules
* Placeholders are allowed in _path, query, headers_ and _body_ only
* Use this format to add a placeholder: `${PLACEHOLDER_NAME}`
* Placeholders tag names must be uppercase (although lowercase keys can be used when replacing them)
* You can have the same placeholder multiple times across all options, it will be replaced in all of them.
##### Replacing a placeholder in the request
To replace the placeholders, pass an array with the calculated values when creating the request 
```php
secure_api:
  endpoint: https//topsecret.com
  headers:
    api_key: MyPersonalApiKey
    token: ${TOKEN}
  requests:
    get_user:
      path: /users/${USER_ID}
```

```php
$request = $client->create('secure_api', 'get_user', [
  'token'   => $oath->sign($secret),
  'user_id' => $userId
]);
```
### Building the client
There are 2 different clients that allow to execute requests:
* `Cmp\Http\Client\MultiClientInterface`: This client has access to all services
* `Cmp\Http\Client\ServiceClientInterface`: This client can execute request from a single service only

__*NOTE*__: You _should_ try to inject always a `ServiceClientInterface` in your services, this will prevent sideeffects triggering requests from other services by mistake

#### Building a multi client
The easiest way to built the client is to use the provided builder
`Cmp\Http\ClientBuilder`
```php
$builder = Cmp\Http\ClientBuilder::create();
```
It implement fluent interface `Cmp\Http\Client\MultiClientInterface` to configure the client by chaining methods before building it
```php
// Build the client with the default options
$client = Cmp\Http\ClientBuilder::create()->withConfig($config)->build();
```
#### Building a service client
To build a service client specify the name of the service when building the client in the last step of the builder
`Cmp\Http\ClientBuilder`
```php
$myServiceApi = Cmp\Http\ClientBuilder::create('my_service');
```
It implement fluent interface `Cmp\Http\Client\iClientInterface` to configure the client by chaining methods before building it
```php
// Build the client with the default options
$client = Cmp\Http\ClientBuilder::create()->withConfig($config)->build();
```
#### Customizing the client
The client require 3 dependencies to work, the builder has methods to override all of them with custom implementations
##### Sender
This objects will receive the built requests and execute them, it has to implement the `Cmp\Http\Sender\SenderInterface`. It has to return Psr-7 compatible responses

The builder will use the `Cmp\Http\Sender\GuzzleSender` by default, to specify a different one use:
```php
// Custom sender
$builder->withSender($sender);

// GuzzleSender with custom client (for customization, testing, mocking, etc)
$builder->withGuzzleSender($mockedGuzzle);
```
##### Config
The only required dependency, the requests configuration, pass it with:
```php
$builder->withConfig($config);
```
If `symfony/yaml` is installed, you can pass a file path with a yaml configuration
```php
$builder->withYamlConfig($file);
```
##### Logger
You can pass a `Psr\Log\LoggerInterface` to receive debug and error messages
```php
$builder->withLogger($logger);
```
If you want to debug the raw http request/responses in the console you can activate the debug output in the builder (You'll need to have `symfony/console` installed)
```php
$builder->withConsoleDebug();
```
### Creating a request
To create a request you have to tell the client the service and the request name, to be built. Additionally you can pass dynamic values to substitute placeholders
```php
$request = $client->create('weather', 'forecast', ['city' => $city]);
```
The request are object `Cmp\Http\Message\Request`, which implement `Psr\Http\Message\RequestInterface`, making them easy to share between libraries and frameworks

The library request object provides some helpers to work with them in an easier way:
* `withQueryParameter($key, $value)`: Allows you to add or modify a query parameter
* `withPost(array $params)`: Allows to pass a key-value array of params to send as body
* `withJsonPost(array $params)`: Allows to pass a key-value array of params; it will be codified as json and, the header `Content-Type: application/json` will be added
* `__toString()`: This makes the request embeddable in string
#### Customising the request creation
If you provide a request factory to the builder that complais with `Cmp\Http\RequestFactoryInterface`, you'll be able to extend the Request class and change the behaviour.  

You may ask _"hey, why would I like to do this for?"_  For example to: 
* Change all outgoing endpoints to a dummy server to prevent to executed certain requests on test environments
* Apply a configuration to all requests from all services
```php
$factory = new TestEnvironmentsRequestFactory($config);
$builder->withRequestFactory($factory);
```
### Sending the request
Once you've configure completely your request, use the same client to send it, it will return a `Cmp\Http\Client\Client\Response` compatible with `Psr\Http\Message\ResponseInterface`

This response object also provides some helper methods:
* `json($asArray = true)`: Parses the body as a json an returns either a `stdClass` object or an `array`
* `jsonAsArray()`: Same as before, but forcing the return type to be an array
* `__toString()`: This makes the response embeddable in string

#### Execution shortcuts
If you don't have to modify the request dynamically you can create and execute a request in a single operation
```php
// Get a response
$response = $builder->execute('my_api', 'list_categories');

// Get the contents of the response directly
$body = $builder->body('my_api', 'list_categories');

// Get the contents of the response parsing as json
$asArray = true;
$body = $builder->json('my_api', 'list_categories', [], $asArray);
```
### Exceptions
Not always works as expected, to make your life easier handling this, the library provides a small set of exceptions:
* `Cmp\Http\Exception\RuntimeException`: This is the base exception used in the library, all the exceptions thrown within the library code uses or extends this one
* `Cmp\Http\Exception\RequestBuildException`: This exception is thrown when the process of building a request cannot be completed
* `Cmp\Http\Exception\RequestExecutionException`: This exception is thrown when some error happens sending the request  
---
## REST API integration example
I'm going to show you how to perform the most common operation in a REST API.  
Lets imagine that we want to interact with an API endpoint to manage our application users, user entity looks like:
```javascript
{
  "id": "1",
  "first_name": "John",
  "last_name": "Doe"
}
```
Now let's define the typical requests in our configuration file
```yaml
# Service definition
my_app:
  # The endpoint is required
  endpoint: https://api.myapp.com/v1
  options:
    auth: ['apikey', 'secret']

  # At least one request is required too
  requests:
    list_users:
      path: /users

    get_user:
      path: /users/${USER_ID}

    delete_user:
      path: /users/${USER_ID}
      method: DELETE

    put_user:
      path: /users/${USER_ID}
      method: PUT
      headers:
        Content-Type: application/json
      body:
        first_name: ${FIRST_NAME}
        last_name: ${LAST_NAME}

    create_user:
      path: /users
      method: POST
      headers:
        Content-Type: application/json
      body:
        first_name: ${FIRST_NAME}
        last_name: ${LAST_NAME}

    update_user:
      path: /users/${USER_ID}
      method: PATCH
      headers:
        Content-Type: application/json
      # body: No need to define all the body params here, we can do it at request time
```
### Building the API client
Let's build a service client for our api, as per the configuration, all the requests will include a basic authentication
```php
$api = $builder->build('my_app'); 
```
###List users
This call would return an array of users
```php
// We are going to modify the request to pass a limit on the number of users to retrieve
$users = $api->send($api->request('list_users')->withQueryParameter('limit', 25))->json();
```
### Get a user
This call will inject the user id in the path of the uri to request the user
```php
// The json shortcut creates, executes and parses the body response all in a single call
$users = $api->json('get_user', ['user_id => 1]);
```
### Create a user
Here we're going to replace the placeholders in the body array, if the api only accepts a json body, we have to add the correct content type header, like in the example so the request is created correctly
```php
$user = $api->json('create_user', ['name' => 'Jane', 'last_name' => 'Roe']);
```
### Replace a user
We can replace placeholders in both path or body params at the same time
```php
$user = $api->json('put_user', ['user_id' => 1, 'name' => 'Jane', 'last_name' => 'Roe']);
```
### Update a user
When we update some fields only, we don't known in advance what fields we are going to send, so it's better to decide later
```php
$request = $api->create('update_user', ['user_id' => 1]);
// Remember to assign the result, as the request return always a new instance when modifying it
$request = $request->withPost(['name' => 'Jane']);
$updatedUser = $api->send($request)->json();
```
### Delete a user
Deleting a user is even easier
```php
$api->execute('delete_user', ['user_id' => 1]);
```
---
## Annexes
### Pimple Service Provider
The library includes a service provider for Pimple to register a client in an easy way:
`Cmp\Http\Provider\HttpClientServiceProvider`
The provider will register the builder `Cmp\Http\ClientBuilder` object at key `http_client.builder` and a general purpouse multi-client `Cmp\Http\Client\MultiClient` in `http_client.client`, but it also accepts alias for both services
```php
$container->register(new HttpClientServiceProvider('requester', 'client'), [
    'http_client.yaml' => 'config/requests.yml',
]);
$builder = $container['requester']; // or http_client.builder
$client = $container['client'];     // or http_client.client
```
The options that the provider accepts are:
* `http_client.yaml`: A filepath with a ymal configuration file
* `http_client.config`: A configuration array with the requests
* `http_client.logger`: Pass a Psr\Logger\LoggerInterface object to add logging to the client
* `http_client.sender`: A custom implementation of the `Cmp\Http\Sender\SenderInterface` interface
* `http_client.guzzle`: A custom `GuzzleHttp\ClientInterface` object (useful to keep a reference)
* `http_client.factory`: A custom `Cmp\Http\RequestFactoryInterface` implementation
* `http_client.debug`: Pass true and this will activate console output for debugging

---
### Development environment
To build the test environment you'll need docker and docker-compose installed:
```
make dev
```
#### Running the tests
```bash
make unit
make integration
```
You can run the tests only for a php version like this
```bash
make unit PHP_VERSION=5.6
make integration PHP_VERSION=5.6
```
#### Code-coverage
You can build a report for code coverage in HTML format. It will be available in `bin/code-coverage`
```bash
make code-coverage
```
#### Stop the environment
```
make nodev
```
#### Delete the environment
You can delete the docker images for a total clean-up
```bash
 make nodev IMAGES=true
```
