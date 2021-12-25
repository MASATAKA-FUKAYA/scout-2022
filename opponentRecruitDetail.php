<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「対戦相手募集詳細ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証は後で

//=====================
//画面処理
//=====================

//GETデータを格納
$b_id = (!empty($_GET['b_id'])) ? $_GET['b_id'] : '';
debug('募集ID：'.$b_id);

//DBから掲示板、チームデータを取得
$dbBoardData = (!empty($b_id)) ? getOneOppBoard($b_id) : '';
debug('DB掲示板データ：'.print_r($dbBoardData,true));
$dbTeamData = getTeam($dbBoardData['team_id']);
debug('DBチームデータ：'.print_r($dbTeamData,true));
//チーム代表者のID（応募ボタンを表示する判定で使用）を取得
$hostUserId = $dbTeamData['host_user_id'];
debug('チーム代表者ID：'. print_r($hostUserId, true));


//パラメータ改ざんチェック
//====================
//GETパラメータはあるが改竄されている（URLをいじった）場合、正しいチームデータが取れないのでメンバー募集検索ページへ遷移
if(!empty($b_id) && empty($dbBoardData)){
    //b_idをいじってあった場合
    debug('GETパラメータの掲示板IDが違います。メンバー募集検索ページへ遷移します。');
    header("Location:memberRecruit.php");
    exit();
}

//曜日取得=========================
//日付を指定
$strtotime = $dbBoardData['r_year']. $dbBoardData['r_month']. $dbBoardData['r_day'];
//指定日の曜日を取得する
$date = date('w', strtotime($strtotime));

$week = [
  '日', //0
  '月', //1
  '火', //2
  '水', //3
  '木', //4
  '金', //5
  '土', //6
];

//対戦相手募集なので1。メッセージ画面での判定で使用するためここで格納
$badge = 1;

//POST送信があった場合、掲示板を作成して遷移
if(!empty($_POST['submit'])){
    debug('POST送信があります。');
    //まずログイン認証
    require('auth.php');

    try{
        //DB接続
        $dbh = dbConnect();
        $sql = 'INSERT INTO msg_board (badge, host_team_id, guest_user_id, create_date) VALUES (:badge, :host_team_id, :guest_user_id, :create_date)';
        $data = array(':badge' => $badge, ':host_team_id' => $dbBoardData['team_id'], ':guest_user_id' => $_SESSION['user_id'], ':create_date' => date('Y-m-d H:i:s'));

        debug('SQL:' . $sql);
        debug('流し込みデータ：' . print_r($data,true));
        $stmt = queryPost($dbh, $sql, $data);

        if(!empty($stmt)){
            $b_id = $dbh->lastInsertId();
            debug('メッセージ画面へ遷移します。');
            header("Location:msg.php?b_id={$b_id}");
            exit();
        }

    }catch(Exception $e){
        error_log('エラー発生：' . $e->getMessage());
        $err_msg['common'] = MSG07;
      }
}

debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>

<?php
    $title = '対戦相手を募集';
    $originalCss = "css/opponentRecruitDetail.css";
    require('head.php');
