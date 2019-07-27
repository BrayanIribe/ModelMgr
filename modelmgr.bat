@echo off
@title ModelMgr
@rmdir app\models /S /Q
php -e modelmgr/build.php %*
pause > nul