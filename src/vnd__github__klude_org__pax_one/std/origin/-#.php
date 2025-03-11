<?php namespace vnd__github__klude_org__pax_one\std;

class origin extends \stdClass {
    
    public readonly array $_;
    public readonly array $CONFIG;
    public readonly string $LIB_DIR;
    public readonly string $INCP_DIR;
    public readonly string $INTFC;
    public readonly string $LOCAL_DIR;
    public readonly array $ENV_VARS;
    public readonly bool $IS_CLI;
    public readonly bool $IS_WEB;
    public readonly bool $IS_API;
    public readonly int $OB_OUT;
    public readonly int $OB_TOP;
    public readonly string $TSP_PATH;
    
    public int|bool|null $VERBOSITY = null;
    
    public static function _() { static $i;  return $i ?: ($i = new static()); }
    
    protected function __construct(){
        $this->ENV_VARS = $_ENV;
        $_ENV = $this;
        $this->config($_);
        $this->_ = $_[\_::class] ?? [];
        $this->CONFIG = $_;
        $this->LIB_DIR = \_\LIB_DIR;
        $this->INCP_DIR = \str_replace('\\','/', \realpath(\dirname($_SERVER['SCRIPT_FILENAME'])));
        $this->INTFC = $this->_['INTFC'] 
            ?? $_SERVER['HTTP_REQUEST_INTERFACE'] 
            ?? (empty($_SERVER['DOCUMENT_ROOT']) 
                ? 'cli' 
                : 'web'
            )
        ;
        $this->LOCAL_DIR = \_\LIB_DIR.'/.local';
        \date_default_timezone_set($this->_['TIMEZONE'] ?? \getenv('FW_TIMEZONE') ?: 'Australia/Adelaide');
        \define('_\IS_CLI', $this->IS_CLI = ($this->INTFC === 'cli'));
        \define('_\IS_WEB', $this->IS_WEB = ($this->INTFC === 'web'));
        \define('_\IS_API', $this->IS_API = ($this->INTFC === 'api'));
        \define('_\OB_OUT', $this->OB_OUT = \ob_get_level());
        $this->IS_WEB AND \ob_start();
        \define('_\OB_TOP', $this->OB_TOP = \ob_get_level());
        $this->TSP_PATH = !empty($this->_['TSP']['PATH'])
            ? $this->_['TSP']['PATH']
            : \implode(PATH_SEPARATOR, $this->TSP_LIST = \array_keys($x = \array_filter([
                ($d = $this->INCP_DIR.'/-app') => \is_dir($d),
                ...\iterator_to_array((function(){
                    global $_;
                    foreach([
                        $this->_['*']['modules'] ?? [],
                        $this->_[$this->INTFC]['modules'] ?? [],
                    ] as $m){
                        foreach($m as $expr => $en){
                            if($en){
                                $found = false;
                                if($f = $this->resolve($expr, $en)){
                                    $found = true;
                                    yield \str_replace('\\','/', \realpath($f)) => true;
                                }
                                if(!$found){
                                    $this->TRACE[] = "!!! Module Not Found: '{$expr}'";
                                }
                            } else {
                                $this->TRACE[] = "??? Module Disabled '{$expr}'";   
                            }
                        }
                    }
                })()),
                ...\iterator_to_array((function(){
                    foreach(\explode(PATH_SEPARATOR,\get_include_path()) as $v){
                        yield \str_replace('\\','/', $v) => true;
                    }
                })()),
            ])))
        ;
    }
    
    protected function config(&$_){
        
    }

    protected function fn($fname){
        return function(...$args) use($fname){
            return $fname(...$args);
        };
    }
    
