<script type="text/javascript">
	$(document).ready(function(){

		var r = document.getElementById("round-select");
		var round_uuid = r.options[r.selectedIndex].value; 
		var participant_uuid = $('#participant-uuid').attr('data-type');
		
		$('#round-select').select2();
		
		changeCalendar(participant_uuid, round_uuid);

		$(document).on('change','#round-select',function(){
			var r = document.getElementById("round-select");

			round_uuid = r.options[r.selectedIndex].value;
			
			changeCalendar(participant_uuid, round_uuid);
       
	  	});
				
		
	  	function changeCalendar(participant_uuid, round_uuid){
	  		
	  		$.get("<?=@base_url('Participant/getParticipantDashboardData/');?>" + participant_uuid + '/' + round_uuid, function(CalendarData){
	  			// alert(round_uuid);
		    	document.getElementById('calendar-view').innerHTML = CalendarData;
		    	// $('#calendar-view').replaceWith(CalendarData);

		    	if($('#calendar')[0]){
				$('#calendar').fullCalendar({
					header: {
						left: 'prev,next today',
						center: 'title',
						right: 'agendaYear,month,agendaWeek,agendaDay'
					},
					eventSources: [
						{
							url: '<?= @base_url('Participant/getCalendarData'); ?>' ,
							type: 'POST',
							data: {
								round_id : round_uuid
							},
							error: function() {
								alert('There was an error while fetching events!');
							}
						}
					]
				});	
			}else{
				alert("calendar not loading");
			}
			});


	  	}


		




	});
</script>