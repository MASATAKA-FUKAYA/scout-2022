<?php
//================================
// ログ
//================================
//ログを取るか
ini_set('log_errors','on');
//ログの出力ファイルを指定
ini_set('error_log','php.log');

//================================
// デバッグ
//================================
//デバッグフラグ
$debug_flg = true;
//デバッグログ関数
function debug($str){
  global $debug_flg;
  if(!empty($debug_flg)){
    error_log('デバッグ：'.$str);
  }
}


//================================
// セッション準備・セッション有効期限を延ばす
//================================
//セッションファイルの置き場を変更する（/var/tmp/以下に置くと30日は削除されない）
session_save_path("/var/tmp/");
//ガーベージコレクションが削除するセッションの有効期限を設定（30日以上経っているものに対してだけ１００分の１の確率で削除）
ini_set('session.gc_maxlifetime', 60*60*24*30);
//ブラウザを閉じても削除されないようにクッキー自体の有効期限を延ばす
ini_set('session.cookie_lifetime ', 60*60*24*30);
//セッションを使う
session_start();
//現在のセッションIDを新しく生成したものと置き換える（なりすましのセキュリティ対策）
session_regenerate_id();


//========================
//画面表示処理開始ログ吐き出し関数
//========================
function debugLogStart(){
    debug('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>画面表示処理開始');
    debug('セッションID：'.session_id());
    debug('セッション変数の中身：'.print_r($_SESSION,true));
    debug('現在日時タイムスタンプ：'.time());
    if(!empty($_SESSION['login_date']) && !empty($_SESSION['login_limit'])){
        debug('ログイン期限日時タイムスタンプ：'.($_SESSION['login_date'] + $_SESSION['login_limit']));
    }
}


//========================
//定数
//========================
//エラ〜メッセージは定数に入れておく
define('MSG01','入力必須です');
define('MSG02','Emailの形式で入力してください');
define('MSG03','パスワード（再入力）が合っていません');
define('MSG04','半角英数字のみご利用いただけます');
define('MSG05','文字以上で入力してください');
define('MSG06','文字以内で入力してください');
define('MSG07','エラーが発生しました。しばらく経ってからやり直してください。');
define('MSG08','そのEmailは既に使用されています');
define('MSG09','メールアドレスまたはパスワードが違います');
define('MSG10','電話番号の形式が違います');
define('MSG11','郵便番号の形式が違います');
define('MSG12','半角数字のみご利用いただけます');
define('MSG13','古いパスワードが違います');
define('MSG14','古いパスワードと同じです');
define('MSG15','文字で入力してください');
define('MSG16','認証キーが正しくありません');
define('MSG17','有効期限が切れています');
define('SUC01','パスワードを変更しました');
define('SUC02','プロフィールを変更しました');
define('SUC03','メールを送信しました');
define('SUC04','ログインしました');
define('SUC05','チーム情報を登録しました');
define('SUC06','メンバー情報を登録しました');
define('SUC07','募集を投稿しました');


//========================
//バリデーション関数
//========================
//エラ〜メッセージ格納用の配列
$err_msg = array();

//バリデーション関数（未入力チェック）
function validRequired($str,$key){
    if(empty($str)){
        global $err_msg;
        $err_msg[$key] = MSG01;
    }
}

//バリデーション関数（Email形式チェック）
function validEmail($str,$key){
    if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $str)){
        global $err_msg;
        $err_msg[$key] = MSG02;
    }
}

//Email重複チェック
function validEmailDup($email){
    global $err_msg;
    //例外処理
    try{
        //DBへ接続
        $dbh = dbConnect();
        //SQL文作成
        $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
        $data = array(':email' => $email);
        //クエリ実行
        $stmt = queryPost($dbh,$sql,$data);
        //クエリ結果の値を取得
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if(!empty($result['count(*)'])){
            $err_msg['email'] = MSG08;
        }
    }catch(Exception $e){
        error_log('エラー発生：' . $e->getMessage());
        $err_msg['common'] = MSG07;
    }
}

