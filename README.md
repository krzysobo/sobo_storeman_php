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


TODO as of 06.03.2026 (0.0.4) and 19.03.2026 (0.0.4)
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Now/Soon:
- AWS (in progress)
    - what is done: 
        -- login/check login data via variables 
        -- S3: bucket list, bucket objects
    - what to do:
        -- auth methods - FURTHER FUTURE:
            https://docs.aws.amazon.com/IAM/latest/UserGuide/reference_sigv-authentication-methods.html       
            https://docs.aws.amazon.com/cli/latest/userguide/cli-chap-authentication.html     
            --- using parameters stored in the DB (encrypted)
            --- using parameters from a file on the backend storage
            --- using the assumed roles managed from the server within the cloud;
            --- !!! all of the above require strong authentication to the backend itself,
            and thus, storing users. This way, Storeman becomes a "service provider" to access AWS services (like S3, SQS etc), which can be useful for multiple purposes, such as 
            running a centralized ecosystem for company's Intranet, HR system, CRM, ERP, etc.
        -- S3: 
            OBJECTS:
            --- object upload (putObject) from file path and file body 
            --- object download (getObject)
            --- object delete
            --- object copy (between buckets and in the same bucket)
            --- object rename
            --- move objects (a separate endpoint is unnecessary, since it is just copy + delete )

            BUCKETS:
            --- create a new bucket
            --- delete bucket
            --- download whole bucket
            --- rename bucket


- frontend in Rust with either Cranpose or Makepad, both for Web, Desktop and, later, Mobile

- frontend in Angular (in progress)
    - what is done: just the basic project setup only (Angular), no content
    - what to do: EVERYTHING :)
        -- login page
        -- established connection page (multiple connections in separate tabs)
            --- S3 bucket list
                ACTIONS:                 
                    ---- VIEW (just list) with sorting and filtering (
                            locally in the frontend only, whole bucket is downloaded first)
                    ---- DELETE whole bucket
                    ---- DOWNLOAD the whole bucket
                    ---- RENAME whole bucket
                    ---- CREATE a new bucket
            --- S3 selected bucket contents
                ACTIONS:
                    VIEW (just list) with sorting and filtering
                    ---- DELETE one or more objects (multiple selection)
                    ---- DOWNLOAD one object or more objects (ZIPped/TARred)
                    ---- OPEN/VIEW object in the frontend if possible
                    ---- UPLOAD one or more objects
                    ---- COPY object to another bucket (with multiple buckets on the screen)
                    ---- MOVE object to another bucket (with multiple buckets on the screen)


(V?) - OpenAPI/Swagger for all endpoints (planned)
(V?) - tests - done: unit tests for AwsClientHelper, AwsCredentialsHelper and JwtHelper, also AwsS3ObjectHelper and AwsS3BucketHelper
- tests: (planned)
    (XX) - unit tests: ResponseHelper
    (V?) - integration tests for all endpoints


Farther Future (Plans):
- AWS: SQS, DynamoDB
- cli (for commands)  (planned)
- ncurses interface (just because of love for retro "commander-type" software :)) (planned)
- Google Drive (planned)
- Dropbox (planned)
- Nextcloud (planned)



03.03.2026 (0.0.4):
~~~~~~~~~~~~~~~~~~~~~~~~
- unit tests 
- adding PHP-DI - just starting now...
    composer require php-di/slim-bridge

        https://php-di.org/doc/frameworks/slim.html


- adding nyholm/psr7
    composer require nyholm/psr7

- adding guzzlehttp/psr7
    composer require guzzlehttp/psr7



27.02.2026 (0.0.3):
~~~~~~~~~~~~~~~~~~~~~~~~
- removal of pretty-php because it conflicts with phpunit ^11

- removal of php_codesniffer, since it is no longer needed with php-cs-fixer

- installing PHPUnit 11 (for PHP 8.4+)
    composer require --dev phpunit/phpunit ^11 

- installing php-cs-fixer as both a linter and a fixer, to be used INSTEAD pretty-php and php_codesniffer
    composer require --dev friendsofphp/php-cs-fixer:^3

- tests...



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



TODO as of 24.02.2026 (0.0.1):
~~~~~~~~~~~~~~~~~~~~~~~~~~~
Now/Soon:
- AWS (in progress)
- frontend in Angular (in progress)

- OpenAPI/Swagger for all endpoints (planned)
- tests: (planned)
    - unit tests for AwsHelper, JwtHelper and ResponseHelper
    - integration tests for all endpoints


===========================================================================
===========================================================================


TODO - Future:
~~~~~~~~~~~~~~~~~~~~~~~~~~~~
- cli (for commands)  (planned)
- ncurses interface (just because of love for retro "commander-type" software :)) (planned)
- Google Drive (planned)
- Dropbox (planned)
- Nextcloud (planned)

-- SQS:
    -- handle queues
    -- handle messages
-- DynamoDB:
    -- handle everything, but this will be later
