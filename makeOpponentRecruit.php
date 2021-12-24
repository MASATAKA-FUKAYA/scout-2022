<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「対戦相手募集作成・編集ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//=====================
//画面処理
//=====================

//GETデータを格納
$b_id = (!empty($_GET['b_id'])) ? $_GET['b_id'] : '';
debug('募集ID：'.$b_id);

//DBからチームデータを取得
$dbFormData = (!empty($b_id)) ? getOneOppBoard($b_id) : '';
debug('フォーム用DB募集データ：'.print_r($dbFormData,true));


//新規登録画面か編集画面か判別用フラグ
$edit_flg = (empty($dbFormData)) ? 0 : 1;
debug('判別フラグ：'. $edit_flg);

//パラメータ改ざんチェック
//====================
//GETパラメータはあるが改竄されている（URLをいじった）場合、正しいチームデータが取れないのでメンバー募集検索ページへ遷移
if(!empty($b_id) && empty($dbFormData)){
    //b_idをいじってあった場合
    debug('GETパラメータのチームIDが違います。対戦相手募集検索ページへ遷移します。');
    header("Location:opponentRecruit.php");
    exit();
}
if(!empty($dbFormData) && $dbFormData['host_user_id'] !== $_SESSION['user_id']){
    //ログイン中のユーザーと掲示板を作成したユーザーが違う場合
    debug('不正な操作が行われた可能性があります。対戦相手募集検索ページへ遷移します。');
    header("Location:opponentRecruit.php");
    exit();
}

