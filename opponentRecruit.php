<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「対戦相手募集検索ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証なし

//=====================
//画面処理
//=====================

//DBからチーム種類、レベル、頻度、審判のデータを取得
$dbCategoryData = getCategory();
$dbLevelData = getLevel();
$dbUmpireData = getUmpire();

//foreachで引っかかるので先に宣言
$result = '';

//POSTされているかチェック
if(!empty($_POST)){
    debug('POST送信があります');
    debug('デバッグ：$_POST'. print_r($_POST,true));

    //POST情報を変数へ代入
    $t_name = $_POST['team_name'];
    $pref = $_POST['prefectures'];
    $city = $_POST['city'];
    $category = $_POST['category_id'];
    $g_flg = (!empty($_POST['g_flg'])) ? 1 : 0;
    $g_name = $_POST['g_name'];
    $level_low = $_POST['level_id_low'];
    $level_high = $_POST['level_id_high'];
    $r_year = $_POST['r_year'];
    $r_month = $_POST['r_month'];
    $r_day = $_POST['r_day'];
    $umpire = $_POST['umpire_id'];

    //SQL文のforeach用
    $options = array('all_attack', 're_enter', 'lend_member', 'pay_all');

    //バリデーション
    //今回は未入力はなし
    //チーム名、グラウンド名・最大文字数
    if(!empty($t_name)){
        validMaxLen($t_name, 'team_name', $max = 50);
    }
    if(!empty($g_name)){
        validMaxLen($g_name, 'g_name', $max = 50);
    }

    if(empty($err_msg)){
        debug('バリデーションチェックOK');

        //DB接続
        try{
            //SELECT文、if文で検索を絞っていく
            $dbh = dbConnect();
            $sql = 'SELECT * FROM opp_rec_board WHERE delete_flg = 0';
            $data = array();

            if(!empty($t_name)){
                $sql .= ' AND team_name = :team_name';
                $data[':team_name'] = $t_name;
            }
            if(!empty($pref)){
                $sql .= ' AND prefectures = :prefectures';
                $data[':prefectures'] = $pref;
                if(!empty($city)){
                    $sql .= ' AND city = :city';
                    $data[':city'] = $city;
                }
            }
            if(!empty($category)){
                $sql .= ' AND category_id = :category_id';
                $data[':category_id'] = $category;
            }
            if(!empty($g_flg)){
                $sql .= ' AND g_flg = :g_flg';
                $data[':g_flg'] = $g_flg;
                if(!empty($g_name)){
                    $sql .= ' AND g_name = :g_name';
                    $data[':g_name'] = $g_name;
                }
            }
            if(!empty($level_low)){
                $sql .= ' AND level_id_low >= :level_id_low';
                $data[':level_id_low'] = $level_low;
            }
            if(!empty($level_high)){
                $sql .= ' AND level_id_high <= :level_id_high';
                $data[':level_id_high'] = $level_high;
            }

            foreach($options as $key => $val){
                if(!empty($_POST["{$val}_flg"])){
                    $sql .= " AND {$val}_flg = :{$val}_flg";
                    $data[":{$val}_flg"] = 1;
                }
            }

            debug('SQL:' . $sql);
            debug('流し込みデータ：' . print_r($data,true));
            //SQL実行
            $stmt = queryPost($dbh, $sql, $data);

            //クエリ成功の場合
            if($stmt){
                //結果を変数に代入
                $result = $stmt->fetchAll();
                debug('$resultの中身：'. print_r($result,true));
            }else{
                $result = false;
            }

            //$resultで画面表示へ

        }catch(Exception $e){
            error_log('エラー発生：' . $e->getMessage());
            $err_msg['common'] = MSG07;
        }
    }
}

//$検索結果の曜日取得用=========================
$week = [
  '日', //0
  '月', //1
  '火', //2
  '水', //3
  '木', //4
  '金', //5
  '土', //6
];

debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>

