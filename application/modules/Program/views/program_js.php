<script>
$(document).ready(function(){

	var round = $('#round').attr('data-type'); 
	var county = 0;
	var facility = 0;

	changeGraphs(round,county,facility);

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

	    $.get("<?=@base_url('Program/OverallResponses/');?>" + round + '/' + county + '/' + facility, function(ChartData){
	        // console.log(ChartData);

	        var ctx1 = document.getElementById('graph-1');
	        var chart = new Chart(ctx1, {
	             	type: 'pie',
    				data: ChartData,
			        options: {
				        datasets: [{
						    dataLabels: { 
						    	display: true,          //  disabled by default
						        colors: ['#fff', '#ccc', '#000'], //  Array colors for each labels
						        minRadius: 30, //  minimum radius for display labels (on pie charts)
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
	        // console.log(ChartData);

	        var ctx2 = document.getElementById('graph-2');
	        var chart = new Chart(ctx2, {
	             	type: 'pie',
    				data: ChartData,
			        options: {
				        datasets: [{
						    dataLabels: { 
						    	display: true,          //  disabled by default
						        colors: ['#fff', '#ccc', '#000'], //  Array colors for each labels
						        minRadius: 30, //  minimum radius for display labels (on pie charts)
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
	        // console.log(ChartData);

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
	        // console.log(ChartData);

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
	        // console.log(ChartData);

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
	        // console.log(ChartData);

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


		// var barChartData = {
  //           labels: ["January", "February", "March", "April", "May", "June", "July"],
  //           datasets: [{
  //               label: 'Dataset 1',
  //               backgroundColor: '#ccc',
  //               data: [10, 20, 30, 40]
  //           }, {
  //               label: 'Dataset 2',
  //               backgroundColor: '#000',
  //               data: [10, 30, 50, 70]
  //           }, {
  //               label: 'Dataset 3',
  //               backgroundColor: 'rgba(127,140,141,0.5)',
  //               data: [10, 40, 80, 120],
  //               type: 'line'
  //           }]

  //       };


        // var ctx = document.getElementById("graph-5").getContext("2d");
        // window.myBar = new Chart(ctx, {
        //     type: 'bar',
        //     data: barChartData,
        //     options: {
        //         title:{
        //             display:true,
        //             text:"Chart.js Bar Chart - Stacked"
        //         },
        //         tooltips: {
        //             mode: 'index',
        //             intersect: false
        //         },
        //         responsive: true,
        //         scales: {
        //             xAxes: [{
        //                 stacked: true,
        //             }],
        //             yAxes: [{
        //                 stacked: true
        //             }]
        //         }
        //     }
        // });
        




    }

                   
});    
</script>