var chart;

$(document).ready(function() {

  //window.onload = function(){
  //  loading();
  //}

  drawGraph('totalAssets', 0);
  getExpenseList();
  loading();

  $('[name=graphList]').change(function() {
      $("#container").empty();
      $("#loading").show();
      var graphType = $('[name=graphList]').val();
      var txt = $('[name=graphList] option:selected').text();
      drawGraph(graphType, txt);
      loading();
  });

});

function loading() {
  $("#container").fadeIn();
  $("#loading").fadeOut(3000);
}
function getExpenseList() {
  $.ajax({
    type: 'GET',
    url: 'gnucash_expense_list.php',
    dataType: 'json',
    success: function(json) {
      for(var i in json.guid){
        $('<option value="'+json.guid[i]+'">'+json.name[i]+'</option>').insertAfter('#monthlyAsset');
      }
    }
  });
}
function drawGraph(graphType, txt) {
  $.ajax({
    type: 'GET',
    url: 'gnucash.php',
    dataType: 'json',
    data: {graphType: graphType, name: txt},
    success: function(json) {
      var chartOptions = getChartOptions(json);
      chart = new Highcharts.Chart(chartOptions);
    }
  });
}

function getChartOptions(data) {
  var chartOptions = {
    chart: {
        //グラフ表示させるdivをidで設定
        renderTo: 'container',
        //グラフ右側のマージンを設定
        marginRight: 140,
        //グラフ左側のマージンを設定
        marginBottom: 40
    },
    //グラフのタイトルを設定
    title: {
        text: data.setting.title
    },
    //x軸の設定
    xAxis: {
        title: {
            text: 'day'
        },
        //x軸に表示するデータを設定
        categories:data.date,
        //小数点刻みにしない
        allowDecimals: false
    },
    //y軸の設定
    yAxis: [{
        title: {
            //タイトル名の設定
            text: "yen",
            style: {
               //タイトルの色を設定
               color: '#4572A7',
            }
        },
        //y軸の表記設定
      labels: {
        formatter: function() {
          return (this.value  + "yen");
        }
      },
      //小数点刻みにしない
        allowDecimals: true,
        //最大値を設定
        //max: 8000000,
        max: data.setting.yMax,
        //最小値を設定
        //min: 2000000
        min: data.setting.yMin
    }],
    //グラフにマウスオーバーすると出てくるポップアップの表示設定
    tooltip: {
        formatter: function () {
            return '<b>' + this.series.name + '</b><br/>' +
    this.x + ': ' + this.y + "yen";
        }
    },
    //凡例の設定
    legend: {
        //凡例が縦に並ぶ
        layout: 'vertical',
        //凡例の横位置
        align: 'right',
        //凡例の縦位置
        verticalAlign: 'top'
    },
    //グラフデータの設定
    series: [{
        //名前を設定
        name: data.setting.name,
        //色の設定
        color: '#4572A7',
        //グラフタイプの設定(column：棒グラフ)  pie：円グラフ  line:折れ線グラフ
        type: 'line',
        //x,y軸の設定
        data: data.total
    }]
  };
  return chartOptions
}
