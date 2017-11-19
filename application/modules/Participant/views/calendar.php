
	<div class="row">
		<div class = "block">
		<strong id="participant-uuid" data-type="<?= @$participant->uuid; ?>"></strong>
	    <div class = "block col-md-3">
	        <fieldset class="form-group">
	            <label>Round</label>
	            <?= @$round_option; ?>
	        </fieldset>
	    </div>
	</div>
	</div>

<div id="calendar-view">Loading Calendar...</div>

	<!-- <div class="row">
		<div class="col-md-8">
			<div class="card">
				<div class="card-header">
					<div class="col-sm-8">
						PT Round: (<?= @$dashboard_data->pt_round->pt_round_no;?>) Calendar
					</div>
					<div class="col-sm-4">
					<?php
						if($dashboard_data->pt_round->type == "ongoing"){
					?>
							<a href = "<?= @base_url('Participant/PTRound/Round/' . $dashboard_data->pt_round->uuid); ?>" class = "btn btn-primary pull-right">Open Round</a>
					<?php
						}
					?>
					</div>
				</div>
				<div class="card-block">
					<table class = "table table-bordered">
						<tr>
							<td>
								<p>Round Duration</p>
								<h6>
									From: <?= @date('d/m/Y', strtotime($dashboard_data->pt_round->from)); ?> To: <?= @date('d/m/Y', strtotime($dashboard_data->pt_round->to)); ?>
								</h6>
							</td>

							<td>
								<p>Total Days Left</p>
								<h6>
									<?php
										$date_time_to = date_create($dashboard_data->pt_round->to);
										$data_time_now = date_create(date('Y-m-d'));
										$difference = date_diff($date_time_to, $data_time_now);

										echo $difference->format('%a Days');
									?>
								</h6>
							</td>
							<td style="background-color: <?= @$dashboard_data->calendar_current->color; ?>;">
								<p>Current Item By Calendar</p>
								<h6>
									<?= @$dashboard_data->calendar_current->name; ?>
								</h6>
							</td>
						</tr>
					</table>

					<div id = "calendar"></div>
				</div>
			</div>
		</div>
		<div class="col-md-4" style="position:fixed;top: 20%;right: -5%;">
			<div class="card">
				<div class="card-block">
					<h5 class = "mb-1">Legend</h5>
					<hr>
					<?= @$dashboard_data->calendar_legend; ?>
				</div>
			</div>
		</div>
	</div> -->