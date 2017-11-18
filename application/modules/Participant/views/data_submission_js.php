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


   	$(".check-complete").click(function(e) {

        e.preventDefault();
        var equipmentid = $(this).val();

  		 // alert(equipmentid);
        swal({
            title: "Are you sure you want to Mark as Complete ?",
            text: "Once marked as complete, changes won't be made on this equipment",
            type: "warning",
            showCancelButton: true,
            closeOnConfirm: false,
            showLoaderOnConfirm: true
        }, function(){
            $.get('<?= @base_url('Participant/PTRound/EquipmentComplete/'); ?>'+equipmentid+'/'+round, function(data){
                if(data.response == true){
                    window.location = "<?= @base_url('Participant/PTRound/Round/'); ?>"+round;
                    $("#data-info").html("Saving Data ...");
                }else{
                    sweetAlert("Oops...", data.message, "error");
                }
            });
        });
    });

 //    if(document.getElementById('isAgeSelected').checked) {
	//   $(":input:not([name=tloEnable], [name=filename], [name=notifyUsers])")
 //        .prop("disabled", true);
	// }



   });

</script>