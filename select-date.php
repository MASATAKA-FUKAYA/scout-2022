<script>
    $(function(){
    function formSetDay(){
      var lastday = formSetLastDay($('.js-changeYear').val(), $('.js-changeMonth').val());
      var option = '';
      for (var i = 1; i <= lastday; i++) {
        if (i === $('.js-changeDay').val()){
          option += '<option value="' + i + '" selected="selected">' + i + '</option>\n';
        }else{
          option += '<option value="' + i + '">' + i + '</option>\n';
        }
      }
      $('.js-changeDay').html(option);
    }

    function formSetLastDay(year, month){
      var lastday = new Array('', 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);//0月はないので空文字
      if ((year % 4 === 0 && year % 100 !== 0) || year % 400 === 0){
        //閏年判定
        lastday[2] = 29;
      }
      return lastday[month];
    }

    $('.js-changeYear, .js-changeMonth').change(function(){
      formSetDay();
    });

    <?php
        $jsData['r_year'] = getFormdata('r_year');
        $jsData['r_month'] = getFormdata('r_month');
        $jsData['r_day'] = getFormdata('r_day');
        if(!empty($jsData['r_year'])) :
    ?>
        $(function(){
          $("#r_year").val("<?php echo $jsData['r_year']; ?>");

            <?php if(!empty($jsData['r_month'])) : ?>
              $("#r_month").val("<?php echo $jsData['r_month']; ?>");

                <?php if(!empty($jsData['r_day'])) : ?>
                  $("#r_day").val("<?php echo $jsData['r_day']; ?>");
                <?php endif; ?>

            <?php endif; ?>
        });
    <?php endif; ?>

  });
</script>