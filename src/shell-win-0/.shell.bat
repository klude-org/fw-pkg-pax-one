::<?php echo "\r   \r"; if(0): ?>
:: Installed: #__FW_INSTALLED__#
:: #####################################################################################################################
:: #region LICENSE
::     /* 
::                                                EPX-WIN-SHELL
::     PROVIDER : KLUDE PTY LTD
::     PACKAGE  : EPX-PAX
::     AUTHOR   : BRIAN PINTO
::     RELEASED : 2025-03-10
::     
::     The MIT License
::     
::     Copyright (c) 2017-2025 Klude Pty Ltd. https://klude.com.au
::     
::     of this software and associated documentation files (the "Software"), to deal
::     in the Software without restriction, including without limitation the rights
::     to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
::     copies of the Software, and to permit persons to whom the Software is
::     furnished to do so, subject to the following conditions:
::     
::     The above copyright notice and this permission notice shall be included in
::     all copies or substantial portions of the Software.
::     
::     THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
::     IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
::     FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
::     AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
::     LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
::     OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
::     THE SOFTWARE.
::         
::     */
:: #endregion
:: # ###################################################################################################################
:: # i'd like to be a tree - pilu (._.) // please keep this line in all versions - BP
:: FW__ - variable
:: FX__ - constant
:: FY__ - env
@echo off
::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
:: SESSION
goto :L__START
:getGUID
for /f "tokens=2 delims==" %%a in ('wmic os get localdatetime /value') do set dt=%%a
set "%~1=%dt:~0,8%-%dt:~8,4%-%dt:~12,2%%dt:~15,3%-%dt:~6,2%%dt:~8,2%%dt:~10,2%-%dt:~15,3%%dt:~12,2%%dt:~6,2%"
exit /b
:loadCONFIG
    if not exist %~dp0.local mkdir %~dp0.local
    if not exist "%~dp0.local\.config.bat" (
        echo SET FX__PHP_EXEC_STD_PATH=C:/xampp/current/php/php.exe > "%~dp0.local\.config.bat"
        echo SET FX__PHP_EXEC_XDBG_PATH=C:/xampp/current/php__xdbg/php.exe >> "%~dp0.local\.config.bat"
        echo SET FX__ENV_FILE="%~dp0.local\___set_cli_env_vars__.bat" >> "%~dp0.local\.config.bat"
        echo SET FX__DEBUG=0 >> "%~dp0.local\.config.bat"
        echo SET FX__CONFIG_LOADED=1 >> "%~dp0.local\.config.bat"
    )
    call %~dp0.local\.config.bat
:L__START

::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
:: LIB DIR
if not defined FX__LIB_DIR  SET "FX__LIB_DIR=%~dp0\.."

::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
:: SESSION
if not defined FX__SESSION call :getGUID FX__SESSION

::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
:: CONFIG
if not defined FX__CONFIG_LOADED call :loadCONFIG

::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
:: PATH
if not defined FY__ORIGINAL_PATH SET "FY__ORIGINAL_PATH=%Path%"
SET PATH=%~dp0;%FY__ORIGINAL_PATH%

::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
:: SHELL
if not defined FY__SHELL (
    SET FY__SHELL=1
    echo [92mEPX WIN SHELL 250306-03[0m
    cmd /k
    exit /b 0
)

::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
:: PHP
if "%FX__DEBUG%" NEQ "" (
    SET FW__PHP_EXEC_PATH=%FX__PHP_EXEC_XDBG_PATH%
) else (
    SET FW__PHP_EXEC_PATH=%FX__PHP_EXEC_STD_PATH%
)

if NOT exist %FW__PHP_EXEC_PATH% (
    SET FW__PHP_EXEC_PATH=php.exe
)

::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
:: PHP ENV
if defined FW__ENV_FILE (
    SET "FW__ENV_FILE=%FX__ENV_FILE%"
)

SET CMD_FILE_A=%~dp0__\%1\-@cli%FY__AGENT%.bat
SET CMD_FILE_B=%~dp0__\%1-@cli%FY__AGENT%.bat

if exist "%CMD_FILE_A%" (
    call %CMD_FILE_B% %*
    goto :L__DONE
) else if exist "%CMD_FILE_B%" (
    call %CMD_FILE_B% %*
    goto :L__DONE
)

if exist "%cd%\index.php" (
    %FW__PHP_EXEC_PATH% "%cd%\index.php" %*
) else (
    %FW__PHP_EXEC_PATH% "%~f0" %*
)

:L__DONE
if %ERRORLEVEL%==2 (
    %FW__PHP_EXEC_PATH% "%~f0" %*
) else if exist "%FW__ENV_FILE%" (
    call "%FW__ENV_FILE%"
    del "%FW__ENV_FILE%"
)
  
exit /b 0

<?php endif; 

include __DIR__.'/../.start.php';

