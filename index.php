<?php

/**
 * Nginx
 * client_max_body_size 500M;
 *
 * php.ini
 * post_max_size = 500M
 * upload_max_filesize = 500M
 * */

error_reporting(E_ERROR);
ini_set('memory_limit', '500M');
ini_set('max_execution_time', 0);

const DATA_DIR = '/tmp';
const DB_FILE = DATA_DIR . '/Easy.db';
const FILE_DIR = DATA_DIR . '/file';
define('GD_EXISTS', extension_loaded('gd'));

$Action = $_GET['action'] ? $_GET['action'] : 'index';

switch ($Action) {
    case 'download' :
        downLoad($_GET['id']);
        break;
    case 'upload' :
        if ($_FILES["uploadfile"]) {
            mvFile($_FILES["uploadfile"]);
        } else {
            insertStr();
        }
        break;
    case 'delete' :
        delete($_GET['id']);
        break;
    case 'qrcode' :
        qrCode(base64_decode($_GET['txt']));
        break;
    case 'init' :
        init();
        break;
    default :
        pageIndex();
        break;
}


function pageIndex() {
    $LogoUrl = qrUrl(selfUrl());
    $FileList = easyKvReadAll();
    $ListStr = '';
    $ListStr2 = '';
    foreach ($FileList as $k => $v) {
        if ($v['type'] == 'f') {
            $Url = "/index.php?action=download&id={$k}";
            $UrlQr = qrUrl(selfUrl() . $Url);
            $date = date('m-d H:i', $v['time']);
            $ShortName = mb_substr($v['name'], 0, 20, 'utf8');
            $fileSize = round($v['size'] / 1024 / 1024, 4) . "M";
            $ListStr .= "<tr><td>[$date]</td><td><a href='{$Url}' title='点击下载({$v['name']})'>{$ShortName}</a></td><td>&nbsp;&nbsp;&nbsp;&nbsp;{$fileSize}&nbsp;[&nbsp;<a href='{$UrlQr}' target='_blank' title='通过二维码下载({$v['name']})'>二维码</a>&nbsp;/&nbsp;<a href='/index.php?action=delete&id={$k}' title='删除({$v['name']})'>删除</a>&nbsp;]<td></tr>";
        } elseif ($v['type'] == 's') {
            $OutStr = htmlentities($v['str']);
            $ListStr2 .= $OutStr;
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
                <td style="border-left: 1px solid" class="spadding" rowspan="2">
                    <form enctype="multipart/form-data" method="POST" action="/index.php?action=upload">
                        <table>
                            <tr><td><textarea style='width:500px;height:300px;' name='str'>{$ListStr2}</textarea><td></tr>
                        </table>
                        <input type="submit" value="保存" style="margin-left:50px;margin-top:10px;"/>
                    </form>

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
 
            </tr>
        </table>
        <hr/>
    </body>
</html>
POUT;

}

function pageTips($Url = '/', $Time = 2, $Tips = '操作成功') {
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

function downLoad($id) {
    $info = easyKvGet($id);
    $DownLoadFile = FILE_DIR . '/' . $info['save_name'];
    if ($info && file_exists($DownLoadFile)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $info['name'] . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . $info['size']);
        readfile($DownLoadFile);
        exit;
    } else {
        pageTips('/', 2, '下载失败,文件不存在');
    }
}

function delete($id) {
    $Data = easyKvGet($id);
    easyKvDel($id);
    unlink(FILE_DIR . '/' . $Data['save_name']);
    pageTips('/', 2, $Data['name'] . '删除成功');
}

function init() {
    mkdir(DATA_DIR);
    mkdir(FILE_DIR);
    pageTips('/', 2, '初始化完成');
}

function qrUrl($Txt) {
    return selfUrl() . "/index.php?action=qrcode&txt=" . base64_encode($Txt);
}

function qrCode($Txt) {
    if (GD_EXISTS) {
        include('./phpqrcode.php');
        QRcode::png($Txt, false, QR_ECLEVEL_L, 5, 1);
    } else {
        header('Content-type: image/png');
        echo file_get_contents('./gdinstall.png');
    }
}

function mvFile($FileInfo) {
    if (is_uploaded_file($FileInfo['tmp_name'])) {
        $PathInfo = pathinfo($FileInfo['name']);
        $FileId = md5($FileInfo['name']);
        $FileName = $FileId . '.' . $PathInfo['extension'];
        if (move_uploaded_file($FileInfo['tmp_name'], FILE_DIR . '/' . $FileName)) {
            easyKvSet($FileId, array('name' => $FileInfo['name'], 'size' => $FileInfo['size'], 'save_name' => $FileName, 'time' => time(), 'type' => 'f'));
            pageTips('/', 2, '文件上传成功');
        } else {
            pageTips('/', 2, '文件上传失败');
        }
    } else {
        pageTips('/', 2, '文件上传失败');
    }

}

function insertStr() {
    $Str = $_POST['str'];
    if ($Str) {
        $Key = md5('TextArea');
        easyKvSet($Key, array('str' => $Str, 'time' => time(), 'type' => 's'));
        pageTips('/', 2, '提交成功');
    } else {
        pageTips('/', 2, '提交失败');
    }
}

function selfUrl() {
    return (strtolower(current(explode('/', $_SERVER['SERVER_PROTOCOL']))) . '://' . $_SERVER['HTTP_HOST']);
}


//暂不考虑并发情况

function easyKvSet($k, $v) {
    $Arr = easyKvOpen();
    $Arr[$k] = $v;
    return EasyKvWrite($Arr);
}

function easyKvReadAll() {
    return easyKvOpen();
}

function easyKvGet($k) {
    $Arr = easyKvOpen();
    return $Arr[$k] ? $Arr[$k] : false;
}

function easyKvDel($k) {
    $Arr = easyKvOpen();
    unset($Arr[$k]);
    return EasyKvWrite($Arr);
}

function easyKvOpen() {
    $Str = file_get_contents(DB_FILE);
    return unserialize($Str);
}

function EasyKvWrite($Arr) {
    $Str = serialize($Arr);
    return file_put_contents(DB_FILE, $Str);
}
