<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「パスワード編集ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//=====================
//画面処理
//=====================

//DBからユーザー情報を取得
$dbFormData = getUser($_SESSION['user_id']);
debug('取得したユーザーDBデータ：'. print_r($dbFormData, true));

//POSTされているかチェック
if(!empty($_POST)){
    debug('POST送信があります');

    $pass_old = $_POST['pass_old'];
    $pass_new = $_POST['pass_new'];
    $pass_new_re = $_POST['pass_new_re'];

    //バリデーションチェック
    //未入力
    validRequired($pass_old, 'pass_old');
    validRequired($pass_new, 'pass_new');
    validRequired($pass_new_re, 'pass_new_re');

    if(empty($err_msg)){
        debug('未入力チェックOK');

        //古いパスワードがDBのパスワードと同じかチェック
        if(!password_verify($pass_old, $dbFormData['password'])){
            $err_msg['pass_old'] = MSG13;
          }

        //古いパスワードと新しいパスワードが違うものかチェック
        if($pass_old === $pass_new){
            $err_msg['pass_new'] = MSG14;
        }

        //半角英数字、最大、最小文字数チェック
        validPass($pass_new, 'pass_new');

        //新しいパスワードと再入力が合っているかチェック
        validMatch($pass_new, $pass_new_re, 'pass_new_re');

        if(empty($err_msg)){
            debug('バリデーションチェックOK');

            //DB接続
            try{
                $dbh = dbConnect();
                //SQL文作成
                $sql = 'UPDATE users SET password = :pass WHERE id = :id';
                $data = array(':pass' => password_hash($pass_new, PASSWORD_DEFAULT), ':id' => $_SESSION['user_id']);
                //クエリ実行
                $stmt = queryPost($dbh, $sql, $data);

                //クエリ成功の場合
                if($stmt){
                    $_SESSION['msg_success'] = SUC01;

                    //メール送信
                    $username = ($dbFormData['u_name']) ? $dbFormData['u_name'] : 'Scoutユーザー';
                    $from = 'info@Scout.com';
                    $to = $dbFormData['email'];
                    $subject = 'パスワード変更通知　｜　Scout運営チーム';
                    //EOTはEndOfTextの略。別に何でもいい。ただし先頭の<<<の後の文字列と合わせる。最後のEOTの前後に空白など何も入れてはいけない。
                    //EOT内は半角空白なども全てそのまま半角空白として扱われるので、インデントなどはしないこと
                    $comment = <<<EOT
                    {$username}さん
                    パスワードが変更されました。

                    /////////////////////////////
                    Scout運営チーム
                    URL  http://Scout.com/
                    E-mail info@Scout.com
                    /////////////////////////////
                    EOT;
                    sendMail($from, $to, $subject, $comment);

                    //マイページへ遷移
                    header("Location:mypage.php");
                }

            }catch(Exception $e){
                error_log('エラー発生：'.$e->getMessage());
                $err_msg['common'] = MSG07;
            }
        }
    }
}




?>
<?php
$title = 'パスワード編集';
require('head.php');
?>

    <body>

        <?php
            require('header.php');
        ?>

        <div id="main-2culum" class="site-width">
            <div id="main-left">
                <section class="main-left-white">

                    <h2 class="section-title">パスワード変更</h2>

                    <form action="" method="post">
                        <div class="area-msg">
                            <?php echo getErrMsg('common'); ?>
                        </div>
                        <div class="form-part">
                            <label for="pass_old">
                                古いパスワード
                            </label>
                            <div class="area-msg">
                                <?php echo getErrMsg('pass_old'); ?>
                            </div>
                            <input type="password" name="pass_old" id="pass_old">
                        </div>
                        <div class="form-part">
                            <label for="pass_new">
                                新しいパスワード
                            </label>
                            <div class="area-msg">
                                <?php echo getErrMsg('pass_new'); ?>
                            </div>
                            <input type="password" name="pass_new" id="pass_new">
                        </div>
                        <div class="form-part">
                            <label for="pass_new_re">
                                新しいパスワード（再入力）
                            </label>
                            <div class="area-msg">
                                <?php echo getErrMsg('pass_new_re'); ?>
                            </div>
                            <input type="password" name="pass_new_re" id="pass_new_re">
                        </div>

                        <input type="submit" value="パスワード変更" class="btn btn-gray">
                    </form>
                </section>
            </div>

            <?php
                require('sidebar.php');
            ?>

        </div>

        <?php
            require('footer.php');
        ?>