//バリデーション関数（同値チェック）
function validMatch($str1, $str2, $key){
    if($str1 !== $str2){
        global $err_msg;
        $err_msg[$key] = MSG03;
    }
}

//バリデーション関数（最小文字数チェック）
function validMinlen($str, $key, $min = 6){
    if(mb_strlen($str) < $min){
        global $err_msg;
        $err_msg[$key] = $min. MSG05;
    }
}

//バリデーション関数（最大文字数チェック）
function validMaxLen($str, $key, $max = 256){
    if(mb_strlen($str) > $max){
        global $err_msg;
        $err_msg[$key] = $max. MSG06;
    }
}

//バリデーション関数（半角英数字チェック）
function validHalf($str, $key){
    if (!preg_match("/^[a-zA-Z0-9]+$/", $str)){
        global $err_msg;
        $err_msg[$key] = MSG04;
    }
}

//バリデーション関数（電話番号形式チェック）
function validTel($str, $key){
    if (!preg_match("/0\d{1,4}\d{1,4}\d{4}/", $str)){
        global $err_msg;
        $err_msg[$key] = MSG10;
    }
}

//バリデーション関数（郵便番号形式チェック）
function validZip($str, $key){
    if (!preg_match("/^\d{7}$/", $str)){
        global $err_msg;
        $err_msg[$key] = MSG11;
    }
}

//バリデーション関数（半角数字チェック）
function validNumber($str, $key){
    if (!preg_match("/^[0-9]+$/", $str)){
        global $err_msg;
        $err_msg[$key] = MSG12;
    }
}

//固定長チェック
function validLength($str, $key, $len = 8){
    if( mb_strlen($str) !== $len ){
        global $err_msg;
        $err_msg[$key] = $len. MSG15;
    }
}

//バリデーション関数（パスワード形式チェック）
function validPass($str, $key){
    //半角英数字チェック
    validHalf($str, $key);
    //最大文字数チェック
    validMaxLen($str, $key);
    //最小文字数チェック
    validMinlen($str, $key);
}

//エラーメッセージ表示
function getErrMsg($key){
    global $err_msg;
    if(!empty($err_msg[$key])){
        return '*'.$err_msg[$key];
    }
}


//=======================
//DB関連
//=======================
//DB接続関数
function dbConnect(){
    //DBへの接続準備
    $dsn = 'mysql:dbname=Scout;host=localhost;charset=utf8';
    $user = 'root';
    $password = 'root';
    $options = array(
        //SQL実行時にはエラーコードのみ設定
        PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
        //デフォルトフェッチモードを連想配列形式に設定
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        //バッファードクエリを使う（一度に結果セットを全て取得し、サーバー負荷を軽減）
        //SELECTで得た結果に対してもrowCountメソッドを使えるようにする
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
    );
    //PDOオブジェクト作成（DBへ接続）
    $dbh = new PDO($dsn, $user, $password, $options);
    return $dbh;
}

function queryPost($dbh, $sql, $data){
    //クエリー作成
    $stmt = $dbh->prepare($sql);
    //プレースホルダに値をセットし、SQL文を実行
    if(!$stmt->execute($data)){
      debug('クエリに失敗しました。');
      debug('失敗したSQL：'.print_r($stmt,true));
      global $err_msg;
      $err_msg['common'] = MSG07;
      return 0;
    }
    debug('クエリ成功。');
    return $stmt;
}

//DBデータ取得関連========
//ユーザー情報
function getUser($u_id){
    debug('ユーザー情報を取得します。');

    try{
        //DB接続
        $dbh = dbConnect();
        //SQL文作成
        $sql = 'SELECT * FROM users WHERE id = :u_id AND delete_flg = 0';
        $data = array(':u_id' => $u_id);
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        //クエリ結果のデータを１レコード返却
        if($stmt){
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }else{
            return false;
        }
    }catch(Exception $e){
        error_log('エラー発生：'.$e->getMessage());
    }
}