//DBからチーム種類、レベル、頻度、審判のデータを取得
$dbCategoryData = getCategory();
$dbLevelData = getLevel();
$dbFrequencyData = getFrequency();
$dbUmpireData = getUmpire();

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
    $r_year = $_POST['r_year'];
    $r_month = $_POST['r_month'];
    $r_day = $_POST['r_day'];
    $pref = $_POST['prefectures'];
    $city= $_POST['city'];
    $category = (!empty($_POST['category_id'])) ? $_POST['category_id'] : null;
    $g_flg = (int)$_POST['g_flg'];
    $g_name = $_POST['g_name'];
    $level_low = (!empty($_POST['level_id_low'])) ? $_POST['level_id_low'] : null;
    $level_high = (!empty($_POST['level_id_high'])) ? $_POST['level_id_high'] : null;
    $umpire = (!empty($_POST['umpire_id'])) ? $_POST['umpire_id'] : null;
    $all_attack = (!empty($_POST['all_attack_flg'])) ? 1 : 0;
    $re_enter = (!empty($_POST['re_enter_flg'])) ? 1 : 0;
    $lend_member = (!empty($_POST['lend_member_flg'])) ? 1 : 0;
    $pay_all = (!empty($_POST['pay_all_flg'])) ? 1 : 0;
    $comment = $_POST['comment'];

    //バリデーションチェック
    //未入力
    validRequired($team_id, 'team_id');
    validRequired($title, 'title');
    validRequired($r_year, 'r_year');
    validRequired($r_month, 'r_month');
    validRequired($r_day, 'r_day');
    validRequired($pref, 'prefectures');
    validRequired($city, 'city');
    //g_flgは入力必須であるが、必ずどちらかは選択されるので未入力バリデーションは行わない

    if($g_flg === 1 && empty($g_name)){
        $err_msg['g_name'] = 'グラウンド名を入力してください';
    }

    if(empty($err_msg)){
        debug('未入力チェックOK');

        //バリデーション続き
        //タイトル、グラウンド、コメント・最大文字数
        validMaxLen($title, 'title', 50);
        validMaxLen($g_name, 'g_name');
        validMaxLen($comment, 'comment');

        if(empty($err_msg)){
            debug('バリデーションチェックOK');

            //DB新規登録または更新
            try{
                $dbh = dbConnect();

                switch($edit_flg){
                    case 0:
                        debug('新規登録です');
                        $sql = 'INSERT INTO opp_rec_board (team_id, host_user_id, title, r_year, r_month, r_day, prefectures, city, category_id, g_flg, g_name, level_id_low, level_id_high, umpire_id, all_attack_flg, re_enter_flg, lend_member_flg, pay_all_flg, comment, create_date)
                                VALUES (:team_id, :host_user_id, :title, :r_year, :r_month, :r_day, :prefectures, :city, :category_id, :g_flg, :g_name, :level_id_low, :level_id_high, :umpire_id, :all_attack_flg, :re_enter_flg, :lend_member_flg, :pay_all_flg, :comment, :create_date)';
                        $data = array(':team_id' => $team_id, ':host_user_id' => $_SESSION['user_id'], ':title' => $title, ':r_year' => $r_year, ':r_month' => $r_month, ':r_day' => $r_day, ':prefectures' => $pref, ':city' => $city, ':category_id' => $category, ':g_flg' => $g_flg, ':g_name' => $g_name, ':level_id_low' => $level_low, ':level_id_high' => $level_high,
                                ':umpire_id' => $umpire, ':all_attack_flg' => $all_attack, ':re_enter_flg' => $re_enter, ':lend_member_flg' => $lend_member, ':pay_all_flg' => $pay_all, ':comment' => $comment, ':create_date' => date('Y-m-d H:i:s'));

                                debug('SQL:' . $sql);
                                debug('流し込みデータ：' . print_r($data,true));

                                //SQL実行
                                $stmt = queryPost($dbh, $sql, $data);
                                $b_id = $dbh->lastInsertId();

                                debug('$b_id:'. print_r($b_id, true));
                        break;
                    case 1:
                        debug('DB更新です');
                        $sql = 'UPDATE opp_rec_board SET team_id = :team_id, title = :title, r_year = :r_year, r_month = :r_month, r_day = :r_day, prefectures = :prefectures, city = :city, category_id = :category_id, g_flg = :g_flg, g_name = :g_name, level_id_low = :level_id_low, level_id_high = :level_id_high,
                                umpire_id = :umpire_id, all_attack_flg = :all_attack_flg, re_enter_flg = :re_enter_flg, lend_member_flg = :lend_member_flg, pay_all_flg = :pay_all_flg, comment = :comment WHERE id = :b_id';
                        $data = array(':team_id' => $team_id, ':title' => $title, ':r_year' => $r_year, ':r_month' => $r_month, ':r_day' => $r_day, ':prefectures' => $pref, ':city' => $city, ':category_id' => $category, ':g_flg' => $g_flg, ':g_name' => $g_name, ':level_id_low' => $level_low, ':level_id_high' => $level_high,
                                ':umpire_id' => $umpire, ':all_attack_flg' => $all_attack, ':re_enter_flg' => $re_enter, ':lend_member_flg' => $lend_member, ':pay_all_flg' => $pay_all, ':comment' => $comment,':b_id' => $b_id);

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
                    header("Location:opponentRecruitDetail.php?b_id={$b_id}");
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
    $title = '対戦相手募集';
    $originalCss = "css/makeRecruit.css";
    require('head.php');
?>

    <body>

        <?php require('header.php'); ?>

        <div id="main-2culum" class="site-width">
            <div id="main-left">
                <section class="main-left-white">

                    <h2 class="section-title">対戦相手募集</h2>

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
                            <input type="text" name="title" id="title" placeholder="50文字以内" value="<?php echo getFormData('title'); ?>">
                        </div>

                        <div class="form-part">
                            <label for="r_year">
                                試合日程<span class="form-info">※必須</span>
                            </label>
                            <div class="area-msg">
                                <?php echo getErrMsg('r_year'); ?>
                            </div>
                            <select name="r_year" id="r_year" class="js-changeYear">
                                <?php
                                    $i = 2021;
                                    while($i <= 2025) :
                                ?>
                                    <option value="<?php echo $i; ?>" <?php if($i == getFormData('r_year')){ echo 'selected'; } ?>><?php echo $i; ?></option>
                                <?php
                                    $i++;
                                    endwhile;
                                ?>
                            </select>
                            <span>年</span>
                            <div class="area-msg">
                                <?php echo getErrMsg('r_month'); ?>
                            </div>
                            <select name="r_month" id="r_month" class="js-changeMonth">
                                <option value="" <?php if(empty($dbFormData['r_month'])){ echo 'selected'; } ?>>--</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                                <option value="8">8</option>
                                <option value="9">9</option>
                                <option value="10">10</option>
                                <option value="11">11</option>
                                <option value="12">12</option>
                            </select>
                            <span>月</span>
                            <div class="area-msg">
                                <?php echo getErrMsg('r_day'); ?>
                            </div>
                            <select name="r_day" id="r_day" class="js-changeDay">
                                <option value="">--</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                                <option value="8">8</option>
                                <option value="9">9</option>
                                <option value="10">10</option>
                                <option value="11">11</option>
                                <option value="12">12</option>
                                <option value="13">13</option>
                                <option value="14">14</option>
                                <option value="15">15</option>
                                <option value="16">16</option>
                                <option value="17">17</option>
                                <option value="18">18</option>
                                <option value="19">19</option>
                                <option value="20">20</option>
                                <option value="21">21</option>
                                <option value="22">22</option>
                                <option value="23">23</option>
                                <option value="24">24</option>
                                <option value="25">25</option>
                                <option value="26">26</option>
                                <option value="27">27</option>
                                <option value="28">28</option>
                                <option value="29">29</option>
                                <option value="30">30</option>
                                <option value="31">31</option>
                            </select>
                            <span>日</span>
                        </div>

                        <div class="form-part">
                            <label for="prefectures">
                                エリア<span class="form-info">※必須</span>
                            </label>
                            <div class="area-msg">
                                <?php echo getErrMsg('prefectures'); ?>
                            </div>
                            <select id="prefectures" name="prefectures">
                            </select>
                            <div class="area-msg">
                                <?php echo getErrMsg('city'); ?>
                            </div>
                            <select id="city" name="city" onFocus="change()">
                                <!-- jsの関係で、cityの方は先にoptionタグを作っておく -->
                                <?php
                                    $cityData = getFormData('city');
                                    if(!empty($cityData)) :
                                ?>
                                    <option value="<?php echo $cityData; ?>" selected><?php echo $cityData; ?></option>
                                <?php endif; ?>
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
                            <label for="g_name">
                                グラウンド
                            </label>
                            <div class="area-msg">
                                <?php echo getErrMsg('g_flg'); ?>
                            </div>
                            <input type="radio" id="yes" name="g_flg" value="1" checked>
                            <label class="label-radio" for="yes">有り</label>

                            <input type="radio" id="no" name="g_flg" value="0" <?php if(getFormData('g_flg') == 0){ echo 'checked'; } ?>>
                            <label class="label-radio" for="no">無し<span class="form-info">※必須</span></label>

                            <div class="area-msg">
                                <?php echo getErrMsg('g_name'); ?>
                            </div>
                            <input type="text" name="g_name" id="g_name" placeholder="場所：" value=<?php echo getFormData('g_name'); ?>>
                        </div>

                        <div class="form-part">
                            <label for="level_id_low">
                                募集対戦相手レベル
                            </label>

                            <select name="level_id_low" id="level_id_low">
                                <option value="" <?php if(empty($dbFormData['level_id_low'])){ echo 'selected'; } ?>>--選択してください--</option>
                                <?php
                                    foreach($dbLevelData as $key => $val) :
                                ?>
                                    <option value="<?php echo $val['id']; ?>" <?php if($dbFormData['level_id_low'] === $val['id'] || $_POST['level_id_low'] === $val['id']){ echo 'selected'; } ?>>
                                        LEVEL <?php echo $val['id']; ?> : <?php echo $val['name']; ?>中心
                                    </option>
                                <?php
                                    endforeach;
                                ?>
                            </select>
                            <span class="t-level-text">から</span>
                            <select name="level_id_high" id="level_id_high">
                                <option value="" <?php if(empty($dbFormData['level_id_high'])){ echo 'selected'; } ?>>--選択してください--</option>
                                <?php
                                    foreach($dbLevelData as $key => $val) :
                                ?>
                                    <option value="<?php echo $val['id']; ?>" <?php if($dbFormData['level_id_high'] === $val['id'] || $_POST['level_id_high'] === $val['id']){ echo 'selected'; } ?>>
                                        LEVEL <?php echo $val['id']; ?> : <?php echo $val['name']; ?>中心
                                    </option>
                                <?php
                                    endforeach;
                                ?>
                            </select>
                            <span class="t-level-text">まで</span>
                        </div>

                        <div class="form-part">
                            <label for="umpire_id">
                                審判
                            </label>
                            <select name="umpire_id" id="umpire_id">
                                <option value="" <?php if(empty($dbFormData['umpire_id'])){ echo 'selected'; } ?>>--選択してください--</option>
                                <?php
                                    foreach($dbUmpireData as $key => $val) :
                                ?>
                                    <option value="<?php echo $val['id']; ?>" <?php if($dbFormData['umpire_id'] === $val['id'] || $_POST['umpire_id'] === $val['id']){ echo 'selected'; } ?>>
                                        <?php echo $val['name_jpn']; ?>
                                    </option>
                                <?php
                                    endforeach;
                                ?>
                            </select>
                        </div>

                        <div class="form-part">
                            <label for="">
                                その他
                            </label>
                            <div>
                                <input type="checkbox" id="all_attack" name="all_attack_flg" <?php if(!empty($dbFormData['all_attack_flg']) || !empty($_POST['all_attack_flg'])){ echo 'checked'; } ?>>
                                <label class="label-radio" for="all_attack">全員打撃可</label>
                            </div>
                            <div>
                                <input type="checkbox" id="re_enter" name="re_enter_flg" <?php if(!empty($dbFormData['re_enter_flg']) || !empty($_POST['re_enter_flg'])){ echo 'checked'; } ?>>
                                <label class="label-radio" for="re_enter">再出場可</label>
                            </div>
                            <div>
                                <input type="checkbox" id="lend_member" name="lend_member_flg" <?php if(!empty($dbFormData['lend_member_flg']) || !empty($_POST['lend_member_flg'])){ echo 'checked'; } ?>>
                                <label class="label-radio" for="lend_member">メンバー貸し出し可</label>
                            </div>
                            <div>
                                <input type="checkbox" id="pay_all" name="pay_all_flg" <?php if(!empty($dbFormData['pay_all_flg']) || !empty($_POST['pay_all_flg'])){ echo 'checked'; } ?>>
                                <label class="label-radio" for="pay_all">費用は主催者負担</label>
                            </div>
                        </div>

                        <div class="form-part">
                            <label for="comment">
                                コメント
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

            <?php require('sidebar.php'); ?>

        </div>

        <?php require('footer.php'); ?>