?>

    <body>

        <?php require('header.php'); ?>

        <div id="main-2culum" class="site-width">
            <div id="main-left">
                <section class="main-left-gray">
                    <a class="return-page" href="opponentRecruit.php">募集一覧へ戻る</a>

                    <h2 class="section-title">対戦相手を募集</h2>

                    <!-- Topに戻る -->
                    <div class="p-scroll">
                        <a href="#">Page Top<br><i class="fas fa-chevron-up"></i></a>
                    </div><!-- /Topに戻る -->

                    <section class="opponent-rec-view">
                        <p class="opponent-rec-date"><?php echo $dbBoardData['r_year']. '/' .$dbBoardData['r_month']. '/'. $dbBoardData['r_day']; ?>（<?php echo $week[$date]; ?>）</p>
                        <h3 class="rec-title"><?php echo $dbBoardData['title']; ?></h3>
                        <p class="opponent-rec-area">エリア：<?php echo $dbBoardData['prefectures'].$dbBoardData['city']; ?></p>
                    </section>

                    <div class="result-wrapper">
                        <div class="result-img"><img src="<?php echo $dbTeamData['pic']; ?>" alt="<?php echo $dbTeamData['team_name']; ?>"></div>
                        <div class="result-text">
                            <h4><?php echo $dbTeamData['team_name']; ?></h4>
                            <p><?php echo getLevelName($dbTeamData['level_id'])['name']; ?>中心/<?php echo getCategoryName($dbTeamData['category_id'])['name']; ?></p>
                        </div>
                    </div>

                    <section class="recruit-detail">
                        <h3 class="detail-title">募集詳細</h3>
                        <table>
                            <tr>
                                <th>試合日時</th>
                                <td><?php echo $dbBoardData['r_month']; ?>/<?php echo $dbBoardData['r_day']; ?>（<?php echo $week[$date]; ?>）</td>
                            </tr>
                            <tr>
                                <th>エリア</th>
                                <td><?php echo $dbBoardData['prefectures']. $dbBoardData['city']; ?></td>
                            </tr>
                            <tr>
                                <th>チーム属性</th>
                                <td><?php echo getCategoryName($dbBoardData['category_id'])['name']; ?></td>
                            </tr>
                            <tr>
                                <th>グラウンド</th>
                                <td><?php echo (!empty($dbBoardData['g_name'])) ? $dbBoardData['g_name'] : 'グラウンド無し'; ?></td>
                            </tr>
                            <tr>
                                <th>希望対戦相手レベル</th>
                                <?php if(empty($dbBoardData['level_id_low']) && empty($dbBoardData['level_id_high'])) : ?>
                                    <td>設定されていません。</td>
                                <?php elseif(empty($dbBoardData['level_id_high'])): ?>
                                    <td>LEVEL<?php echo $dbBoardData['level_id_low']; ?> ~</td>
                                <?php elseif(empty($dbBoardData['level_id_low'])): ?>
                                    <td>~ LEVEL<?php echo $dbBoardData['level_id_high']; ?></td>
                                <?php else: ?>
                                    <td>LEVEL<?php echo $dbBoardData['level_id_low']; ?> ~ LEVEL<?php echo $dbBoardData['level_id_high']; ?></td>
                                <?php endif; ?>
                            </tr>
                            <tr>
                                <th>審判</th>
                                <?php if((int)$dbBoardData['umpire_id'] !== 0): ?>
                                    <td><?php echo getUmpireName($dbBoardData['umpire_id']); ?></td>
                                <?php else: ?>
                                    <td>設定されていません。</td>
                                <?php endif; ?>
                            </tr>
                            <tr>
                                <th>その他</th>
                                <td>
                                    <?php if(!empty($dbBoardData['all_attack_flg'])){ echo '全員打撃可'; } ?>
                                    <?php if(!empty($dbBoardData['re_enter_flg'])){ echo '再出場可'; } ?>
                                    <?php if(!empty($dbBoardData['lend_member_flg'])){ echo 'メンバー貸し出し可'; } ?>
                                    <?php if(!empty($dbBoardData['pay_all_flg'])){ echo '費用は主催者負担'; } ?>
                                    <!-- 全て空だった場合 -->
                                    <?php if(empty($dbBoardData['all_attack_flg']) && empty($dbBoardData['re_enter_flg']) && empty($dbBoardData['lend_member_flg']) && empty($dbBoardData['pay_all_flg'])): ?>
                                        設定されていません。
                                    <?php endif ;?>      
                                </td>
                            </tr>
                            <tr>
                                <th>コメント</th>
                                <?php if(!empty($dbBoardData['comment'])): ?>
                                    <td><?php echo $dbBoardData['comment']; ?></td>
                                <?php else: ?>
                                    <td>コメントはありません。</td>
                                <?php endif; ?>
                            </tr>
                        </table>
                    </section>

                    <?php if(!empty($_SESSION['user_id']) && $hostUserId != $_SESSION['user_id']) : ?>
                        <!-- ログインユーザーが募集の作成者ではない場合、応募するボタン -->
                        <form action="#" method="post">
                            <div class="area-msg">
                                <?php echo getErrMsg('comment'); ?>
                            </div>
                            <input type="submit" name="submit" class="btn btn-gray" value="対戦申し込み">
                        </form>

                    <?php elseif(!empty($_SESSION['user_id']) && $hostUserId == $_SESSION['user_id']) : ?>
                        <!-- ログインユーザーが募集の作成者の場合、募集を編集するボタン -->
                        <div class="btn btn-gray">
                            <a href="makeOpponentRecruit.php?b_id=<?php echo $b_id; ?>">募集を編集する</a>
                        </div>

                    <?php elseif(empty($_SESSION['user_id'])) : ?>
                        <!-- そもそもユーザーが未ログインの場合、ログインさせる -->
                        <div class="btn btn-signup">
                            <a href="signup.php">ユーザー登録</a>
                        </div>
                        <div class="btn btn-login">
                            <a href="login.php">ログイン</a>
                        </div>
                        <p class="btn-info">※対戦申し込みにはユーザー登録またはログインが必要です。</p>

                    <?php endif; ?>

                </section>
            </div>

            <?php require('sidebar.php'); ?>

        </div>

        <?php require('footer.php'); ?>