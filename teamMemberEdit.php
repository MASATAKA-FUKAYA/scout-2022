<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「チームメンバー登録・編集ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//=====================
//画面処理
//=====================

//GETデータを格納
$t_id = (!empty($_GET['t_id'])) ? $_GET['t_id'] : '';
debug('チームID：'.$t_id);
$m_id = (!empty($_GET['m_id'])) ? $_GET['m_id'] : '';
debug('メンバーID：'.$m_id);

//DBからチーム、メンバーデータを取得
$dbFormData = (!empty($m_id)) ? getOneMember($m_id) : '';
debug('フォーム用DBチームデータ：'.print_r($dbFormData,true));


//新規登録画面か編集画面か判別用フラグ
$edit_flg = (empty($dbFormData)) ? 0 : 1;
debug('判別フラグ：'. $edit_flg);

//パラメータ改ざんチェック
//====================
//GETパラメータはあるが改竄されている（URLをいじった）場合、正しいチームデータが取れないのでマイページへ遷移
if(!empty($m_id) && empty($dbFormData)){
  debug('GETパラメータのチームIDが違います。マイページへ遷移します。');
  header("Location:mypage.php");
  exit();
}

//DBから投打、ポジション、レベルデータを取得
$dbLevelData = getLevel();
$dbPitbatData = getPitbat();
$dbPositionData = getPosition();

//POST送信されているかチェック
if(!empty($_POST)){
    debug('POST送信があります。');
    debug('POST情報：' . print_r($_POST,true));
    debug('FILE情報：' . print_r($_FILES,true));

    //入力されたユーザーからの情報を変数に代入
    $m_name = $_POST['m_name'];
    $m_number = $_POST['m_number'];
    $pit_bat = $_POST['pit_bat_id'];
    $position = $_POST['position_id'];
    $level = $_POST['level_id'];

    //画像をアップロードし、パスを格納
    $pic = ( !empty($_FILES['pic']['name'])) ? upLoadImg($_FILES['pic'],'pic') : '';
    //画像をpostしていない（登録していない）が既にDBに登録されている場合、DBのパスを入れる
    $pic = ( empty($pic) && !empty($dbFormData['pic'])) ? $dbFormData['pic'] : $pic;

    //バリデーションチェック
    //未入力
    validRequired($m_name, 'm_name');

    if(empty($err_msg)){
        debug('未入力チェックOK');

        //バリデーション続き
        //チーム名・最大文字数
        validMaxLen($m_name, 'm_name', 30);
        //背番号・半角数字
        validNumber($m_number, 'm_number');

        if(empty($err_msg)){
            debug('バリデーションチェックOK');

            //DB新規登録または更新
            try{
                $dbh = dbConnect();

                switch($edit_flg){
                    case 0:
                        debug('新規登録です');
                        //user_idは後ほど実装
                        $sql = 'INSERT INTO member (team_id, m_name, m_number, pit_bat_id, position_id, level_id, pic, create_date)
                                VALUES (:team_id, :m_name, :m_number, :pit_bat_id, :position_id, :level_id, :pic, :create_date)';
                        $data = array(':team_id' => $t_id, ':m_name' => $m_name, ':m_number' => $m_number, ':pit_bat_id' => $pit_bat, ':position_id' => $position,
                                ':level_id' => $level, ':pic' => $pic, ':create_date' => date('Y-m-d H:i:s'));
                        break;
                    case 1:
                        debug('DB更新です');
                        //メンバーの移籍はさせないのでteam_idの変更は省略
                        $sql = 'UPDATE member SET m_name = :m_name, m_number = :m_number, pit_bat_id = :pit_bat_id, position_id = :position_id,
                                level_id = :level_id, pic = :pic WHERE id = :id';
                        $data = array(':m_name' => $m_name, ':m_number' => $m_number, ':pit_bat_id' => $pit_bat, ':position_id' => $position,
                                ':level_id' => $level, ':pic' => $pic, ':id' => $m_id);
                        break;
                }

                debug('SQL:' . $sql);
                debug('流し込みデータ：' . print_r($data,true));
                //SQL実行
                $stmt = queryPost($dbh, $sql, $data);

                //$stmtの中身は取り出さないからfetchしない

                //クエリ成功の場合
                if($stmt){
                    $_SESSION['msg_success'] = SUC06;
                    //メンバー一覧画面へ遷移
                    debug('メンバー一覧ページへ遷移します');
                    header("Location:teamMemberList.php?t_id={$t_id}");
                }

            }catch(Exception $e){
                error_log('エラー発生：' . $e->getMessage());
                $err_msg['common'] = MSG07;
              }
        }
    }
}

debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>

<?php
    $title = 'チームメンバー編集';
    $originalCss = "css/teamMemberEdit.css";
    require('head.php');
