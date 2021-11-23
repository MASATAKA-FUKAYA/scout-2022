<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「チームメンバー詳細ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証なし

//=====================
//画面処理
//=====================

//GETの値を取得
$t_id = $_GET['t_id'];
debug('チームID：'.$t_id);

//チーム、メンバー情報を取得
$dbTeamData = getTeam($t_id);
debug('DBチームデータ：'.print_r($dbTeamData,true));
$dbMemberData = getMember($t_id);
debug('DBメンバーデータ：'.print_r($dbTeamData,true));


//パラメータ改ざんチェック
//====================
//GETパラメータはあるが改竄されている（URLをいじった）場合、正しいチームデータが取れないのでマイページへ遷移
if(!empty($t_id) && empty($dbTeamData)){
    debug('GETパラメータのチームIDが違います。マイページへ遷移します。');
    header("Location:mypage.php");
    exit();
}

?>

<?php
    $title = 'チームメンバー一覧';
    $originalCss = "css/teamMenberList.css";
    require('head.php');
?>

    <body>
        <?php
            require('header.php');
        ?>

        <p id="js-show-msg" style="display:none;" class="msg-slide">
            <?php echo getSessionFlash('msg_success'); ?>
        </p>

        <div id="main-2culum" class="site-width">
            <div id="main-left">
                <section class="main-left-white">

                    <h2 class="section-title">チームメンバー</h2>

                    <!-- Topに戻る -->
                    <div class="p-scroll">
                        <a href="#">Page Top<br><i class="fas fa-chevron-up"></i></a>
                    </div><!-- /Topに戻る -->

                    <div class="team-info">
                        <div class="team-img"><img src="<?php echo $dbTeamData['pic']; ?>" alt="<?php echo $dbTeamData['team_name']; ?>"></div>
                        <p class="team-name"><a href="teamDetail.php?t_id=<?php echo $t_id; ?>"><?php echo $dbTeamData['team_name']; ?></a></p>
                    </div>

                    <div class="member-container">
                        <?php foreach($dbMemberData as $key => $val) : ?>
                            <div class="member-info">
                                <a href="teamMemberDetail.php?t_id=<?php echo $t_id; ?>&m_id=<?php echo $val['id']; ?>">
                                <div class="member-img"><img src="<?php if(!empty($val['pic'])){ echo $val['pic']; }else{ echo 'img/user-icon-default.png'; } ?>" alt=""></div>
                                    <div class="member-num"><?php echo $val['m_number']; ?></div>
                                    <div class="member-text">
                                        <p><?php echo getPitbatName($val['pit_bat_id'])['name']; ?><br>
                                            <?php echo getLevelName($val['level_id'])['name']; ?><br>
                                            <?php echo getPositionName($val['position_id'])['name']; ?>
                                        </p>
                                        <h4><?php echo $val['m_name']; ?></h4>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="btn-wrapper-side">
                        <div class="btn btn-gray">
                            <a href="teamDetail.php?t_id=<?php echo $t_id; ?>">チーム情報に戻る</a>
                        </div>
                        <?php if($dbTeamData['host_user_id'] == $_SESSION['user_id']) : ?>
                            <div class="btn btn-gray">
                                <a href="teamMemberEdit.php?t_id=<?php echo $t_id; ?>">メンバーを追加する</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            </div>

            <?php
                require('sidebar.php');
            ?>

        </div>

        <?php
            require('footer.php');
        ?>