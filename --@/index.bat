::<?php echo "\r   \r"; if(0): ?>
:: #####################################################################################################################
:: #region
::     /* 
::                                                EPX-CMD-WIN
::     PROVIDER : KLUDE PTY LTD
::     PACKAGE  : EPX-PAX
::     AUTHOR   : BRIAN PINTO
::     RELEASED : 2025-03-11
::         
::     */
:: #endregion
:: # ###################################################################################################################
:: # i'd like to be a tree - pilu (._.) // please keep this line in all versions - BP
@echo off
SET FY__PANEL=
SET "FW__SHELL_FILE=%~dp0vnd\[src~shell-win-0][github.com~klude-org~fw-pkg-pax-one~archive~refs~heads~main]\.shell.bat"
if exist %FW__SHELL_FILE% (
    call %FW__SHELL_FILE%
) else (
    C:/xampp/current/php__xdbg/php.exe "%~f0" %*;
    if exist %FW__SHELL_FILE% (
        call %FW__SHELL_FILE%
    ) else (
        echo [91m!!! INVALID SHELL[0m
        echo Invalid shell
        pause
    )
)

exit /b 0

<?php endif; 

include '.start.php';
