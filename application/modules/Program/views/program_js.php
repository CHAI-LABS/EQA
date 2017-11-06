<script>
$(document).ready(function(){

	var round = ''; 
	var county = '';
	var facility = '';

	$('#round-select, #county-select, #facility-select').select2();

	

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
	        // console.log(ChartData);
	        
	        var ctx = document.getElementById('graph-1');
	        var chart = new Chart(ctx, {
	            type: 'bar',
	            data: barChartData,
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



                   
});    
</script>