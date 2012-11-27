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
    
    public function loadPlugins($dir) {
        if(!is_dir($dir)) {
            throw new RuntimeExtension('ディレクトリが存在しません。: ' . $dir);
        }
        foreach(new RecursiveIteratorIterator( new RecursiveDirectoryIterator($dir)) as $entry) {
            if(basename($entry) != 'plugin.json') {
                continue;
            }
            $contents = file_get_contents($entry);
            if(!$contents) {
                throw new RuntimeException('プラグイン定義情報の取得に失敗しました。: ' . $entry);
            }
            $jsonData = json_decode($contents, true);
            if(!$jsonData) {
                throw new RuntimeException('プラグイン定義情報の解析に失敗しました。: ' . $entry);
            }
            if(!isset($jsonData['entry_class'])) {
                throw new RuntimeException('定義情報に"entry_class"の定義が存在しません。: ' . $entry);
            }
            $entryClassName = $json['entry_class'];
            if(!class_exists($entryClassName)) {
                throw new RuntimeException('プラグインのクラス"' . $entryClassName . 'は存在しません。": ' . $entry);
            }
            
            $plugin = new $entryClassName();
            $this->addPlugin($plugin);
            
            // $extensionPointMap = $plugin->getExtensionPoints();
            // foreach((array)$extensionPointMap as $extensionPoint=>$callback) {
            //     $this->addExtensionPoint($name, $plugin->getName(), $callback);
            // }
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
