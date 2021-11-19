<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「');
debug('「マイページ');
debug('「「「「「「「「「「「「「「「「「「「');
debugLogStart();

require('auth.php');

$u_id = $_SESSION['user_id'];

//DBからユーザー情報、レベル情報を取得
$dbFormData = getUser($u_id);
debug('取得したユーザーDBデータ：'. print_r($dbFormData, true));

$dbLevelData = getLevelName($dbFormData['level_id']);
debug('取得したレベルDBデータ：'. print_r($dbLevelData, true));

$dbTeamData = getMyALLTeam($u_id);
debug('取得したチームDBデータ：'. print_r($dbTeamData, true));
?>

<?php
    $title = 'マイページ';
    $originalCss = "css/mypage.css";
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
            <section id="main-left">
                <section class="prof-wrapper">
                    <h2 class="prof-title">プロフィール</h2>

                    <!-- Topに戻る -->
                    <div class="p-scroll">
                        <a href="#">Page Top<br><i class="fas fa-chevron-up"></i></a>
                    </div><!-- /Topに戻る -->

                    <div class="prof-contents-2culum">
                        <div class="prof-img"><img src="img/user_prof01.jpeg" alt="プロフ画像"></div>
                        <table class="prof-table">
                            <tbody>
                                <tr>
                                    <th>ニックネーム</th>
                                    <td><?php echo $dbFormData['u_name']; ?></td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td><?php echo $dbFormData['email']; ?></td>
                                </tr>
                                <tr>
                                    <th>レベル</th>
                                    <td><?php echo $dbLevelData['name']; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="btn-wrapper-side">
                        <div class="btn btn-mypage">
                            <a href="profEdit.php">プロフィール編集</a>
                        </div>
                        <div class="btn btn-mypage">
                            <a href="passEdit.php">パスワード変更</a>
                        </div>
                    </div>
                </section>

                <section class="prof-wrapper">
                    <h2 class="prof-title">所属チーム</h2>
                    <div class="prof-contents-2culum">
                        <div class="prof-img"><img src="img/team_logo01.png" alt="チーム画像"></div>
                        <p class="team-name"><a href="teamDetail.php?t_id=1">名古屋ドラゴンズ</a></p>
                    </div>
                    <div class="btn btn-mypage">
                        <a href="teamEdit.php">新規作成</a>
                    </div>
                </section>

                <section class="prof-wrapper">
                    <h2 class="prof-title">募集中の投稿</h2>
                    <table class="table-common">
                        <thead>
                            <tr>
                                <th class="table-common-title01">種別</th>
                                <th class="table-common-title02">更新日時</th>
                                <th class="table-common-title03">募集タイトル</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="table-common-text">対戦相手</td>
                                <td class="table-common-text">21.10.04</td>
                                <td class="table-common-text">来週日曜、練習試合お願いします！</td>
                            </tr>
                            <tr>
                                <td class="table-common-text">メンバー</td>
                                <td class="table-common-text">21.10.04</td>
                                <td class="table-common-text">和気あいあい、楽しさ重視のチーム…</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="btn btn-mypage">
                        <a href="teamEdit.php">メンバー、対戦相手募集</a>
                    </div>
                </section>

                <section class="prof-wrapper">
                    <h2 class="prof-title">メッセージ</h2>
                    <table class="table-common">
                        <thead>
                            <tr>
                                <th class="table-common-title01">相手</th>
                                <th class="table-common-title02">更新日時</th>
                                <th class="table-common-title03">本文</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="table-common-text">ディバラ</td>
                                <td class="table-common-text">21.10.04</td>
                                <td class="table-common-text">グラウンドは、名古屋市緑区の…</td>
                            </tr>
                            <tr>
                                <td class="table-common-text">ラムジー</td>
                                <td class="table-common-text">21.10.04</td>
                                <td class="table-common-text">活動日は主に土曜日の午前中です！</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="btn btn-mypage">
                        <a href="teamEdit.php">メッセージ一覧</a>
                    </div>
                </section>

                <section class="prof-wrapper">
                    <h2 class="prof-title">お気に入りチーム一覧</h2>
                    <div class="prof-contents-2culum">
                        <div class="prof-img"><img src="img/team_logo01.png" alt="チーム画像"></div>
                        <p class="team-name"><a href="teamDetail.php">名古屋ドラゴンズ</a></p>
                    </div>
                    <div class="prof-contents-2culum">
                        <div class="prof-img"><img src="img/team_logo02.png" alt="チーム画像"></div>
                        <p class="team-name"><a href="teamDetail.php">保土ヶ谷ベイスターズ</a></p>
                    </div>
                </section>

            </section>

            <?php
                require('sidebar.php');
            ?>

        </div>

        <?php
            require('footer.php');
        ?>