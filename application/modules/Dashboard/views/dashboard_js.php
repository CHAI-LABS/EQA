<script>
$(document).ready(function(){

	var facility = $('#facility').attr('data-type'); 

	// alert("reached5");

    $.get("<?=@base_url('Dashboard/PassFailGraph/');?>" + facility, function(ChartData){
	    	

        $('#graph-1').replaceWith('<canvas id="graph-1"></canvas>');

        var ctx5 = document.getElementById('graph-1');
        var chart = new Chart(ctx5, {
            type: 'bar',
            data: ChartData,
            options: {
                title:{
                    display:false,
                    text:"Participant Outcome Trends"
                },
                tooltips: {
                    mode: 'index',
                    intersect: false
                },
                responsive: true,
                scales: {
                    xAxes: [{
                        stacked: true,
                    }],
                    yAxes: [{
	    				stacked: true,
                        type: "linear", // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
                        display: true,
                        position: "left",
                        id: "y-axis-1",
                    }, {
                        type: "linear", // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
                        display: true,
                        position: "right",
                        id: "y-axis-2",

                        // grid line settings
                        gridLines: {
                            drawOnChartArea: false, // only want the grid lines for one axis to show up
                        },
                    }]
                }
            }
        });
    });



    $.get("<?=@base_url('Dashboard/PassFailRateGraph/');?>" + facility, function(ChartData){
    	// alert("reached5");

        $('#graph-2').replaceWith('<canvas id="graph-2"></canvas>');

        var ctx5 = document.getElementById('graph-2');
        var chart = new Chart(ctx5, {
            type: 'bar',
            data: ChartData,
            options: {
                title:{
                    display:false,
                    text:"Participant Outcome Trends (%)"
                },
                tooltips: {
                    mode: 'index',
                    intersect: false
                },
                responsive: true,
                scales: {
                    xAxes: [{
                        stacked: true,
                    }],
                    yAxes: [{
	    				stacked: true,
                        type: "linear",
                        display: true,
                        position: "left",
                        id: "y-axis-1",
                    }]
                }
            }
        });
    });



    

                   
});    
</script>