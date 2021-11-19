<?php

//========================
//ログイン認証・自動ログアウト
//========================

//既にログインしているかチェック
if(!empty($_SESSION['login_date'])){
    debug('ログイン済みユーザーです');

    //有効期限外ならセッションをクリアしてログアウト。ログインページへ
    //有効期限外＝現在日時が最終ログイン日時＋有効期限を超えていた場合
    if( ($_SESSION['login_date'] + $_SESSION['login_limit']) < time() ){
        debug('ログイン有効期限オーバーです');

        //ログアウト＝セッションを削除
        session_destroy();
        //ログインページへ
        header("Location:login.php");
        exit();

    }else{  //有効期限内ならマイページへ
        debug('ログイン有効期限内です');
        //最終ログイン日時を現在日時に更新
        $_SESSION['login_time'] = time();
        if(basename($_SERVER['PHP_SELF']) === 'login.php'){
            debug('マイページへ遷移します');
            header("Location:mypage.php");
            exit();
        }

    }

}else{  //セッション自体が存在しない（未ログイン）なら後続処理へ
    debug('セッションがありません。未ログインユーザーです');
    if(basename($_SERVER['PHP_SELF']) !== 'login.php'){
        header("Location:login.php");
    }
}

?>