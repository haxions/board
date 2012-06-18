<?php

// Smartyを取り込む
include_once("../libs/functions.php");
include_once("../libs/MySmarty.class.php");

// セッションスタートとハイジャック対策
session_start();
session_regenerate_id(true);

// Smartyインスタンスの生成
$smarty = new MySmarty;

// 一覧表示のフラグ
$is_list = true;
// クッションページのフラグ
$is_cushion = false;    
// 投稿が押されたかどうかの判定
if (isset($_POST['submit']) ) {
    // POST + DATE
    // titleはスレ作成時，topic_idはレス投稿時，linkは返信時につく
    // スレ作成時のtopic_idとスレ投稿時のres_idはこちらで付与(衝突を避けるため
    if( isset($_POST['title']) ){
        $title = htmlspecialchars($_POST['title']);
    }
    if( isset($_POST['link']) ){
        $link = htmlspecialchars($_POST['link']);
    }
    if( isset($_POST['topic']) ){
        $topic_id = htmlspecialchars($_POST['topic']);
    }
    $author = htmlspecialchars($_POST['author']);
    $contents = htmlspecialchars($_POST['text']);
    $date = date("Y/m/d - H:i:s");

    // 認証を関数化(DBへ問い合わせをして認証を行う)
    if (Authenticator($_POST["name"] , $_POST["password"])) {
        
        // 認証の鍵を保存しておく
        $_SESSION["name"] = $_POST["name"];
       
        // 認証に成功したので、blog_edit.phpへリダイレクトす
        header("Location:HTTP://".$_SERVER['HTTP_HOST']."/~dwango041/24/blog_edit.php" );
        exit;
    }
   
    // ここに遷移してきた場合はログインに失敗している
    // 認証が失敗した場合のエラーメッセージの作成
    if ($_POST["name"] == "") {
        // ユーザー名が入力されていない旨のメッセージ
        $error_message = "のーねーむ";
    } else {
        // 値が入力されていたが、間違えていた:旨のメッセージ
        $error_message = "ねーむかぱすちがう";
    }
    
    $smarty->assign("error_message", $error_message);
    $smarty->assign("name",$_POST["name"]);
    $is_list = false;
}elseif(isset($_GET['thread'])){
    if(ctype_digit($_GET['thread'])){
        $is_list = false;
        $thread_index = $_GET['thread'];
        $thread_title = '猫神様って本当にいるのかしら？';
        $res = array(
                     array('res_id'=>'321_1',
                           'res_author'=>'ペンギンさん',
                           'res_icon'=>3,
                           'res_contents'=>'そんなこと言ったってどうしようもないでしょ？'),
                     array('res_id'=>'321_2',
                           'res_author'=>'ウサギさん',
                           'res_icon'=>4,
                           'res_contents'=>'うへへｈｗｗｗ http://yahoo.co.jp/'),
                     array('res_id'=>'321_3',
                           'res_author'=>'蛇さん',
                           'res_icon'=>1,
                           'res_contents'=>'ぎゃおー！'),
                     array('res_id'=>'321_4',
                           'res_author'=>'人間さん',
                           'res_icon'=>2,
                           'res_contents'=>'ちみたち、どうしてそんな子というの？')
                     );
        $smarty->assign('thread_index',$thread_index);
        $smarty->assign('thread_title',$thread_title);
        $smarty->assign('resary',$res);
    }
}elseif(isset($_GET['url'])){
    $is_list = false;
    $is_cushion = true;
    $url = $_GET['url'];
    if($_SERVER['HTTP_HOST'] == $url){
        header("Location: $url");
        exit;
    }
    $smarty->assign('url',$url);
}
// リスト表示
if($is_list){
    $data = array(
                  array('thread_id'=>321,'thread_title'=>'死ぬ前に何食べたい？','res'=>array(array('res_id'=>'asdfa89x', 'res_icon'=>5, 'res_contents'=>'そんなこといってもねぇ～自分が死ぬことなんて考えたことないし、、どうすればいいのかわからないよ・・そんなこといってもねぇ～自分が死ぬことなんて考えたことないし、、どうすればいいのかわからないよ・・そんなこといってもねぇ～自分が死ぬことなんて考えたことないし、、どうすればいいのかわからないよ・・'),array('res_id'=>'asdfa89x', 'res_icon'=>2, 'res_contents'=>'そんなこといってもねぇ～自分が死ぬことなんて考えたことないし、、どうすればいいのかわからないよ・・そんなこといってもねぇ～自分が死ぬことなんて考えたことないし、、どうすればいいのかわからないよ・・そんなこといってもねぇ～自分が死ぬことなんて考えたことないし、、どうすればいいのかわからないよ・・'),array('res_id'=>'asdfa89x', 'res_icon'=>1, 'res_contents'=>'そんなこといってもねぇ～自分が死ぬことなんて考えたことないし、、どうすればいいのかわからないよ・・そんなこといってもねぇ～自分が死ぬことなんて考えたことないし、、どうすればいいのかわからないよ・・そんなこといってもねぇ～自分が死ぬことなんて考えたことないし、、どうすればいいのかわからないよ・・'))),
                  array('thread_id'=>322,'thread_title'=>'眠たいよね','res'=>array(array('res_id'=>'asdfa89x', 'res_icon'=>4, 'res_contents'=>'そんなこといってもねぇ～自分が死ぬことなんて考えたことないし、、どうすればいいのかわからないよ・・そんなこといってもねぇ～自分が死ぬことなんて考えたことないし、、どうすればいいのかわからないよ・・そんなこといってもねぇ～自分が死ぬことなんて考えたことないし、、どうすればいいのかわからないよ・・'),array('res_id'=>'asdfa89x', 'res_icon'=>1, 'res_contents'=>'そんなこといってもねぇ～自分が死ぬことなんて考えたことないし、、どうすればいいのかわからないよ・・そんなこといってもねぇ～自分が死ぬことなんて考えたことないし、、どうすればいいのかわからないよ・・そんなこといってもねぇ～自分が死ぬことなんて考えたことないし、、どうすればいいのかわからないよ・・'),array('res_id'=>'asdfa89x', 'res_icon'=>3, 'res_contents'=>'そんなこといってもねぇ～自分が死ぬことなんて考えたことないし、、どうすればいいのかわからないよ・・そんなこといってもねぇ～自分が死ぬことなんて考えたことないし、、どうすればいいのかわからないよ・・そんなこといってもねぇ～自分が死ぬことなんて考えたことないし、、どうすればいいのかわからないよ・・'))));
    $smarty->assign('dataary',$data);
}
// 表示
if($is_cushion){
    $smarty->display("cushion.html");
}else{
    $smarty->display("board.html");
}