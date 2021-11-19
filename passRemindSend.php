<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「パスワード再発行ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証なし（ログインできないから）

//=====================
//画面処理
//=====================
//post送信されていた場合
if(!empty($_POST)){
    debug('POST送信があります。');
    debug('POST情報：'.print_r($_POST,true));

    $email = $_POST['email'];

    //未入力チェック
    validRequired($email, 'email');

    if(empty($err_msg)){
      debug('未入力チェックOk。');

      validEmail($email, 'email');
      validMaxLen($email, 'email');

      if(empty($err_msg)){
        debug('バリデーションOK。');

        try{
          $dbh = dbConnect();
          $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
          $data = array(':email' => $email);
          //クエリ実行
          $stmt = queryPost($dbh, $sql, $data);
          //クエリ結果の値を取得
          $result = $stmt->fetch(PDO::FETCH_ASSOC);
          debug('$result = '.print_r($result,true));

          //DBにEmailが登録されている場合
          if($stmt && array_shift($result)){
            debug('クエリ成功、DB登録あり。');
            $_SESSION['msg_success'] = SUC03;

            $auth_key = makeRandKey();//認証キー作成

            //メール送信
            $from = 'info@Scout.com';
            $to = $email;
            $subject = '【パスワード再発行認証】　｜　Scout運営チーム';
            $comment = <<<EOT
            本メールアドレス宛にパスワード再発行のご依頼がありました。
            下記のURLにて認証キーをご入力いただくとパスワードが再発行されます。

            パスワード再発行認証キー入力ページ：http://localhost:8888/Scout!/passRemindRecive.php
            認証キー：{$auth_key}
            ※認証キーの有効期限は30分となります。

            認証キーを再発行されたい場合は下記ページより再発行をお願い致します。
            http://localhost:8888/Scout!/passRemindSend.php

            /////////////////////////////
            Scout運営チーム
            URL  http://Scout.com/
            E-mail info@Scout.com
            /////////////////////////////
            EOT;

            sendMail($from, $to, $subject, $comment);

            //認証に必要な情報をセッションへ保存
            $_SESSION['auth_key'] = $auth_key;
            $_SESSION['auth_email'] = $email;
            $_SESSION['auth_key_limit'] = time() + (60*30); //現在時刻より30分後のUNIXタイムスタンプ
            debug('セッション変数の中身：'.print_r($_SESSION,true));
            debug('認証キー入力ページへ遷移します');

            header("Location:passRemindRecive.php");//認証キー入力ページへ
          }else{
            debug('クエリに失敗したかDBに登録のないEmailが入力されました。');
            $err_msg['common'] = MSG07;
          }
        }catch(Exception $e){
          error_log('エラー発生：' . $e->getMessage());
          $err_msg['common'] = MSG07;
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

        <section id="main-1culum">

            <h2 class="section-title">パスワード再発行</h2>
            <p>ご登録のEmailに、パスワード再発行メールを送信致します。</p>

            <form action="" method="post">
                <div class="area-msg">
                    <?php echo getErrMsg('common'); ?>
                </div>
                <div class="form-part">
                    <label for="email">
                        Email
                    </label>
                    <div class="area-msg">
                        <?php echo getErrMsg('email'); ?>
                    </div>
                    <input type="email" name="email" id="email">
                </div>
                <input type="submit" value="パスワード再発行" class="btn btn-gray">
            </form>
        </section>

        <?php
            require('footer.php');
        ?>