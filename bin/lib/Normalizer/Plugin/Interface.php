<?php



/**
 * ログ情報の正規化を行うプラグインのインターフェース
 */
interface Normalizer_Plugin_Interface extends Plugin_Interface {
    
    /**
     * プラグインの初期化を行う。
     * @param  array $options オプション情報を含んだ配列
     */
    public function initPlugin($options);
    
    
    /**
     * initで渡されたオプションの値の妥当性を検討する。
     * @return void
     * @throws InvalidArgumentException
     */
    public function validateParams();
    
    
    /**
     * コマンドラインからこのプラグインを指定するための名前を返す(半角英数で指定)。
     * @return string
     */
    public function getNormalizerName();
    
    
    /**
     * 変換処理を実行する。
     * @param array $files 処理対象のファイル一覧
     * @return void
     */
    public function normalize($files);
    
    
    /**
     * 変換結果を返す。
     * @return array 変換結果のLogicalEntryを含んだ配列
     */
    public function getResult();
    
}
