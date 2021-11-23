<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「チーム検索ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証なし

//=====================
//画面処理
//=====================



//DBからレベル・カテゴリーデータを取得
$dbLevelData = getLevel();
$dbCategoryData = getCategory();

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
            $sql = 'SELECT * FROM team WHERE delete_flg = 0';
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

            //$ressultで画面表示へ

        }catch(Exception $e){
            error_log('エラー発生：' . $e->getMessage());
            $err_msg['common'] = MSG07;
        }
    }
}

?>
<?php
    $title = 'チーム検索';
    $originalCss = 'css/teamSearch.css';
    require('head.php');
?>
    <body>

        <?php
            require('header.php');
        ?>
        <div id="main-2culum" class="site-width">
            <div id="main-left">
                <section class="main-left-white">

                    <h2 class="section-title">チーム検索</h2>

                    <!-- Topに戻る -->
                    <div class="p-scroll">
                        <a href="#">Page Top<br><i class="fas fa-chevron-up"></i></a>
                    </div><!-- /Topに戻る -->

                    <form action="" method="post"　enctype="multipart/form-data">

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
                                主な活動地域
                            </label>
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
                                <option value="" <?php if(empty($_POST['category_id'])){ echo 'selected';} ?>>--選択してください--</option>
                                <?php
                                    foreach($dbCategoryData as $key => $val) :
                                ?>
                                    <option value="<?php echo $val['id']; ?>" <?php if($_POST['category_id'] == $val['id']){ echo 'selected'; } ?>>
                                        <?php echo $val['name']; ?>
                                    </option>
                                <?php
                                    endforeach;
                                ?>
                            </select>
                        </div>
                        <div class="form-part">
                            <label for="level_id_low">
                                チームレベル
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

                        <input type="submit" value="チームを検索する" class="btn btn-gray">
                    </form>
                </section>

                <section id="search-result">
                    <div class="result-title">
                        <h3>チーム検索結果</h3>
                    </div>
                    <div class="result-container">
                        <?php
                            if ($result != null && is_array($result)) :
                                foreach($result as $key => $val) :
                        ?>
                            <div class="result-wrapper">
                                <a href="teamDetail.php?t_id=<?php echo $val['id']; ?>">
                                    <div class="result-img"><img src="<?php echo $val['pic']; ?>" alt="<?php echo $val['team_name']; ?>"></div>
                                    <div class="result-text">
                                        <h4><?php echo $val['team_name']; ?></h4>
                                        <p><?php echo $val['prefectures'].$val['city']; ?></p>
                                        <p><?php echo getLevelName($val['level_id'])['name']; ?>中心</p>
                                        <p><?php echo getCategoryName($val['category_id'])['name']; ?></p>
                                    </div>
                                </a>
                            </div>
                        <?php
                                endforeach;
                            endif;
                        ?>
                    </div>
                    <!--  ページネーションをここに入れる　-->
                </section>

            </div>

            <?php
                require('sidebar.php');
            ?>

        </div>
        <?php
            require('footer.php');
        ?>