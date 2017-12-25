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
		// var c = document.getElementById("county-select");
		// var f = document.getElementById("facility-select");

		var round = r.options[r.selectedIndex].value;
		// var county = c.options[c.selectedIndex].value;
		// var facility = f.options[f.selectedIndex].value;

		var county = 0;
		var facility = 0;

        changeGraphs(round,county,facility);
  	});

  	$(document).on('change','#county-select',function(){
  		// alert("changed");
  		$("#facility-select").empty();

  		document.getElementById('facility-select').innerHTML = "<option selected='selected' value = '0' > All Facilities</option>";

       	var r = document.getElementById("round-select");
		var c = document.getElementById("county-select");
		var f = document.getElementById("facility-select");


		var round = r.options[r.selectedIndex].value;
		var county = c.options[c.selectedIndex].value;
		var facility = f.options[f.selectedIndex].value;
			
		    changeGraphs(round,county,facility);
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

  		swal({
		  position: 'top-right',
		  type: 'info',
		  title:'Please wait',
		  text: 'Loading Graphs',
		  timer: 7000,
		  showConfirmButton: false
		});

		// swal({
		//   title: "Good job!",
		//   text: "You clicked the button!",
		//   icon: "success",
		// });

  		var axisname = '';

    	if(county !== 0 && facility == 0){
    		axisname = 'Facilities';
    	}else if(facility !== 0 && county !== 0){
    		axisname = 'Participants';
    	}else if(county == 0 && facility == 0){
    		axisname = 'Counties';
    	}else{
    		axisname = 'Count';
    	}


  		$.get("<?=@base_url('Program/getFacilities/');?>" + county, function(facilities){
        	var facOptions = '';

         	facilities.forEach(function(facil) {
			    facOptions += "<option value="+ facil.facility_id +">" + facil.facility_name + "</option>";
			});

			document.getElementById('facility-select').innerHTML += facOptions;
	    });


  		var divs = document.getElementsByClassName('criteria');
  		var div9 = document.getElementById('criteria2');

  		if(county == 0 && facility == 0){
  			for (var i = 0; i < divs.length; i++) {
				    divs[i].innerHTML = "National";
				}
  		}else{
  			$.get("<?=@base_url('Program/getName/');?>" + county + '/' + facility, function(criteria){
	        	
				for (var i = 0; i < divs.length; i++) {
				    divs[i].innerHTML = criteria;

				    if(county !== 0 && facility == 0){
				    	document.getElementById('criteria2').innerHTML = criteria + " Facilities";
				    }   
				}
		    });
  		}


  		



	    $.get("<?=@base_url('Program/OverallResponses/');?>" + round + '/' + county + '/' + facility, function(ChartData){
	    	// console.log(ChartData);

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
	    	// console.log(ChartData.datasets);
	    	
	    	
	        $('#graph-2').replaceWith('<canvas id="graph-2"></canvas>');

	        var part = ChartData['no_participants'];
	        var pass = ChartData['datasets']['0']['data']['0'];
	        var fail = ChartData['datasets']['0']['data']['1'];



	    	document.getElementById('part').innerHTML = part;
	    	document.getElementById('pass').innerHTML = pass;
	    	document.getElementById('fail').innerHTML = fail;

	    	$('#failure').replaceWith('<div id="failure" style="color: blue;"><a class="failedlinks">No. of failed : <strong id="fail"> '+fail+' </strong> (View)</a></div>');

	    	$("a.failedlinks").click(function(){
	    		swal({
					  // position: 'top-right',
					  type: 'info',
					  title:'Please Wait !',
					  html: 'Loading Failed Participants',
					  width: '800px',
					  showConfirmButton: false
					})

		   	$.get("<?=@base_url('Program/createFailedParticipants/');?>" + round + '/' + county + '/' + facility, function(table){
			   		swal({
					  // position: 'top-right',
					  type: 'info',
					  title:'Unable to Respond!',
					  html: table,
					  width: '800px',
					  showConfirmButton: true
					})

					// console.log(table);
				});
			 });

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
	        
	        $('#graph-3').replaceWith('<canvas id="graph-3"></canvas>');

	    	var roundname1 = ChartData['round'];
	    	var enrolled = ChartData['datasets']['0']['data']['0'];
	        var partno = ChartData['datasets']['1']['data']['0'];
	        var nonresp = ChartData['datasets']['2']['data']['0'];
	        var unable = ChartData['datasets']['3']['data']['0'];
	        var disqualified = ChartData['datasets']['4']['data']['0'];
	        var resp = ChartData['responsive'];


	    	document.getElementById('enrolled').innerHTML = enrolled;
	    	document.getElementById('partno').innerHTML = partno;
	    	document.getElementById('disqualified').innerHTML = disqualified;
	    	document.getElementById('unable').innerHTML = unable;
	    	document.getElementById('nonresp').innerHTML = nonresp;
	    	document.getElementById('resp').innerHTML = resp;


	    //     var ctx3 = document.getElementById('graph-3');
	    //     var chart = new Chart(ctx3, {
	    //         type: 'bar',
	    //         data: ChartData,
	    //         options: {
	    //             legend: {
	    //             	backgroundColor: "rgba(255,99,132,0.2)",
					    
	    //                 display: true,
	    //                 position: 'right',
	    //                 fullWidth: true,
	    //                 labels: {
	    //                     fontColor: 'rgb(0, 0, 0)'
	    //                 }
	    //             },
	    //             scales: {
	    //                 yAxes: [{
	    //                     ticks: {
	    //                         beginAtZero:true
	    //                     },
	    //                     scaleLabel: {
	    //                     	display: true,
					//             labelString: 'Number of Participants'
					//         }
	    //                 }]
	    //             },
	    //             tooltips: {
			  //           mode: 'nearest',
			  //           intersect: true
			  //       },
			  //       datasets: [{
					//     dataLabels: { 
					//     	display: true,          //  disabled by default
					//         colors: ['#fff', '#ccc', '#000'], //  Array colors for each labels
					//         minRadius: 30, //  minimum radius for display labels (on pie charts)
					//         align: 'start',
					//         anchor: 'start'
					//     },
					//     borderColor: "rgba(255,99,132,1)",
					//     borderWidth: 2,
					//     hoverBackgroundColor: "rgba(255,99,132,0.4)",
					//     hoverBorderColor: "rgba(255,99,132,1)",
					// }],
	    //             responsive: true,
   	 // 				maintainAspectRatio: false
	    //         }
	    //     });


	    });


	    $.get("<?=@base_url('Program/DisqualifiedParticipants/');?>" + round + '/' + county + '/' + facility, function(ChartData){
	    	// alert("reached4");
	    	// console.log(ChartData.datasets);

	        $('#graph-4').replaceWith('<canvas id="graph-4"></canvas>');

	        var roundname2 = ChartData['round'];
	        // var equip = ChartData['datasets']['0']['data']['0'];
	        // var reag = ChartData['datasets']['1']['data']['0'];
	        // var anal = ChartData['datasets']['2']['data']['0'];
	        // var pend = ChartData['datasets']['3']['data']['0'];

	        // var equip = ChartData['datasets']['0']['data']['0'];
	        // var reag = ChartData['datasets']['0']['data']['1'];
	        // var anal = ChartData['datasets']['0']['data']['2'];
	        // var pend = ChartData['datasets']['0']['data']['3'];

	        


	    	// document.getElementById('roundname2').innerHTML = roundname2;
	    	// document.getElementById('equip').innerHTML = equip;
	    	// document.getElementById('reag').innerHTML = reag;
	    	// document.getElementById('anal').innerHTML = anal;
	    	// document.getElementById('pend').innerHTML = pend;


	        var ctx4 = document.getElementById('graph-4');
	        var chart = new Chart(ctx4, {
	   //      	type: 'pie',
				// data: ChartData,
		  //       options: {
			 //        datasets: [{
				// 	    dataLabels: { 
				// 	    	display: true,         
				// 	        colors: ['#fff', '#ccc', '#000'], 
				// 	        minRadius: 30,
				// 	        align: 'start',
				// 	        anchor: 'start'
				// 	    }
				// 	}],
				// 	cutoutPercentage: 0,
		  //           responsive: true,
				// 	    pieceLabel: {
				// 		    render: 'percentage',
				// 		    fontColor: ['black', 'black', 'black'],
				// 		    precision: 2,
				// 		    position: 'outside'
				// 		  }
		  //       }
	            type: 'bar',
	            data: ChartData,
	            options: {
	                title:{
	                    display:false,
	                    text: "Report / Disqualified Outcome"
	                },
	                legend: {
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
					            labelString: 'Rounds'
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
					            labelString: 'Number of Participants'
					        }
	                    }]
	                },
	            }
	        });
	    });


	    $.get("<?=@base_url('Program/PassFailGraph/');?>" + round + '/' + county + '/' + facility, function(ChartData){
	    	// alert("reached5");

	        $('#graph-5').replaceWith('<canvas id="graph-5"></canvas>');

	        var ctx5 = document.getElementById('graph-5');
	        var chart = new Chart(ctx5, {
	            type: 'line',
	            data: ChartData,
	            options: {
	                title:{
	                    display:true,
	                    text:ChartData['title'] + " Trends"
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
	                    	scaleLabel: {
	                        	display: true,
					            labelString: 'Number of Participants'
					        },
		    				stacked: true,
	                        type: "linear", // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
	                        display: true,
	                        position: "left",
	                        id: "y-axis-1"
	                    }]
	                }
	            }
	        });
	    });



	    $.get("<?=@base_url('Program/PassFailRateGraph/');?>" + round + '/' + county + '/' + facility, function(ChartData){
	    	// alert("reached5");

	        $('#graph-6').replaceWith('<canvas id="graph-6"></canvas>');

	        var ctx6 = document.getElementById('graph-6');
	        var chart = new Chart(ctx6, {
	            type: 'line',
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
	                    	scaleLabel: {
	                        	display: true,
					            labelString: 'Score (%)'
					        },
		    				stacked: true,
	                        type: "linear",
	                        display: true,
	                        position: "left",
	                        id: "y-axis-1"
	                    }]
	                }
	            }
	        });
	    });




	    $.get("<?=@base_url('Program/ResondentNonGraph/');?>" + round + '/' + county + '/' + facility, function(ChartData){
	    	// alert("reached6");

	        $('#graph-7').replaceWith('<canvas id="graph-7"></canvas>');

	        var ctx7 = document.getElementById('graph-7');
	        var chart = new Chart(ctx7, {
	            type: 'bar',
	            data: ChartData,
	            options: {
	                title:{
	                    display:false,
	                    text:"Participant Responsiveness Trends"
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
	                    	scaleLabel: {
	                        	display: true,
					            labelString: 'Number of Participants'
					        },
		    				stacked: true,
	                        type: "linear", // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
	                        display: true,
	                        position: "left",
	                        id: "y-axis-1",
	                    }]
	                }
	            }
	        });
	    });


	    $.get("<?=@base_url('Program/ResondentNonRateGraph/');?>" + round + '/' + county + '/' + facility, function(ChartData){
	    	// alert("reached6");

	        $('#graph-8').replaceWith('<canvas id="graph-8"></canvas>');

	        var ctx8 = document.getElementById('graph-8');
	        var chart = new Chart(ctx8, {
	            type: 'line',
	            data: ChartData,
	            options: {
	                title:{
	                    display:false,
	                    text:"Participant Responsiveness Trends (%)"
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
	                    	scaleLabel: {
	                        	display: true,
					            labelString: 'Score (%)'
					        },
		    				stacked: true,
	                        type: "linear", // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
	                        display: true,
	                        position: "left",
	                        id: "y-axis-1",
	                    }]
	                }
	            }
	        });
	    });


	    $.get("<?=@base_url('Program/OverallOutcomeGraph/');?>" + round + '/' + county + '/' + facility, function(ChartData){

	    	// alert("reached7");
	        $('#graph-9').replaceWith('<canvas id="graph-9"></canvas>');

	        var roundname3 = ChartData['round'];

	        var ctx9 = document.getElementById('graph-9');


	        if(facility == 0){
	        	var chart = new Chart(ctx9, {
		            type: 'bar',
		            data: ChartData,
		            options: {
		                title:{
		                    display:false,
		                    text:"Participants Outcome"
		                },
		                legend: {
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
						            labelString: 'Number of ' + ChartData['x_axis_name']
						        }
		                    }, {
		                        type: "linear", // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
		                        display: true,
		                        position: "right",
		                        id: "y-axis-2",
		                        scaleLabel: {
		                        	display: true,
						            labelString: 'Score (%)'
						        },
		                        // grid line settings
		                        gridLines: {
		                            drawOnChartArea: false, // only want the grid lines for one axis to show up
		                        },
		                    }]
		                },
		            }
		        });

	        }else{
	        	var chart = new Chart(ctx9, {
	        		type: 'line',
		            data: ChartData,
		            options: {
		                title:{
		                    display:false,
		                    text:"Participant Trends (%)"
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
						        }
		                    }],
		                    yAxes: [{
		                    	scaleLabel: {
		                        	display: true,
						            labelString: ChartData['y_axis_name']
						        },
			    				stacked: true,
		                        type: "linear", // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
		                        display: true,
		                        position: "left"
		                    }]
		                }
		            }
		        });
	        }

	    });



    }


    
                   
});    
</script>