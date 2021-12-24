<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「メンバー募集作成・編集ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//=====================
//画面処理
//=====================

//曜日フラグの表示、取得で使用
$days = array('mon' => '月', 'tue' => '火', 'wed' => '水', 'thu' => '木', 'fri' => '金', 'sat' => '土', 'sun' => '日');

//GETデータを格納
$b_id = (!empty($_GET['b_id'])) ? $_GET['b_id'] : '';
debug('募集ID：'.$b_id);


//DBから募集データを取得
$dbFormData = (!empty($b_id)) ? getOneMemBoard($b_id) : '';
debug('フォーム用DB募集データ：'.print_r($dbFormData,true));


//新規登録画面か編集画面か判別用フラグ
$edit_flg = (empty($dbFormData)) ? 0 : 1;
debug('判別フラグ：'. $edit_flg);

//パラメータ改ざんチェック
//====================
//GETパラメータはあるが改竄されている（URLをいじった）場合、正しいチームデータが取れないのでメンバー募集検索ページへ遷移
if(!empty($b_id) && empty($dbFormData)){
    //b_idをいじってあった場合
    debug('GETパラメータのチームIDが違います。メンバー募集検索ページへ遷移します。');
    header("Location:memberRecruit.php");
    exit();
}
if(!empty($dbFormData) && $dbFormData['host_user_id'] !== $_SESSION['user_id']){
    //ログイン中のユーザーと掲示板を作成したユーザーが違う場合
    debug('不正な操作が行われた可能性があります。メンバー募集検索ページへ遷移します。');
    header("Location:memberRecruit.php");
    exit();
}

//DBからチーム種類、レベル、頻度のデータを取得
$dbCategoryData = getCategory();
$dbLevelData = getLevel();
$dbFrequencyData = getFrequency();


//DBからユーザーがホストのチームを取得
$dbMyTeam = getMyTeam($_SESSION['user_id']);
debug('現在のユーザーがホストのチーム：'. print_r($dbMyTeam, true));

