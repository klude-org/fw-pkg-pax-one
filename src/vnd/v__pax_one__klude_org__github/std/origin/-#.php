<?php namespace v__pax_one__klude_org__github\std;

class origin extends \stdClass {
    
    public readonly array $_;
    public readonly array $CONFIG;
    public readonly string $LIB_DIR;
    public readonly string $INCP_DIR;
    public readonly string $CWD_DIR;
    public readonly string $INTFC;
    public readonly string $PANEL;
    public readonly string $LOCAL_DIR;
    public readonly array $ENV_VARS;
    public readonly bool $IS_CLI;
    public readonly bool $IS_WEB;
    public readonly bool $IS_API;
    public readonly int $OB_OUT;
    public readonly int $OB_TOP;
    public readonly string $TSP_PATH;
    public readonly string $CFG_CACHE__F;
    public readonly string $CFG_SITE__F;
    public readonly bool $CFG_CACHE__EN;
    public readonly bool $CFG_FROM_CACHE;
    
    public int|bool|null $VERBOSITY = null;
    
    public static function _() { static $i;  return $i ?: ($i = new static()); }
    
    public function __construct(){
        global $_;
        
        $this->ENV_VARS = $_ENV;
        $_ENV = $this;
        $this->LIB_DIR = \_\LIB_DIR;
        $this->INCP_DIR = \str_replace('\\','/', \realpath(\dirname($_SERVER['SCRIPT_FILENAME'])));
        $this->PANEL = $_SERVER['FY__PANEL'] ?? '';
        $this->LOCAL_DIR = \_\LIB_DIR.'/.local';
        $this->CWD_DIR = \str_replace('\\','/',getcwd());
        
        
        
        $cx = \is_file($this->CFG_CACHE__F = $this->INCP_DIR."/.local/.config-cache.php");
        $sx = \is_file($this->CFG_SITE__F = $this->INCP_DIR."/.config.php");
        //$lx = \is_file($this->CFG_LIB__F = \_\LIB_DIR."/.config.php");
        $this->CFG_CACHE__EN = $GLOBALS['CFG_CACHE'] ?? true;
        if(
            $this->CFG_CACHE__EN
            && $cx
            && (($ct = \filemtime($this->CFG_CACHE__F)) >= \filemtime($_SERVER['SCRIPT_FILENAME']))
            && ($sx ? ($ct >= \filemtime($this->CFG_SITE__F)) : true)
            //&& ($lx ? ($ct >= \filemtime($this->CFG_LIB__F)) : true)
        ){
            $GLOBALS['_TRACE'][] = 'Config: Loading from cache';
            include $this->CFG_CACHE__F;
            $this->CFG_FROM_CACHE = true;
        } else {
            $GLOBALS['_TRACE'][] = 'Config: Loading from site';
            $cx AND \unlink($this->CFG_CACHE__F);
            //$lx AND include $this->CFG_LIB__F;
            $sx AND include $this->CFG_SITE__F;
            $this->CFG_FROM_CACHE = false;
        }        
        
        $this->_ = $_[\_::class] ?? [];
        $this->CONFIG = $_;
        $this->INTFC = $this->_['INTFC'] 
            ?? $_SERVER['HTTP_REQUEST_INTERFACE'] 
            ?? (empty($_SERVER['DOCUMENT_ROOT']) 
                ? 'cli' 
                : 'web'
            )
        ;
        
        \date_default_timezone_set($this->_['TIMEZONE'] ?? \getenv('FW_TIMEZONE') ?: 'Australia/Adelaide');
        \define('_\IS_CLI', $this->IS_CLI = ($this->INTFC === 'cli'));
        \define('_\IS_WEB', $this->IS_WEB = ($this->INTFC === 'web'));
        \define('_\IS_API', $this->IS_API = (!\_\IS_CLI && !\_\IS_WEB));
        \define('_\OB_OUT', $this->OB_OUT = \ob_get_level());
        $this->IS_WEB AND \ob_start();
        \define('_\OB_TOP', $this->OB_TOP = \ob_get_level());
        $this->TSP_PATH = !empty($this->_['TSP']['PATH'])
            ? $this->_['TSP']['PATH']
            : \implode(PATH_SEPARATOR, $this->TSP_LIST = \array_keys($x = \array_filter([
                ($d = $this->INCP_DIR.'/app') => \is_dir($d),
                ...\iterator_to_array((function(){
                    global $_;
                    foreach([
                        ...($this->_['*']['modules'] ?? []),
                        ...($this->_[$this->INTFC]['modules'] ?? []),
                        ...($this->_[$this->INTFC.$this->PANEL]['modules'] ?? []),
                    ] as $expr => $en){
                        if($en){
                            $found = false;
                            if($f = $this->resolve_module($expr, $en)){
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
    public function iterator($d){
        return new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $d, 
                \FilesystemIterator::SKIP_DOTS
            )
        );
    }
    public function liblist(){
        static $I; return $I ?? ($I= \array_keys(\iterator_to_array((function() {
            global $_;
            yield $this->INCP_DIR => true;
            foreach([
                ...($this->_['*']['libraries'] ?? []),
                ...($this->_[$this->INTFC]['libraries'] ?? []),
                ...($this->_[$this->INTFC.$this->PANEL]['libraries'] ?? []),
            ] as $dx => $en){
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
    
    public function resolve_module($expr){
        if(($expr[0]??'')=='/' || ($expr[1]??'')==':'){
            return \str_replace('\\','/', \realpath($expr));
        } else if(\str_starts_with($expr, '[')) {
            if(!\preg_match("#vnd-\[([^\]]*)\]\[([^\]]+)\]$#", \str_replace('~','/', $expr), $m)){
                return false;
            }
            [$null, $m_path, $l_path] = $m;
            $lib_dir = \str_replace('\\','/', $this->LIB_DIR);
            $vnd_dir = \str_replace('\\','/', $this->LIB_DIR.'/vnd');
    
            $l_name = \str_replace('/','~', $l_path);
            $l_url = "https://{$l_path}.zip";
            $l_zip = "{$lib_dir}/.local/vnd-{$l_name}.zip";
    
            $m_name = \str_replace('/','~', $m_path);
            $m_dir = "{$lib_dir}/vnd-[{$m_name}][{$l_name}]";
    
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
                $sub_offset = \rtrim("{$zip_offset}/{$m_path}",'/');
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
            
        } else {
            global $_;
            foreach($this->liblist() ?? [] as $d){
                if(\is_dir($dy = "{$d}/{$expr}")){
                    return \str_replace('\\','/', \realpath($dy));
                }
            }
        }
    }
    
    public function __invoke(){
        $this->build_request();
        $this->VERBOSITY = $_REQUEST['--verbose'] ?? false;
        \set_include_path($this->TSP_PATH);
        if(($key = \array_key_first($_REQUEST)) === 0){
            $access = $this->INTFC.$this->PANEL;
            $panel = $this->PANEL;
            $path = \trim('__'.($_REQUEST['-p'] ?? null ?? '').'/'.\trim($_REQUEST[0] ?? '', '/'), '/');
            $search = [];
            if(
                ($file = \stream_resolve_include_path($search[]= "{$path}/-@{$access}.php"))
                || ($file = \stream_resolve_include_path($search[]= "{$path}-@{$access}.php"))
                || ($file = \stream_resolve_include_path($search[]= "{$path}/-@{$panel}.php"))
                || ($file = \stream_resolve_include_path($search[]= "{$path}-@{$panel}.php"))
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