    protected function build_request(){
        $_REQUEST = ($this->IS_CLI 
            ? function(){
                $parsed = [];
                $key = null;
                $args = \array_slice($argv = $_SERVER['argv'] ?? [], 1);
                foreach ($args as $arg) {
                    if ($key !== null) {
                        $parsed[$key] = $arg;
                        $key = null;
                    } else if(\str_starts_with($arg, '-')){
                        if(\str_ends_with($arg, ':')){
                            $key = \substr($arg,0,-1);
                        } else if(\str_contains($arg,':')) {
                            [$k, $v] = \explode(':', $arg);
                            $parsed[$k] = $v;
                        } else {
                            $parsed[$arg] = true;
                        }
                    } else {
                        $parsed[] = $arg;
                    }
                }
                if ($key !== null) {
                    $parsed[$key] = true;
                }
                $parsed[0] ??= '/';
                return $parsed;
            }
            : function(){
                return $_REQUEST + [ 0 => (function(){
                    $p = \strtok($_SERVER['REQUEST_URI'],'?');;
                    if((\php_sapi_name() == 'cli-server')){
                        return $p;
                    } else {
                        if((\str_starts_with($p, $n = $_SERVER['SCRIPT_NAME']))){
                            return \substr($p,\strlen($n));
                        } else if((($d = \dirname($n = $_SERVER['SCRIPT_NAME'])) == DIRECTORY_SEPARATOR)){
                            return $p;
                        } else {
                            return \substr($p,\strlen($d));
                        }
                    }
                })()];
            }
        )();        
    }
    
    protected function dump ($d){
        echo "\033[97m"
            ."    "
            .\str_replace("\n","\n    ", \json_encode(
                $d, 
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            ))
            .PHP_EOL
            ."\033[0m"
        ;
    }
    
