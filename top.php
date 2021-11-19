<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「');
debug('「トップページ');
debug('「「「「「「「「「「「「「「「「「「「');
debugLogStart();

?>
<?php
    $title = 'Top';
    $originalCss = "css/top.css";
    require('head.php');
?>

    <body>

        <?php
            require('header.php');
        ?>

        <section id="main" class="site-width">

            <!-- Topに戻る -->
            <div class="p-scroll">
                <a href="#">Page Top<br><i class="fas fa-chevron-up"></i></a>
            </div><!-- /Topに戻る -->

            <section id="top-view">
                <h3>草野球を楽しむ、全ての人へ。</h3>
                <h1 id="toppage-view-logo">Scout!</h1>
                <div class="btn-top-container">
                    <div id="btn-top" class="btn btn-signup">
                        <a href="signup.php">ユーザー登録</a>
                    </div>
                    <div id="btn-top" class="btn btn-login">
                        <a href="login.php">ログイン</a>
                    </div>
                </div>
            </section>

            <p id="explanation">
                ・草野球を始めたい。でもついていけるか心配…<br>
                ・実力が同じくらいのチームと練習試合がしたい！<br>
                そんなあなたのために、ピッタリのサービスをご用意しました！<br>　
            </p>

            <section id="point">
                <h2 class="section-title">POINT!!</h2>

                    <div class="cp-section">
                        <div class="cp-container cp-accordion01">

                            <!-- アイテム -->
                            <dl class="cp-accordion01__item">
                                <dt class="cp-accordion01__title js-cp-accordion01__title">チーム検索はレベル毎に行うことができます！</dt>
                                <!-- コンテンツ -->
                                <dd class="cp-accordion01__content">
                                    <p class="cp-text">・LEVEL1　未経験〜小学野球経験者中心<br>・LEVEL2　中学野球経験者中心<br>・LEVEL3　高校野球経験者中心<br>・LEVEL4　大学、社会人野球経験者中心<br>
                                                        もちろん、活動地域、活動日などからも選ぶことができます！</p>
                                </dd><!-- /コンテンツ -->
                            </dl><!-- /アイテム -->

                            <!-- アイテム -->
                            <dl class="cp-accordion01__item">
                                <dt class="cp-accordion01__title js-cp-accordion01__title">チームの募集、応募、連絡等は全てサイト上で完結できます！</dt>
                                <!-- コンテンツ -->
                                <dd class="cp-accordion01__content">
                                    <p class="cp-text">気になるチームがあった時も、サイト上のメッセージ機能を使えばすぐに代表者に連絡をとることができます。</p>
                                </dd><!-- /コンテンツ -->
                            </dl><!-- /アイテム -->
                        </div>
                    </div>
            </section>

            <h3>さあ、はじめよう。</h3>
            <h1 id="toppage-bottom-logo">Scout!</h1>
            <div class="btn-top-container">
                <div id="btn-top" class="btn btn-signup">
                    <a href="signup.php">ユーザー登録</a>
                </div>
                <div id="btn-top" class="btn btn-login">
                    <a href="login.php">ログイン</a>
                </div>
            </div>
        </section>

        <?php
            require('footer.php');
        ?>