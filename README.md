File Archive integration example in plain PHP (improved by some Symfony Components)
=======================

Run it by:

```sh
createuser -P -s user
createdb filearchive-examples -Uuser
psql filearchive-examples -Uuser -W < psql.sql
composer install --dev
php -S localhost:8000 -t public_html/
```
