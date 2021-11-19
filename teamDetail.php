<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「チーム詳細ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証は後で

//=====================
//画面処理
//=====================

//GETデータを格納
$t_id = $_GET['t_id'];
debug('チームID：'.$t_id);

//DBからチームデータを取得
$dbFormData = getTeam($t_id);
debug('フォーム用DBチームデータ：'.print_r($dbFormData,true));

//パラメータ改ざんチェック
//====================
//GETパラメータはあるが改竄されている（URLをいじった）場合、正しいチームデータが取れないのでマイページへ遷移
if(!empty($t_id) && empty($dbFormData)){
  debug('GETパラメータのチームIDが違います。マイページへ遷移します。');
  header("Location:mypage.php");
  exit();
}

//DBからチーム代表者、チームレベル、チーム種類を取得
$host = getTeamHost($t_id);
debug('DBチーム代表者データ：'.print_r($host,true));

$dbLevelData = getLevelName($dbFormData['level_id']);
debug('DBチームレベルデータ：'.print_r($dbLevelData,true));

$dbCategoryData = getCategoryName($dbFormData['category_id']);
debug('DBチーム種類データ：'.print_r($dbCategoryData,true));

?>

<?php
    $title = 'チーム詳細';
    $originalCss = "css/teamDetail.css";
    require('head.php');
?>

    <body>

        <?php
            require('header.php');
        ?>

        <div id="main-2culum" class="site-width">
            <div id="main-left">

                <!-- Topに戻る -->
                <div class="p-scroll">
                    <a href="#">Page Top<br><i class="fas fa-chevron-up"></i></a>
                </div><!-- /Topに戻る -->

                <section class="main-left-white">

                    <div class="team-info">
                        <div class="team-img"><img src="img/team_logo01.png" alt="チーム画像"></div>
                        <p class="team-name"><a href="teamDetail.php?t_id=<?php echo $t_id; ?>"><?php echo $dbFormData['team_name']; ?></a></p>
                    </div>

                    <table>
                        <tbody>
                            <tr>
                                <th>チーム代表者</th>
                                <td><?php echo $host['u_name']; ?></td>
                            </tr>
                            <tr>
                                <th>主な活動地域</th>
                                <td><?php echo $dbFormData['prefectures'].$dbFormData['city']; ?></td>
                            </tr>
                            <tr>
                                <th>チーム種類</th>
                                <td><?php echo $dbCategoryData['name']; ?></td>
                            </tr>
                            <tr>
                                <th>チームレベル</th>
                                <td><?php echo $dbLevelData['name']; ?>中心</td>
                            </tr>
                            <tr>
                                <th>チームメンバー</th>
                                <td><a href="teamMemberList.php?t_id=<?php echo $t_id; ?>">メンバー一覧を見る</a></td>
                            </tr>
                            <tr>
                                <th>チーム紹介</th>
                                <?php if(!empty($dbFormData['comment'])) : ?>
                                    <td><?php echo $dbFormData['comment']; ?></td>
                                <?php else : ?>
                                    <td>未登録</td>
                                <?php endif; ?>
                            </tr>
                            <tr>
                                <th>チームURL</th>
                                <?php if(!empty($dbFormData['url'])) : ?>
                                    <td><?php echo $dbFormData['url']; ?></td>
                                <?php else : ?>
                                    <td>未登録</td>
                                <?php endif; ?>
                            </tr>
                        </tbody>
                    </table>

                    <?php if($dbFormData['host_user_id'] === $_SESSION['user_id']): ?>
                        <div class="btn btn-gray">
                            <a href="teamEdit.php?t_id=<?php echo $t_id; ?>">チーム情報編集</a>
                        </div>
                    <?php else : ?>
                        <div class="btn-wrapper-side">
                            <div class="btn btn-gray">
                                <a href="msg.php?badge=0&h_team_id=<?php echo $t_id; ?>&g_user_id=<?php echo $_SESSION['user_id']; ?>">参加申し込み</a>
                            </div>
                            <div class="btn btn-gray">
                                <a href="msg.php?badge=1&h_team_id=<?php echo $t_id; ?>&g_user_id=<?php echo $_SESSION['user_id']; ?>">対戦申し込み</a>
                            </div>
                        </div>
                    <?php endif; ?>

                </section>
            </div>

            <?php
                require('sidebar.php');
            ?>

        </div>

        <?php
            require('footer.php');
        ?>