//チーム情報
function getTeam($t_id){
    debug('チーム情報を取得します。');

    try{
        //DB接続
        $dbh = dbConnect();
        //SQL文作成
        $sql = 'SELECT * FROM team WHERE id = :t_id AND delete_flg = 0';
        $data = array(':t_id' => $t_id);
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        //クエリ結果のデータを１レコード返却
        if($stmt){
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }else{
            debug('チーム情報の取得に失敗しました');
            return false;
        }
    }catch(Exception $e){
        error_log('エラー発生：'.$e->getMessage());
    }
}

//チーム代表者取得
function getTeamHost($t_id){
    debug('チーム代表者を取得します。');

    try{
        //DB接続
        $dbh = dbConnect();
        //usersとteamを結合、host_user_idから代表者名を取得
        $sql = 'SELECT u.u_name, u.email, u.level_id, u.pic FROM users AS u LEFT JOIN team AS t ON u.id = t.host_user_id WHERE t.id = :t_id AND u.delete_flg = 0';
        $data = array(':t_id' => $t_id);
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        //クエリ結果のデータを１レコード返却
        if($stmt){
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }else{
            debug('チーム代表者の取得に失敗しました');
            return false;
        }
    }catch(Exception $e){
        error_log('エラー発生：'.$e->getMessage());
    }
}

//掲示板用、自分がホストのチームのみ取得
function getMyTeam($u_id){
    debug('現在のユーザーがホストのチームの情報を取得します。');

    try{
        //DB接続
        $dbh = dbConnect();

        //自分がオーナーのチームを取得
        $sql = 'SELECT * FROM team WHERE host_user_id = :u_id AND delete_flg = 0';
        $data = array(':u_id' => $u_id);
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        //クエリ結果のデータを全レコード返却、resultへ格納
        if($stmt){
            $result = $stmt->fetchAll();
            return $result;
        }else{
            debug('自分がホストのチームがありませんでした。');
            return false;
        }

    }catch(Exception $e){
        error_log('エラー発生：'.$e->getMessage());
    }
}

//マイページ用
function getMyALLTeam($u_id){
    debug('所属するチーム全ての情報を取得します。');

    try{
        //DB接続
        $dbh = dbConnect();

        //1.自分がオーナーのチームを取得
        $sql1 = 'SELECT * FROM team WHERE host_user_id = :u_id AND delete_flg = 0';
        $data = array(':u_id' => $u_id);
        //クエリ実行
        $stmt = queryPost($dbh, $sql1, $data);

        //クエリ結果のデータを全レコード返却、resultへ格納
        if($stmt){
            $result = $stmt->fetchAll();
        }else{
            debug('自分がホストのチームがありませんでした。');
        }

        //2.team1_idがあればそのチームを取得
        $sql2 = 'SELECT t.id, t.team_name, t.prefectures, t.city, t.category_id, t.level_id, t.comment, t.url, t.pic FROM team AS t LEFT JOIN users AS u ON t.id = u.team1_id WHERE u.id = :u_id AND t.delete_flg = 0';
        $data2 = array(':u_id' => $u_id);
        //クエリ実行
        $stmt2 = queryPost($dbh, $sql2, $data2);

        //クエリ結果のデータを全レコード返却、resultへ格納
        if(!empty($stmt2->fetchAll())){
            $result[] = $stmt2;
        }else{
            debug('自分が参加しているチームがありませんでした。');
        }

        //3.team2_idがあればそのチームを取得（他人の作ったチームへの参加は２チームまで）
        $sql3 = 'SELECT t.id, t.team_name, t.prefectures, t.city, t.category_id, t.level_id, t.comment, t.url, t.pic FROM team AS t LEFT JOIN users AS u ON t.id = u.team2_id WHERE u.id = :u_id AND t.delete_flg = 0';
        $data3 = array(':u_id' => $u_id);
        //クエリ実行
        $stmt3 = queryPost($dbh, $sql3, $data3);

        //クエリ結果のデータを全レコード返却、resultへ格納
        if(!empty($stmt3->fetchAll())){
            $result[] = $stmt3;
        }else{
            debug('自分が参加しているチームがありませんでした。');
        }

        return $result;

    }catch(Exception $e){
        error_log('エラー発生：'.$e->getMessage());
    }
}

