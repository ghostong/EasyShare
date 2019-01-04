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
define ( 'GD_EXISTS', extension_loaded('gd') );

$Action = $_GET['action'] ? $_GET['action'] : 'index';

switch ( $Action ) {
    case 'download' : 
        DownLoad ($_GET['id']) ;
        break;
    case 'upload' :
        if ( $_FILES["uploadfile"] ) {
            MvFile ($_FILES["uploadfile"]);
        }else{
            InsertStr();
        }
        break;
    case 'delete' :
        Delete($_GET['id']);
        break;
    case 'qrcode' :
        QRcode(base64_decode($_GET['txt']));
        break;
    case 'init' :
        Init ();
        break;
    default :
        PageIndex ();
        break;
}



function PageIndex () {
    $LogoUrl =  QRUrl(SelfUrl());
    $FileList = EasyKvReadAll();
    $ListStr = '';
    $ListStr2 = '';
    foreach ( $FileList as $k => $v ) {
        if($v['type'] == 'f') {
            $Url = "/index.php?action=download&id={$k}";
            $UrlQr = QRUrl(SelfUrl().$Url);
            $date = date('m-d H:i', $v['time']);
            $ShortName = mb_substr($v['Name'],0,20,'utf8');
            $ListStr .= "<tr><td>[$date]</td><td><a href='{$Url}' title='点击下载({$v['Name']})'>{$ShortName}</a></td><td>&nbsp;&nbsp;&nbsp;&nbsp;[&nbsp;<a href='{$UrlQr}' target='_blank' title='通过二维码下载({$v['Name']})'>二维码</a>&nbsp;/&nbsp;<a href='/index.php?action=delete&id={$k}' title='删除({$v['Name']})'>删除</a>&nbsp;]<td></tr>";
        }elseif($v['type'] == 's'){
            $OutStr = htmlentities ( $v['str'] );
            #$OutStr = str_replace (array("\r\n","\n\r","\n","\r"),"<br/>",$OutStr);
            #$OutStr = str_replace (array(" "),"&nbsp;",$OutStr);
            $ListStr2 .= "<tr><td><textarea style='width:500px;height:300px;'>{$OutStr}</textarea><td></tr>";
        }
    }
    if (!$ListStr && !is_dir(FILE_DIR)) {
        $ListStr = '<tr><td style="padding:30px;"><a href="/index.php?action=init" style="color:#ff0000;">初始化存储目录</a></td></tr>';
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
                padding:5px 20px 0px 20px;
            }
            .floatleft {
                float:left;
            }
        </style>
    </head>
    <body>
        <table class="spadding">
            <tr>
                <td>
                    <img src="{$LogoUrl}" style="height:80px;"/>
                </td>
                <td style="font-size:60px;">
                    EasyShare
                </td>
            </tr>
        </table>
        <hr/>
        <table class="spadding" style="width:100%">
            <tr valign="top">
                <td style="border-right: 1px solid; width: 50%">
                    <table>
                        $ListStr
                    </table>
                </td>
                <td style="border-left: 1px solid" class="spadding">
                    <table>
                        $ListStr2
                    </table>
                </td>
            </tr>
            <tr>
                <td style="border-right: 1px solid">
                    <div class="spadding">
                        <form enctype="multipart/form-data" method="POST" action="/index.php?action=upload">
                            <input type="file" name="uploadfile" />
                            <input type="submit" value="上传" />
                        </form>
                    </div>
                </td>
                <td style="border-left: 1px solid" class="spadding">
                    <div class="spadding">
                        <form enctype="multipart/form-data" method="POST" action="/index.php?action=upload">
                            <textarea name="str" style="float:left;height:50px;width:300px;"></textarea> <input type="submit" value="提交" style="margin-left:50px;margin-top:10px;"/>
                        </form>
                    </div>
                </td>
            </tr>
        </table>
        <hr/>
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
            a:link , a:visited , a:hover , a:active {color: #B0BEC5}
            a{text-decoration:none}
        </style>
    </head>
    <body>
        <div class="container">
            <a href="/">{$Tips}</a>
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
    EasyKvDel($id);
    unlink(FILE_DIR.'/'.$Data['SaveName']);
    PageTips('/',2,$Data['Name'].'删除成功');   
}

function Init () {
    mkdir ( DATA_DIR );
    mkdir ( FILE_DIR );
    PageTips('/',2,'初始化完成');   
}

function QRUrl ($Txt) {
    return SelfUrl()."/index.php?action=qrcode&txt=".base64_encode($Txt);
}

function QRcode($Txt) {
    if (GD_EXISTS) {
        include('./phpqrcode.php');
        QRcode::png ($Txt,false,QR_ECLEVEL_L,5,1);
    }else{
        header('Content-type: image/png');
        echo file_get_contents('./gdinstall.png');
    }
}

function MvFile ( $FileInfo ) {
    if ( is_uploaded_file ( $FileInfo['tmp_name'] ) ) {
        $PathInfo = pathinfo($FileInfo['name']);
        $FileId = md5($FileInfo['name']);
        $FileName = $FileId.'.'.$PathInfo['extension'];
        if ( move_uploaded_file ( $FileInfo['tmp_name'] ,FILE_DIR.'/'.$FileName) ) {
            EasyKvSet($FileId,array('Name'=>$FileInfo['name'],'Size'=>$FileInfo['size'],'SaveName'=>$FileName,'time'=>time(),'type'=>'f'));
            PageTips('/',2,'文件上传成功');
        } else {
            PageTips('/',2,'文件上传失败');
        }
    } else {
        PageTips('/',2,'文件上传失败');
    }

}

function InsertStr () {
    $Str = $_POST['str'] ;
    if ( $Str ) {
        $Key = md5('TextArea');
        EasyKvSet($Key,array('str'=>$Str,'time'=>time(),'type'=>'s'));
        PageTips('/',2,'提交成功');
    }else{
        PageTips('/',2,'提交失败');
    }
}

function SelfUrl () {
    return ( strtolower( current( explode('/',$_SERVER['SERVER_PROTOCOL']) ) ).'://'.$_SERVER['HTTP_HOST'] );
}



//暂不考虑并发情况

function EasyKvSet ( $k, $v ) {
    $Arr=EasyKvOpen();
    $Arr[$k] = $v;
    return EasyKvWrite( $Arr );
}

function EasyKvReadAll () {
    $Arr=EasyKvOpen();
    return $Arr;
}

function EasyKvGet ( $k ) {
    $Arr=EasyKvOpen();
    return $Arr[$k] ? $Arr[$k] : false;
}

function EasyKvDel ( $k ) {
    $Arr=EasyKvOpen();
    unset($Arr[$k]);
    return EasyKvWrite( $Arr );
}

function EasyKvOpen () {
    $Str = file_get_contents( DB_FILE );
    $Arr = unserialize($Str);
    return $Arr;
}

function EasyKvWrite ( $Arr ) {
    $Str = serialize($Arr);
    return file_put_contents ( DB_FILE , $Str );
}
