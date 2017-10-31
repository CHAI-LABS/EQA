<script>
    $(document).ready(function(){
        
    'use strict';

    var round = $('#round').attr('data-type');


    // $.get("<?=@base_url('Analysis/graphExample/');?>", function(barChartData){

    //     // console.log(barChartData);
    //     var ctx = document.getElementById('test');
    //     var chart = new Chart(ctx, {
    //         type: 'bar',
    //         data: barChartData,
    //         options: {
    //             legend: {
    //                 display: true,
    //                 labels: {
    //                     fontColor: 'rgb(0, 0, 0)'
    //                 }
    //             },
    //             scales: {
    //                 yAxes: [{
    //                     ticks: {
    //                         beginAtZero:false
    //                     }
    //                 }]
    //             },
    //             responsive: true
    //         }
    //     });
    // });


    $.get("<?=@base_url('Analysis/ParticipationGraph/');?>" + round, function(barChartData){
        console.log(barChartData);
        
        var ctx = document.getElementById('participation');
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




    //Bar Graph Below


    // var randomScalingFactor = function(){ return Math.round(Math.random()*100)};
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
    // var ctx = document.getElementById('canvas-2');
    // var chart = new Chart(ctx, {
    //     type: 'bar',
    //     data: barChartData,
    //     options: {
    //         responsive: true
    //     }
    // });

    //Bar Graph Above



    // Line Graph Below

    // var randomScalingFactor = function(){ return Math.round(Math.random()*100)};
    // var lineChartData = {
    //     labels : ['January','February','March','April','May','June','July'],
    //     datasets : [
    //         {
    //             label: 'My First dataset',
    //             backgroundColor : 'rgba(220,220,220,0.2)',
    //             borderColor : 'rgba(220,220,220,1)',
    //             pointBackgroundColor : 'rgba(220,220,220,1)',
    //             pointBorderColor : '#fff',
    //             data : [randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor()]
    //         },
    //         {
    //             label: 'My Second dataset',
    //             backgroundColor : 'rgba(151,187,205,0.2)',
    //             borderColor : 'rgba(151,187,205,1)',
    //             pointBackgroundColor : 'rgba(151,187,205,1)',
    //             pointBorderColor : '#fff',
    //             data : [randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor()]
    //         }
    //     ]
    // }

    // var ctx = document.getElementById('canvas-1');
    // var chart = new Chart(ctx, {
    //     type: 'line',
    //     data: lineChartData,
    //     options: {
    //         responsive: true
    //     }
    // });



    //Line Graph Above



    // Donut Graph Below


    // var doughnutData = {
    //     labels: [
    //         'Red',
    //         'Green',
    //         'Yellow'
    //     ],
    //     datasets: [{
    //         data: [300, 50, 100],
    //         backgroundColor: [
    //             '#FF6384',
    //             '#36A2EB',
    //             '#FFCE56'
    //         ],
    //         hoverBackgroundColor: [
    //             '#FF6384',
    //             '#36A2EB',
    //             '#FFCE56'
    //         ]
    //     }]
    // };
    // var ctx = document.getElementById('canvas-3');
    // var chart = new Chart(ctx, {
    //     type: 'doughnut',
    //     data: doughnutData,
    //     options: {
    //         responsive: true
    //     }
    // });



    //Donut Graph Above


    // Radar Graph Below


    // var radarChartData = {
    //     labels: ['Eating', 'Drinking', 'Sleeping', 'Designing', 'Coding', 'Cycling', 'Running'],
    //     datasets: [
    //         {
    //             label: 'My First dataset',
    //             backgroundColor: 'rgba(220,220,220,0.2)',
    //             borderColor: 'rgba(220,220,220,1)',
    //             pointBackgroundColor: 'rgba(220,220,220,1)',
    //             pointBorderColor: '#fff',
    //             pointHighlightFill: '#fff',
    //             pointHighlightStroke: 'rgba(220,220,220,1)',
    //             data: [65,59,90,81,56,55,40]
    //         },
    //         {
    //             label: 'My Second dataset',
    //             backgroundColor: 'rgba(151,187,205,0.2)',
    //             borderColor: 'rgba(151,187,205,1)',
    //             pointBackgroundColor: 'rgba(151,187,205,1)',
    //             pointBorderColor: '#fff',
    //             pointHighlightFill: '#fff',
    //             pointHighlightStroke: 'rgba(151,187,205,1)',
    //             data: [28,48,40,19,96,27,100]
    //         }
    //     ]
    // };
    // var ctx = document.getElementById('canvas-4');
    // var chart = new Chart(ctx, {
    //     type: 'radar',
    //     data: radarChartData,
    //     options: {
    //         responsive: true
    //     }
    // });


    //Radar Graph Above



    //Pie Graph Below


    // var pieData = {
    //     labels: [
    //         'Red',
    //         'Green',
    //         'Yellow'
    //     ],
    //     datasets: [{
    //         data: [300, 50, 100],
    //         backgroundColor: [
    //             '#FF6384',
    //             '#36A2EB',
    //             '#FFCE56'
    //         ],
    //         hoverBackgroundColor: [
    //             '#FF6384',
    //             '#36A2EB',
    //             '#FFCE56'
    //         ]
    //     }]
    // };
    // var ctx = document.getElementById('canvas-5');
    // var chart = new Chart(ctx, {
    //     type: 'pie',
    //     data: pieData,
    //     options: {
    //         responsive: true
    //     }
    // });


    //Pie Graph Above



    // Polar Graph Below


    // var polarData = {
    //     datasets: [{
    //         data: [
    //             11,
    //             16,
    //             7,
    //             3,
    //             14
    //         ],
    //         backgroundColor: [
    //             '#FF6384',
    //             '#4BC0C0',
    //             '#FFCE56',
    //             '#E7E9ED',
    //             '#36A2EB'
    //         ],
    //         label: 'My dataset' // for legend
    //     }],
    //     labels: [
    //         'Red',
    //         'Green',
    //         'Yellow',
    //         'Grey',
    //         'Blue'
    //     ]
    // };
    // var ctx = document.getElementById('canvas-6');
    // var chart = new Chart(ctx, {
    //     type: 'polarArea',
    //     data: polarData,
    //     options: {
    //         responsive: true
    //     }
    // });



    // Polar Graph Above




});    
</script>