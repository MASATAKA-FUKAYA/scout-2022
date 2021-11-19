<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「退会ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//=====================
//画面処理
//=====================

if(!empty($_POST)){
    debug('POST送信があります');

    try{
        $dbh = dbConnect();
        $sql1 = 'UPDATE users SET delete_flg = 1 WHERE id = :id';
        $sql2 = 'UPDATE msg_board SET delete_flg = 1 WHERE host_user_id = :id OR guest_user_id = :id';
        //teamテーブルなどのhost_user_idはとりあえず消さない
        $data = array(':id' => $_SESSION['user_id']);

        $stmt1 = queryPost($dbh, $sql1, $data);
        $stmt2 = queryPost($dbh, $sql2, $data);

        //クエリが成功したら（最悪usersテーブルが削除できていればOKにする）
        if($stmt1){
            //セッション削除
            session_destroy();
            debug('セッション変数の中身：'. print_r($_SESSION, true));
            debug('トップページへ遷移します');
            header("Location:top.php");
        }else{
            debug('クエリが失敗しました');
            $err_msg['common'] = MSG07;
        }

    }catch(Exception $e){
        error_log('エラー発生：' . $e->getMassage());
        $err_msg['common'] = MSG07;
    }
}

?>

<?php
$title = '退会';
$originalCss = "css/withdraw.css";
require('head.php');
?>

    <body>
        <?php require('header.php'); ?>

        <div id="main-2culum" class="site-width">
            <div id="main-left">
                <section class="widhtraw">
                    <h2 class="section-title">退会しますがよろしいですか？</h2>
                    <form action="" method="post">
                        <input type="submit" value="退会する" class="btn btn-gray">
                    </form>
                </section>
            </div>

            <?php require('sidebar.php'); ?>

        </div>

        <?php require('footer.php'); ?>