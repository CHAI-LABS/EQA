<script>   
    $(document).ready(function(){

    	$('#ptround-form').validate({
			rules: {
				question_1: {
					required: true
				},
				question_2: {
					required: true
				},
				question_3: {
					required: true
				},
				question_5: {
					required: true
				}
			},
			messages : {
				question_1: {
					required: "Please select answer for Question 1"
				},
				question_2: {
					required: "Please select answer for Question 2"
				},
				question_3: {
					required: "Please select answer for Question 3"
				},
				question_5: {
					required: "Please select answer for Question 5"
				}
			}
		});


    	var question_4_1 = document.getElementById("question_4_1");
    	var question_4_2_0 = document.getElementById("question_4_2_1");
    	var question_4_2_1 = document.getElementById("question_4_2_0");


	   	$('#checkNoAnswers').hide();
		question_4_1.disabled = true;
		question_4_2_0.disabled = true;
		question_4_2_1.disabled = true;

		$(question11).val();
        var question11= $(this).attr('question_1_1');
        var question21= $(this).attr('question_2_1');
        var question31= $(this).attr('question_2_1');


	$('input[type="radio"]').click(function() {
       if(($(this).attr('id') == 'question_1_0')) {
	   	    $divcheck = $('#checkNoAnswers').is(":visible"); 	
	       	if(!($divcheck)){
				activatequestion();
	       	}
            
       	}
   });

	$('input[type="radio"]').click(function() {
       if(($(this).attr('id') == 'question_2_0')) {
	   	    $divcheck = $('#checkNoAnswers').is(":visible"); 	
	       	if(!($divcheck)){
				activatequestion();
	       	}
            
       	}
   });

	$('input[type="radio"]').click(function() {
       if(($(this).attr('id') == 'question_3_0')) {
	   	    $divcheck = $('#checkNoAnswers').is(":visible"); 	
	       	if(!($divcheck)){
				activatequestion();
	       	}
            
       	}
   });

	$('input[type="radio"]').click(function() {
	 	if(($(this).attr('id') == 'question_1_1')){
       		$checkNo = checkIfNoAnswer();
	       	if(!($checkNo)){
	       		deactivatequestion(); 
	       	}
       	}
   });

	$('input[type="radio"]').click(function() {
	 	if(($(this).attr('id') == 'question_2_1')){
       		$checkNo = checkIfNoAnswer();
	       	if(!($checkNo)){
	       		deactivatequestion(); 
	       	}
       	}
   });

	$('input[type="radio"]').click(function() {
	 	if(($(this).attr('id') == 'question_3_1')){
       		$checkNo = checkIfNoAnswer();
	       	if(!($checkNo)){
	       		deactivatequestion(); 
	       	}
       	}
   });



	function activatequestion(){
		$('#checkNoAnswers').slideDown();  
        question_4_1.disabled = false;
		question_4_2_0.disabled = false;
		question_4_2_1.disabled = false;  

		$('#ptround-form').validate({
			rules: {
				question_4_1: {
					required: true
				},
				question_4_2: {
					required: true
				}
			},
			messages : {
				question_4_1: {
					required: "Please select answer for Question 4.1"
				},
				question_4_2: {
					required: "Please select answer for Question 4.2"
				}
			}
		});
	}

	function deactivatequestion(){
		$('#checkNoAnswers').slideUp();
		question_4_1.disabled = true;
		question_4_2_0.disabled = true;
		question_4_2_1.disabled = true;
	}

	function checkIfNoAnswer(){
		$que1 = $('#question_1_0').is(":checked"); 
		$que2 = $('#question_2_0').is(":checked"); 
		$que3 = $('#question_3_0').is(":checked");

		if($que1 || $que2 || $que3){
			return true;
		} else{
			return false;
		}
	}


    });
</script>