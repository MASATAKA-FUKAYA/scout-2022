<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「メッセージページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//=====================
//画面処理
//=====================
//GETパラメータを取得
$badge = $_GET['badge'];
if($badge == 0){
    debug('チームへ参加希望です');
}else{
    debug('対戦希望です');
}

$h_team_id = $_GET['h_team_id'];
$g_user_id = $_GET['g_user_id'];

//DBからデータを取得========
//掲示板とメッセージデータ



//チームデータ、代表者データ
$dbTeamData = getTeam($h_team_id);
debug('チームデータ：'. print_r($dbTeamData, true));

$hostUser = getTeamHost($h_team_id);
debug('チーム代表者データ：'. print_r($hostUser, true));

//相手と自分のユーザー情報===============
//from_user_idはログインしているユーザー
$fromUserData = getUser($_SESSION['user_id']);
debug('メッセージ送信者データ：'. print_r($fromUserData, true));

//host_user_idと$_SESSIONのuser_idが同じなら送信者はチーム代表者。メッセージのto_user_idはguest_user_id
if($dbTeamData['host_user_id'] === $_SESSION['user_id']){
    $toUserData = getUser();
    debug('メッセージ受信者データ：'. print_r($toUserData, true));
}else{//違うなら、送信者はゲスト。to_user_idはhost_user_id
    $toUserData = getUser($dbTeamData['host_user_id']);
    debug('メッセージ受信者データ：'. print_r($toUserData, true));
}

//POST送信チェック
if(!empty($_POST)){
    debug('POST送信があります');

    $msg = $_POST['msg'];

    //バリデーションチェック、未入力・最大文字数
    validRequired($msg, 'msg');
    validMaxLen($msg, 'msg');

    if(empty($err_msg)){
        debug('バリデーションOKです');

        //DBへメッセージを登録
        try{
            //DB接続
            $dbh = dbConnect();
            $sql = 'INSERT INTO ';

        }catch(Exception $e){
            error_log('エラー発生：' . $e->getMessage());
            $err_msg['common'] = MSG07;
        }
    }
}



//自画面へ遷移

debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>

<?php
    $title = 'メッセージ';
    $originalCss = "css/msg.css";
    require('head.php');
?>

    <body>

        <?php require('header.php'); ?>

        <div id="main-2culum" class="site-width">
            <div id="main-left">
                <section class="main-left-gray">

                    <h2 class="section-title">メッセージ</h2>

                    <!-- Topに戻る -->
                    <div class="p-scroll">
                        <a href="#">Page Top<br><i class="fas fa-chevron-up"></i></a>
                    </div><!-- /Topに戻る -->

                    <div class="msg-header">
                        <?php if($badge == 0) : ?>
                            <div class="badge badge-mem">参加希望</div>

                        <?php else : ?>
                            <div class="badge badge-opp">対戦希望</div>

                        <?php endif; ?>

                        <div class="opponent-team">
                            <div class="opponent-team-img">
                                <img src="<?php echo $dbTeamData['pic']; ?>" alt="<?php echo $dbTeamData['team_name']; ?>">
                            </div>
                            <p><?php echo $dbTeamData['team_name']; ?></p>
                        </div>
                    </div>

                    <div class="opponent-info">
                        <div class="opponent-info-img">
                            <img src="<?php echo $hostUser['pic']; ?>" alt="プロフ画像">
                        </div>
                        <div>
                            <p style="font-size: 12px;">代表者</p>
                            <p style="font-size: 28px;"><?php echo $hostUser['u_name']; ?></p>
                        </div>
                    </div>

                    <div class="area-board">
                        <div class="msg-container">
                            <div class="user-wrapper-right">
                                <div class="user-img">
                                    <img src="img/user_prof01.jpg" alt="">
                                </div>
                                <p class="user-name">自分</p>
                            </div>
                            <div class="text-wrapper-right">
                                <div class='msg-text-right'>
                                    こんにちは！掲示板を見てご連絡させていただきました！練習試合をお願いしたいです！
                                </div>
                                <p class="send-date">21.10.13 19:36</p>
                            </div>
                        </div>

                        <div class="msg-container">
                            <div class="user-wrapper-left">
                                <div class="user-img">
                                    <img src="img/user_prof01.jpg" alt="">
                                </div>
                                <p class="user-name">立風　和義</p>
                            </div>
                            <div class="text-wrapper-left">
                                <div class='msg-text-left'>
                                    ご連絡ありがとうございます！名古屋ドラゴンズ代表の立風です！
                                </div>
                                <p class="send-date">21.10.13 19:36</p>
                            </div>
                        </div>

                        <div class="msg-container">
                            <div class="user-wrapper-left">
                                <div class="user-img">
                                    <img src="img/user_prof01.jpg" alt="">
                                </div>
                                <p class="user-name">立風　和義</p>
                            </div>
                            <div class="text-wrapper-left">
                                <div class='msg-text-left'>
                                    ご連絡ありがとうございます！名古屋ドラゴンズ代表の立風です！
                                </div>
                                <p class="send-date">21.10.13 19:36</p>
                            </div>
                        </div>

                        <div class="msg-container">
                            <div class="user-wrapper-left">
                                <div class="user-img">
                                    <img src="img/user_prof01.jpg" alt="">
                                </div>
                                <p class="user-name">立風　和義</p>
                            </div>
                            <div class="text-wrapper-left">
                                <div class='msg-text-left'>
                                    ご連絡ありがとうございます！名古屋ドラゴンズ代表の立風です！
                                </div>
                                <p class="send-date">21.10.13 19:36</p>
                            </div>
                        </div>

                        <div class="msg-container">
                            <div class="user-wrapper-right">
                                <div class="user-img">
                                    <img src="img/user_prof01.jpg" alt="">
                                </div>
                                <p class="user-name">自分</p>
                            </div>
                            <div class="text-wrapper-right">
                                <div class='msg-text-right'>
                                    こんにちは！掲示板を見てご連絡させていただきました！練習試合をお願いしたいです！
                                </div>
                                <p class="send-date">21.10.13 19:36</p>
                            </div>
                        </div>

                        <div class="msg-container">
                            <div class="user-wrapper-right">
                                <div class="user-img">
                                    <img src="img/user_prof01.jpg" alt="">
                                </div>
                                <p class="user-name">自分</p>
                            </div>
                            <div class="text-wrapper-right">
                                <div class='msg-text-right'>
                                    こんにちは！掲示板を見てご連絡させていただきました！練習試合をお願いしたいです！
                                </div>
                                <p class="send-date">21.10.13 19:36</p>
                            </div>
                        </div>
                    </div>

                    <form action="" method="post">
                        <textarea name="msg" id="send-text" cols="30" rows="5" placeholder="メッセージを入力"></textarea>
                        <input type="submit" class="btn btn-gray" value="メッセージ送信">
                    </form>

                </section>
            </div>

            <?php require('sidebar.php'); ?>

        </div>

        <?php require('footer.php'); ?>