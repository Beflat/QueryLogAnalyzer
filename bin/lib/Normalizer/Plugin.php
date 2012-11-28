<?php


/**
 * ログ情報の正規化を行うプラグインの基底クラス
 */
abstract class Normalizer_Plugin implements Plugin_Interface {
    
    const TYPE = 'Normalizer';
    
    /**
     * プラグインの動作を調整するためのオプション
     * @var array
     */
    protected $options;
    
    /**
     * 変換結果を格納する配列(LogicalEntryの配列)
     * @var array
     */
    protected $result;
    
    /**
     * プラグイン名を返す。
     * @return string
     */
    abstract public function getName();
    
    
    /**
     * プラグインの種類を返す。
     * @return string
     */
    public final function getType() {
        return self::TYPE;
    }
    
    
    public function getCommandName() {
        $definition = $this->getCommandDefinition();
        return $definition['name'];
    }
    
    /**
     * サブコマンドとして登録するためのオブジェクトを返す。
     * オプションや説明などの情報を含んでいる。
     * @return array Console_CommandLineのサブコマンドを定義するための情報を含んだ配列。次のフォーマットに従う。
     * 
     * array(
     *     'name' => 'サブコマンド名',
     *     'options' => array('オプション名。addOptionの第1引数' => array([addOptionに渡すパラメータ情報])),
     *     'arguments' => array('引数名。addArgumentの第2引数' => array([addArgumentに渡すパラメータ情報])),
     * );
     */
    abstract function getCommandDefinition();
    
    
    /**
     * プラグインの初期化を行う。
     * @param  array $options オプション情報を含んだ配列
     */
    public function initPlugin($options) {
        $this->options;
    }
    
    
    /**
     * initで渡されたオプションの値の妥当性を検討する。
     * @return void
     * @throws InvalidArgumentException
     */
    public function validateParams() {
    }
    
    
    /**
     * 変換処理を実行する。
     * @param array $files 処理対象のファイル一覧
     * @return void
     */
    abstract public function normalize($files);
    
    
    /**
     * 変換結果を返す。
     * @return array 変換結果のLogicalEntryを含んだ配列
     */
    abstract public function getResult();
    
    
    public static function getPluginByCommandName($plugins, $commandName) {
        foreach($plugins as $idx=>$plugin) {
            if($plugin->getCommandName() == $commandName) {
                return $pluginx[$idx];
            }
        }
        throw new RuntimeException('無効なコマンド名です。: ' . $commandName);
    }
}
