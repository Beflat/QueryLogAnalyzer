<?php


/**
 * Normalizerのプラグインの一覧を管理するクラス
 */
class Normalizer_Manager {
    
    /**
     * Normalizerの配列。
     * @var array
     */
    private $plugins;
    
    public function __construct() {
    }
    
    
    public function addNormalizer(Normalizer_Plugin_Interface $plugin) {
        $name = $plugin->getCommandName();
        $this->plugins[$name] = $plugin;
    }
    
    
    /**
     * 複数のNormalizerを一括で登録する。
     * @param array $plugins プラグインの配列。
     */
    public function addNormalizers($plugins) {
        foreach((array)$plugins as $plugin) {
            $this->addNormalizer($plugin);
        }
    }
    
    
    /**
     * 指定された名前に合致するNormalizerを返す。
     * @param  string $name Normalizerの名前。(コマンドラインのサブコマンド名として指定する際の名前)
     * @return Normalizer_Plugin 合致したNormalizerを返す。
     */
    public function getNormalizer($name) {
        if(!isset($this->plugins[$name])) {
            throw new UnexpectedValueException('無効な名前です。: ' . $name);
        }
        
        return $this->plugins[$name];
    }
    
    
    /**
     * コマンドラインで指定するコマンド名の一覧を返す。
     * @return array 
     */
    public function getCommandNameList() {
        return array_keys($this->plugins);
    }
    
    
    /**
     * コマンドラインパーサーの初期化時、プラグイン固有の処理を実行するために呼び出されるメソッド。
     * @param  Console_CommandLine $parser コマンドラインパーサー。COnsole_CommandLineオブジェクト。
     * @return void
     */
    public function onInitCommand(Console_CommandLine $parser) {
        foreach($this->plugins as $plugin) {
            $plugin->onInitCommand($parser);
        }
    }
}