    protected function fs_delete($d){
        if(\is_dir($d)){
            foreach(new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($d, \RecursiveDirectoryIterator::SKIP_DOTS)
                , \RecursiveIteratorIterator::CHILD_FIRST
            ) as $f) {
                if ($f->isDir()){
                    \rmdir($f->getRealPath());
                } else {
                    unlink($f->getRealPath());
                }
            }
            \rmdir($d);
        }
    }
    
    protected function curl($url, $file = null){
        try{
            if($this->IS_CLI && $this->VERBOSITY){
                echo "Remote: {$url}\n";
            }
            if(!($ch = \curl_init($url))){
                throw new \Exception("Failed: Unable to initialze curl");
            };
            \curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  // Follow redirects
            \curl_setopt($ch, CURLOPT_USERAGENT, 'PHP');      // Set User-Agent header to avoid 403
            \curl_setopt($ch, CURLOPT_VERBOSE, $this->VERBOSITY ? true : false);
            if($file){
                if(!($fp = \fopen($file, 'w'))){
                    throw new \Exception("Failed: Unable to open tempfile for writing");
                };
                \curl_setopt($ch, CURLOPT_FILE, $fp);
                \curl_exec($ch);
                if (\curl_errno($ch)) {
                    throw new \Exception("Failed: cURL Error: " . \curl_error($ch));
                }
                if(($h = curl_getinfo($ch, CURLINFO_HTTP_CODE)) != 200){
                    \is_file($file) AND unlink($file);
                    throw new \Exception("Failed: Server responded with an {$h} error");
                }
                return \is_file($file);
            } else {
                \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = \curl_exec($ch);
                if (\curl_errno($ch)) {
                    throw new \Exception("Failed: cURL Error: " . \curl_error($ch));
                }
                $result = \json_decode($response, true);
                if (\json_last_error() === JSON_ERROR_NONE) {
                    return $result;
                } else {
                    throw new \Exception("Json Error Code:(".\json_last_error()."): ".\json_last_error_msg());
                }
            }
        } catch (\Throwable $ex) {
            if($file && \is_file($file)){
                \unlink($file);
            }
            throw $ex;
        } finally {
            empty($fp) OR \fclose($fp);
            empty($ch) OR \curl_close($ch);
        }
    }
    protected function iterator($d){
        return new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $d, 
                \FilesystemIterator::SKIP_DOTS
            )
        );
    }
    protected function liblist(){
        static $I; return $I ?? ($I= \array_keys(\iterator_to_array((function() {
            global $_;
            yield $this->INCP_DIR => true;
            foreach($this->_['LIB']['*'] ?? [] as $dx => $en){
                if($en){
                    if(\is_dir($dx)){
                        yield \str_replace('\\','/', \realpath($dx)) => true;
                    } else {
                        \_\DBG AND $this->TRACE[] = "Boot Warning: Library directory not found: '".\str_replace('\\','/', $dx)."'";
                    }
                } else {
                    \_\DBG AND $this->TRACE[] = "Boot Notice: Library directory disabled: '".\str_replace('\\','/', $dx)."'";
                }
            }
            foreach([$this->INCP_DIR, $this->LIB_DIR] as $dx){
                for (
                    $i=0;
                    $dx && $i < 20 ; 
                    $i++, $dx = (\strchr($dx, DIRECTORY_SEPARATOR) != DIRECTORY_SEPARATOR) ? \dirname($dx) : null
                ){ 
                    if(\is_dir($dy = $dx."/--epx")){
                        yield \str_replace('\\','/', $dy) => true;
                    }
                }
            }
            yield $this->LIB_DIR => true;
        })())));
    }
    
    protected function resolve($expr){
        if(($expr[0]??'')=='/' || ($expr[1]??'')==':'){
            return \str_replace('\\','/', \realpath($expr));
        } else if(\str_starts_with($expr, '[')) {
            if(!\preg_match('#^(?<lib>\[(?<url>[^\]]+)\])(?<sub>.*)#',$expr, $m)){
                if($this->IS_CLI && $this->VERBOSITY){
                    echo "Invalid library expression";
                }
                return;
            }
            
            $m = \array_filter($m, fn($k) => !is_numeric($k), ARRAY_FILTER_USE_KEY);
            $temp_id = \uniqid();
            $libname = \str_replace('/','][', "lib-{$m['lib']}");
            $subpath = \trim($m['sub'] ?? 'src', '/');
            $subname = '['.\str_replace('/','][', $subpath).']';
            $lib_dir = $this->LOCAL_DIR."/{$libname}";
            $out_dir = "{$lib_dir}/{$subname}";
            
            if(!\is_dir($out_dir)){
                $lib_url = "https://{$m['url']}.zip";
                $lib_zip = $this->LOCAL_DIR."/{$libname}/code.zip";
                if(!\is_file($lib_zip)){
                    \is_dir($d = \dirname($lib_zip)) OR \mkdir($d, 0777, true);
                    if(!$this->curl($lib_url, $lib_zip)){
                        if($this->IS_CLI && $this->VERBOSITY){
                            echo "\033[91mFailed: Library couldn't be dowloaded\033[0m\n";
                        }
                        return;
                    }
                    if($this->IS_CLI && $this->VERBOSITY){
                        $sha = \sha1_file($lib_zip);
                        echo "Library Downloaded: '{$lib_zip}' {$sha}\n";
                    }
                }
                
                if (!(($zip = new \ZipArchive)->open($lib_zip) === true)) {
                    if($this->IS_CLI && $this->VERBOSITY){
                        echo "\033[91mFailed: Library couldn't be dowloaded\033[0m\n";
                    }
                    return;
                }
    
                $extracted = false;
                $zip_offset = \substr($s = $zip->getNameIndex(0), 0, \strpos($s, '/'));
                $sub_offset = "{$zip_offset}/{$subpath}";
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $fileName = $zip->getNameIndex($i);
                    if (\str_starts_with($fileName, $sub_offset)) {
                        $target_path = $out_dir.substr($fileName, strlen($sub_offset));
                        if (str_ends_with($fileName, '/')) {
                            is_dir($target_path) OR @mkdir($target_path, 0777, true);
                        } else {
                            is_dir(dirname($target_path)) OR @mkdir($d, 0777, true);
                            file_put_contents($target_path, $zip->getFromIndex($i));
                        }
                        $extracted = true;
                    }
                }
                
                if(!$extracted){
                    if($this->IS_CLI && $this->VERBOSITY){
                        echo "\033[91mFailed: Library couldn't be dowloaded\033[0m\n";
                    }
                    return;
                }
            }
            if($this->IS_CLI && $this->VERBOSITY){
                echo "\033[92mLibrary Path '{$out_dir}'\033[0m\n";
            }
            return $out_dir;
        } else {
            global $_;
            foreach($this->liblist() ?? [] as $d){
                if(\is_dir($dy = "{$d}/{$expr}")){
                    return \str_replace('\\','/', \realpath($dy));
                }
            }
        }
    }
    
    public function ensure_module($module){
        //$module = ".local-[src~shell-win-0][github.com~klude-org~fw-pkg-pax-one~archive~refs~heads~main]";
        if(!\preg_match("#.local-\[([^\]]+)\]\[([^\]]+)\]$#", \str_replace('~','/', $module), $m)){
            return false;
        }
        [$null, $m_path, $l_path] = $m;
        $lib_dir = \str_replace('\\','/', __DIR__);
        $local_dir = \str_replace('\\','/', __DIR__.'/.local');

        $l_name = \str_replace('/','~', $l_path);
        $l_url = "https://{$l_path}.zip";
        $l_zip = "{$local_dir}/{$l_name}-code.zip";

        $m_name = \str_replace('/','~', $m_path);
        $m_dir = "{$lib_dir}/.local-[{$m_name}][{$l_name}]";

        try {
            if(!\is_file($l_zip)){
                \is_dir($d = \dirname($l_zip)) OR \mkdir($d, 0777, true);
                if(!($ch = \curl_init($l_url))){
                    throw new \Exception("Failed: Unable to initialze curl");
                };
                \curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                \curl_setopt($ch, CURLOPT_USERAGENT, 'PHP');
                \curl_setopt($ch, CURLOPT_VERBOSE, false);
                if(!($fp = \fopen($l_zip, 'w'))){
                    throw new \Exception("Failed: Unable to open tempfile for writing");
                };
                \curl_setopt($ch, CURLOPT_FILE, $fp);
                \curl_exec($ch);
                if (\curl_errno($ch)) {
                    throw new \Exception("Failed: cURL Error: " . \curl_error($ch));
                }
                if(($h = curl_getinfo($ch, CURLINFO_HTTP_CODE)) != 200){
                    \is_file($l_zip) AND unlink($l_zip);
                    throw new \Exception("Failed: Server responded with an {$h} error");
                }
                if(!\is_file($l_zip)){
                    throw new \Exception("Failed: Unable to download file");
                }
            }
        } finally {
            empty($fp) OR \fclose($fp);
            empty($ch) OR \curl_close($ch);
        }
        
        try {
            if (!(($zip = new \ZipArchive)->open($l_zip) === true)) {
                throw new \Exception("Failed: Library couldn't be dowloaded");
            }
            $extracted = false;
            $zip_offset = \substr($s = $zip->getNameIndex(0), 0, \strpos($s, '/'));
            $sub_offset = "{$zip_offset}/{$m_path}";
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $fileName = $zip->getNameIndex($i);
                if (\str_starts_with($fileName, $sub_offset)) {
                    $target_path = $m_dir.substr($fileName, strlen($sub_offset));
                    if (str_ends_with($fileName, '/')) {
                        is_dir($target_path) OR @mkdir($target_path, 0777, true);
                    } else {
                        is_dir(dirname($target_path)) OR @mkdir($d, 0777, true);
                        file_put_contents($target_path, $zip->getFromIndex($i));
                    }
                    $extracted = true;
                }
            }
            if(!$extracted){
                throw new \Exception("Failed: Library couldn't be extracted");
            }    
        } finally {
            $zip->close();
        }
        
        return $m_dir;
    }    
    
    
    public function __invoke(){
        $this->build_request();
        $this->VERBOSITY = $_REQUEST['--verbose'] ?? false;
        \set_include_path($this->TSP_PATH);
        if(($key = \array_key_first($_REQUEST)) === 0){
            $intfc = $this->INTFC;
            $path = \trim('__'.($_REQUEST['-p'] ?? null ?? '').'/'.\trim($_REQUEST[0] ?? '', '/'), '/');
            if(
                ($file = \stream_resolve_include_path("{$path}/-@{$intfc}.php"))
                || ($file = \stream_resolve_include_path("{$path}-@{$intfc}.php"))
                || ($file = \stream_resolve_include_path("{$path}/-@.php"))
                || ($file = \stream_resolve_include_path("{$path}-@.php"))
            ){ 
                (function() use($file){
                    (function(){
                        foreach(\explode(PATH_SEPARATOR,get_include_path()) as $d){
                            \is_file($f = "{$d}/.functions.php") AND include_once $f;
                        }
                    })();
                    if(\is_callable($o = (include $file))){
                        ($o)();
                    }
                })->bindTo($GLOBALS['--CTLR'] = (object)['fn' => (object)[]])();
            } else {
                throw new \Exception("Not Found: ".($_REQUEST[0] ?? "/"));
            }
        }        
    }    
    
}