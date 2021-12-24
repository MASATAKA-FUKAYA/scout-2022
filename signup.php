<?php

require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「ユーザー登録ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

if(!empty($_POST)){

    //ユーザー情報を代入
    $name = $_POST['name'];
    $email = $_POST['email'];
    $pass = $_POST['pass'];
    $pass_re = $_POST['pass_re'];

    //バリデーションチェック-------------------
        //未入力
        validRequired($name, 'name');
        validRequired($email, 'email');
        validRequired($pass, 'pass');
        validRequired($pass_re, 'pass_re');

        if(empty($err_msg)){
            //ユーザーネーム・最大文字数
            validMaxLen($name, 'name');

            //Email・最大文字数、形式
            validMaxLen($email, 'email');
            validEmail($email, 'email');

            //パスワード・最小、最大文字数、半角英数字、同値
            validPass($pass, 'pass');
            validMatch($pass, $pass_re, 'pass_re');

            if(empty($err_msg)){
                //Email重複（DB接続）
                validEmailDup($email);

                if(empty($err_msg)){
                    //DB接続
                    try{
                        $dbh = dbConnect();

                        $sql = 'INSERT INTO users (u_name, email, password, login_time, create_date) VALUES(:u_name, :email, :password, :login_time, :create_date)';

                        $data = array(':u_name' => $name, ':email' => $email, ':password' => password_hash($pass, PASSWORD_DEFAULT),
                                ':login_time' => date('Y-m-d H:i:s'), ':create_date' => date('Y-m-d H:i:s'));

                        //クエリ実行
                        $stmt = queryPost($dbh, $sql, $data);

                        //クエリ成功の場合
                        if($stmt){
                            //ログイン有効期限（デフォルトを1時間に）
                            $sesLimit = 60 * 60;
                            //最終ログイン日時を現在日時に
                            $_SESSION['login.date'] = time();
                            $_SESSION['login_limit'] = $sesLimit;
                            //ユーザーIDを格納
                            $_SESSION['user_id'] = $dbh->lastInsertId();

                            debug('セッション変数の中身：'.print_r($_SESSION,true));
                            header("location:mypage.php");
                            exit();//マイページへ
                        }

                    }catch(Exception $e){
                        error_log('エラー発生：' . $e->getMassage());
                        $err_msg['common'] = MSG07;
                    }
                }
            }
        }
}
?>

<?php
$title = 'ユーザー登録';
require('head.php');
?>

    <body>

        <?php
            require('header.php');
        ?>

        <section id="main-1culum">
            <h2 class="section-title">ユーザー登録</h2>
            <form action="signup.php" method="post">

                <div class="area-msg">
                    <?php echo getErrMsg('common'); ?>
                </div>

                <div class="form-part">
                    <label for="name">
                        ユーザーネーム
                    </label>
                    <div class="area-msg">
                        <?php echo getErrMsg('name'); ?>
                    </div>
                    <input type="text" name="name" id="name">
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
                        パスワード<span class="form-info">※半角英数字8文字以上</span>
                    </label>
                    <div class="area-msg">
                        <?php echo getErrMsg('pass'); ?>
                    </div>
                    <input type="password" name="pass" id="pass">
                </div>

                <div class="form-part">
                    <label for="pass_re">
                        パスワード（再入力）
                    </label>
                    <div class="area-msg">
                        <?php echo getErrMsg('pass_re'); ?>
                    </div>
                    <input type="password" name="pass_re" id="pass_re">
                </div>

                <input type="submit" value="登録する" class="btn btn-signup">
            </form>

        </section>

        <?php
            require('footer.php');
        ?>