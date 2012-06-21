<?php
// Smartyを取り込む
define('LIBS_PATH',dirname(__FILE__).'/../libs/');
include_once(LIBS_PATH.'MySmarty.class.php');
include_once(LIBS_PATH.'BoardDB.php');

$board = new Board();
$board->selectAction();

class Board{
    private $params;
    private $sm_ary;
    private $ttype;

    function __construct(){
        $params = array();
        $this->setParams();
        $this->setTType('thread_list');
    }
    /**
     * 処理の分岐
     **/
    public function selectAction(){
        $is_success = false;
        if( isset($this->params['submit']) && isset($this->params['contents']) && isset($this->params['author']) ){
            if( isset($this->params['title']) )
                // スレッド作成
                $this->setParam('index', $this->accessDB('setthread', $this->params['title']));
            if( isset($this->params['index']) )
                // コメント追加
                if( $this->accessDB('setcontents', array($this->params['index'], $this->params['contents'],$this->params['author'])) )
                    $is_success = true;
            if($is_success) $thread = $this->params['index'];
            else $thread = 'abc';
            // そのページに移動
            header("Location: http://{$_SERVER['HTTP_HOST']}/index.php?index={$thread}");
            exit;
        }elseif( isset($this->params['index']) ){
            // スレッド表示
            if( ctype_digit($this->params['index']) ){
                $this->setSmAry( $this->accessDB('getcontents', $this->params['index']) );
                // if( !empty($this->setSmAry( $this->accessDB('getcontents', $this->params['index']) )) )
                    $this->setTType('thread_contents');
            }
        }elseif( isset($this->params['cmd']) && $this->params['cmd'] == 100 ){
            // スレッド作成
            $ttype = 'thread_new';
            $this->setTType('thread_new');
        }elseif( isset($this->params['url']) ){
            // クッションページ
            if(strpos($this->params['url'], 'http://'.$_SERVER['SERVER_NAME']) === 0){
                header("Location: $url");
                exit;
            }
            $this->setTType('cushion');
        }else{
            // スレッド一覧
            $this->setSmAry( $this->accessDB('getlist',null) );
        }
        $this->setSmarty();
    }
    /**
     * パラメータ配列の値の更新
     **/
    public function setParam($key, $val){
        if($val !== false && $val != "") $this->params[$key] = $val;
    }
    /**
     * Smarty処理
     **/
    private function setSmarty(){
        $smarty = new MySmarty;
        $list = array('index','url');
        // 変数をassign
        if( isset($this->sm_ary) ) $smarty->assign('data_ary', $this->sm_ary);
        foreach($list as $i)
            if( isset($this->params[$i]) ) $smarty->assign($i, $this->params[$i]);
        // テンプレートを選択
        if( !empty($this->ttype) ) $smarty->display($this->ttype.'.html');
    }
    /**
     * GET/POSTパラメータの取得
     */
    private function setParams(){
        $posts = array('submit','title','index','author','contents');
        $gets = array('thread','url','index','cmd');
        foreach($posts as $label)
            if( isset($_POST[$label]) && $_POST[$label] != "")
                $this->params[$label] = htmlspecialchars($_POST[$label]);
        foreach($gets as $label)
            if( isset($_GET[$label]) && $_GET[$label] != "")
                $this->params[$label] = htmlspecialchars($_GET[$label]);
    }
    /**
     * Smarty用のデータ配列に値を代入
     **/
    private function setSmAry($ary){
        if( !empty($ary) ) $this->sm_ary = $ary;
        return $this->sm_ary;
    }
    /**
     * Smartyテンプレートの値を更新
     **/
    private function setTType($type){
        if( !empty($type) ) $this->ttype = $type;
    }
    /**
     * BoardDBへのアクセス
     **/
    private function accessDB($act, $param){
        $bdb = new BoardDB();
        switch($act){
        case 'setthread':
            return $bdb->setNewThread($param);
        case 'setcontents':
            return $bdb->setResponse($param);
        case 'getlist':
            return $bdb->getThreadList();
        case 'getcontents':
            return $bdb->getThreadContents($param);
        default:
            return false;
        }
    }
}
