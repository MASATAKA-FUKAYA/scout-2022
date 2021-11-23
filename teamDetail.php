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


//POST送信があった場合、掲示板を作成して遷移
if(!empty($_POST)){
    debug('POST送信があります。');

    if(!empty($_POST['badge=0'])){
        debug('参加の申し込みです。');
        $badge = 0;
    }elseif(!empty($_POST['badge=1'])){
        debug('対戦の申し込みです。');
        $badge = 1;
    }else{
        debug('不正な値が入りました。');
    }

    try{

        //DB接続
        $dbh = dbConnect();
        $sql = 'INSERT INTO msg_board (host_team_id, guest_user_id, create_date) VALUES (:host_team_id, :guest_user_id, :create_date)';
        $data = array(':host_team_id' => $dbFormData['id'], ':guest_user_id' => $_SESSION['user_id'], ':create_date' => date('Y-m-d H:i:s'));

        debug('SQL:' . $sql);
        debug('流し込みデータ：' . print_r($data,true));
        $stmt = queryPost($dbh, $sql, $data);

        if(!empty($stmt)){
            $b_id = $dbh->lastInsertId();
            debug('メッセージ画面へ遷移します。');
            header("Location:msg.php?badge={$badge}&b_id={$b_id}");
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

                            <form action="" method="post">
                                <input type="submit" name="badge=0" class="btn btn-gray" value="参加申し込み">
                                <input type="submit" name="badge=1" class="btn btn-gray" value="対戦申し込み">
                            </form>

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