<?php 
#####################################################################################################################
#region
    /* 
                                               EPX-PAX-START
    PROVIDER : KLUDE PTY LTD
    PACKAGE  : EPX-PAX
    AUTHOR   : BRIAN PINTO
    RELEASED : 2025-03-11
    
    */
#endregion
# ###################################################################################################################
# i'd like to be a tree - pilu (._.) // please keep this line in all versions - BP
try { (function(){
    \defined('_\MSTART') OR \define('_\MSTART', \microtime(true));
    global $_;
    (isset($_) && \is_array($_)) OR $_ = [];
    \define('_\LIB_DIR', \str_replace('\\','/',__DIR__));
    \is_file($f = \_\LIB_DIR.'/.config.php') AND include $f;
    \spl_autoload_extensions('-#.php,/-#.php');
    \spl_autoload_register();
    \set_include_path(\_\LIB_DIR.'/vnd'.PATH_SEPARATOR.get_include_path());
    \spl_autoload_register(function($n){
        if(!\preg_match(
            "#^vnd/(?<w_domain>.*?)__(?<w_owner>.*?)__(?<w_repo>[^/]+)#",
            $p = \str_replace('\\','/', $n),
            $m
        )){
            return;
        }
        if(!\is_file($f_path = \_\LIB_DIR."/{$p}/-#.php")){
            \extract($m = \array_filter($m, fn($k) => !is_numeric($k), ARRAY_FILTER_USE_KEY)); 
            $w_owner = \str_replace('_','-',$w_owner);
            $w_repo = "fw-pkg-".\str_replace('_','-',$w_repo);
            $u_path = "{$p}/".\urlencode('-#.php');
            $url = match($w_domain){
                'github' => "https://raw.githubusercontent.com/{$w_owner}/{$w_repo}/main/src/{$u_path}",
                'epx' => "https://epx-modules/neocloud/{$w_owner}/{$w_repo}/live/{$u_path}",
            };
            if(!($contents = \file_get_contents($url))){
                throw new \Exception("Failed: Unable to download type '{$n}'");
            }
            \is_dir($d = \dirname($f_path)) OR @mkdir($d,0777,true);
            \file_put_contents($f_path, $contents);
        }
        include $f_path;
    },true,false);
    \set_error_handler(function($severity, $message, $file, $line){
        throw new \ErrorException(
            $message, 
            0,
            $severity, 
            $file, 
            $line
        );
    });
    $origin = ($_['.'] ?? null ?: \vnd\github__klude_org__pax_one\std\origin::class)::_();
    if(\basename($_SERVER['SCRIPT_FILENAME']) == 'index.bat'){
        return function() use($origin){
            if($origin->resolve_module(\basename(\dirname($_SERVER['FW__SHELL_FILE'])))){
                exit(0);
            } else {
                exit(1);
            }
        };
    } else {
        return $origin;
    }
})->bindTo((object)[])()(); } catch (\Throwable $ex) {
    global $_;
    $intfc = $_['INTFC'] 
        ?? $_SERVER['HTTP_REQUEST_INTERFACE'] 
        ?? (empty($_SERVER['DOCUMENT_ROOT']) 
            ? 'cli' 
            : 'web' 
        )
    ;
    switch($intfc){
        case 'cli':{
            if($_REQUEST['--verbose'] ?? null){
                print_r(\json_encode([
                    $_SERVER,
                    get_defined_constants(true)['user'],
                    $_ENV,
                    \explode(PATH_SEPARATOR, get_include_path())
                ],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL);
                echo "\033[91m\n"
                    .$ex::class.": {$ex->getMessage()}\n"
                    ."File: {$ex->getFile()}\n"
                    ."Line: {$ex->getLine()}\n"
                    ."\033[31m{$ex}\033[0m\n"
                ;
            } else {
                echo "\033[91m{$ex->getMessage()}\033[0m\n";
            }
        } break;
        case 'web':{
            \http_response_code(500);
            exit(<<<HTML
                <pre style="overflow:auto; color:red;border:1px solid red;padding:5px;">Unhandled Exception:
                <i>Request: {$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}</i>
                <b>{$ex}</b>
                </pre>
            HTML);
        } break;
        default:
        case 'rest':
        case 'rpc':
        case 'api':{
            \http_response_code(500);
            \header('Content-Type: application/json');
            exit(\json_encode([
                'status' => "error",
                'message' => $ex->getMessage(),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } break;
    }
}


