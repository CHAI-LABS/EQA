<script>
$(document).ready(function(){

	// document.getElementById('enrolled').innerHTML = enrolled;
	// $('#content').replaceWith('<div id="content">Facility '+facility+' selected</div>');

	// $.get("<?=@base_url('Program/ParticipantPass/');?>" + round + '/' + county + '/' + facility, function(ChartData){

	// }

	var round = $('#round').attr('data-type'); 
	var county = 0;
	var facility = 0;

	changeFacility(round,county,facility);

	$('#county-select, #facility-select').select2();

  	$(document).on('change','#county-select',function(){
  		// alert("changed");
  		$("#facility-select").empty();

  		document.getElementById('facility-select').innerHTML = "<option selected='selected' value = '0' > All Facilities</option>";

		var c = document.getElementById("county-select");
		var f = document.getElementById("facility-select");

		var county = c.options[c.selectedIndex].value;
		var facility = f.options[f.selectedIndex].value;

		$.get("<?=@base_url('Program/getFacilities/');?>" + county, function(facilities){
        	var facOptions = '';

         	facilities.forEach(function(facil) {
			    facOptions += "<option value="+ facil.facility_id +">" + facil.facility_name + "</option>";
			});

			document.getElementById('facility-select').innerHTML += facOptions;
	    });
			
		    changeFacility(round,county,facility);
  	});



  	$(document).on('change','#facility-select',function(){
  		// alert("changed");
		var c = document.getElementById("county-select");
		var f = document.getElementById("facility-select");

		var county = c.options[c.selectedIndex].value;
		var facility = f.options[f.selectedIndex].value;

       changeFacility(round,county,facility);
  	});


  	function changeFacility(round,county, facility){
  		

    }

                   
});    
</script>