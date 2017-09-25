<?php
    $colspan = "4";
?>

<div class = "row">
    <div class="col-md-12">
        <div class = "card">
            <div class="card-header col-4">
                <i class = "icon-user"></i>
                &nbsp;
                <?= @$pagetitle; ?>
                
            </div>
            <div class = "card-block">
                <div class = "table-responsive">
                    <table class = "table table-outline mb-0 hidden-sm-down">
                        <thead class = "thead-default">
                            <th colspan = "<?= @$colspan; ?>"><center>Future Rounds</center></th>
                        </thead>
                        <thead>
                            <th>Round ID</th>
                            <th>Created</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </thead>
                        <tbody>
                            <?php if($pt_rounds['future']) { ?>
                                <?= @$pt_rounds['future']; ?>
                            <?php } else { ?>
                            <tr>
                                <td colspan = "<?= @$colspan; ?>"><center>There are no future rounds</center></td>
                            </tr>
                            <?php } ?>
                        </tbody>

                        <thead class = "thead-default">
                            <th colspan = "<?= @$colspan; ?>"><center>Ongoing Round</center></th>
                        </thead>
                        <thead id = "title">
                            <th>Round ID</th>
                            <th>Created</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </thead>
                        <tbody>
                            <?php if($pt_rounds['ongoing']) { ?>
                                <?= @$pt_rounds['ongoing']; ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan = "<?= @$colspan; ?>"><center>There are no ongoing rounds</center></td>
                                </tr>
                            <?php } ?>
                        </tbody>

                        <thead class = "thead-default">
                            <th colspan = "<?= @$colspan; ?>"><center>Previous Rounds</center></th>
                        </thead>
                        <thead>
                            <th>Round ID</th>
                            <th>Created</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </thead>
                        <tbody>
                            <?php if($pt_rounds['previous']) { ?>
                                <?= @$pt_rounds['previous']; ?>
                            <?php } else { ?>
                            <tr>
                                <td colspan = "<?= @$colspan; ?>"><center>There are no previous rounds</center></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                        
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>