//メンバー情報
function getMember($t_id){
    debug('メンバー情報を取得します。');

    try{
        //DB接続
        $dbh = dbConnect();
        //SQL文作成
        //背番号順で並べる
        $sql = 'SELECT * FROM member WHERE team_id = :t_id AND delete_flg = 0 ORDER BY m_number';
        $data = array(':t_id' => $t_id);
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        //クエリ結果のデータを全レコード返却
        if($stmt){
            return $stmt->fetchAll();
        }else{
            debug('メンバー情報の取得に失敗しました');
            return false;
        }
    }catch(Exception $e){
        error_log('エラー発生：'.$e->getMessage());
    }
}

function getOneMember($m_id){
    debug('メンバー情報を取得します。');

    try{
        //DB接続
        $dbh = dbConnect();
        //SQL文作成
        //背番号順で並べる
        $sql = 'SELECT * FROM member WHERE id = :id AND delete_flg = 0';
        $data = array(':id' => $m_id);
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        //クエリ結果のデータを全レコード返却
        if($stmt){
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }else{
            debug('メンバー情報の取得に失敗しました');
            return false;
        }
    }catch(Exception $e){
        error_log('エラー発生：'.$e->getMessage());
    }
}
//レベル情報
function getLevel(){
    debug('レベル情報を取得します。');

    try{
        //DB接続
        $dbh = dbConnect();
        //SQL文作成
        $sql = 'SELECT id,name FROM `level` WHERE delete_flg = 0';
        $data = array();
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        //クエリ結果のデータを1レコード返却
        if($stmt){
            return $stmt->fetchAll();
        }else{
            return false;
        }
    }catch(Exception $e){
        error_log('エラー発生：'.$e->getMessage());
    }
}

function getLevelName($l_id){
    debug('レベル情報を取得します。');

    try{
        //DB接続
        $dbh = dbConnect();
        //SQL文作成
        $sql = 'SELECT name FROM `level` WHERE id = :id AND delete_flg = 0';
        $data = array(':id' => $l_id);
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        //クエリ結果のデータを1レコード返却
        if($stmt){
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }else{
            return false;
        }
    }catch(Exception $e){
        error_log('エラー発生：'.$e->getMessage());
    }
}

//カテゴリー情報
function getCategory(){
    debug('カテゴリー情報を取得します。');

    try{
        //DB接続
        $dbh = dbConnect();
        //SQL文作成
        $sql = 'SELECT id,name FROM category WHERE delete_flg = 0';
        $data = array();
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        //クエリ結果のデータを全レコード返却
        if($stmt){
            return $stmt->fetchAll();
        }else{
            return false;
        }
    }catch(Exception $e){
        error_log('エラー発生：'.$e->getMessage());
    }
}

function getCategoryName($c_id){
    debug('カテゴリー情報を取得します。');

    try{
        //DB接続
        $dbh = dbConnect();
        //SQL文作成
        $sql = 'SELECT name FROM category WHERE id = :id AND delete_flg = 0';
        $data = array(':id' => $c_id);
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        //クエリ結果のデータを1レコード返却
        if($stmt){
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }else{
            return false;
        }
    }catch(Exception $e){
        error_log('エラー発生：'.$e->getMessage());
    }
}

