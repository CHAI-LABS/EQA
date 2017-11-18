<script>
$(document).ready(function(){

	$('input[name="expiry_date[]"]').datepicker({
            todayHighlight: true
    });

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

		county = c.options[c.selectedIndex].value;
		facility = f.options[f.selectedIndex].value;

		changeFacility(round,county,facility);
  	});



  	$(document).on('change','#facility-select',function(){
  		// alert("facility changed");
		var c = document.getElementById("county-select");
		var f = document.getElementById("facility-select");

		county = c.options[c.selectedIndex].value;
		facility = f.options[f.selectedIndex].value;

       changeFacility(round,county,facility);
  	});





  	function changeFacility(round, county, facility){
  		
  		$.get("<?=@base_url('Program/getFacilities/');?>" + county, function(facilities){
        	var facOptions = '';

         	facilities.forEach(function(facil) {
			    facOptions += "<option value="+ facil.facility_id +">" + facil.facility_name + "</option>";
			});

			document.getElementById('facility-select').innerHTML += facOptions;
	    });

	    $.get("<?=@base_url('PTRounds/getRound/');?>" + round + '/' + facility, function(formdata){
        	// console.log(formdata['equipment_tabs']);
			// $('#content').replaceWith(formdata['equipment_tabs']);
			document.getElementById('content').innerHTML += formdata['equipment_tabs'];

	    });

		if($(".form").length){
			console.log("Loaded");
		}else{
			alert("Click OK once forms have loaded");
		}

    }

	var round_uuid = $(".ptround").val();



	$("form").submit(function(e){
		// alert("submitting");
		 e.preventDefault();
		  var form = $(this);
		  var id = form.attr('id');
	      // alert('id');
		  var formData = new FormData(this);

		dataSubmit(id, formData);
	 
	});

	function dataSubmit(equipmentid,formData){
		 // alert(facility);

		 if(facility == 0){
		 	alert("Please select a facility first");
		 }else{
		 	$.ajax({
			   	type: "POST",
			   	url: "<?= @base_url('PTRounds/dataSubmission/'); ?>"+equipmentid+ '/' +round_uuid,
				data: formData,
	            processData: false,
	            contentType: false,
			   success: function(html){   
	            // alert(html);
			   		if(html){

	                	$("#data-info").html(html);
	                    window.location = "<?= @base_url('PTRounds/SubmitReport/'); ?>"+round_uuid;
	                }else{
	                	
	                	$("#data-info").html("Failed to save the data ...");
	                	// window.location = "<?= @base_url('PTRounds/PTRounds/SubmitReport/'); ?>"+round_uuid;
	                }	
			   },
	           error: function(){

	           },
			   beforeSend:function()
			   {
				// $("#add_err").css('display', 'inline', 'important');
				// $("#add_err").html("<img src='images/ajax-loader.gif' /> Loading...")
			   }
		  	});
		 }


	  	
	}

	$('#add-reagent').click(function(){
        var items = $('tr.reagent_row').length;
        console.log(items);
        if(items == 11){
            alert("Cannot add more Reagents. Maximum limit exceeded");
        }else if(items == 10){
            $(this).attr('disabled', 'disabled');
            alert("These are now 10")
            addReagentRow(items);
        }else{
            addReagentRow(items);
        }
        
    });

    function addReagentRow(no_items){
        $('tr.reagent_row').eq(no_items-2).after("<?= @$row_blueprint; ?>");
    }






                   
});    
</script>