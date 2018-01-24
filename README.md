ContinuumTest
=============

A Symfony project created on January 23, 2018, 12:21 pm.

Instal
======
Install symfony from here:
`https://symfony.com/doc/current/setup.html`

Install Composer
`sudo apt-get install composer`
`compser update`

Pull Code into a new symfony project/download files and copy them to a directory on your server

Usage
======
`php bin/console app:marvel-report <characterName> <dataType>`

Data type can either be: `comics`, `events`, `series`, `stories`

Testing
======
`php ./vendor/bin/phpunit tests/AppBundle/Command/MarvelReportCommandTest.php`

Output
======
The files will be placed in:
`bin/output`
