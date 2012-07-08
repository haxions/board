<?php
define('HOME','/home/rackhuber/');
define('LIBS',HOME.'libs/');
define('MODELS',HOME.'models/');
require LIBS.'Slim/Slim.php';
require LIBS.'Slim/SmartyView.php';
require LIBS.'MySmarty.class.php';
require LIBS.'BoardDB.php';
$app = new Slim(array(
                      'debug' => false,
                      'templates.path' => '../templates',
                      'view' => new SmartyView()
                      ));
    
// HOME (cushion)
$app->get('/', function() use ($app){
        $url = $app->request()->get('url');
        if( !empty($url) ){
            if(strpos($url, 'http://'.$_SERVER['SERVER_NAME']) === 0){
                header("Location: ".htmlspecialchars($url));
                exit;
            }
            return $app->render( 'cushion.html', array('url'=>$url) );
        }
        $board = new Board();
        $list = $board->accessDB('getlist', null);
        return $app->render( 'thread_list.html', array('data_ary'=>$list) );
    });

// SHOW ADD-FORM 
$app->get('/new', function() use ($app){
        return $app->render('thread_new.html');
    });

// ADD THREAD
$app->post('/', function() use ($app){
        $board = new Board();
        $request = $app->request();
        $title = $request->post('title');
        $author = $request->post('author');
        $contents = $request->post('contents');
        $path = substr( $_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'],'/') );
        if( !empty($title) && !empty($author) && !empty($contents) ){
            $id = $board->accessDB('setthread',$title);
            if($id !== false){
                $is_set = $board->accessDB( 'setcontents', array($id, $contents, $author) );
                header("Location: http://{$_SERVER['HTTP_HOST']}{$path}/thread/{$id}");
                exit;
            }else{
                header("Location: http://{$_SERVER['HTTP_HOST']}{$path}");
                exit;
            }
        }
        return $app->render('thread_new.html',
                            array('title'=>$title, 'author'=>$author, 'contents'=>$contents));
    });

// READ THREAD
$app->get('/thread/:id', function($id) use ($app){
        $board = new Board();
        $list = $board->accessDB('getcontents',$id);
        return $app->render( 'thread_contents.html', array( 'thread_index'=>$id, 'data_ary'=>$list) );
    })->conditions(array('id'=>'[0-9]+'));

// ADD COMMENT
$app->post('/thread/:id', function($id) use ($app){
        $board = new Board();
        $request = $app->request();
        $author = $request->post('author');
        $contents = $request->post('contents');
        $is_set = $board->accessDB( 'setcontents', array($id, $contents, $author) );
        if($is_set){
            $path = substr( $_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'],'/') );
            header("Location: http://{$_SERVER['HTTP_HOST']}{$path}/thread/{$id}");
            exit;            
        }else{
            $list = $board->accessDB('getlist', null);
            return $app->render( 'thread_list.html', array( 'data_ary'=>$list) );
        }
    })->conditions(array('id'=>'[0-9]+'));

// RUN
$app->run();

class Board{
    /**
     * BoardDBへのアクセス
     **/
    public function accessDB($act, $param){
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