//投打情報
function getPitbat(){
    debug('投打情報を取得します。');

    try{
        //DB接続
        $dbh = dbConnect();
        //SQL文作成
        $sql = 'SELECT id,name FROM `pit-bat` WHERE delete_flg = 0';
        $data = array();
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        //クエリ結果のデータを全レコード返却
        if($stmt){
            return $stmt->fetchAll();
        }else{
            return false;
        }
    }catch(Exception $e){
        error_log('エラー発生：'.$e->getMessage());
    }
}

function getPitbatName($c_id){
    debug('投打情報を取得します。');

    try{
        //DB接続
        $dbh = dbConnect();
        //SQL文作成
        $sql = 'SELECT name FROM `pit-bat` WHERE id = :id AND delete_flg = 0';
        $data = array(':id' => $c_id);
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        //クエリ結果のデータを1レコード返却
        if($stmt){
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }else{
            return false;
        }
    }catch(Exception $e){
        error_log('エラー発生：'.$e->getMessage());
    }
}

//ポジション情報
function getPosition(){
    debug('ポジション情報を取得します。');

    try{
        //DB接続
        $dbh = dbConnect();
        //SQL文作成
        $sql = 'SELECT id,name FROM position WHERE delete_flg = 0';
        $data = array();
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        //クエリ結果のデータを全レコード返却
        if($stmt){
            return $stmt->fetchAll();
        }else{
            return false;
        }
    }catch(Exception $e){
        error_log('エラー発生：'.$e->getMessage());
    }
}

function getPositionName($c_id){
    debug('ポジション情報を取得します。');

    try{
        //DB接続
        $dbh = dbConnect();
        //SQL文作成
        $sql = 'SELECT name FROM position WHERE id = :id AND delete_flg = 0';
        $data = array(':id' => $c_id);
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        //クエリ結果のデータを1レコード返却
        if($stmt){
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }else{
            return false;
        }
    }catch(Exception $e){
        error_log('エラー発生：'.$e->getMessage());
    }
}

//頻度情報
function getFrequency(){
    debug('活動頻度情報を取得します。');

    try{
        //DB接続
        $dbh = dbConnect();
        //SQL文作成
        $sql = 'SELECT id,name FROM frequency WHERE delete_flg = 0';
        $data = array();
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        //クエリ結果のデータを全レコード返却
        if($stmt){
            return $stmt->fetchAll();
        }else{
            return false;
        }
    }catch(Exception $e){
        error_log('エラー発生：'.$e->getMessage());
    }
}

function getFrequencyName($f_id){
    debug('活動頻度情報を取得します。');

    try{
        //DB接続
        $dbh = dbConnect();
        //SQL文作成
        $sql = 'SELECT name FROM frequency WHERE id = :id AND delete_flg = 0';
        $data = array(':id' => $f_id);
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        //クエリ結果のデータを1レコード返却
        if($stmt){
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }else{
            return false;
        }
    }catch(Exception $e){
        error_log('エラー発生：'.$e->getMessage());
    }
}

//審判情報
function getUmpire(){
    debug('審判情報を取得します。');

    try{
        //DB接続
        $dbh = dbConnect();
        //SQL文作成
        $sql = 'SELECT id,name_jpn,name_eng FROM umpire WHERE delete_flg = 0';
        $data = array();
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        //クエリ結果のデータを全レコード返却
        if($stmt){
            return $stmt->fetchAll();
        }else{
            return false;
        }
    }catch(Exception $e){
        error_log('エラー発生：'.$e->getMessage());
    }
}

function getUmpireName($u_id){
    debug('審判情報を取得します。');

    try{
        //DB接続
        $dbh = dbConnect();
        //SQL文作成
        $sql = 'SELECT name_jpn FROM umpire WHERE id = :id AND delete_flg = 0';
        $data = array(':id' => $u_id);
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        //クエリ結果のデータを1レコード返却
        if($stmt){
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['name_jpn'];
        }else{
            return false;
        }
    }catch(Exception $e){
        error_log('エラー発生：'.$e->getMessage());
    }
}

