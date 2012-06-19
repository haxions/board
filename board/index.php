<?php

// Smartyを取り込む
define('LIBS_PATH',dirname(__FILE__).'/../libs/');
include_once(LIBS_PATH.'functions.php');
include_once(LIBS_PATH.'MySmarty.class.php');
include_once(LIBS_PATH.'BoardDB.php');

// Smartyインスタンスの生成
$smarty = new MySmarty;
// smartyテンプレートの種類
$ttype = 'thread_list';
// 一覧表示のフラグ
$is_list = true;
if(isset($_POST['submit'])){
    // スレッド作成・レス追加
    // titleはスレ作成時，threadはレス投稿時
    // スレ作成時のthread_indexとスレ投稿時のres_idはこちらで付与(衝突を避けるため
    if( isset($_POST['title']) ){
        $title = htmlspecialchars($_POST['title']);
    }
    if( isset($_POST['thread']) ){
        $thread_index = htmlspecialchars($_POST['thread']);
    }
    $author = htmlspecialchars($_POST['author']);
    $contents = htmlspecialchars($_POST['contents']);
    // データをDBに保存
    if(!empty($contents)){
        $bdb = new BoardDB();
        if(isset($title)){
            if(!empty($title)){
                $thread_index = $bdb->setNewThread($title);
            }
        }
        $bdb->setResponse($thread_index, $contents, $author);
    }else $thread_index = "abc";
    // そのページに移動
    header("Location: http://{$_SERVER['HTTP_HOST']}/index.php?thread={$thread_index}");
    exit;
}elseif(isset($_GET['thread'])){
    // あるスレッドを表示
    $thread_index = htmlspecialchars($_GET['thread']);
    if(ctype_digit($thread_index)){
        $bdb = new BoardDB();
        $thread_data = $bdb->getThreadContents($thread_index);
        if(count($thread_data['res'])){
            $smarty->assign('thread_data',$thread_data);
            $smarty->assign('thread_index',$thread_index);
            $is_list = false;
            $ttype = 'thread_contents';
        }
    }
}elseif(isset($_GET['url'])){
    // クッションページ用
    $is_list = false;
    $ttype = 'cushion';
    $url = htmlspecialchars($_GET['url']);
    if(strpos($url, 'http://'.$_SERVER['SERVER_NAME']) === 0){
        header("Location: $url");
        exit;
    }
    $smarty->assign('url',$url);
}elseif(isset($_GET['cmd'])){
    // その他の操作
    $cmd = htmlspecialchars($_GET['cmd']);
    if($cmd == 100){
        $is_list = false;
        $ttype = 'thread_new';
    }
}
// リスト表示
if($is_list){
    $bdb = new BoardDB();
    $thread_list = $bdb->getThreadList();
    $smarty->assign('thread_list',$thread_list);
    $ttype = 'thread_list';
}
// 表示 (テンプレート選択)
if(!empty($ttype)){
    $smarty->display($ttype.'.html');
}