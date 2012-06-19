<?php
/**
 * DBとのやり取りを行う
 **/
class BoardDB{
    /**
     * すべてのスレッドのList情報を取得
     **/
    function __construct(){
        date_default_timezone_set('Asia/Tokyo');
    }
    public function getThreadList(){
        $db = $this->db_connect();
        $stmt = $db->query("(SELECT thread_id,contents,id FROM response GROUP BY thread_id) UNION (SELECT f.thread_id, f.contents, f.id FROM (SELECT thread_id, max(id) AS max_id FROM response GROUP BY thread_id) AS x INNER JOIN response AS f ON f.thread_id = x.thread_id AND f.id >= x.max_id-1 ORDER BY thread_id);");
        $contents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = $db->query('SELECT id,title,last_update FROM thread ORDER BY last_update DESC');
        $titles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // 返り値配列に整形
        $result = array();
        $indexAry = array();
        foreach($titles AS $tt){
            $id = $tt['id'];
            $title = $tt['title'];
            $index = count($result);
            $result[$index]['id'] = $id;
            $result[$index]['title'] = $title;
            $result[$index]['res'] = array();
            $indexAry[$id] = $index;
        }
        foreach($contents AS $con){
            if(isset($indexAry[$con['thread_id']])){
                $index = $indexAry[$con['thread_id']];
                $result[$index]['res'][] = array('contents'=>$con['contents']);
            }
        }
        return $result;
    }
    /**
     * あるスレッドの中身をすべて取得
     **/
    public function getThreadContents($thread_id){
        $db = $this->db_connect();
        $sql = "SELECT title FROM thread WHERE id=:thread";
        $param = array(':thread'=>$thread_id);
        $stmt = $db->prepare($sql);
        $stmt->execute($param);
        $title = $stmt->fetch(PDO::FETCH_COLUMN,0);
        $sql = "SELECT id, contents, author, date FROM response WHERE thread_id=:thread";
        $stmt = $db->prepare($sql);
        $stmt->execute($param);
        $contents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // 返り値配列に整形
        $result = array('title'=>$title,'res'=>$contents);
        return $result;
    }
    /**
     * 新しいスレッドを追加
     **/
    public function setNewThread($title){
        $db = $this->db_connect();
        try{
            $date = date('Y-m-d H:i:s');
            $db->beginTransaction();
            $sql = "INSERT INTO thread(title, last_update) VALUES(:title, :date)";
            $param = array(':title'=>$title, ':date'=>$date);
            $stmt = $db->prepare($sql);
            $stmt->execute($param);
            $sql = "SELECT id FROM thread WHERE title=:title AND last_update=:date";
            $stmt = $db->prepare($sql);
            $stmt->execute($param);
            $id = $stmt->fetch(PDO::FETCH_COLUMN,0);
            $db->commit();
            return $id;
        }catch(Exception $e){
            $db->rollBack();
        }
    }
    /**
     * あるスレッドにレスを追加
     **/
    public function setResponse($thread_index, $contents, $author){
        $db = $this->db_connect();
        try{
            $date = date('Y-m-d H:i:s');
            $db->beginTransaction();
            $sql = "INSERT INTO response(id, thread_id, contents, author,date) SELECT COUNT(id), :thread, :res, :author,:date FROM response where thread_id = :thread";
            $param = array(':thread'=>$thread_index, ':res'=>$contents,':author'=>$author,':date'=>$date);
            $stmt = $db->prepare($sql);
            $stmt->execute($param);
            $db->query("UPDATE thread SET last_update='${date}' WHERE id=${thread_index}");
            $db->commit();
        }catch(Exception $e){
            $db->rollBack();
        }
    }
    /**
     * テーブル作成
     **/
    public function createTable(){
        $db = $this->db_connect();
        $db->query("DROP TABLE IF EXISTS thread,response");
        $db->query("CREATE TABLE thread(id INT AUTO_INCREMENT PRIMARY KEY,title VARCHAR(128), last_update DATETIME)");
        $db->query("CREATE TABLE response(no INT AUTO_INCREMENT PRIMARY KEY,thread_id INT NOT NULL, id INT NOT NULL, contents TEXT, author VARCHAR(56), date DATETIME DEFAULT CURRENT_TIMESTAMP)");
        }
    /**
     * DBとの接続
     **/
    private function db_connect(){
        try{
            $dsn = "mysql:host=localhost;dbname=board";
            $user = 'root';
            $pass = 'v3CcKmjM';
            $pdo = new PDO($dsn, $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        }catch(PDOException $e){
            var_dump($e->getMessage());
        }
    }
}
//$bdb = new BoardDB();
//print_r($bdb->getThreadContents(2));
//$bdb->getThreadList();
//$bdb->createTable();
//$id = $bdb->setNewThread('あの日に戻れるなら');
//$bdb->setResponse(5, '猫のダンス','Cat');
/*
$id = $bdb->setNewThread('あの日に戻れるなら');
$bdb->setResponse($id, '猫のダンス','Cat');
$bdb->setResponse($id, 'アヒル','Cat');
$bdb->setResponse($id, '人生をやり直したい','Cat');
$bdb->setResponse($id, 'もう一度大学受験したい','Cat');
$bdb->setResponse($id, '結婚やり直したいｗ','Cat');
$id = $bdb->setNewThread('お勧めの本');
$bdb->setResponse($id, '吾輩は','Cat');
$bdb->setResponse($id, '一寸法師','Cat');
$bdb->setResponse($id, 'ドラゴンボール','Cat');
$bdb->setResponse($id, 'それ漫画だろ','Cat');
$bdb->setResponse($id, 'ルパン三世','Cat');
*/