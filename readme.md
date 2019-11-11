## Installation
- `git clone git@github.com:Andr1yk0/propertymate_test.git`
- `composer install`
- set up database connection inside `.env` file
- `php bin/console doctrine:database:create`
- `php bin/console doctrine:migrations:migrate`
- `php bin/console doctrine:fixtures:load`

### Run server
- `php bin/console server:start`
- Try `http://127.0.0.1:8000/api/contacts`

### Run tests
- `php bin/phpunit`
