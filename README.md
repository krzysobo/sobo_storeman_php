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



TODO as of 24.02.2026:
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
