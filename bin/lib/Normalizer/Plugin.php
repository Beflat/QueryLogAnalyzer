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
    public function getType() {
        return self::TYPE;
    }
    
    /**
     * プラグインの初期化を行う。
     * @param  array $options オプション情報を含んだ配列
     */
    public function initPlugin($options) {
        
    }
    
    
    /**
     * initで渡されたオプションの値の妥当性を検討する。
     * @return void
     * @throws InvalidArgumentException
     */
    public function validateParams() {
    }
    
    
    /**
     * コマンドラインからこのプラグインを指定するための名前を返す(半角英数で指定)。
     * @return string
     */
    abstract function getNormalizerName();
    
    
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
    
    
    /**
     * このプラグインがどの拡張ポイントでどの処理を実行するかの情報を返す。
     * @return array 拡張ポイントとコールバック関数の対応付けを定義した連想配列
     */
    public function getExtensionPoints() {
        return array();
    }

    
    
}
