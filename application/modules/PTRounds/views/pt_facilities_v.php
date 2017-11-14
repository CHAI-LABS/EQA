<style type="text/css">
	.facility-details .card{
		border-top-width: 1px;
	}

	a.btn{
		color: white !important;
	}
</style>
<div class="card-group facility-details">
	<?= @$statistics; ?>
</div>

<div class="form-group row">
	<div class="col-md-5">
		<div class="input-group">
			<select name="type" class="form-control" placeholder="Filter">
			</select>
			<span class="input-group-btn">
				<button type="button" class="btn btn-primary">Filter</button>
			</span>
		</div>
	</div>
	<div class="col-md-7">
		<div class = "pull-right">
			<a href = "<?= @base_url('PTRounds/sendemails/' . $pt_details->uuid); ?>" class = "btn btn-warning"><i class = "fa fa-mail-forward"></i>&nbsp;&nbsp;Send Emails</a>
			<a href = "" class = "btn btn-success"><i class = "fa fa-file-excel-o"></i>&nbsp;&nbsp;Download Excel</a>
		</div>
	</div>
</div>
<table class = "table table-bordered" id = "facilities">
	<thead>
		<th>MFL Code</th>
		<th>Facility Name</th>
		<!-- <th>Status</th>
		<th>Smart Status</th> -->
		<th>Actions</th>
	</thead>
</table>