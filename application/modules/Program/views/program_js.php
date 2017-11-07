<script>
$(document).ready(function(){

	var round = $('#round').attr('data-type'); 
	var county = 0;
	var facility = 0;

	$('#round-select, #county-select, #facility-select').select2();


	$.get("<?=@base_url('Program/OverallInfo/');?>" + round + '/' + county + '/' + facility, function(ChartData){
        // console.log(ChartData);
        
        // var ctx = document.getElementById('graph-3');
        // var chart = new Chart(ctx, {
        //     type: 'bar',
        //     data: ChartData,
        //     options: {
        //         legend: {
        //             display: true,
        //             labels: {
        //                 fontColor: 'rgb(0, 0, 0)'
        //             }
        //         },
        //         scales: {
        //             yAxes: [{
        //                 ticks: {
        //                     beginAtZero:false
        //                 }
        //             }]
        //         },
        //         responsive: true
        //     }
        // });


        var ctx = document.getElementById('graph-3').getContext("2d");
        var chart = new Chart(ctx, {
            type: 'bar',
            data: ChartData,
            options: {
                legend: {
                    display: true,
                    labels: {
                        fontColor: 'rgb(0, 0, 0)'
                    }
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero:false
                        }
                    }]
                },
                tooltips: {
		            mode: 'nearest',
		            intersect: true
		        },
		        datasets: [{
				    dataLabels: { 
				    	display: true,          //  disabled by default
				        colors: ['#fff', '#ccc', '#000'], //  Array colors for each labels
				        minRadius: 30, //  minimum radius for display labels (on pie charts)
				        align: 'start',
				        anchor: 'start'
				    }
				}],
                responsive: true
            }
        });

    });

	

	$(document).on('change','#round-select',function(){
		var r = document.getElementById("round-select");
		var c = document.getElementById("county-select");
		var f = document.getElementById("facility-select");


		var round = r.options[r.selectedIndex].value;
		var county = c.options[c.selectedIndex].value;
		var facility = f.options[f.selectedIndex].value;

        changeGraphs(round,county,facility);
  	});

  	$(document).on('change','#county-select',function(){
  		$("#facility-select").empty();

       	var r = document.getElementById("round-select");
		var c = document.getElementById("county-select");
		var f = document.getElementById("facility-select");


		var round = r.options[r.selectedIndex].value;
		var county = c.options[c.selectedIndex].value;
		var facility = 0;

		if(county == 0){
			
		}else{
			
			$.get("<?=@base_url('Program/getFacilities/');?>" + county, function(counties){
	        	var facOptions = '';

	         	counties.forEach(function(county) {
				    facOptions += "<option value="+ county.facility_id +">" + county.facility_name + "</option>";
				});

				document.getElementById('facility-select').innerHTML += facOptions;
		    });
		}

        changeGraphs(round,county,facility);
  	});

  	$(document).on('change','#facility-select',function(){
       	var r = document.getElementById("round-select");
		var c = document.getElementById("county-select");
		var f = document.getElementById("facility-select");


		var round = r.options[r.selectedIndex].value;
		var county = c.options[c.selectedIndex].value;
		var facility = f.options[f.selectedIndex].value;

       changeGraphs(round,county,facility);
  	});


  	function changeGraphs(round, county, facility){
        // alert(" Round  = " + round + " County = " + county + " Facility = " + facility);
        $.get("<?=@base_url('Program/OverallInfo/');?>" + round + '/' + county + '/' + facility, function(ChartData){
	        console.log(ChartData);
	        
	        var ctx = document.getElementById('graph-1');
	        var chart = new Chart(ctx, {
	            type: 'bar',
	            data: ChartData,
	            options: {
	                legend: {
	                    display: true,
	                    labels: {
	                        fontColor: 'rgb(0, 0, 0)'
	                    }
	                },
	                scales: {
	                    yAxes: [{
	                        ticks: {
	                            beginAtZero:false
	                        }
	                    }]
	                },
	                responsive: true
	            }
	        });
	    });




    }



    // Chart.plugins.register({
    //         afterDatasetsDraw: function(chart, easing) {
    //             // To only draw at the end of animation, check for easing === 1
    //             var ctx = chart.ctx;

    //             chart.data.datasets.forEach(function (dataset, i) {
    //                 var meta = chart.getDatasetMeta(i);
    //                 if (!meta.hidden) {
    //                     meta.data.forEach(function(element, index) {
    //                         // Draw the text in black, with the specified font
    //                         ctx.fillStyle = 'rgb(0, 0, 0)';

    //                         var fontSize = 16;
    //                         var fontStyle = 'normal';
    //                         var fontFamily = 'Helvetica Neue';
    //                         ctx.font = Chart.helpers.fontString(fontSize, fontStyle, fontFamily);

    //                         // Just naively convert to string for now
    //                         var dataString = dataset.data[index].toString();

    //                         // Make sure alignment settings are correct
    //                         ctx.textAlign = 'center';
    //                         ctx.textBaseline = 'middle';

    //                         var padding = 5;
    //                         var position = element.tooltipPosition();
    //                         ctx.fillText(dataString, position.x, position.y - (fontSize / 2) - padding);
    //                     });
    //                 }
    //             });
    //         }
    //     });



                   
});    
</script>