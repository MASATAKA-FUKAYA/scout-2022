<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「チームメンバー詳細ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証は後で

//=====================
//画面処理
//=====================

//GETデータを格納
$t_id = $_GET['t_id'];
debug('チームID：'.$t_id);
$m_id = $_GET['m_id'];
debug('メンバーID：'.$m_id);

//DBからチーム、メンバーデータを取得
$dbTeamData = getTeam($t_id);
debug('DBチームデータ：'.print_r($dbTeamData,true));
$dbMemberData = getOneMember($m_id);
debug('DBメンバーデータ：'.print_r($dbMemberData,true));

//パラメータ改ざんチェック
//====================
//GETパラメータはあるが改竄されている（URLをいじった）場合、正しいチームデータが取れないのでマイページへ遷移
if(!empty($t_id) && empty($dbTeamData)){
  debug('GETパラメータのチームIDが違います。メンバー一覧ページへ遷移します。');
  header("Location:teamMemberList.php?t_id={$t_id}");
  exit();
}



?>

<?php
    $title = 'チームメンバー詳細';
    $originalCss = "css/teamDetail.css";
    $originalCss2 = "css/teamMemberDetail.css";
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
                        <p class="team-name"><a href="teamDetail.php?t_id=<?php echo $t_id; ?>"><?php echo $dbTeamData['team_name']; ?></a></p>
                    </div>

                    <table>
                        <tbody>
                            <tr>
                                <th>メンバー名</th>
                                <td><?php echo $dbMemberData['m_name']; ?></td>
                            </tr>
                            <tr>
                                <th>背番号</th>
                                <td><?php echo $dbMemberData['m_number']; ?></td>
                            </tr>
                            <tr>
                                <th>投打</th>
                                <td><?php echo getPitbatName($dbMemberData['pit_bat_id'])['name']; ?></td>
                            </tr>
                            <tr>
                                <th>メインポジション</th>
                                <td><?php echo getPositionName($dbMemberData['position_id'])['name']; ?></td>
                            </tr>
                            <tr>
                                <th>プレーヤーレベル</th>
                                <td><?php echo getLevelName($dbMemberData['level_id'])['name']; ?></td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="btn-wrapper-side">
                        <div class="btn btn-gray">
                            <a href="teamMemberList.php?t_id=<?php echo $t_id; ?>">メンバー一覧へ</a>
                        </div>
                    </div>
                    <?php if($dbTeamData['host_user_id'] === $_SESSION['user_id']): ?>
                        <div class="btn btn-gray">
                            <a href="teamMemberEdit.php?t_id=<?php echo $t_id; ?>&m_id=<?php echo $m_id; ?>">メンバー情報編集</a>
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