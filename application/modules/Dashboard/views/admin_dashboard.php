<!-- 
<div class="row">
    <div class="col-sm-6 col-lg-4">
        <div class="card">
            <div class="card-block">
                <div class="h4 m-0"><?= @$pending_participants; ?></div>
                <div class="pb-1">Users Pending Approval</div>
                <progress class="progress progress-xs progress-warning" value="100" max="100"></progress>
                <small class="text-muted">Users Pending Approval</small>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-4">
        <div class="card">
            <div class="card-block">
                <div class="h4 m-0"><?= @$new_equipments; ?></div>
                <div class="pb-1">New Equipmets registered by Users</div>
                <progress class="progress progress-xs progress-primary" value="100" max="100"></progress>
                <small class="text-muted">New Equipmets registered by Users</small>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-4">
        <div class="card">
            <div class="card-block">
                <div class="h4 m-0">220</div>
                <div class="pb-1">Submitted Readiness Forms by Participants</div>
                <progress class="progress progress-xs progress-success" value="100" max="100"></progress>
                <small class="text-muted">Submitted Readiness Forms by Participants</small>
            </div>
        </div>
    </div>

</div>


<div class="row">
    <div class="col-sm-6 col-lg-4">
        <div class="card">
            <div class="card-block">
                <div class="h4 m-0">215</div>
                <div class="pb-1">Participants who Received PT Panels at Site</div>
                <progress class="progress progress-xs progress-success" value="100" max="100"></progress>
                <small class="text-muted">Participants who Received PT Panels at Site</small>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-4">
        <div class="card">
            <div class="card-block">
                <div class="h4 m-0">5</div>
                <div class="pb-1">Participants not Received their PT Panels at Site</div>
                <progress class="progress progress-xs progress-danger" value="100" max="100"></progress>
                <small class="text-muted">Participants not Received their PT Panels at Site</small>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-4">
        <div class="card">
            <div class="card-block">
                <div class="h4 m-0">200</div>
                <div class="pb-1">Participants submitted Pending QA review</div>
                <progress class="progress progress-xs progress-success" value="100" max="100"></progress>
                <small class="text-muted">Participants submitted Pending QA review</small>
            </div>
        </div>
    </div>

</div>


<div class="row">
    <div class="col-sm-6 col-lg-4">
        <div class="card">
            <div class="card-block">
                <div class="h4 m-0">15</div>
                <div class="pb-1">Participants yet to submit QA review</div>
                <progress class="progress progress-xs progress-warning" value="100" max="100"></progress>
                <small class="text-muted">Participants yet to submit QA review</small>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-4">
        <div class="card">
            <div class="card-block">
                <div class="h4 m-0">212</div>
                <div class="pb-1">Participants completed and reviewed QA</div>
                <progress class="progress progress-xs progress-success" value="100" max="100"></progress>
                <small class="text-muted">Participants that have completed and reviewed</small>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-4">
        <div class="card">
            <div class="card-block">
                <div class="h4 m-0">3</div>
                <div class="pb-1">Participants not completed and reviewed QA</div>
                <progress class="progress progress-xs progress-alert" value="100" max="100"></progress>
                <div class="pb-1">Participants that have not completed and reviewed</div>
            </div>
        </div>
    </div>


</div> -->

<div class="card-group mb-1">
    <div class="card">
        <div class="card-block">
            <div class="h1 text-muted text-xs-right mb-2">
                <i class="icon-user-following"></i>
            </div>
            <div class="h4 mb-0"><?= @$pending_participants; ?></div>
            <small class="text-muted text-uppercase font-weight-bold">Users Pending Approval</small>
        </div>
    </div>
    <div class="card">
    	<div class="card-block">
            <div class="h1 text-muted text-xs-right mb-2">
                <i class="icon-wrench"></i>
            </div>
            <div class="h4 mb-0"><?= @$new_equipments; ?></div>
            <small class="text-muted text-uppercase font-weight-bold">New User Registered Equipment</small>
        </div>
    </div>
    <div class="card">
    	<div class="card-block">
            <div class="h1 text-muted text-xs-right mb-2">
                <i class="icon-like"></i>
            </div>
            <div class="h4 mb-0"><?= @$stats->readiness_submissions; ?></div>
            <small class="text-muted text-uppercase font-weight-bold">Readiness Submissions</small>
        </div>
    </div>
    <div class="card">
    	<div class="card-block">
            <div class="h1 text-muted text-xs-right mb-2">
                <i class="icon-envelope-open"></i>
            </div>
            <div class="h4 mb-0"><?= @$stats->received_panels; ?></div>
            <small class="text-muted text-uppercase font-weight-bold">Received PT Panels</small>
        </div>
    </div>
    <div class="card">
    	<div class="card-block">
            <div class="h1 text-muted text-xs-right mb-2">
                <i class="icon-envelope"></i>
            </div>
            <div class="h4 mb-0"><?= @$stats->not_received_panels; ?></div>
            <small class="text-muted text-uppercase font-weight-bold">PT Panels Not Yet Received</small>
        </div>
    </div>
</div>

<!-- <div class="row">
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-block">
                <div class="h4 m-0">200</div>
                <div class="pb-1">Pending QA review</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-block">
                <div class="h4 m-0">200</div>
                <div class="pb-1">Yet to Submit Response</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-block">
                <div class="h4 m-0">200</div>
                <div class="pb-1">Completed and Reviewed</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-block">
                <div class="h4 m-0">200</div>
                <div class="pb-1">Not completed and Reviewed</div>
            </div>
        </div>
    </div>
</div> -->

<!-- <div class="row">
    <div class="col-md-12">
        <divc class="card">
            <div class="card-body">
                <div class="card-block">
                    <div class="col-md-6">
                        <a href = "<?= @base_url('PTRounds/Unable/'); ?><?= @$round_uuid?>"> <button class = "btn btn-primary btn-sm"><i class = "fa fa-arrow-right"></i> View Participants Unable to Respond  </button></a>
                    </div>
                </div>
            </div>
        </divc>
    </div>
</div> -->

<div class="row">
    <div class="col-md-12">
        <divc class="card">
            <div class="card-header">
                Calendar
            </div>
            <div class="card-body">
                <div class="card-block">
                    <div class="col-md-6">
                        <a href = "<?= @base_url('PTRounds/calendar/'); ?><?= @$round_uuid?>"> <button class = "btn btn-primary btn-sm"><i class = "fa fa-arrow-up"></i> Click to view current PT calendar  </button></a>
                    </div>
                    <div class="col-md-6">
                        <a href = "<?= @base_url('PTRounds/create/calendar/'); ?><?= @$round_uuid?>"> <button class = "btn btn-primary btn-sm"><i class = "fa fa-arrow-down"></i> Click to edit current PT calendar </button></a>
                    </div>
                </div>
            </div>
        </divc>
    </div>
</div>