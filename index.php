<?php

/**
 * Nginx
 * client_max_body_size 500M;
 *
 * php.ini
 * post_max_size = 500M
 * upload_max_filesize = 500M
 * */

error_reporting( E_ERROR );
ini_set ('memory_limit','500M');
ini_set ('max_execution_time',0);

define ( 'DATA_DIR', '/tmp' );
define ( 'DB_FILE', DATA_DIR.'/Easy.db' );
define ( 'FILE_DIR', DATA_DIR.'/file' );

$Action = $_GET['action'] ? $_GET['action'] : 'index';

switch ( $Action ) {
    case 'download' : 
        DownLoad ($_GET['id']) ;
        break;
    case 'upload' :
        MvFile ($_FILES["uploadfile"]);
        break;
    case 'delete' :
        Delete($_GET['id']);
        break;
    case 'init' :
        Init ();
        break;
    default :
        PageIndex ();
        break;
}



function PageIndex () {
    $FileList = EasyKvReadAll();
    $ListStr = '';
    foreach ( $FileList as $k => $v ) {
        $date = date('Y-m-d H:i:s', $v['time']);
        $ListStr .= "<li>[$date] {$v['Name']} &nbsp;&nbsp;[&nbsp;<a href='/index.php?action=download&id={$k}'>下载</a>&nbsp;/&nbsp;<a href='/index.php?action=delete&id={$k}'>删除</a>&nbsp;]</li>";
    }
    if (!$ListStr && !is_dir(FILE_DIR)) {
        $ListStr = '<li><a href="/index.php?action=init">初始化存储目录</a></li>';
    }
    echo <<<POUT
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="blank">
        <meta name="format-detection" content="telephone=no">
        <style>
            body {
                margin: 0;
                padding: 0;
                width: 100%;
                height: 100%;
                color: #646464;
                display: table;
                font-weight: 100;
                font-family: 'Lato';
            }
            a:link , a:visited , a:hover , a:active {color: #111111}
            a{text-decoration:none}
            .spadding {
                padding:20px;
            }
        </style>
    </head>
    <body>
        <div class="spadding">
            <ul>
                $ListStr
            </ul>
        </div>
        <hr/>
        <div class="spadding">
            <form enctype="multipart/form-data" method="POST" action="/index.php?action=upload">
                <input type="file" name="uploadfile" />
                <input type="submit" value="上传" />
            </form>
        </div>
    </body>
</html>
POUT;

}

function PageTips ( $Url='/', $Time=2, $Tips = '操作成功' ) {
    echo <<<POUT
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="blank">
        <meta name="format-detection" content="telephone=no">
        <meta http-equiv="Refresh" content="{$Time}; url={$Url}" />
        <style>
            body {
                margin: 0;
                padding: 0;
                width: 100%;
                height: 100%;
                color: #B0BEC5;
                display: table;
                font-weight: 100;
                font-family: 'Lato';
            }
            .container {
                text-align: center;
                display: table-cell;
                vertical-align: middle;
                font-size: 54px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            {$Tips}
        </div>
    </body>
</html>
POUT;

}

function DownLoad ($id) {
    $info = EasyKvGet ($id) ;
    $DownLoadFile = FILE_DIR.'/'.$info['SaveName'];
    if ( $info && file_exists( $DownLoadFile ) ) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.$info['Name'].'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . $info['Size'] );
        readfile($DownLoadFile);
        exit;
    }else{
        PageTips('/',2,'下载失败,文件不存在');   
    }
}

function Delete ( $id ) {
    $Data = EasyKvGet($id) ;
    EasyKvDB($id,'','del');
    unlink(FILE_DIR.'/'.$Data['SaveName']);
    PageTips('/',2,$Data['Name'].'删除成功');   
}

function Init () {
    mkdir ( DATA_DIR );
    mkdir ( FILE_DIR );
    PageTips('/',2,'初始化完成');   
}

function MvFile ( $FileInfo ) {
    if ( is_uploaded_file ( $FileInfo['tmp_name'] ) ) {
        $PathInfo = pathinfo($FileInfo['name']);
        $FileId = md5($FileInfo['name']);
        $FileName = $FileId.'.'.$PathInfo['extension'];
        if ( move_uploaded_file ( $FileInfo['tmp_name'] ,FILE_DIR.'/'.$FileName) ) {
            EasyKvDB($FileId,array('Name'=>$FileInfo['name'],'Size'=>$FileInfo['size'],'SaveName'=>$FileName,'time'=>time()));
            PageTips('/',2,'文件上传成功');
        } else {
            PageTips('/',2,'文件上传失败');
        }
    } else {
        PageTips('/',2,'文件上传失败');
    }

}

function EasyKvDB ( $k, $v , $Action = '' ) {
    $Str = file_get_contents( DB_FILE );
    $Arr = unserialize($Str);
    if ($Action == 'del') {
        unset($Arr[$k]);
    }else{
        $Arr[$k] = $v;
    }
    $Str = serialize($Arr);
    return file_put_contents ( DB_FILE , $Str );
}

function EasyKvReadAll () {
    $Str = file_get_contents( DB_FILE );
    $Arr = unserialize($Str);
    return $Arr;
}

function EasyKvGet ( $k ) {
    $Str = file_get_contents( DB_FILE );
    $Arr = unserialize($Str);
    return $Arr[$k] ? $Arr[$k] : false;
}
