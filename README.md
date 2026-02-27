# sobo_storeman_php
Mass-Storage manager in PHP + Angular (Client - Server)

WORK IN PROGRESS!!!


Application model:
~~~~~~~~~~~~~~~~~~~~~~~~~~~
Client-Server
- Server side: RESTful API in PHP + Slim Framework 
- Client side: application in Angular, working in any web browser


Technological stack:
~~~~~~~~~~~~~~~~~~~~~~~~~~~
- PHP 8.4/8.5                   https://www.php.net/releases/8.4/en.php
- Composer                      https://getcomposer.org/
- Slim Framework                https://www.slimframework.com/
- AWS SDK for PHP (official)    https://github.com/aws/aws-sdk-php
- firebase/php-jwt              https://github.com/firebase/php-jwt
- defuse/php-encryption         https://github.com/defuse/php-encryption
- Angular 18                    https://angular.dev/



TODO as of 24.02.2026 (0.0.1):
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Now/Soon:
- AWS (in progress)
- frontend in Angular (in progress)

- OpenAPI/Swagger for all endpoints (planned)
- tests: (planned)
    - unit tests for AwsHelper, JwtHelper and ResponseHelper
    - integration tests for all endpoints

Future:
- cli (for commands)  (planned)
- ncurses interface (just because of love for retro "commander-type" software :)) (planned)
- Google Drive (planned)
- Dropbox (planned)
- Nextcloud (planned)


27.02.2026 (0.0.2):
~~~~~~~~~~~~~~~~~~~~~~~~

- AwsHelper split into smaller classes
- CloneWithProps trait created, for cloning a PHP object while updating some properties; for compatibility between 8.5.x and <8.5.x
- AwsCredentials DTO used instead of arrays

- adding php-parallel-lint
    composer require --dev php-parallel-lint/php-parallel-lint

(XX-removed to install phpunit ^11) - adding pretty-php (code cleaner)

(XX-removed after installing php-cs-fixer) - adding php_codesniffer
    composer require --dev squizlabs/php_codesniffer

    


27.02.2026 (0.0.3):
~~~~~~~~~~~~~~~~~~~~~~~~
- removal of pretty-php because it conflicts with phpunit ^11

- removal of php_codesniffer, since it is no longer needed with php-cs-fixer

- installing PHPUnit 11 (for PHP 8.4+)
    composer require --dev phpunit/phpunit ^11 

- installing php-cs-fixer as both a linter and a fixer, to be used INSTEAD pretty-php and php_codesniffer
    composer require --dev friendsofphp/php-cs-fixer:^3

- tests...