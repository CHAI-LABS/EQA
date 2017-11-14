<script>
$(document).ready(function(){

	var round = $('#round').attr('data-type'); 
	var county = 0;
	var facility = 0;

	changeGraphs(round,county,facility);

	$('#round-select, #county-select, #facility-select').select2();


	$(document).on('change','#round-select',function(){
		// alert("changed");
		var r = document.getElementById("round-select");
		var c = document.getElementById("county-select");
		var f = document.getElementById("facility-select");


		var round = r.options[r.selectedIndex].value;
		var county = c.options[c.selectedIndex].value;
		var facility = f.options[f.selectedIndex].value;

        changeGraphs(round,county,facility);
  	});

  	$(document).on('change','#county-select',function(){
  		// alert("changed");
  		$("#facility-select").empty();

  		document.getElementById('facility-select').innerHTML = "<option selected='selected' value=0> All Facilities</option>";

       	var r = document.getElementById("round-select");
		var c = document.getElementById("county-select");
		var f = document.getElementById("facility-select");


		var round = r.options[r.selectedIndex].value;
		var county = c.options[c.selectedIndex].value;
		var facility = 0;

		if(county == 0){
			changeGraphs(round,0,0);
		}else{
			
			$.get("<?=@base_url('Program/getFacilities/');?>" + county, function(counties){
	        	var facOptions = '';

	         	counties.forEach(function(county) {
				    facOptions += "<option value="+ county.facility_id +">" + county.facility_name + "</option>";
				});

				document.getElementById('facility-select').innerHTML += facOptions;
		    });
		    changeGraphs(round,county,facility);
		}
  	});

  	$(document).on('change','#facility-select',function(){
  		// alert("changed");
       	var r = document.getElementById("round-select");
		var c = document.getElementById("county-select");
		var f = document.getElementById("facility-select");


		var round = r.options[r.selectedIndex].value;
		var county = c.options[c.selectedIndex].value;
		var facility = f.options[f.selectedIndex].value;

       changeGraphs(round,county,facility);
  	});


  	function changeGraphs(round, county, facility){

  		
  		
	    $.get("<?=@base_url('Program/OverallResponses/');?>" + round + '/' + county + '/' + facility, function(ChartData){
	    	// alert("reached1");

	    	$('#graph-1').replaceWith('<canvas id="graph-1"></canvas>');

	        var ctx1 = document.getElementById('graph-1');
	        var chart = new Chart(ctx1, {
	             	type: 'pie',
    				data: ChartData,
			        options: {
				        datasets: [{
						    dataLabels: { 
						    	display: true,       
						        colors: ['#fff', '#ccc', '#000'], 
						        minRadius: 30,
						        align: 'start',
						        anchor: 'start'
						    }
						}],
						cutoutPercentage: 0,
			            responsive: true,
						    pieceLabel: {
							    render: 'percentage',
							    fontColor: ['black', 'black', 'black'],
							    precision: 2,
							    position: 'outside'
							  }
			        }
	        });
	    });

	    $.get("<?=@base_url('Program/ParticipantPass/');?>" + round + '/' + county + '/' + facility, function(ChartData){
	    	// alert("reached2");

	        $('#graph-2').replaceWith('<canvas id="graph-2"></canvas>');

	        var part = ChartData['datasets']['0']['data']['0'];
	        var pass = ChartData['datasets']['0']['data']['1'];
	        var fail = ChartData['datasets']['0']['data']['2'];


	    	document.getElementById('part').innerHTML = part;
	    	document.getElementById('pass').innerHTML = pass;
	    	document.getElementById('fail').innerHTML = fail;

	        var ctx2 = document.getElementById('graph-2');
	        var chart = new Chart(ctx2, {
	             	type: 'pie',
    				data: ChartData,
			        options: {
				        datasets: [{
						    dataLabels: { 
						    	display: true,         
						        colors: ['#fff', '#ccc', '#000'], 
						        minRadius: 30,
						        align: 'start',
						        anchor: 'start'
						    }
						}],
						cutoutPercentage: 0,
			            responsive: true,
						    pieceLabel: {
							    render: 'percentage',
							    fontColor: ['black', 'black', 'black'],
							    precision: 2,
							    position: 'outside'
							  }
			        }
	        });
	    });


	    $.get("<?=@base_url('Program/OverallInfo/');?>" + round + '/' + county + '/' + facility, function(ChartData){
	    	// alert("reached3");
	        

	        $('#graph-3').replaceWith('<canvas id="graph-3"></canvas>');

	    	var roundname1 = ChartData['round'];
	    	var enrolled = ChartData['datasets']['0']['data']['0'];
	        var partno = ChartData['datasets']['1']['data']['0'];
	        var nonresp = ChartData['datasets']['2']['data']['0'];
	        var unable = ChartData['datasets']['3']['data']['0'];
	        var disqualified = ChartData['datasets']['4']['data']['0'];
	        var resp = ChartData['responsive'];


	    	document.getElementById('enrolled').innerHTML = enrolled;
	    	document.getElementById('roundname1').innerHTML = roundname1;
	    	document.getElementById('partno').innerHTML = partno;
	    	document.getElementById('disqualified').innerHTML = disqualified;
	    	document.getElementById('unable').innerHTML = unable;
	    	document.getElementById('nonresp').innerHTML = nonresp;
	    	document.getElementById('resp').innerHTML = resp;

	        var ctx3 = document.getElementById('graph-3');
	        var chart = new Chart(ctx3, {
	            type: 'bar',
	            data: ChartData,
	            options: {
	                legend: {
	                	backgroundColor: "rgba(255,99,132,0.2)",
					    
	                    display: true,
	                    position: 'right',
	                    fullWidth: true,
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
					    },
					    borderColor: "rgba(255,99,132,1)",
					    borderWidth: 2,
					    hoverBackgroundColor: "rgba(255,99,132,0.4)",
					    hoverBorderColor: "rgba(255,99,132,1)",
					}],
	                responsive: true,
   	 				maintainAspectRatio: false
	            }
	        });
	    });


	    $.get("<?=@base_url('Program/DisqualifiedParticipants/');?>" + round + '/' + county + '/' + facility, function(ChartData){
	    	// alert("reached4");


	        $('#graph-4').replaceWith('<canvas id="graph-4"></canvas>');

	        var roundname2 = ChartData['round'];
	        var equip = ChartData['datasets']['0']['data']['0'];
	        var reag = ChartData['datasets']['1']['data']['0'];
	        var anal = ChartData['datasets']['2']['data']['0'];
	        var pend = ChartData['datasets']['3']['data']['0'];


	    	document.getElementById('roundname2').innerHTML = roundname2;
	    	document.getElementById('equip').innerHTML = equip;
	    	document.getElementById('reag').innerHTML = reag;
	    	document.getElementById('anal').innerHTML = anal;
	    	document.getElementById('pend').innerHTML = pend;

	        var ctx4 = document.getElementById('graph-4');
	        var chart = new Chart(ctx4, {
	            type: 'bar',
	            data: ChartData,
	            options: {
	                legend: {
	                	backgroundColor: "rgba(255,99,132,0.2)",
					    
	                    display: true,
	                    position: 'right',
	                    fullWidth: true,
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
					    },
					    borderColor: "rgba(255,99,132,1)",
					    borderWidth: 2,
					    hoverBackgroundColor: "rgba(255,99,132,0.4)",
					    hoverBorderColor: "rgba(255,99,132,1)",
					}],
	                responsive: true,
   	 				maintainAspectRatio: false
	            }
	        });
	    });


	    $.get("<?=@base_url('Program/PassFailGraph/');?>" + round + '/' + county + '/' + facility, function(ChartData){
	    	// alert("reached5");

	        $('#graph-5').replaceWith('<canvas id="graph-5"></canvas>');

	        var ctx5 = document.getElementById('graph-5');
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
	                        stacked: true
	                    }]
	                }
	            }
	        });
	    });


	    $.get("<?=@base_url('Program/ResondentNonGraph/');?>" + round + '/' + county + '/' + facility, function(ChartData){
	    	// alert("reached6");
	       	

	        $('#graph-6').replaceWith('<canvas id="graph-6"></canvas>');

	        var ctx5 = document.getElementById('graph-6');
	        var chart = new Chart(ctx5, {
	            type: 'bar',
	            data: ChartData,
	            options: {
	                title:{
	                    display:false,
	                    text:"Participant Enrolled vs Responded Trends"
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
	                        stacked: true
	                    }]
	                }
	            }
	        });
	    });


	    $.get("<?=@base_url('Program/OverallOutcomeGraph/');?>" + round + '/' + county + '/' + facility, function(ChartData){

	    	// alert("reached7");
	        $('#graph-7').replaceWith('<canvas id="graph-7"></canvas>');

	        var roundname3 = ChartData['round'];

	        document.getElementById('roundname3').innerHTML = roundname3;

	        var ctx5 = document.getElementById('graph-7');
	        var chart = new Chart(ctx5, {
	            type: 'bar',
	            data: ChartData,
	            options: {
	                title:{
	                    display:false,
	                    text:ChartData['x_axis_name'] + " Outcome"
	                },
	                legend: {
	                	// backgroundColor: "rgba(255,99,132,0.2)",
					    
	                    display: true,
	                    position: 'top',
	                    fullWidth: true,
	                    labels: {
	                        fontColor: 'rgb(0, 0, 0)'
	                    }
	                },
	                tooltips: {
	                    mode: 'index',
	                    intersect: false
	                },
	                responsive: true,
	                scales: {
	                    xAxes: [{
	                        stacked: true,
	                        scaleLabel: {
	                        	display: true,
					            labelString: ChartData['x_axis_name']
					        },
					        ticks: {
					            stepSize: 1,
					            min: 0,
					            autoSkip: false
					        }
	                    }],
	                    yAxes: [{
	                        stacked: true,
	                        scaleLabel: {
	                        	display: true,
					            labelString: 'Number #'
					        }
	                    }]
	                },
	            }
	        });
	    });



    }

                   
});    
</script>