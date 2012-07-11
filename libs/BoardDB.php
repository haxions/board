<?php
/**
 * DBとのやり取りを行う
 **/
class BoardDB{
    private $pdo;
    /**
     * すべてのスレッドのList情報を取得
     **/
    function __construct(){
        date_default_timezone_set('Asia/Tokyo');
        $this->pdo = $this->db_connect();
    }
    public function getThreadList($db){
        if( empty($db) ) return false;
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
    public function getThreadContents($db, $thread_index){
        if( empty($db) ) return false;
        $sql = "SELECT title FROM thread WHERE id=:thread";
        $param = array(':thread'=>$thread_index);
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
    public function setNewThread($db, $title){
        if( empty($db) ) return false;
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
            return false;
        }
    }
    /**
     * あるスレッドにレスを追加
     **/
    public function setResponse($db, $params){
        if( empty($db) ) return false;
        if(count($params) != 3) return false;
        $thread_index = $params[0];
        $contents = $params[1];
        $author = $params[2];
        try{
            $date = date('Y-m-d H:i:s');
            $db->beginTransaction();
            $sql = "INSERT INTO response(id, thread_id, contents, author,date) SELECT COUNT(id), :thread, :res, :author,:date FROM response where thread_id = :thread";
            $param = array(':thread'=>$thread_index, ':res'=>$contents,':author'=>$author,':date'=>$date);
            $stmt = $db->prepare($sql);
            $stmt->execute($param);
            $db->query("UPDATE thread SET last_update='${date}' WHERE id=${thread_index}");
            $db->commit();
            return true;
        }catch(Exception $e){
            $db->rollBack();
            return false;
        }
    }
    /**
     * テーブル作成
     **/
    public function createTable($db){
        if( empty($db) ) return false;
        $db->query("DROP TABLE IF EXISTS thread,response");
        $db->query("CREATE TABLE thread(id INT AUTO_INCREMENT PRIMARY KEY,title VARCHAR(128), last_update DATETIME)");
        $db->query("CREATE TABLE response(no INT AUTO_INCREMENT PRIMARY KEY,thread_id INT NOT NULL, id INT NOT NULL, contents TEXT, author VARCHAR(56), date DATETIME DEFAULT CURRENT_TIMESTAMP)");
        return true;
    }
    /**
     * DBとの接続
     **/
    private function db_connect(){
        $file = dirname(__FILE__).'/dbpswd';
        try{
            if(!file_exists($file)) return null;
            $ary = explode("\t", file_get_contents($file));
            $pdo = new PDO($ary[0], $ary[1], $ary[2]);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        }catch(PDOException $e){
            var_dump($e->getMessage());
        }
    }
    /**
     * PDOを外部から呼び出す
     **/
    public function getPDO(){
        return $this->pdo;
    }
}