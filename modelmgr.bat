@echo off
@title ModelMgr
@rmdir ..\app\models /S /Q
php -e build.php %*
pause > nul