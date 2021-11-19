<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「メンバー募集詳細ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証なし

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

//パラメータ改ざんチェック
//====================
//GETパラメータはあるが改竄されている（URLをいじった）場合、正しいチームデータが取れないのでメンバー募集検索ページへ遷移
if(!empty($b_id) && empty($dbBoardData)){
    //b_idをいじってあった場合
    debug('GETパラメータの掲示板IDが違います。メンバー募集検索ページへ遷移します。');
    header("Location:memberRecruit.php");
    exit();
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
                    <?php if(!empty($_SESSION['user_id'])) : ?>
                        <a class="return-page" href="makeMemberRecruit.php?b_id=<?php echo $b_id; ?>">募集を編集する</a>
                    <?php endif; ?>

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

                    <?php if(!empty($_SESSION['user_id'])) : ?>
                        <form action="">
                            <input type="submit" class="btn btn-gray" value="募集に応募する">
                        </form>
                    <?php else : ?>
                        <div class="btn btn-signup">
                            <a href="signup.php">ユーザー登録</a>
                        </div>
                        <p class="btn-info">※募集に応募するにはユーザー登録が必要です。</p>
                    <?php endif; ?>
                </section>
            </div>

            <?php require('sidebar.php'); ?>

        </div>

        <?php require('footer.php'); ?>