<?php
    $title = '対戦相手検索';
    $originalCss = "css/makeRecruit.css";
    $originalCss2 = "css/opponentRecruit.css";
    require('head.php');
?>

    <body>

        <?php require('header.php'); ?>

        <div id="main-2culum" class="site-width">
            <div id="main-left">
                <section class="main-left-white">

                    <h2 class="section-title">対戦相手検索</h2>

                    <!-- Topに戻る -->
                    <div class="p-scroll">
                        <a href="#">Page Top<br><i class="fas fa-chevron-up"></i></a>
                    </div><!-- /Topに戻る -->

                    <form action="" method="post">

                        <div class="area-msg">
                            <?php echo getErrMsg('common'); ?>
                        </div>

                        <div class="form-part">
                            <label for="team_name">
                                チーム名
                            </label>
                            <div class="area-msg">
                                <?php echo getErrMsg('team_name'); ?>
                            </div>
                            <input type="text" name="team_name" id="team_name" placeholder="50文字以内" value="<?php if(!empty($_POST['team_name'])){ echo $_POST['team_name']; } ?>">
                        </div>

                        <div class="form-part">
                            <label for="prefectures">
                                エリア
                            </label>
                            <select id="prefectures" name="prefectures">
                            </select>
                            <select id="city" name="city" onFocus="change()">
                                <!-- jsの関係で、cityの方は先にoptionタグを作っておく -->
                                <?php if(!empty($_POST['city'])) : ?>
                                    <option value="<?php echo $_POST['city']; ?>" selected><?php echo $_POST['city']; ?></option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-part">
                            <label for="category_id">
                                チーム種類
                            </label>
                            <select name="category_id" id="category_id">
                                <option value="" selected>--選択してください--</option>
                                    <?php
                                        foreach($dbCategoryData as $key => $val) :
                                    ?>
                                        <option value="<?php echo $val['id']; ?>" <?php if( !empty($_POST['category_id']) && $_POST['category_id'] == $val['id']){ echo 'selected'; } ?>>
                                            <?php echo $val['name']; ?>
                                        </option>
                                    <?php
                                        endforeach;
                                    ?>
                            </select>
                        </div>

                        <div class="form-part">
                            <label for="yes">
                                グラウンド
                            </label>
                            <input type="radio" id="yes" name="g_flg" value="1" <?php if($_POST['g_flg'] == 1){ echo 'checked'; } ?>>
                            <label class="label-radio1" for="yes">有り</label>

                            <input type="radio" id="no" name="g_flg" value="0" <?php if($_POST['g_flg'] == 0){ echo 'checked'; } ?>>
                            <label class="label-radio2" for="no">無し</label>

                            <input type="text" name="g_name" id="g_name" placeholder="場所：50文字以内" value=<?php if(!empty($_POST['g_name'])){ echo ($_POST['g_name']); } ?>>
                        </div>

                        <div class="form-part">
                            <label for="t_level">
                                募集チームレベル
                            </label>
                            <select name="level_id_low" id="level_id_low">
                                <option value="" <?php if(empty($_POST['level_id_low'])){ echo 'selected';} ?>>--選択してください--</option>
                                <?php
                                    foreach($dbLevelData as $key => $val) :
                                ?>
                                    <option value="<?php echo $val['id']; ?>" <?php if($_POST['level_id_low'] == $val['id']){ echo 'selected'; } ?>>
                                        LEVEL <?php echo $val['id']; ?> : <?php echo $val['name']; ?>中心
                                    </option>
                                <?php
                                    endforeach;
                                ?>
                            </select>
                            <span class="t-level-text">から</span>
                            <select name="level_id_high" id="level_id_high">
                                <option value="" <?php if(empty($_POST['level_id_high'])){ echo 'selected';} ?>>--選択してください--</option>
                                <?php
                                    foreach($dbLevelData as $key => $val) :
                                ?>
                                    <option value="<?php echo $val['id']; ?>" <?php if($_POST['level_id_high'] == $val['id']){ echo 'selected'; } ?>>
                                        LEVEL <?php echo $val['id']; ?> : <?php echo $val['name']; ?>中心
                                    </option>
                                <?php
                                    endforeach;
                                ?>
                            </select>
                            <span class="t-level-text">まで</span>
                        </div>

                        <div class="form-part">
                            <label for="r_year">
                                試合日程
                            </label>
                            <select name="r_year" id="r_year" class="js-changeYear">
                                <?php
                                    $i = 2021;
                                    while($i <= 2025) :
                                ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                <?php
                                    $i++;
                                    endwhile;
                                ?>
                            </select>
                            <span>年</span>
                            <select name="r_month" id="r_month" class="js-changeMonth">
                                <option value="" <?php if(empty($_POST['r_month'])){ echo 'selected'; } ?>>--</option>
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
                                <input type="checkbox" id="all_attack" name="all_attack_flg" <?php if(!empty($_POST['all_attack_flg'])){ echo 'checked'; } ?>>
                                <label class="label-radio" for="all_attack">全員打撃可</label>
                            </div>
                            <div>
                                <input type="checkbox" id="re_enter" name="re_enter_flg" <?php if(!empty($_POST['re_enter_flg'])){ echo 'checked'; } ?>>
                                <label class="label-radio" for="re_enter">再出場可</label>
                            </div>
                            <div>
                                <input type="checkbox" id="lend_member" name="lend_member_flg" <?php if(!empty($_POST['lend_member_flg'])){ echo 'checked'; } ?>>
                                <label class="label-radio" for="lend_member">メンバー貸し出し可</label>
                            </div>
                            <div>
                                <input type="checkbox" id="pay_all" name="pay_all_flg" <?php if(!empty($_POST['pay_all_flg'])){ echo 'checked'; } ?>>
                                <label class="label-radio" for="pay_all">費用は主催者負担</label>
                            </div>
                        </div>

                        <input type="submit" value="募集を検索する" class="btn btn-gray">

                    </form>
                </section>

                <section id="search-result">
                    <div class="result-title">
                        <h3>チーム検索結果</h3>
                    </div>

                    <?php foreach($result as $key => $val) : ?>
                        <div class="result-container">
                            <section>
                                <a href="opponentRecruitDetail.php?b_id=<?php echo $val['id']; ?>">
                                    <?php
                                        //日付を指定
                                        $strtotime = $val['r_year']. $val['r_month']. $val['r_day'];
                                        //指定日の曜日を取得する
                                        $date = date('w', strtotime($strtotime));
                                        $week = ['日','月','火','水','木','金','土'];
                                    ?>
                                    <p class="opponent-rec-date"><?php echo $val['r_year']. '/'. $val['r_month']. '/'. $val['r_day']; ?>（<?php echo $week[$date]; ?>） 10:00~12:00</p>
                                    <h3 class="rec-title"><?php echo $val['title']; ?></h3>
                                    <p class="opponent-rec-text">エリア：<?php echo $val['prefectures']. $val['city']; ?></p>
                                </a>
                            </section>
                            <div class="result-wrapper">
                                <?php $teamData = getTeam($val['team_id']); ?>
                                    <a href="teamDetail.php?t_id=<?php echo $val['team_id']; ?>">
                                        <div class="result-img"><img src="<?php echo $teamData['pic']; ?>" alt="<?php echo $teamData['team_name']; ?>"></div>
                                        <div class="result-text">
                                            <h4><?php echo $teamData['team_name']; ?></h4>
                                            <p><?php echo getLevelName($teamData['level_id'])['name']; ?>中心/<?php echo getCategoryName($teamData['category_id'])['name']; ?></p>
                                        </div>
                                    </a>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!--  ページネーションをここに入れる　-->
                </section>

            </div>

            <?php require('sidebar.php'); ?>

        </div>

        <?php require('footer.php'); ?>