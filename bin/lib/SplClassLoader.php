<?php

class SplClassLoader {
    
    protected $includePath;
    
    public function __construct($includePath=null) {
        $this->includePath = $includePath;
    }
    
    public function setIncludePath($path) {
        $this->includePath = $path;
        return $this;
    }
    
    public function register() {
        spl_autoload_register(array($this, 'loadClass'), false);
    }
    
    public function registerAsPear() {
        set_include_path(implode(PATH_SEPARATOR, array(
        $this->includePath,
        get_include_path())));
        $this->register();
    }
    
    protected function loadClass($className) {
        $fileName = str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
        $filePath = (is_null($this->includePath) ? '' : $this->includePath . DIRECTORY_SEPARATOR) . $fileName;
        
        if(!is_file($filePath)) {
            return;
        }
        
        require $filePath;
    }
    
}
