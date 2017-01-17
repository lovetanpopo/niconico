<?php
/**
  (参考文献)
  - ニコニコ動画のAPIまとめ
    http://efcl.info/niconicoapi/

  - ニコニコAPIを叩いてゲットした情報をjsonで返却するスクリプト
    http://absg.hatenablog.com/entry/2014/07/12/183032

  - PHP cURLの色々な使い方
    http://qiita.com/wanwanland/items/a5f9574fadd214d7b5c8

  - HTTP POST/GET クッキー認証によるWebサイトへのログイン
    http://c-loft.com/blog/?p=1196

 */

$user = "**********";
$pass = "**********";
$url  = "https://secure.nicovideo.jp/secure/login?site=niconico";

$tmp_path =  tempnam(sys_get_temp_dir(), "CKI");

// POST
$params = array(
          'next_url' => ''
        , 'mail'     => $user
        , 'password' => $pass
    );

// ニコニコ動画にログインする
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_POST, TRUE);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
curl_setopt($ch, CURLOPT_COOKIEFILE,$tmp_path);
curl_setopt($ch, CURLOPT_COOKIEJAR, $tmp_path);
$html = curl_exec($ch);
curl_close($ch);


// マイリストへアクセス
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://www.nicovideo.jp/my/mylist');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_COOKIEFILE,$tmp_path);
curl_setopt($ch, CURLOPT_COOKIEJAR, $tmp_path);
$html = curl_exec($ch);
curl_close($ch);


// ページ内に埋め込まれているトークンの取得
$token = '';
$lines = explode("\n", $html);
foreach ($lines as $line) {
  if (strpos($line, 'NicoAPI.token') !== false) {
    // ex) NicoAPI.token = "117071-1484660508-a99529838ea59571b44f7f9ad42071fd4436de67";
    list(, $token,) = explode('"', trim($line));
  }
}


// 新規マイリストの作成
$params = array(
          'name'   => 'apiCreate'
        , 'public' => 1
        , 'token'  => $token
    );
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://www.nicovideo.jp/api/mylistgroup/add');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_POST, TRUE);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
curl_setopt($ch, CURLOPT_COOKIEFILE,$tmp_path);
curl_setopt($ch, CURLOPT_COOKIEJAR, $tmp_path);
$json = curl_exec($ch);
curl_close($ch);


// マイリストのIDを取得
$tmp = json_decode($json, true); // {"id":58052920,"status":"ok"}
$mylist_id = $tmp['id'];


// 動画の登録
$params = array(
          'group_id'  => $mylist_id
        , 'item_id'   => 'sm30085125'
        , 'item_type' => 0
        , 'token'  => $token
    );
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://www.nicovideo.jp/api/mylist/add');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_POST, TRUE);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
curl_setopt($ch, CURLOPT_COOKIEFILE,$tmp_path);
curl_setopt($ch, CURLOPT_COOKIEJAR, $tmp_path);
$json = curl_exec($ch);
curl_close($ch);

var_dump($json);

/**

  公開マイリストのURL
  http://www.nicovideo.jp/mylist/58053024
    - 末尾が GoupId
 */
