<div class="row">
	<div class="col-sm-12">

	<div class = 'alert alert-warning'>
        <h5>Refreshing the page will cause a loss of some data, and would require another login attempt, through your email</h5>
    </div>


	<div class="col-sm-12">

	    <div class="card">
	        <div class="card-header">
	            <strong>Corrective and Preventive Action Form (CAPA)</strong>
	        </div>
	        <div class="card-block">
	          <form method = "post" action="<?php echo base_url('Participant/Participant/submitCAPA');?>" class="p-a-4" id="page-signin-form">
				<input type="hidden" class="page-signin-form-control form-control" value="<?= @$pt_uuid; ?>" id="capa-form" name="ptround">

				<div class = "row">
				    <div class="col-md-12">
				        <div class = "card">
				            <div class="card-header col-4">
				                <i class = "icon-chart"></i>
				                &nbsp;

			                    <strong>Description of Occurrence</strong>

				            </div>

				            <div class = "card-block">
				            	<div class="col-md-3">
				            		CD4 Results that you provided
				            		<br/> 
			                		<?= @$sampledata; ?>
			                	</div> 

			                	<div class="col-md-9">
				                    <div class = "form-group">
				                        1. Explanation of the incorrect results
				                        
			                            <textarea id="occurrence" name="occurrence" rows="8" maxlength="500" class="form-control" placeholder="Enter reason here..."></textarea>
				                    </div>
			                    </div>

				            </div>
				        </div>
				    </div>
				</div>

				<div class = "row">
				    <div class="col-md-12">
				        <div class = "card">
				            <div class="card-header col-4">
				                <i class = "icon-chart"></i>
				                &nbsp;

			                    <strong>Root Cause</strong>

				            </div>

				            <div class = "card-block">
					            <div class="col-md-12">
			                    	<div class="form-group col-md-4">
		                        		1. Please select applicable testing phase(s)
								    </div>
			                    	<div class="col-md-8">
		                        		<div class="checkbox">
		                        			<label class="checkbox-inline" for="testing1" />Pre-analytical 
	                                        <input type="checkbox" id="testing1" name="tests[]" value="Pre-Analytical">

	                                        &nbsp;&nbsp;<label class="checkbox-inline" for="testing2" />Analytical
	                                        <input type="checkbox" id="testing2" name="tests[]" value="Analytical">

	                                        &nbsp;&nbsp;<label class="checkbox-inline" for="testing3" />Post-analytical
	                                        <input type="checkbox" id="testing3" name="tests[]" value="Post-Analytical">
	                                    </div>
								    </div>
			                    </div>

			                    <div class="col-md-12">
				                    <div class = "col-md-4 form-group">
				                        2. Description of root cause
				                        <br/><br/>
				                    </div>

				                    <div class="col-md-8">
			                            <textarea id="cause" name="cause" rows="8" maxlength="500" class="form-control" placeholder="Enter cause(s) here..."></textarea>
				                    </div>
			                    </div>

			                    <div class="col-md-12">
			                    	<div class="col-md-4">
		                        		3. Attributing factor(s)
								    </div>
			                    	<div class="col-md-8">
		                        		<div class="checkbox">
		                        			<label class="checkbox-inline" for="attribute1" />Equipment failure 
	                                        <input type="checkbox" id="attribute1" name="attribute[]" value="Equipment-Failure">

	                                        &nbsp;&nbsp;<label class="checkbox-inline" for="attribute2" />Personnel Error
	                                        <input type="checkbox" id="attribute2" name="attribute[]" value="Personnel-Error">

	                                        &nbsp;&nbsp;<label class="checkbox-inline" for="attribute3" />Other
	                                        <input type="checkbox" id="attribute3" name="attribute[]" value="Other">
										</div>
										<div id="otherdiv" class="otherdiv">
	                                        	Please Specify
			                        			<br/><br/>
	                                        	<textarea id="other" name="other" rows="8" maxlength="500" class="form-control" placeholder="Enter specifics here..."></textarea>
	                                    </div>
								    </div>
			                    </div>

				            </div>

				        </div>
				    </div>
				</div>

				<div class = "row">
				    <div class="col-md-12">
				        <div class = "card">
				            <div class="card-header col-4">
				                <i class = "icon-chart"></i>
				                &nbsp;

			                    <strong>Corrective Action</strong>

				            </div>

				            <div class = "card-block">
				            <div class="col-md-12">
			                    <div class = "form-group">
			                        1. Describe corrective measures taken
			                        <br/><br/>
			                        
		                            <textarea id="correction" name="correction" rows="8" maxlength="500" class="form-control" placeholder="Enter measures here..."></textarea>
			                    </div>
		                    </div>
		                    <br/><br/>
		                    <div>
		                    	<div class="col-md-6">
	                        		2. Was the corrective action effective?
							    </div>
		                    	<div class="col-md-6">
	                        		<label class="radio-inline" for="effective1" />
            							<input type="radio" id="effective1" name="effective" value="1">Yes</label>
	    							<label class="radio-inline" for="effective0" />
										<input type="radio" id="effective0" name="effective" value="0">No</label>
							    </div>
		                    </div>
				            </div>

				        </div>
				    </div>
				</div>

				<div class = "row">
				    <div class="col-md-12">
				        <div class = "card">
				            <div class="card-header col-4">
				                <i class = "icon-chart"></i>
				                &nbsp;

			                    <strong>Preventive Action</strong>

				            </div>

				            <div class = "card-block">
				            <div class="col-md-12">
			                    <div class = "form-group">
			                        1. Describe action(s) taken to prevent recurrence
			                        <br/><br/>
			                        
		                            <textarea id="prevention" name="prevention" rows="8" maxlength="500" class="form-control" placeholder="Enter description here..."></textarea>
			                    </div>
		                    </div>
				            </div>

				        </div>
				    </div>
				</div>


                <button id="submit-capa" type="submit" class="btn btn-block btn-primary">Submit CAPA Form to NHRL</button>
          	</form>
	            
	        </div>
	    </div>
	</div>


</div>