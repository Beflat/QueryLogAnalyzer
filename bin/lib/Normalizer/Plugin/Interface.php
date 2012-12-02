<?php



/**
 * ログ情報の正規化を行うプラグインのインターフェース
 */
interface Normalizer_Plugin_Interface extends Plugin_Interface {
    
    /**
     * プラグイン名を返す。
     * @return string
     */
    public function getName();
    
    
    /**
     * プラグインの種類を返す。
     * @return string
     */
    public function getType();
    
    
    public function getCommandName();
    
    /**
     * コマンドラインパーサーの初期化時、プラグイン固有の処理を実行するために呼び出されるメソッド。
     * @param  Console_CommandLine $parser コマンドラインパーサー。COnsole_CommandLineオブジェクト。
     * @return void
     * 
     * パラメータや引数の追加などを行う。
     */
    public function onInitCommand(Console_CommandLine $parser);
    
    
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
