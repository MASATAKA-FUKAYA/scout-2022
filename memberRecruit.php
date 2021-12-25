<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「メンバー募集検索ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証なし

//=====================
//画面処理
//=====================

//DBからチーム種類、レベル、頻度のデータを取得
$dbCategoryData = getCategory();
$dbLevelData = getLevel();
$dbFrequencyData = getFrequency();

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
    $level_low = $_POST['level_id_low'];
    $level_high = $_POST['level_id_high'];
    $frequency = $_POST['frequency_id'];

    //SQL文のforeach、画面の活動曜日で使用
    $activeDays = array("mon" => '月', "tue" => '火', "wed" => '水', "thu" => '木', "fri" => '金', "sat" => '土', "sun" => '日');

    //バリデーション
    //今回は未入力はなし
    //チーム名、最大文字数
    if(!empty($t_name)){
       validMaxLen($t_name, 'team_name', $max = 30);
    }

    if(empty($err_msg)){
        debug('バリデーションチェックOK');

        //DB接続
        try{
            //SELECT文、if文で検索を絞っていく
            $dbh = dbConnect();
            $sql = 'SELECT * FROM mem_rec_board WHERE delete_flg = 0';
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
            if(!empty($level_low)){
                $sql .= ' AND level_id >= :level_id_low';
                $data[':level_id_low'] = $level_low;
            }
            if(!empty($level_high)){
                $sql .= ' AND level_id <= :level_id_high';
                $data[':level_id_high'] = $level_high;
            }

            foreach($activeDays as $key => $val){
                if(!empty($_POST[$key])){
                    $sql .= " AND flg_{$key} = :flg_{$key}";
                    $data[":flg_{$key}"] = 1;
                }
            }

            if(!empty($frequency)){
                $sql .= ' AND frequency_id <= :frequency_id';
                $data[':frequency_id'] = $frequency;
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

debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>

<?php
    $title = 'メンバー募集検索';
    $originalCss = "css/makeRecruit.css";
    $originalCss2 = "css/opponentRecruit.css";
    require('head.php');
?>

    <body>

        <?php require('header.php'); ?>

        <div id="main-2culum" class="site-width">
            <div id="main-left">
                <section class="main-left-white">

                    <h2 class="section-title">メンバー募集検索</h2>

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
                            <input type="text" name="team_name" id="team_name" placeholder="30文字以内" value="<?php if(!empty($_POST['team_name'])){ echo $_POST['team_name']; } ?>">
                        </div>

                        <div class="form-part">
                            <label for="prefectures">
                                エリア
                            </label>
                            <div class="area-msg">
                                <?php echo getErrMsg('prefectures'); ?>
                            </div>
                            <select id="prefectures" name="prefectures">
                                <?php
                                    if(!empty($_POST['prefectures'])){
                                        echo '<option value='.$_POST['prefectures'].' selected>'.$_POST['prefectures'].'</option>';
                                    }
                                ?>
                            </select>
                            <select id="city" name="city" onFocus="change()">
                                <?php
                                    if(!empty($_POST['city'])){
                                        echo '<option value='.$_POST['city'].' selected>'.$_POST['city'].'</option>';
                                    }
                                ?>
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
                            <label for="level_id_low">
                                募集プレーヤーレベル
                            </label>
                            <select name="level_id_low" id="level_id_low">
                                <option value="" <?php if(empty($_POST['level_id_low'])){ echo 'selected';} ?>>--選択してください--</option>
                                <?php
                                    foreach($dbLevelData as $key => $val) :
                                ?>
                                    <option value="<?php echo $val['id']; ?>" <?php if($_POST['level_id_low'] == $val['id']){ echo 'selected'; } ?>>
                                        LEVEL <?php echo $val['id']; ?> : <?php echo $val['name']; ?>
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
                                        LEVEL <?php echo $val['id']; ?> : <?php echo $val['name']; ?>
                                    </option>
                                <?php
                                    endforeach;
                                ?>
                            </select>
                            <span class="t-level-text">まで</span>
                        </div>

                        <div class="form-part">
                            <label for="">
                                活動曜日
                            </label>
                            <div class="checkbox-container">

                                <?php foreach($activeDays as $key => $val): ?>
                                    <div>
                                        <input type="checkbox" id="<?php echo $key ?>" name="<?php echo $key ?>" value="1">
                                        <label class="label-radio" for="<?php echo $key ?>"><?php echo $val ?></label>
                                    </div>
                                <?php endforeach; ?>
                                
                            </div>
                        </div>

                        <div class="form-part">
                            <label for="frequency_id">
                                活動頻度
                            </label>
                            <select name="frequency_id" id="frequency_id">
                                <option value="" <?php if(empty($_POST['frequency_id'])){ echo 'selected'; } ?>>--選択してください--</option>
                                <?php
                                    foreach($dbFrequencyData as $key => $val) :
                                ?>
                                    <option value="<?php echo $val['id']; ?>" <?php if($_POST['frequency_id'] === $val['id']){ echo 'selected'; } ?>>
                                        <?php echo $val['name']; ?>
                                    </option>
                                <?php
                                    endforeach;
                                ?>
                            </select>
                            <span style="font-size:20px;">以上</span>
                        </div>

                        <input type="submit" value="チームを検索する" class="btn btn-gray">
                    </form>
                </section>

                <section id="search-result">
                    <div class="result-title">
                        <h3>検索結果</h3>
                    </div>
                    <!-- そもそもPOST送信がない場合（初めてページを読み込んだ時） --> 
                    <?php if(empty($_POST)): ?>
                        <p style="font-size:20px;margin:5px 0;">検索条件を指定してください。</p>
                    
                    <!-- 検索条件にマッチするチームがあった時 -->
                    <?php elseif(!empty($result)): ?>
                        <?php foreach($result as $key => $val) : ?>
                            <div class="result-container">
                                <section>
                                    <a href="memberRecruitDetail.php?b_id=<?php echo $val['id']; ?>">
                                        <h3 class="rec-title"><?php echo $val['title']; ?></h3>
                                        <p class="opponent-rec-text">募集プレーヤーレベル：LEVEL<?php echo $val['level_id']; ?></p>
                                        <p class="opponent-rec-text">
                                            活動曜日：
                                            <?php
                                            foreach($activeDays as $key2 => $val2){
                                                if($val["flg_{$key2}"] == 1){
                                                    echo $val2. ' ';
                                                }
                                            }
                                        ?>
                                        </p>
                                        <p class="opponent-rec-text">活動頻度：<?php echo getFrequencyName($val['frequency_id'])['name']; ?></p>
                                    </a>
                                </section>
                                <div class="result-wrapper">
                                    <?php $teamData = getTeam($val['team_id']); ?>
                                        <a href="teamDetail.php?t_id=<?php echo $val['team_id']; ?>">
                                            <div class="result-img"><img src="<?php echo $teamData['pic']; ?>" alt="<?php echo $teamData['team_name']; ?>"></div>
                                            <div class="result-text">
                                                <h4><?php echo $teamData['team_name']; ?></h4>
                                                <p><?php echo $teamData['prefectures']. $teamData['city']; ?><br><?php echo getLevelName($teamData['level_id'])['name']; ?>中心<br><?php echo getCategoryName($teamData['category_id'])['name']; ?></p>
                                            </div>
                                        </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    
                    <!-- 一致する検索結果がない場合 --> 
                    <?php elseif(empty($result)): ?>
                        <p style="font-size:20px;margin:5px 0;">検索条件に一致するチームはありません。</p>
                    
                    <?php endif; ?>

                    <!--  ページネーションをここに入れる　-->
                </section>

            </div>

            <?php require('sidebar.php'); ?>

        </div>

        <?php require('footer.php'); ?>