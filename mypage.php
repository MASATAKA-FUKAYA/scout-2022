<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「');
debug('「マイページ');
debug('「「「「「「「「「「「「「「「「「「「');
debugLogStart();

require('auth.php');

$u_id = $_SESSION['user_id'];

//DBからページに表示する情報を取得
$dbFormData = getUser($u_id);
debug('取得したユーザーDBデータ：'. print_r($dbFormData, true));

$dbLevelData = getLevelName($dbFormData['level_id']);
debug('取得したレベルDBデータ：'. print_r($dbLevelData, true));

$dbTeamData = getMyALLTeam($u_id);
debug('取得したチームDBデータ：'. print_r($dbTeamData, true));

//掲示板データは長いのでデバッグ省略
$dbRecBoardData = getMyRecBoard($u_id);

$dbMsgBoardData = getMyMsgBoard($u_id);

debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
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
                        <div class="prof-img"><img src="<?php if(!empty($dbFormData['pic'])){ echo $dbFormData['pic']; }else{ echo 'img/user-icon-default.png'; } ?>" alt="プロフ画像"></div>
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

                        <?php if(!empty($dbTeamData)):?>

                            <?php foreach($dbTeamData as $key => $val): ?>
                                <div class="prof-contents-2culum">
                                    <div class="prof-img"><img src="<?php echo $val['pic']; ?>" alt="チーム画像"></div>
                                    <p class="team-name"><a href="teamDetail.php?t_id=<?php echo $val['id']; ?>"><?php echo $val['team_name']; ?></a></p>
                                </div>
                            <?php endforeach; ?>

                        <?php else: ?>

                            <div class="prof-contents-2culum">
                                <p class="team-name">まだチームに参加していません！</p>
                            </div>

                        <?php endif; ?>

                    <div class="btn-wrapper-side">
                        <div class="btn btn-mypage">
                            <a href="teamEdit.php">新規作成</a>
                        </div>
                        <div class="btn btn-mypage">
                            <a href="teamSearch.php">チーム検索</a>
                        </div>
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
                            <?php if($dbRecBoardData['opp']): ?>
                                <?php foreach($dbRecBoardData['opp'] as $key => $val): ?>
                                        <tr>
                                            <td>対戦相手</td>
                                            <td><?php echo mb_substr($val['update_date'], 0, 10) ; ?></td>
                                            <td><a href="opponentRecruitDetail.php?b_id=<?php echo $val['id']; ?>" style="color:black;"><?php echo $val['title']; ?></a></td>
                                        </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td>対戦相手</td>
                                    <td></td>
                                    <td>まだ投稿がありません。</td>
                                </tr>
                            <?php endif; ?>

                            <?php if($dbRecBoardData['mem']): ?>
                                <?php foreach($dbRecBoardData['mem'] as $key => $val): ?>
                                    <tr>
                                        <td>メンバー</td>
                                        <td><?php echo mb_substr($val['update_date'], 0, 10) ; ?></td>
                                        <td><a href="memberRecruitDetail.php?b_id=<?php echo $val['id']; ?>" style="color:black;"><?php echo $val['title']; ?></a></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td>メンバー</td>
                                    <td></td>
                                    <td>まだ投稿がありません。</td>
                                </tr>
                            <?php endif; ?>

                        </tbody>
                    </table>
                    <?php if(!empty($dbTeamData[0])): ?>
                        <div class="btn-wrapper-side">
                            <div class="btn btn-mypage">
                                <a href="makeOpponentRecruit.php">対戦相手募集</a>
                            </div>
                            <div class="btn btn-mypage">
                                <a href="makeMemberRecruit.php">メンバー募集</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="btn btn-mypage">
                            <a href="teamEdit.php">チーム作成</a>
                        </div>
                        <p>※チームのホストになると、対戦相手やメンバーを募集することができます。</p>
                    <?php endif; ?>

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

                            <?php if(!empty($dbMsgBoardData)): ?>
                                <?php
                                    foreach($dbMsgBoardData as $key => $val):
                                        if($val['msg']['to_user_id'] == $_SESSION['user_id']){
                                            $msgPartnerId = $val['msg']['from_user_id'];
                                        }else{
                                            $msgPartnerId = $val['msg']['to_user_id'];
                                        }
                                ?>
                                    <tr>
                                        <td><?php echo getUser($msgPartnerId)['u_name']; ?></td>
                                        <td><?php echo mb_substr($val['msg']['update_date'], 0, 10) ; ?></td>
                                        <td><a href="msg.php?b_id=<?php echo $val['msg_board_id']; ?>" style="color:black;"><?php if(mb_strlen($val['msg']['msg_text']) < 20){ echo $val['msg']['msg_text']; }else{ echo mb_substr($val['msg']['msg_text'], 0, 20). '…'; }?></a></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td>メンバー</td>
                                    <td></td>
                                    <td>まだ投稿がありません。</td>
                                </tr>
                            <?php endif; ?>

                        </tbody>
                    </table>
                    <div class="btn btn-mypage">
                        <a href="teamEdit.php">メッセージ一覧</a>
                    </div>
                </section>

                <!-- お気に入りチームを追加するならここに -->

            </section>

            <?php
                require('sidebar.php');
            ?>

        </div>

        <?php
            require('footer.php');
        ?>