<?php



class Plugin_Manager {
    
    /**
     * プラグインの配列
     * @var Plugin_Interface[]
     */
    private $plugins;
    
    /**
     * 現在登録されている拡張ポイントの一覧。
     * @var array
     * 
     * $extensionPoints = array(
     *    '[拡張ポイント名]' => array([コールバック関数], [コールバック関数], ...)
     * );
     */
    private $extensionPoints;
    
    public function __construct() {
        $this->init();
    }
    
    
    public function init() {
        $this->extensionPoints = array();
        $this->plugins = array();
    }
    
    
    /**
     * 指定されたファイルをプラグインの設定ファイルとみなして解析する。
     * プラグインディレクトリは外部でAutoLoadの設定が行われている前提。
     * @param  string $configFile プラグイン設定ファイル。プラグインクラス名を列挙したフもの。
     * @throws RuntimeException
     */
    public function loadPlugins($configFile) {
        if(!is_file($configFile)) {
            throw new RuntimeExtension('設定ファイルが存在しません。: ' . $configFile);
        }
        
        $fp = fopen($configFile, 'r');
        if(!$fp) {
            throw new RuntimeException('設定ファイルの読み込みに失敗しました。: ' . $configFile);
        }
        
        while(!feof($fp)) {
            $line = rtrim(fgets($fp, 4196), "\r\n");
            if($line[0] == '#') {
                //先頭が#の場合はコメントと見て読み飛ばす。
                continue;
            }
            //設定ファイルの内容はクラス名とみなす。
            if(!class_exists($entryClassName)) {
                throw new RuntimeException('プラグインのクラス"' . $entryClassName . 'は存在しません。": ' . $entry);
            }
            
            $plugin = new $line();
            $this->addPlugin($plugin);
        }
    }
    
    
    public function addPlugin(Plugin_Interface $plugin) {
        $name = $plugin->getName();
        $this->plugins[$name] = $plugin;
    }
    
    
    /**
     * 指定されたタイプのプラグインの一覧を返す。
     * @param  string $type プラグインの種類を識別する文字列
     * @return array 合致したプラグインを含む配列
     */
    public function getPluginsByType($type) {
        $matched = array();
        foreach($this->plugins as $name=>$plugin) {
            if($plugin->getType() == $type) {
                $matched[$name] = $plugin;
            }
        }
        return $matched;
    }
    
    /**
     * 拡張ポイントを追加する。現在は優先度は指定できない。削除もできない。
     */
    public function addExtensionPoint($name, $pluginName, $callback) {
        if(!isset($this->extensionPoints[$name])) {
            $this->extensionPoints[$name] = array();
        }
        $this->extensionPoints[$name][$pluginName] = $callback;
    }
    
    
    /**
     * 拡張ポイントを実行する。
     * @param  string $extensionPoint 拡張ポイント名
     * @param ... コールバック関数に渡すパラメータ
     * @return array プラグイン名をキーにした、拡張ポイントの実行結果を含む配列
     */
    public function call($extensionPoint) {
        if(!isset($this->extensionPoints[$extensionPoint])) {
            return false;
        }
        
        $params = array_shift(func_get_args());
        
        $results = array();
        foreach($this->extensionPoints[$extensionPoint] as $pluginName=>$callback) {
            $results[$pluginName] = call_user_func_array($callback, $params);
        }
        
        return $results;
    }
    
    
    /**
     * 現在の拡張ポイントの登録状況を返す。デバッグ目的で使用。
     * @return array
     */
    public function getExtensionPoints() {
        return $this->extensionPoints;
    }
    
}
