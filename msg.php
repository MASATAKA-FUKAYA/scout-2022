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

$b_id = $_GET['b_id'];

//DBからデータを取得========
//掲示板、メッセージデータ
$dbBoardData = getMsgBoard($b_id);
debug('掲示板・メッセージデータ：'. print_r($dbBoardData, true));
$badge = $dbBoardData['badge'];
if($badge == 0){
    debug('チームへ参加希望です');
}else{
    debug('対戦希望です');
}

//チームデータ、代表者データ
$dbTeamData = getTeam($dbBoardData['host_team_id']);
debug('チームデータ：'. print_r($dbTeamData, true));

$hostUser = getTeamHost($dbBoardData['host_team_id']);
debug('チーム代表者データ：'. print_r($hostUser, true));

//相手と自分のユーザー情報===============
//from_user_id（メッセージの送信者）はログインしているユーザー
$fromUserData = getUser($_SESSION['user_id']);
debug('メッセージ送信者データ：'. print_r($fromUserData, true));

//host_user_idと$_SESSIONのuser_idが同じなら送信者はチーム代表者。メッセージのto_user_id（受信者）はguest_user_id
if($dbTeamData['host_user_id'] === $_SESSION['user_id']){
    $toUserData = getUser($dbBoardData['guest_user_id']);
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
            $sql = 'INSERT INTO message (msg_board_id, to_user_id, from_user_id, msg, create_date) VALUES (:msg_board_id, :to_user_id, :from_user_id, :msg, :create_date)';
            $data = array('msg_board_id' => $b_id, 'to_user_id' => $toUserData['id'], 'from_user_id' => $_SESSION['user_id'], 'msg' => $msg, ':create_date' => date('Y-m-d H:i:s'));

            $stmt = queryPost($dbh, $sql, $data);

            if($stmt){
                $_POST = array();     //POSTをクリア
                debug('メッセージページへ遷移します。');
                header("Location:" .$_SERVER['PHP_SELF']. '?b_id=' .$b_id. '&badge='. $badge);    //自分自身へ遷移する
            }

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
                            <img src="<?php echo $hostUser['pic']; ?>" alt="<?php echo $hostUser['u_name']; ?>">
                        </div>
                        <div>
                            <p style="font-size: 12px;">代表者</p>
                            <p style="font-size: 28px;"><?php echo $hostUser['u_name']; ?></p>
                        </div>
                    </div>

                    <div class="area-board">

                        <?php foreach($dbBoardData['msg'] as $key => $val) : ?>

                            <!-- 自分が送信したメッセージ -->
                            <?php if($val['from_user_id'] == $_SESSION['user_id']): ?>
                                <!-- $fromUserDataは必ず現在ログインしているユーザーなので、その情報を使う -->
                                <div class="msg-container">
                                    <div class="user-wrapper-right">
                                        <div class="user-img">
                                            <img src="<?php if(!empty($fromUserData['pic'])){ echo $fromUserData['pic']; } else { echo 'img/user-icon-default.png'; } ?>" alt="<?php echo $fromUserData['u_name']; ?>">
                                        </div>
                                        <p class="user-name"><?php echo $fromUserData['u_name']; ?></p>
                                    </div>
                                    <div class="text-wrapper-right">
                                        <div class='msg-text-right'>
                                            <?php echo $val['msg']; ?>
                                        </div>
                                        <p class="send-date"><?php echo $val['create_date']; ?></p>
                                    </div>
                                </div>

                            <!-- 相手が送信したメッセージ（相手が自分に送ったメッセージ） -->
                            <?php elseif($val['to_user_id'] == $_SESSION['user_id']): ?>
                                <!-- 相手はチーム代表者の場合も、ゲスト（募集応募者）の場合もあるので、改めてユーザー情報を取り直す -->
                                <?php $partnerUser = getUser($val['from_user_id']); ?>
                                    <div class="msg-container">
                                        <div class="user-wrapper-left">
                                            <div class="user-img">
                                                <img src="<?php if(!empty($partnerUser['pic'])){ echo $partnerUser['pic']; } else { echo 'img/user-icon-default.png'; } ?>" alt="<?php echo $$partnerUser['u_name']; ?>">
                                            </div>
                                            <p class="user-name"><?php echo $partnerUser['u_name']; ?></p>
                                        </div>
                                        <div class="text-wrapper-left">
                                            <div class='msg-text-left'>
                                                <?php echo $val['msg']; ?>
                                            </div>
                                            <p class="send-date"><?php echo $val['create_date']; ?></p>
                                        </div>
                                    </div>
                            <?php endif; ?>

                        <?php endforeach;?>

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