//メンバー募集掲示板
function getOneMemBoard($b_id){
    debug('メンバー募集掲示板情報を取得します。');

    try{
        //DB接続
        $dbh = dbConnect();
        //SQL文作成
        $sql = 'SELECT * FROM mem_rec_board WHERE id = :b_id AND delete_flg = 0';
        $data = array(':b_id' => $b_id);
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        //クエリ結果のデータを1レコード返却
        if($stmt){
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }else{
            return false;
        }
    }catch(Exception $e){
        error_log('エラー発生：'.$e->getMessage());
    }
}

//対戦相手募集掲示板
function getOneOppBoard($b_id){
    debug('対戦相手募集掲示板情報を取得します。');

    try{
        //DB接続
        $dbh = dbConnect();
        //SQL文作成
        $sql = 'SELECT * FROM opp_rec_board WHERE id = :b_id AND delete_flg = 0';
        $data = array(':b_id' => $b_id);
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        //クエリ結果のデータを1レコード返却
        if($stmt){
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }else{
            return false;
        }
    }catch(Exception $e){
        error_log('エラー発生：'.$e->getMessage());
    }
}

//メッセージ掲示板
function getMsgBoard($b_id){
    debug('メッセージ情報を取得します。');

    try{
        //DB接続
        $dbh = dbConnect();
        //SQL文作成
        $sql = 'SELECT * FROM msg_board WHERE id = :b_id AND delete_flg = 0';
        $data = array(':b_id' => $b_id);
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        if($stmt){
            //クエリ結果のデータを1レコード返却（これでメッセージ掲示板が決まる）
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            //古いメッセージは上に
            $sql2 = 'SELECT * FROM message WHERE msg_board_id = :b_id AND delete_flg = 0 ORDER BY create_date';
            $data2 = array(':b_id' => $result['id']);

            $stmt2 = queryPost($dbh, $sql2, $data2);

            if($stmt2){
                $result['msg'] = $stmt2->fetchAll();
            }

        }else{
            $result = 0;
        }

        return $result;

    }catch(Exception $e){
        error_log('エラー発生：'.$e->getMessage());
    }
}

//=======================
//その他
//=======================
//メール送信
//===========================
function sendMail($from, $to, $subject, $comment){
    if(!empty($to) && !empty($subject) && !empty($comment)){
        //文字化けしないように設定（お決まりパターン）
        mb_language("Japanese");//現在使っている言語を設定
        mb_internal_encoding("UTF-8");//内部の日本語をどうエンコーディング（機械語へ変換）するかを設定

        //メール送信（送信結果はtrue or falseで返ってくる）
        $result = mb_send_mail($to, $subject, $comment, "From: ".$from);
        //送信結果を判定
        if($result){
            debug('メールを送信しました。');
        }else{
            debug('【エラー発生】メールの送信に失敗しました。');
        }
    }
}


//サニタイズ
function sanitize($str){
    return htmlspecialchars($str,ENT_QUOTES);
}

//フォーム入力保持
function getFormData($str, $flg = false){
    if($flg){
        $method = $_GET;
    }else{
        $method = $_POST;
    }
    global $dbFormData;
    //ユーザーデータがある場合
    if(!empty($dbFormData)){
        //フォームのエラーがある場合
        if(!empty($err_msg[$str])){
            //POSTにデータがある場合
            if(isset($method[$str])){ //数字や数値の０が入っている場合があるのでisset
                return sanitize($method[$str]);
            }else{
                //ない場合（フォームにエラーがあるならpostされているはずだから、基本はあり得ない）はDBの情報を表示
                return sanitize($dbFormData[$str]);
            }
        }else{
            //POSTにデータがあり、DBの情報と違う場合（このフォームも変更していてエラーはないが、他のフォームで引っかかっている状態）
            if(isset($method[$str]) && $method[$str] !== $dbFormData[$str]){
                return sanitize($method[$str]);
            }else{ //そもそも変更していない
                return sanitize($dbFormData[$str]);
            }
        }
    }else{
        if(isset($method[$str])){
            return sanitize($method[$str]);
        }
    }
}

