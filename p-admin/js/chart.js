function column_chart(title,titleh,titlev,cdata,id){
    $(document).ready(function(){
        google.charts.load('current', {packages: ['corechart', 'bar']});
        google.charts.setOnLoadCallback(drawBasic);

        function drawBasic() {
            var data = google.visualization.arrayToDataTable(cdata);

            var options = {
              title: title,
              hAxis: {
                title: titleh,
              },
              vAxis: {
                title: titlev
              }
            };

            var chart = new google.visualization.ColumnChart(
              document.getElementById(id));

            chart.draw(data, options);
        }
    });
}


