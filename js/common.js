$(function() {

    //ページ固定
    var $ftr = $('footer');
    if( window.innerHeight > $ftr.offset().top + $ftr.outerHeight() ){
      console.log(window.innerHeight);
      console.log($ftr.offset().top);
      console.log($ftr.outerHeight());
      $ftr.attr({'style': 'position:fixed; top:' + (window.innerHeight - $ftr.outerHeight()) +'px;' });
    };

    //スライドメッセージ
    var $jsShowMsg = $('#js-show-msg');
    var msg = $jsShowMsg.text();
    if(msg.replace(/^[\s　]+|[\s　]+$/g, "").length){
      $jsShowMsg.slideToggle('slow');
      setTimeout(function(){ $jsShowMsg.slideToggle('slow'); }, 5000);
    };

    // アコーディオン
    $('.js-cp-accordion01__title').on('click', function () {
      $(this).next().slideToggle(200);
      $(this).toggleClass('active', 200);
    });

    // スムーススクロール
    $('a[href^="#"]').click(function(){
      var speed = 300;
      var href= $(this).attr("href");
      var target = $(href == "#" || href == "" ? 'html' : href);
      var position = target.offset().top;
      $("html, body").animate({scrollTop:position}, speed, "swing");
      return false;
    });

    //画像ライブプレビュー
    var $dropArea = $('.area-drop');
    var $fileInput = $('.input-file');
    $dropArea.on('dragover', function(e){
      console.log($dropArea);
      e.stopPropagation();
      e.preventDefault();
      $(this).css('border', '3px #ccc dashed');
    });
    $dropArea.on('dragleave', function(e){
      e.stopPropagation();
      e.preventDefault();
      $(this).css('border', 'none');
    });
    $fileInput.on('change', function(e){
      $dropArea.css('border', 'none');
      var file = this.files[0],     //2.files配列にファイルが入っている
          $img = $(this).siblings('.prev-img'),     //3.jQueryのsublingsメソッドで兄弟のimgを取得
          fileReader = new FileReader();           //4.ファイルを読み込むFileReaderオブジェクト

      //5.読み込みが完了した際のイベントハンドラ。imgのsrcにデータをセット
      fileReader.onload = function(event){
        //読み込んだデータをimgに設定
        $img.attr('src', event.target.result).show();
      };

      //6.画像読み込み
      fileReader.readAsDataURL(file);
    });
});
