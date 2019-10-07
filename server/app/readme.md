`Api` folder contains native PHP implementation for application API such as CRUD (Create, Read, Update and Delete) operations.

`Commands` folder contains your application console commands which could be executed from [composer](https://getcomposer.org/). A well commented command boilerplate could be generated with
 ```bash
 composer l:commands create <Name of New Command Class>
 ```

`Container` folder contains configurators for application [Container (PSR 11)](http://www.php-fig.org/psr/). If you need external libraries accessible from the application container (e.g. mailer, payments, etc) you can add it here.

`Routes` folder contains routing for web and API.

`Validation` folder contains validation rules and validators for HTTP forms, HTTP query parameters and JSON API inputs.  
