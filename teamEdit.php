<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「チーム登録・編集ページ');
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

//DBからチームデータを取得
$dbFormData = (!empty($t_id)) ? getTeam($t_id) : '';
debug('フォーム用DBデータ：'.print_r($dbFormData,true));

//新規登録画面か編集画面か判別用フラグ
$edit_flg = (empty($dbFormData)) ? 0 : 1;
debug('判別フラグ：'. $edit_flg);

//DBからレベル・カテゴリーデータを取得
$dbLevelData = getLevel();
$dbCategoryData = getCategory();

//パラメータ改ざんチェック
//====================
//GETパラメータはあるが改竄されている（URLをいじった）場合、正しいチームデータが取れないのでマイページへ遷移
if(!empty($t_id) && empty($dbFormData)){
  debug('GETパラメータのチームIDが違います。マイページへ遷移します。');
  header("Location:mypage.php");
  exit();
}


//POST送信されているかチェック
if(!empty($_POST)){
    debug('POST送信があります。');
    debug('POST情報：' . print_r($_POST,true));
    debug('FILE情報：' . print_r($_FILES,true));

    //入力されたユーザーからの情報を変数に代入
    $t_name = $_POST['team_name'];
    $pref = $_POST['prefectures'];
    $city = $_POST['city'];
    $category = $_POST['category_id'];
    $level = $_POST['level_id'];
    $comment = $_POST['comment'];
    $url = $_POST['url'];

    //画像をアップロードし、パスを格納
    $pic = ( !empty($_FILES['pic']['name'])) ? upLoadImg($_FILES['pic'],'pic') : '';
    //画像をpostしていない（登録していない）が既にDBに登録されている場合、DBのパスを入れる
    $pic = ( empty($pic) && !empty($dbFormData['pic'])) ? $dbFormData['pic'] : $pic;

    //バリデーションチェック
    //未入力
    validRequired($t_name, 'team_name');
    validRequired($pref, 'prefectures');
    validRequired($city, 'city');
    validRequired($category, 'category_id');

    if(empty($err_msg)){
        debug('未入力チェックOK');

        //バリデーション続き
        //チーム名、チーム紹介、チームURL・最大文字数
        validMaxLen($t_name, 'team_name', 30);
        validMaxLen($comment, 'comment', 500);
        validMaxLen($url, 'url');

        if(empty($err_msg)){
            debug('バリデーションチェックOK');

            //DB新規登録または更新
            try{
                $dbh = dbConnect();

                switch($edit_flg){
                    case 0:
                        debug('新規登録です');
                        $sql = 'INSERT INTO team (team_name, host_user_id, prefectures, city, category_id, level_id, comment, url, pic, create_date)
                                VALUES (:team_name, :host_user_id, :prefectures, :city, :category_id, :level_id, :comment, :url, :pic, :create_date)';
                        $data = array(':team_name' => $t_name, ':host_user_id' => $_SESSION['user_id'], ':prefectures' => $pref, ':city' => $city, ':category_id' => $category,
                                ':level_id' => $level, ':comment' => $comment, ':url' => $url, ':pic' => $pic, ':create_date' => date('Y-m-d H:i:s'));
                        break;
                    case 1:
                        debug('DB更新です');
                        $sql = 'UPDATE team SET team_name = :team_name, prefectures = :prefectures, city = :city, category_id = :category_id,
                                level_id = :level_id, comment = :comment, url = :url, pic = :pic WHERE id = :id AND host_user_id = :host_user_id';
                        $data = array(':team_name' => $t_name, ':host_user_id' => $_SESSION['user_id'], ':prefectures' => $pref, ':city' => $city, ':category_id' => $category,
                                ':level_id' => $level, ':comment' => $comment, ':url' => $url, ':pic' => $pic, ':id' => $t_id);
                        break;
                }

                debug('SQL:' . $sql);
                debug('流し込みデータ：' . print_r($data,true));
                //SQL実行
                $stmt = queryPost($dbh, $sql, $data);

                //$stmtの中身は取り出さないからfetchしない

                //クエリ成功の場合
                if($stmt){
                    $_SESSION['msg_success'] = SUC05;
                    debug('マイページへ遷移します');
                    header("Location:mypage.php");
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
    $title = 'チーム編集';
    $originalCss = "css/teamEdit.css";
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

                    <h2 class="section-title">チーム情報編集</h2>

                    <form action="" method="post" enctype="multipart/form-data">

                        <div class="area-msg">
                            <?php echo getErrMsg('common'); ?>
                        </div>

                        <div class="form-part">
                            <label for="team_name">
                                チーム名<span class="form-info">※必須</span>
                            </label>
                            <div class="area-msg">
                                <?php echo getErrMsg('team_name'); ?>
                            </div>
                            <input type="text" name="team_name" id="team_name" placeholder="30文字以内" value="<?php if( !empty(getFormData('team_name')) ){ echo getFormData('team_name'); } ?>">
                        </div>

                        <div class="form-part">
                            <label for="prefectures">
                                主な活動地域<span class="form-info">※必須</span>
                            </label>
                            <div class="area-msg">
                                <?php echo getErrMsg('prefectures'); ?>
                            </div>
                            <select id="prefectures" name="prefectures">
                                <?php
                                    if(!empty($dbFormData['prefectures'])) :
                                ?>
                                    <option value="<?php echo $dbFormData['prefectures']; ?>"><?php echo $dbFormData['prefectures']; ?></option>
                                <?php
                                    endif;
                                ?>
                            </select>
                            <div class="area-msg">
                                <?php echo getErrMsg('city'); ?>
                            </div>
                            <select id="city" name="city" onFocus="change()">
                                <?php
                                    if(!empty($dbFormData['city'])) :
                                ?>
                                    <option value="<?php echo $dbFormData['city']; ?>"><?php echo $dbFormData['city']; ?></option>
                                <?php
                                    endif;
                                ?>
                            </select>
                        </div>

                        <div class="form-part">
                            <label for="category_id">
                                チーム種類<span class="form-info">※必須</span>
                            </label>
                            <select name="category_id" id="category_id">
                                <?php
                                    if(empty($dbFormData['category_id'])){
                                        echo '<option value="" selected>--選択してください--</option>';
                                    }
                                ?>
                                <?php
                                    foreach($dbCategoryData as $key => $val) :
                                ?>
                                    <option value="<?php echo $val['id']; ?>" <?php if( !empty($dbFormData) && $val['id'] == $dbFormData['category_id']){ echo 'selected'; } ?>>
                                        <?php echo $val['name']; ?>
                                    </option>
                                <?php
                                    endforeach;
                                ?>
                            </select>
                        </div>

                        <div class="form-part">
                            <label for="level_id">
                                チームレベル
                            </label>
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
                                        LEVEL <?php echo $val['id']; ?> : <?php echo $val['name']; ?>中心
                                    </option>
                                <?php
                                    endforeach;
                                ?>
                            </select>
                        </div>

                        <div class="form-part">
                            <label for="comment">
                                チーム紹介
                            </label>
                            <div class="area-msg">
                                <?php echo getErrMsg('comment'); ?>
                            </div>
                            <textarea name="comment" id="comment" cols="30" rows="10" placeholder="500文字以内"><?php if( !empty(getFormData('comment')) ){ echo getFormData('comment'); } ?></textarea>
                        </div>

                        <div class="form-part">
                            <label for="url">
                                チームURL
                            </label>
                            <div class="area-msg">
                                <?php echo getErrMsg('url'); ?>
                            </div>
                            <input type="text" name="url" id="url" value="<?php if( !empty(getFormData('url')) ){ echo getFormData('url'); } ?>">
                        </div>

                        <div class="form-part">
                            <label for="pic">
                                チーム画像
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

                        <input type="submit" value="チーム情報保存" class="btn btn-gray">

                    </form>
                    <div class="memberedit">
                        <div class="btn btn-gray">
                            <a href="teamMemberEdit.php?t_id=<?php echo $t_id; ?>">メンバー追加画面へ</a>
                        </div>
                        <p>※チーム情報を保存してから移動してください。</p>
                    </div>
                </section>
            </div>

            <?php
                require('sidebar.php');
            ?>

        </div>

        <?php
            require('footer.php');
        ?>