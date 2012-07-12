<?php
require_once 'PHPUnit/Extensions/Database/TestCase.php';
require_once dirname(__FILE__).'/../libs/BoardDB.php';
                              
class BoardDBTest2 extends PHPUnit_Extensions_Database_TestCase{
    private $pdo = null;
    protected function getConnection(){
        $file = dirname(__FILE__).'/../libs/dbpswd';
        if(!file_exists($file)) return null;
        $ary = explode("\t", file_get_contents($file));       
        $this->pdo = new PDO('mysql:host=localhost;dbname=test', $ary[1], $ary[2]);
        return $this->createDefaultDBConnection($this->pdo, 'test');
    }

    protected function getDataSet(){
        return $this->createFlatXMLDataSet(dirname(__FILE__).'/board-seed.xml');
    }
    /**
     * スレッド一覧の取得
     **/
    public function testGetThreadList(){
        $bdb = new BoardDB();
        // 最新のものが先に来るようになっている(掲示板表示の仕様)
        $result = array(
                        array(
                              'id'=>'3',
                              'title'=>'PHPUnitTest',
                              'res'=>array()
                              ),
                        array(
                              'id'=>'2',
                              'title'=>'テスト板',
                              'res'=>array(array('contents'=>'レス1'))
                              ),
                        array(
                              'id'=>'1',
                              'title'=>'人間っていいなぁ',
                              'res'=>array(
                                           array('contents'=>'レス1'),
                                           array('contents'=>'レス3'),
                                           array('contents'=>'レス4'))
                              )
                        
                        );
        $this->assertEquals($result, $bdb->getThreadList($this->pdo));
    }
    /**
     * スレッド内容取得
     **/
    public function testGetThreadContents(){
        $bdb = new BoardDB();
        $thread_index = 2;
        $results = array(
                         'title'=>'テスト板', 
                         'res'=>array(
                                      array(
                                            'id'=>'0',
                                            'contents'=>'レス1',
                                            'author'=>'名無し',
                                            'date'=>'2012-01-01 02:00:00')));
        $this->assertEquals($results, $bdb->getThreadContents($this->pdo, $thread_index) );
    }
    /**
     * スレッドを作成1
     **/
    public function testSetNewThread(){
        $title = 'newThread';
        $bdb = new BoardDB();
        // check return
        $this->assertEquals(4, $bdb->setNewThread($this->pdo, $title) );

        // check insert2DB
        $queryTable = $this->getConnection()->createQueryTable('newthread', 'SELECT id,title FROM thread');
        $expectedTable = $this->createFlatXmlDataSet('newThread-seed.xml')->getTable('newthread');
        $this->assertTablesEqual($expectedTable, $queryTable);
    }
    /**
     * レスポンス追加
     **/
    public function testSetResponse(){
        $bdb = new BoardDB();
        $params = array(4, '新しいスレ立ててみた', '名無し');
        // try  
        $bdb->setResponse($this->pdo, $params);
        // check insert2DB
        $queryTable = $this->getConnection()->createQueryTable('newresponse', 'SELECT no,thread_id,id,contents,author FROM response');
        $expectedTable = $this->createFlatXmlDataSet('newThread-seed.xml')->getTable('newresponse');
        $this->assertTablesEqual($expectedTable, $queryTable);        
    }
}
?>