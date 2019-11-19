@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../drush/drush/drush
sh "%BIN_TARGET%" %*
