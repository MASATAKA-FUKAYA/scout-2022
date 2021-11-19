<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「プロフ編集ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//=====================
//画面処理
//=====================

//DBからユーザー情報、投打、ポジション、レベル情報を取得
$dbFormData = getUser($_SESSION['user_id']);
$dbPitbatData = getPitbat();
$dbPositionData = getPosition();
$dbLevelData = getLevel();
debug('取得したユーザーDBデータ：'. print_r($dbFormData, true));
debug('取得した投打DBデータ：'. print_r($dbPitbatData, true));
debug('取得したポジションDBデータ：'. print_r($dbPositionData, true));
debug('取得したレベルDBデータ：'. print_r($dbLevelData, true));


//POSTされているかチェック
if(!empty($_POST)){
    debug('POST送信があります。');
    debug('POST情報：'.print_r($_POST,true));
    debug('FILE情報：'.print_r($_FILES,true));
    var_dump($_POST);
    var_dump($_FILES);

    //POSTされてきた情報を代入
    $u_name = $_POST['u_name'];
    $email = $_POST['email'];
    $pit_bat = $_POST['pit_bat_id'];
    $position = $_POST['position_id'];
    $level_id = $_POST['level_id'];
    //画像をアップロードし、パスを格納
    $pic = (!empty($_FILES['pic']['name'])) ? upLoadImg($_FILES['pic'], 'pic') : '';
    //画像をpostしていない（今回登録していない）が既にDBに登録されている場合、DBのパスを入れる（postに反映されないので）
    $pic = (empty($_FILES['pic']) && !empty($dbFormData['pic'])) ? $dbFormData['pic'] : $pic;

    //DBの情報とPOSTされた情報を比べて、違いがあればバリデーションチェック
    if($dbFormData['u_name'] !== $u_name){
        //最大文字数
        validMaxLen($u_name, 'u_name');
    }
    if($dbFormData['email'] !== $email){
        //最大文字数、形式、重複チェック
        validMaxLen($email, 'email');
        validEmail($email, 'email');
        validEmailDup($email);
    }

    if(empty($err_msg)){
        debug('バリデーションOKです');

        try{
            //DB接続
            $dbh = dbConnect();
            //レコード更新
            $sql = 'UPDATE users SET u_name = :u_name, email = :email, pit_bat_id = :pit_bat_id, position_id = :position_id, level_id = :level_id, pic = :pic WHERE id = :id';
            $data = array(':u_name' => $u_name, ':email' => $email, ':pit_bat_id' => $pit_bat, ':position_id' => $position, ':level_id' => $level_id, ':pic' => $pic, ':id' => $dbFormData['id']);

            $stmt = queryPost($dbh, $sql, $data);

            if($stmt){
                $_SESSION['msg_success'] = SUC02;
                //マイページへ遷移
                debug('マイページへ遷移します。');
                header("Location:mypage.php");
            }
        }catch(Exception $e){
            error_log('エラー発生:' . $e->getMessage());
            $err_msg['common'] = MSG07;
        }
    }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>

<?php
$title = 'プロフ編集';
require('head.php');
?>

    <body>
        <?php require('header.php'); ?>

        <div id="main-2culum" class="site-width">
            <div id="main-left">
                <section class="main-left-white">
                    <h2 class="section-title">プロフィール編集</h2>

                    <!-- Topに戻る -->
                    <div class="p-scroll">
                        <a href="#">Page Top<br><i class="fas fa-chevron-up"></i></a>
                    </div><!-- /Topに戻る -->

                    <form action="" method="post" enctype="multipart/form-data">

                        <div class="area-msg">
                            <?php echo getErrMsg('common'); ?>
                        </div>

                        <div class="form-part">
                            <label for="u_name">
                                ニックネーム
                            </label>
                            <div class="area-msg">
                                <?php echo getErrMsg('u_name'); ?>
                            </div>
                            <input type="text" name="u_name" id="u_name" value="<?php if( !empty(getFormData('u_name')) ){ echo getFormData('u_name'); } ?>">
                        </div>

                        <div class="form-part">
                            <label for="email">
                                Email
                            </label>
                            <div class="area-msg">
                                <?php echo getErrMsg('email'); ?>
                            </div>
                            <input type="email" name="email" id="email" value="<?php if( !empty(getFormData('email')) ){ echo getFormData('email'); } ?>">
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
                                <?php foreach($dbPitbatData as $key => $val) : ?>
                                    <option value="<?php echo $val['id']; ?>" <?php if($val['id'] == $dbFormData['pit_bat_id']){ echo 'selected'; } ?>>
                                        <?php echo $val['name']; ?>
                                    </option>
                                <?php endforeach; ?>
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
                                <?php foreach($dbPositionData as $key => $val) : ?>
                                    <option value="<?php echo $val['id']; ?>" <?php if($val['id'] == $dbFormData['position_id']){ echo 'selected'; } ?>>
                                        <?php echo $val['name']; ?>
                                    </option>
                                <?php endforeach; ?>
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
                                <?php foreach($dbLevelData as $key => $val) : ?>
                                    <option value="<?php echo $val['id']; ?>" <?php if($val['id'] == $dbFormData['level_id']){ echo 'selected'; } ?>>
                                        LEVEL <?php echo $val['id']; ?> : <?php echo $val['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-part">
                            <label for="pic">
                                プロフィール画像
                            </label>
                            <div class="area-msg">
                                <?php echo getErrMsg('pic'); ?>
                            </div>
                            <label class="area-drop">
                                <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                                <input type="file" name="pic" id="pic" class="input-file">
                                    <img src="<?php if(!empty(getFormData('pic'))){ echo getFormData('pic');} ?>" alt="" class="prev-img" style="<?php if(empty(getFormData('pic'))) echo 'display:none;'; ?>">
                                    ドラッグ&ドロップ
                            </label>
                        </div>
                        <!-- 所属チーム追加はマイページから -->
                        <input type="submit" value="プロフィール変更" class="btn btn-gray">
                    </form>
                </section>
            </div>

            <?php require('sidebar.php'); ?>
        </div>

        <?php require('footer.php'); ?>


