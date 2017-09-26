<script>   
    $(document).ready(function(){

    	$('#ptround-form').validate({
			rules: {
				occurrence: {
					required: true
				},
				tests: {
					required: true
				},
				cause: {
					required: true
				},
				attribute: {
					required: true
				},
				correction: {
					required: true
				},
				effective: {
					required: true
				},
				prevention: {
					required: true
				}
			},
			messages : {
				occurrence: {
					required: "Please enter the explanation of the incorrect values"
				},
				tests: {
					required: "Please select at least one test"
				},
				cause: {
					required: "Please enter the description of the root cause"
				},
				attribute: {
					required: "Please select at least one attribute"
				},
				correction: {
					required: "Please enter the description of the corrective measures taken"
				},
				effective: {
					required: "Please choose if the corrective action was effective or not"
				},
				prevention: {
					required: "Please enter the description of the action(s) taken to prevent recurrence"
				}
			}
		});


    	var other = document.getElementById("other");


	   	$('#otherdiv').hide();
		other.disabled = true;

	$('input[type="checkbox"]').click(function() {
       if(($(this).attr('id') == 'attribute3')) {
	   	    $divcheck = $('#otherdiv').is(":visible"); 	
	       	if(!($divcheck)){
				activateother();
	       	}
            
       	}
   });

	$('input[type="checkbox"]').click(function() {
	 	if(($(this).attr('id') == 'attribute3')){
       		$checkNo = checkIfOther();
	       	if(!($checkNo)){
	       		deactivateother(); 
	       	}
       	}
   });


	function activateother(){
		$('#otherdiv').slideDown();  
        other.disabled = false;

		$('#capa-form').validate({
			rules: {
				other: {
					required: true
				}
			},
			messages : {
				other: {
					required: "Please specify other attribute of failure"
				}
			}
		});
	}

	function deactivateother(){
		$('#otherdiv').slideUp();
		other.disabled = true;
	}

	function checkIfOther(){
		$ot = $('#attribute3').is(":checked"); 

		if($ot){
			return true;
		} else{
			return false;
		}
	}


    });


</script>