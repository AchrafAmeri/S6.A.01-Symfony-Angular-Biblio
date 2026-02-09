@echo off

copy _php.ini C:\tools\php84\php.ini
copy _symfony.exe C:\tools\php84\symfony.exe

php testinstall.php
php composer.phar --version
symfony version

pause
