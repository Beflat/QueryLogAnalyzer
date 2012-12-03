<?php


/**
 * ログ情報の正規化を行うプラグインの標準動作の一部を実装したクラス
 */
abstract class Normalizer_Plugin implements Normalizer_Plugin_Interface {
    
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
    
    
    abstract public function getCommandName();
    
    /**
     * コマンドラインパーサーの初期化時、プラグイン固有の処理を実行するために呼び出されるメソッド。
     * @param  Console_CommandLine $parser コマンドラインパーサー。COnsole_CommandLineオブジェクト。
     * @return void
     * 
     * パラメータや引数の追加などを行う。
     */
    public function onInitCommand(Console_CommandLine $parser) {
        //デフォルトでは何もしない。
    }
    
    
    /**
     * プラグインの初期化を行う。
     * @param  array $options オプション情報を含んだ配列
     */
    public function initPlugin($options) {
        $this->options = $options;
    }
    
    
    /**
     * initで渡されたオプションの値の妥当性を検討する。
     * @return void
     * @throws InvalidArgumentException
     */
    public function validateParams() {
        //デフォルトでは何もしない。
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
}
