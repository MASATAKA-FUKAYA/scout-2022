<?php

//共通変数、関数ファイル読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「パスワード再発行認証キー入力ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証なし（ログインできない人が使うから）

//sessionに認証キーがあるか確認、なければリダイレクト
if(empty($_SESSION['auth_key'])){
    debug('認証キーがありません。メール送信ページへ遷移します');
    header("Location:passRemindSend.php");
    exit();
}

//====================
//画面処理
//====================
//post送信されていた場合
if(!empty($_POST)){
  debug('POST送信があります。');
  debug('POST情報：'.print_r($_POST,true));

  $auth_key = $_POST['token'];

  //未入力チェック
  validRequired($auth_key, 'token');

  if(empty($err_msg)){
    debug('未入力チェックOK。');

    //固定長チェック
    validLength($auth_key, 'token');
    //半角チェック
    validHalf($auth_key, 'token');

    if(empty($err_msg)){
      debug('バリデーションOK。');

      if($auth_key !== $_SESSION['auth_key']){
        $err_msg['common'] = MSG16;
      }
      if(time() > $_SESSION['auth_key_limit']){
        $err_msg['common'] = MSG17;
      }

      if(empty($err_msg)){
        debug('認証OK。');

        $pass = makeRandKey();//パスワード生成

        try{
          $dbh = dbConnect();
          $sql = 'UPDATE users SET password = :pass WHERE email = :email AND delete_flg = 0';
          $data = array(':pass' => password_hash($pass, PASSWORD_DEFAULT), ':email' => $_SESSION['auth_email']);
          //クエリ実行
          $stmt = queryPost($dbh, $sql, $data);

          //クエリ成功の場合
          if($stmt){
            debug('クエリ成功');

            //メール送信
            $from = 'info@Scout.com';
            $to = $_SESSION['auth_email'];
            $subject = '【パスワード再発行認証】　｜　Scout運営チーム';
            $comment = <<<EOT
            本メールアドレス宛にパスワードの再発行を致しました。
            下記のURLにて再発行パスワードをご入力いただき、ログインください。

            ログインページ：http://localhost:8888/Scout!/login.php
            再発行パスワード：{$pass}
            ※ログイン後、パスワードのご変更をお願い致します。

            /////////////////////////////
            Scout運営チーム
            URL  http://Scout.com/
            E-mail info@Scout.com
            /////////////////////////////
            EOT;

            sendMail($from, $to, $subject, $comment);

            //セッション削除
            session_unset();//destroyを使うとセッションIDがなくなってしまうので、以下のメッセージが表示できない
            $_SESSION['msg_success'] = SUC03;
            debug('セッション変数の中身：'. print_r($_SESSION,true));

            header("Location:login.php");
          }else{
            debug('クエリに失敗しました。');
            $err_msg['common'] = MSG07;
          }
        }catch(Exception $e){
          error_log('エラー発生：' . $e->getMessage());
          $err_msg['common'] = MSG07;
        }
      }
    }
  }
}
?>
<?php
$title = 'パスワード再発行';
require('head.php');
?>

    <body>

        <?php
            require('header.php');
        ?>

        <p id="js-show-msg" style="display:none;" class="msg-slide">
            <?php echo getSessionFlash('msg_success'); ?>
        </p>

        <section id="main-1culum">

            <h2 class="section-title">パスワード更新（認証キー確認）</h2>
            <p>受信されたメールに記載された、認証キーをご入力ください。</p>

            <form action="" method="post">
                <div class="area-msg">
                    <?php echo getErrMsg('common'); ?>
                </div>
                <div class="form-part">
                    <label for="token">
                        更新用パスワード
                    </label>
                    <div class="area-msg">
                        <?php echo getErrMsg('token'); ?>
                    </div>
                    <input type="password" name="token" id="token">
                </div>
                <input type="submit" value="ログイン" class="btn btn-login">
            </form>
            <div class="btn btn-gray">
                <a href="passRemindsend.php">メールを再送信する</a>
            </div>
        </section>

        <?php
            require('footer.php');
        ?>