//画像処理関数
function upLoadImg($file,$key){
    debug('画像アップロード処理開始');
    debug('FILE情報：' . print_r($file,true));

    if(isset($file['error']) && is_int($file['error'])){
        try{
            //バリデーション
            //$file['error]の値を確認。配列内には[UPLOAD_ERR_OK]などの定数が入っている。
            //[UPLOAD_ERR_OK]などの定数はphpでファイルアップロードの時に自動的に定義される。定数には値として0や1などの数値が入っている。
            switch($file['error']){
                case UPLOAD_ERR_OK: //OKの場合
                    break;
                case UPLOAD_ERR_NO_FILE:    //ファイル未選択の場合
                    throw new RuntimeException('ファイルが選択されていません');
                case UPLOAD_ERR_INI_SIZE:   //php.ini定義の最大サイズを超えた場合
                    throw new RuntimeException('ファイルサイズが大きすぎます（INI指定）');
                case UPLOAD_ERR_FORM_SIZE:  //フォームで指定した最大サイズを超えた場合
                    throw new RuntimeException('ファイルサイズが大きすぎます（フォーム指定）');
                default:   //その他の場合
                throw new RuntimeException('その他のエラーが発生しました');
            }

            // $file['mime']の値はブラウザ側で偽装可能なので、MIMEタイプを自前で確認する
            // exif_imagetype関数は「IMAGETYPE_GIF」「IMAGETYPE_JPEG」などの定数を返す
            // この関数はエラーが出ることがあるが、それでも後続の処理を続けたいので、「エラーを無視する」という意味で＠を付ける
            $type = @exif_imagetype($file['tmp_name']);
            if(!in_array($type,[IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)){  //第三引数にtrueを設定すると厳密にチェックしてくれるので必ずつける
                throw new RuntimeException('画像タイプが未対応です');
            }

            //ファイルデータからSHA-1ハッシュを取ってファイル名を決定し、ファイルを保存する
            //ハッシュ化せずアップロードされたファイル名そのままで保存してしまうと、同じファイル名がアップロードされる可能性があり、
            //DBにパスを保存した場合、どっちの画像のパスなのか判断がつかなくなってしまう
            //image_type_to_extension関数はファイルの拡張子を取得するもの
            $path = 'uploads/' .sha1_file($file['tmp_name']).image_type_to_extension($type);

            if(!move_uploaded_file($file['tmp_name'], $path)){
                throw new RuntimeException('ファイル保存時にエラーが発生しました');
            }
            //保存したファイルパスのパーミッション（権限）を変更する
            chmod($path,0644);

            debug('ファイルは正常にアップロードされました');
            debug('ファイルパス：' . $path);
            return $path;

        }catch(RuntimeException $e){
            debug($e->getMessage());
            global $err_msg;
            $err_msg[$key] = $e->getMessage();
        }
    }
}

//画像表示関数
function showImg($path){
    if(empty($path)){
        return 'img/sample-img.png';
    }else{
        return $path;
    }
}

//sessionを一度だけ取得できる
function getSessionFlash($key){
    if(!empty($_SESSION[$key])){
        $data = $_SESSION[$key];
        $_SESSION[$key] = '';
        return $data;
    }
}

//認証キー作成
function makeRandKey($length = 8){
    static $chars = 'abcdefghijklmnopqistuvwxyzABCDEFGHIJKLMNOPQISTUVWXYZ0123456789';
    $str = '';
    for($i = 0; $i < $length; ++$i){
        $str.=$chars[mt_rand(0,61)];
    }
    return $str;
}

?>