//POST送信されているかチェック
if(!empty($_POST)){
    debug('POST送信があります。');
    debug('POST情報：' . print_r($_POST,true));

    //入力されたユーザーからの情報を変数に代入
    $team_id = $_POST['team_id'];
    $title = $_POST['title'];
    $pref = $_POST['prefectures'];
    $city= $_POST['city'];
    $category = (!empty($_POST['category_id'])) ? $_POST['category_id'] : 0;
    $level = (!empty($_POST['level_id'])) ? $_POST['level_id'] : 0;
    $mon = (!empty($_POST['mon'])) ? 1 : 0;
    $tue = (!empty($_POST['tue'])) ? 1 : 0;
    $wed = (!empty($_POST['wed'])) ? 1 : 0;
    $thu = (!empty($_POST['thu'])) ? 1 : 0;
    $fri = (!empty($_POST['fri'])) ? 1 : 0;
    $sat = (!empty($_POST['sat'])) ? 1 : 0;
    $sun = (!empty($_POST['sun'])) ? 1 : 0;
    $frequency = (!empty($_POST['frequency_id'])) ? $_POST['frequency_id'] : 0;
    $comment = $_POST['comment'];

    //バリデーションチェック
    //未入力
    validRequired($team_id, 'team_id');
    validRequired($title, 'title');
    validRequired($pref, 'prefectures');
    validRequired($city, 'city');

    if(empty($err_msg)){
        debug('未入力チェックOK');

        //バリデーション続き
        //タイトル、コメント・最大文字数
        validMaxLen($title, 'title', 30);
        validMaxLen($comment, 'comment');

        if(empty($err_msg)){
            debug('バリデーションチェックOK');

            //DB新規登録または更新
            try{
                $dbh = dbConnect();

                switch($edit_flg){
                    case 0:
                        debug('新規登録です');
                        $sql = 'INSERT INTO mem_rec_board (team_id, host_user_id, title, prefectures, city, category_id, level_id, flg_mon, flg_tue, flg_wed, flg_thu, flg_fri, flg_sat, flg_sun, frequency_id, comment, create_date)
                                VALUES (:team_id, :host_user_id, :title, :prefectures, :city, :category_id, :level_id, :flg_mon, :flg_tue, :flg_wed, :flg_thu, :flg_fri, :flg_sat, :flg_sun, :frequency_id, :comment, :create_date)';
                        $data = array(':team_id' => $team_id, ':host_user_id' => $_SESSION['user_id'], ':title' => $title, ':prefectures' => $pref, ':city' => $city, ':category_id' => $category, ':level_id' => $level,
                                ':flg_mon' => $mon, ':flg_tue' => $tue, ':flg_wed' => $wed, ':flg_thu' => $thu, ':flg_fri' => $fri, ':flg_sat' => $sat, ':flg_sun' => $sun, ':frequency_id' => $frequency, ':comment' => $comment, ':create_date' => date('Y-m-d H:i:s'));

                                debug('SQL:' . $sql);
                                debug('流し込みデータ：' . print_r($data,true));

                                //SQL実行
                                $stmt = queryPost($dbh, $sql, $data);
                                $b_id = $dbh->lastInsertId();

                                debug('$b_id:'. print_r($b_id, true));
                        break;
                    case 1:
                        debug('DB更新です');
                        $sql = 'UPDATE mem_rec_board SET team_id = :team_id, title = :title, prefectures = :prefectures, city = :city, category_id = :category_id, level_id = :level_id,
                                flg_mon = :flg_mon, flg_tue = :flg_tue, flg_wed = :flg_wed, flg_thu = :flg_thu, flg_fri = :flg_fri, flg_sat = :flg_sat, flg_sun = :flg_sun, frequency_id = :frequency_id, comment = :comment WHERE id = :b_id';
                        $data = array(':team_id' => $team_id, ':title' => $title, ':prefectures' => $pref, ':city' => $city, ':category_id' => $category, ':level_id' => $level,
                                ':flg_mon' => $mon, ':flg_tue' => $tue, ':flg_wed' => $wed, ':flg_thu' => $thu, ':flg_fri' => $fri, ':flg_sat' => $sat, ':flg_sun' => $sun, ':frequency_id' => $frequency, ':comment' => $comment,':b_id' => $b_id);

                                debug('SQL:' . $sql);
                                debug('流し込みデータ：' . print_r($data,true));
                                //SQL実行
                                $stmt = queryPost($dbh, $sql, $data);
                        break;
                }

                //クエリ成功の場合
                if($stmt){
                    $_SESSION['msg_success'] = SUC07;
                    //メンバー一覧画面へ遷移
                    debug('募集詳細ページへ遷移します');
                    header("Location:memberRecruitDetail.php?b_id={$b_id}");
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
    $title = 'メンバー募集';
    $originalCss = "css/makeRecruit.css";
    require('head.php');
?>

    <body>

        <?php require('header.php'); ?>

        <div id="main-2culum" class="site-width">
            <div id="main-left">
                <section class="main-left-white">

                    <h2 class="section-title">メンバーを募集</h2>

                    <!-- Topに戻る -->
                    <div class="p-scroll">
                        <a href="#">Page Top<br><i class="fas fa-chevron-up"></i></a>
                    </div><!-- /Topに戻る -->

                    <form action="" method="post">

                        <div class="area-msg">
                            <?php echo getErrMsg('common'); ?>
                        </div>

                        <div class="form-part">
                            <label for="team_id">
                                自チーム<span class="form-info">※必須</span>
                            </label>
                            <div class="area-msg">
                                <?php echo getErrMsg('team_id'); ?>
                            </div>
                            <select name="team_id" id="team_id">
                                <option value="" <?php if(empty($dbMyTeam)) echo 'selected'; ?>>--選択してください--</option>
                                <?php
                                    foreach($dbMyTeam as $key => $val) :
                                ?>
                                    <option value="<?php echo $val['id']; ?>" <?php if($dbFormData['team_id'] === $val['id'] || $_POST['team_id'] === $val['id']){ echo 'selected'; } ?>>
                                        <?php echo $val['team_name']; ?>
                                    </option>
                                <?php
                                    endforeach;
                                ?>
                            </select>
                        </div>

                        <div class="form-part">
                            <label for="title">
                                募集タイトル<span class="form-info">※必須</span>
                            </label>
                            <div class="area-msg">
                                <?php echo getErrMsg('title'); ?>
                            </div>
                            <input type="text" name="title" id="title" placeholder="40文字以内" value="<?php echo getFormData('title'); ?>">
                        </div>

                        <div class="form-part">
                            <label for="prefectures">
                                エリア<span class="form-info">※必須</span>
                            </label>
                            <div class="area-msg">
                                <?php echo getErrMsg('prefectures'); ?>
                            </div>
                            <select id="prefectures" name="prefectures">
                                <?php
                                    echo '<option value='.getFormData('prefectures').' selected>'.getFormData('prefectures').'</option>';
                                ?>
                            </select>
                            <div class="area-msg">
                                <?php echo getErrMsg('city'); ?>
                            </div>
                            <select id="city" name="city" onFocus="change()">
                                <?php
                                    echo '<option value='.getFormData('city').' selected>'.getFormData('city').'</option>';
                                ?>
                            </select>
                        </div>

                        <div class="form-part">
                            <label for="category_id">
                                チーム種類
                            </label>
                            <select name="category_id" id="category_id">
                                <option value="" <?php if(empty($dbFormData['category_id'])){ echo 'selected'; } ?>>--選択してください--</option>
                                <?php
                                    foreach($dbCategoryData as $key => $val) :
                                ?>
                                    <option value="<?php echo $val['id']; ?>" <?php if($dbFormData['category_id'] === $val['id'] || $_POST['category_id'] === $val['id']){ echo 'selected'; } ?>>
                                        <?php echo $val['name']; ?>
                                    </option>
                                <?php
                                    endforeach;
                                ?>
                            </select>
                        </div>

                        <div class="form-part">
                            <label for="level_id">
                                募集プレーヤーレベル
                            </label>

                            <select name="level_id" id="level_id">
                                <option value="" <?php if(empty($dbFormData['level_id'])){ echo 'selected'; } ?>>--選択してください--</option>
                                <?php
                                    foreach($dbLevelData as $key => $val) :
                                ?>
                                    <option value="<?php echo $val['id']; ?>" <?php if($dbFormData['level_id'] === $val['id'] || $_POST['level_id'] === $val['id']){ echo 'selected'; } ?>>
                                        LEVEL <?php echo $val['id']; ?> : <?php echo $val['name']; ?> 以上
                                    </option>
                                <?php
                                    endforeach;
                                ?>
                            </select>
                        </div>

                        <div class="form-part">
                            <label for="">
                                活動曜日
                            </label>
                            <div class="checkbox-container">
                                <?php foreach($days as $key => $val): ?>
                                    <div>
                                        <input type="checkbox" id="<?php echo $key ?>" name="<?php echo $key ?>" <?php if($dbFormData["flg_{$key}"] == 1 || $_POST["flg_{$key}"] == 1){ echo 'checked'; } ?> value=1>
                                        <label class="label-radio" for="<?php echo $key ?>"><?php echo $val ?></label>
                                    </div>
                                <?php endforeach ; ?>
                                
                            </div>
                        </div>

                        <div class="form-part">
                            <label for="frequency_id">
                                活動頻度
                            </label>
                            <select name="frequency_id" id="frequency_id">
                                <option value="" <?php if(empty($dbFormData['frequency_id'])){ echo 'selected'; } ?>>--選択してください--</option>
                                <?php
                                    foreach($dbFrequencyData as $key => $val) :
                                ?>
                                    <option value="<?php echo $val['id']; ?>" <?php if($dbFormData['frequency_id'] === $val['id'] || $_POST['frequency_id'] === $val['id']){ echo 'selected'; } ?>>
                                        <?php echo $val['name']; ?>
                                    </option>
                                <?php
                                    endforeach;
                                ?>
                            </select>
                        </div>

                        <div class="form-part">
                            <label for="comment">
                                コメント<span class="area-msg">
                            </label>
                            <div class="area-msg">
                                <?php echo getErrMsg('comment'); ?>
                            </div>
                            <textarea name="comment" id="comment" cols="30" rows="10"><?php echo getFormData('comment'); ?></textarea>
                        </div>

                        <input type="submit" value="募集を投稿する" class="btn btn-gray">
                    </form>
                </section>
            </div>

            <?php require('sidebar.php');?>

        </div>

        <?php require('footer.php'); ?>