?>

    <body>

        <?php
            require('header.php');
        ?>
        <div id="main-2culum" class="site-width">

            <!-- Topに戻る -->
            <div class="p-scroll">
                <a href="#">Page Top<br><i class="fas fa-chevron-up"></i></a>
            </div><!-- /Topに戻る -->

            <div id="main-left">
                <section class="main-left-white">

                    <h2 class="section-title">メンバー追加・編集</h2>

                    <div class="team-info">
                        <div class="team-img"><img src="img/team_logo01.png" alt="チーム画像"></div>
                        <p class="team-name"><a href="teamDetail.php">名古屋ドラゴンズ</a></p>
                    </div>
                    <div class="btn btn-gray">
                        <a href="">Scout!ユーザーを登録する</a><!-- 後ほど実装 -->
                    </div>

                    <form action="" method="post" enctype="multipart/form-data">

                        <div class="area-msg">
                            <?php echo getErrMsg('common'); ?>
                        </div>

                        <div class="form-part">
                            <label for="m_name">
                                メンバー名<span class=form-info>※必須</span>
                            </label>
                            <div class="area-msg">
                                <?php echo getErrMsg('name'); ?>
                            </div>
                            <input type="text" name="m_name" id="m_name" placeholder="30文字以内" value="<?php if( !empty(getFormData('m_name')) ){ echo getFormData('m_name'); } ?>">
                        </div>

                        <div class="form-part">
                            <label for="m_number">
                                背番号<span class=form-info>※半角数字</span>
                            </label>
                            <div class="area-msg">
                                <?php echo getErrMsg('m_number'); ?>
                            </div>
                            <input type="number" name="m_number" id="m_number" value="<?php if( !empty(getFormData('m_number')) ){ echo getFormData('m_number'); } ?>">
                        </div>

                        <div class="form-part">
                            <label for="pit_bat_id">
                                投打
                            </label>
                            <div class="area-msg">
                                <?php echo getErrMsg('pit_bat_id'); ?>
                            </div>
                            <select name="pit_bat_id" id="pit_bat_id">
                                <?php
                                    if(empty($dbFormData['pit_bat_id'])){
                                        echo '<option value="" selected>--選択してください--</option>';
                                    }
                                ?>
                                <?php
                                    foreach($dbPitbatData as $key => $val) :
                                ?>
                                    <option value="<?php echo $val['id']; ?>" <?php if( !empty($dbFormData) && $val['id'] == $dbFormData['position_id']){ echo 'selected'; } ?>>
                                        <?php echo $val['name']; ?>
                                    </option>
                                <?php
                                    endforeach;
                                ?>
                            </select>
                        </div>

                        <div class="form-part">
                            <label for="position_id">
                                メインポジション
                            </label>
                            <div class="area-msg">
                                <?php echo getErrMsg('position_id'); ?>
                            </div>
                            <select name="position_id" id="position_id">
                                <?php
                                    if(empty($dbFormData['position_id'])){
                                        echo '<option value="" selected>--選択してください--</option>';
                                    }
                                ?>
                                <?php
                                    foreach($dbPositionData as $key => $val) :
                                ?>
                                    <option value="<?php echo $val['id']; ?>" <?php if( !empty($dbFormData) && $val['id'] == $dbFormData['position_id']){ echo 'selected'; } ?>>
                                        <?php echo $val['name']; ?>
                                    </option>
                                <?php
                                    endforeach;
                                ?>
                            </select>
                        </div>

                        <div class="form-part">
                            <label for="level_id">
                                プレーヤーレベル
                            </label>
                            <div class="area-msg">
                                <?php echo getErrMsg('level_id'); ?>
                            </div>
                            <select name="level_id" id="level_id">
                                <?php
                                    if(empty($dbFormData['level_id'])){
                                        echo '<option value="" selected>--選択してください--</option>';
                                    }
                                ?>
                                <?php
                                    foreach($dbLevelData as $key => $val) :
                                ?>
                                    <option value="<?php echo $val['id']; ?>" <?php if( !empty($dbFormData) && $val['id'] == $dbFormData['level_id']){ echo 'selected'; } ?>>
                                        LEVEL <?php echo $val['id']; ?> : <?php echo $val['name']; ?>
                                    </option>
                                <?php
                                    endforeach;
                                ?>
                            </select>
                        </div>

                        <div class="form-part">
                            <label for="m_pic">
                                プレーヤー画像
                            </label>
                            <div class="area-msg">
                                <?php echo getErrMsg('pic'); ?>
                            </div>
                            <label class="area-drop">
                                <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                                <input type="file" name="pic" id="pic" class="input-file" style="height:370px;">
                                <img src="<?php if(!empty(getFormData('pic'))){ echo getFormData('pic');} ?>" alt="" class="prev-img" style="<?php if(empty(getFormData('pic'))) echo 'display:none;'; ?>">
                                  ドラッグ＆ドロップ
                            </label>

                        </div>

                        <input type="submit" value="メンバー情報保存" class="btn btn-gray">

                    </form>
                </section>
            </div>

            <?php
                require('sidebar.php');
            ?>

        </div>

        <?php
            require('footer.php');
        ?>