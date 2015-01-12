var chart;

$(document).ready(function() {

  getMonthList('#startMonth');
  getMonthList('#endMonth');
  loading();

  $('[name=display]').click(function() {
      $("#container").empty();
      $("#loading").show();
      var startMonth = $('[name=startMonth]').val();
      var endMonth = $('[name=endMonth]').val();
      getProfitAndLoss(startMonth, endMonth);
      loading();
  });

});

function loading() {
  $("#container").fadeIn();
  $("#loading").fadeOut(3000);
}
function getMonthList(dateType) {
  $.ajax({
    type: 'GET',
    url: 'month_list.php',
    dataType: 'json',
    success: function(json) {
      for(var i in json.month){
        var mLen = json.month.length - 1;
        $('<option value="'+json.month[i]+'">'+json.month[i]+'</option>').appendTo(dateType);
        // 当月を選択状態にする
        if (mLen == i) {
          $('[value='+json.month[i]+']').attr('selected', 'selected');
        }
      }
    }
  });
}
function getProfitAndLoss(startMonth, endMonth) {
  $.ajax({
    type: 'GET',
    url: 'profit_and_loss.php',
    dataType: 'json',
    data: {startMonth: startMonth, endMonth: endMonth},
    success: function(json) {
      $('<table id="profitAndLoss"></table>').appendTo('#container');
      for(var i in json.accountName){
        $('<tr id="accountName'+i+'"></tr>').appendTo('#profitAndLoss');
        $('<th style="text-align: left;">'+json.accountName[i]+'</th>').appendTo('#accountName'+i);
        $('<td>'+json.amount[i]+'</td>').appendTo('#accountName'+i);
      }
    }
  });
}

