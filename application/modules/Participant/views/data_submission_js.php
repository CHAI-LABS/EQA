<script>

$(document).ready(function(){

    $('input[name="expiry_date[]"]').datepicker({
            todayHighlight: true
        });

	var round = $(".ptround").val();
	

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


    $("form").submit(function(e){
         e.preventDefault();
      var form = $(this);
      var id = form.attr('id');
      // alert('id');
      var formData = new FormData(this);

        dataSubmit(id, formData);
     
    });
	

	function dataSubmit(equipmentid,formData){
		 // alert(round);
	  	$.ajax({
		   	type: "POST",
		   	url: "<?= @base_url('Participant/PTRound/dataSubmission/'); ?>"+equipmentid+ '/' +round,
			data: formData,
            processData: false,
            contentType: false,
		   success: function(html){   
            // alert(html);
		   		if(html){

                	$("#data-info").html("Successfully saved the data");
                    window.location = "<?= @base_url('Participant/PTRound/Round/'); ?>"+round;
                }else{
                	
                	$("#data-info").html("Failed to save the data ...");
                	// window.location = "<?= @base_url('Participant/PTRound/Round/'); ?>"+round;
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


   	$(".unable").click(function(e) {
        var type = $(this).attr('data-type');
        var value = $(this).attr('data-value');

        if(type == 'unable'){
            // alert(value);
            swal(
              'Unable to Respond!',
              value,
              'error'
            )
        }
    });

 //    if(document.getElementById('isAgeSelected').checked) {
	//   $(":input:not([name=tloEnable], [name=filename], [name=notifyUsers])")
 //        .prop("disabled", true);
	// }


    



   });

</script>