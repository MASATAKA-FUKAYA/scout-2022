<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「メンバー募集詳細ページ');
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
$dbBoardData = (!empty($b_id)) ? getOneMemBoard($b_id) : '';
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

//POST送信があった場合、掲示板を作成して遷移
if(!empty($_POST['submit'])){
    debug('POST送信があります。');

    try{

        //DB接続
        $dbh = dbConnect();
        $sql = 'INSERT INTO msg_board (host_team_id, guest_user_id, create_date) VALUES (:host_team_id, :guest_user_id, :create_date)';
        $data = array(':host_team_id' => $dbBoardData['team_id'], ':guest_user_id' => $_SESSION['user_id'], ':create_date' => date('Y-m-d H:i:s'));

        debug('SQL:' . $sql);
        debug('流し込みデータ：' . print_r($data,true));
        $stmt = queryPost($dbh, $sql, $data);

        if(!empty($stmt)){
            $b_id = $dbh->lastInsertId();
            debug('メッセージ画面へ遷移します。');
            header("Location:msg.php?badge=0&b_id={$b_id}");
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
    $title = 'メンバーを募集';
    $originalCss = "css/memberRecruitDetail.css";
    require('head.php');
?>

    <body>

    <?php require('header.php'); ?>

        <div id="main-2culum" class="site-width">
            <div id="main-left">
                <section class="main-left-gray">
                    <a class="return-page" href="memberRecruit.php">募集一覧へ戻る</a>

                    <h2 class="section-title">メンバーを募集</h2>

                    <!-- Topに戻る -->
                    <div class="p-scroll">
                        <a href="#">Page Top<br><i class="fas fa-chevron-up"></i></a>
                    </div><!-- /Topに戻る -->

                    <h3 class="rec-title"><?php echo $dbBoardData['title']; ?></h3>

                    <div class="result-wrapper">
                        <div class="result-img"><img src="<?php echo $dbTeamData['pic']; ?>" alt="<?php echo $dbTeamData['team_name']; ?>"></div>
                        <div class="result-text">
                            <h4><?php echo $dbTeamData['team_name']; ?></h4>
                            <p><?php echo $dbTeamData['prefectures']. $dbTeamData['city']; ?></p>
                            <p><?php echo getLevelName($dbTeamData['level_id'])['name']; ?>中心/<?php echo getCategoryName($dbTeamData['category_id'])['name']; ?></p>
                        </div>
                    </div>

                    <section class="recruit-detail">
                        <h3 class="detail-title">募集詳細</h3>
                        <table>
                            <tr>
                                <th>エリア</th>
                                <td><?php echo $dbBoardData['prefectures']. $dbBoardData['city']; ?></td>
                            </tr>
                            <tr>
                                <th>募集プレーヤーレベル</th>
                                <td>LEVEL<?php echo $dbBoardData['level_id']; ?>：<?php echo getLevelName($dbBoardData['level_id'])['name']; ?></td>
                            </tr>
                            <tr>
                                <th>活動曜日</th>
                                <td>
                                    <?php
                                        $days = array("mon" => '月', "tue" => '火', "wed" => '水', "thu" => '木', "fri" => '金', "sat" => '土', "sun" => '日');
                                        foreach($days as $key => $val){
                                            if($dbBoardData["flg_{$key}"] == 1){
                                                echo $val. ' ';
                                            }
                                        }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th>活動頻度</th>
                                <td><?php echo getFrequencyName($dbBoardData['frequency_id'])['name']; ?></td>
                            </tr>
                            <tr>
                                <th>コメント</th>
                                <td><?php echo $dbTeamData['comment']; ?></td>
                            </tr>
                        </table>
                    </section>

                    <?php if(!empty($_SESSION['user_id']) && $hostUserId != $_SESSION['user_id']) : ?>
                        <!-- ログインユーザーが募集の作成者ではない場合、応募するボタン -->
                        <form action="#" method="post">
                            <div class="area-msg">
                                <?php echo getErrMsg('comment'); ?>
                            </div>
                            <input type="submit" name="submit" class="btn btn-gray" value="募集に応募する">
                        </form>

                    <?php elseif(!empty($_SESSION['user_id']) && $hostUserId == $_SESSION['user_id']) : ?>
                        <!-- ログインユーザーが募集の作成者の場合、募集を編集するボタン -->
                        <div class="btn btn-gray">
                            <a href="makeMemberRecruit.php?b_id=<?php echo $b_id; ?>">募集を編集する</a>
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