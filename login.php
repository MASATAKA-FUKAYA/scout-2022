<?php

require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「ログインページ');
debugLogStart();

//既にログインしているかチェック（ログイン認証）
require('auth.php');


//============================
//ログイン画面処理
//============================

//POSTされているかチェック
if(!empty($_POST)){
    debug('POST送信があります');

    $email = $_POST['email'];
    $pass = $_POST['pass'];
    $pass_save = (!empty($_POST['pass_save'])) ? true : false;

    //バリデーションチェック
    //未入力
    validRequired($email, 'email');
    validRequired($pass, 'pass');

    if(empty($err_msg)){
        //email最大文字数、形式
        validMaxLen($email, 'email');
        validEmail($email, 'email');

        //パスワード最大・最小文字数、形式
        validPass($pass, 'pass');


        if(empty($err_msg)){
            debug('バリデーションOKです');

            try{
                //DB接続しパスワード取得
                $dbh = dbConnect();
                $sql = 'SELECT password,id FROM users WHERE email = :email';
                //後でarray_shiftでパスワードを抜き出すのでpasswordが先
                $data = array(':email' => $email);

                $stmt = queryPost($dbh, $sql, $data);

                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                debug('クエリ結果の中身：'. print_r($result, true));

                //パスワード照合
                if(!empty($result) && password_verify($pass, array_shift($result))){
                    debug('パスワードがマッチしました');

                    //セッションに１「最終ログイン日時」、２「ログイン有効期限」、３「ユーザーID」を保存

                    //１、最終ログイン日時は現在時刻に
                    $_SESSION['login_date'] = time();

                    //２、ログイン有効期限（デフォルトは1時間）
                    $sesLimit = 60*60;

                    if(!empty($pass_save)){  //ログイン保持にチェックがあれば「ログイン有効期限」を30日に延ばす
                        debug('ログイン保持にチェックがあります');
                        $_SESSION['login_limit'] = $sesLimit * 24 * 30;
                    }else{
                        debug('ログイン保持にチェックはありません');
                        $_SESSION['login_limit'] = $sesLimit;
                    }

                    //３、ユーザーIDを保存
                    $_SESSION['user_id'] = $result['id'];
                    $_SESSION['msg_success'] = SUC04;

                    debug('セッション変数の中身：'. print_r($_SESSION, true));

                    //マイページへ
                    debug('マイページへ遷移します');
                    header("Location:mypage.php");

                }else{
                    debug('パスワードがマッチしません');
                    $err_msg['pass'] = MSG09;
                }
            }catch(Exception $e){
                error_log('エラー発生：' . $e->getMassage());
                $err_msg['common'] = MSG07;
            }
        }
    }
}
?>

<?php
$title = 'ログイン';
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
            <h2 class="section-title">ログイン</h2>
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
                <div class="form-part">
                    <label for="pass">
                        パスワード
                    </label>
                    <div class="area-msg">
                        <?php echo getErrMsg('pass'); ?>
                    </div>
                    <input type="password" name="pass" id="pass">

                </div>
                <div class="form-part">
                    <input type="checkbox" name="pass_save" id="pass_save">
                    <label class="label-radio" for="pass_save">ログイン状態を保持する</label>
                </div>
                <input type="submit" value="ログイン" class="btn btn-login">
            </form>
            <div class="btn btn-gray">
                <a href="passRemindsend.php">パスワードをお忘れの方はこちら</a>
            </div>
        </section>

        <?php
            require('footer.php');
        ?>