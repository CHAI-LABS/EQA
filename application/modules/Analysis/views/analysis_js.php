<script>
    $(document).ready(function(){

        $('table').dataTable({
        	"createdRow": function( row, data, dataIndex){
                if( data[10] ==  `Unsatisfactory Performance`){
                    $(row).addClass('red');
                }else if(data[10] ==  `Incomplete Submission`){
                	$(row).addClass('orange');
                }
            }
        });

    var randomScalingFactor = function(){ return Math.round(Math.random()*100)};
    // var barChartData = {
    //     labels : ['January','February','March','April','May','June','July'],
    //     datasets : [
    //         {
    //             backgroundColor : 'rgba(220,220,220,0.5)',
    //             borderColor : 'rgba(220,220,220,0.8)',
    //             highlightFill: 'rgba(220,220,220,0.75)',
    //             highlightStroke: 'rgba(220,220,220,1)',
    //             data : [randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor()]
    //         },
    //         {
    //             backgroundColor : 'rgba(151,187,205,0.5)',
    //             borderColor : 'rgba(151,187,205,0.8)',
    //             highlightFill : 'rgba(151,187,205,0.75)',
    //             highlightStroke : 'rgba(151,187,205,1)',
    //             data : [randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor()]
    //         }
    //     ]
    // }

    var barChartData = {"labels":["SS-R17-036","SS-R17-037","SS-R17-038"],"datasets":[{"label":"CD3","backgroundColor":"rgba(220,220,220,0.5)","data":["474.0571428571429","474.4","432.14285714285717"]},{"label":"CD4","backgroundColor":"rgba(151,187,205,0.5)","data":["469.42857142857144","537.7714285714286","389.37142857142857"]}]};

    $.get("<?=@base_url('Analysis/graphExample/');?>", function(res){
        barChartData = res;

        var ctx = document.getElementById('canvas-2');
        var chart = new Chart(ctx, {
            type: 'bar',
            data: barChartData,
            options: {
                responsive: true
            }
        });
    })
    
                   
});    
</script>