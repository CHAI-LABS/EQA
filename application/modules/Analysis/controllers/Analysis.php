<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Analysis extends DashboardController {

	public function __construct(){
		parent::__construct();

		$this->load->library('table');
        $this->load->config('table');
        $this->load->module('Export');
		$this->load->model('Analysis_m');

	}
	
	public function index()
	{	
		$data = [];
        $title = "Analysis";

        $data = [
            'page_title' => 'PT Round List',
            'back_text' => 'Back to Dashboard',
            'back_link' => base_url('Dashboard/'),
            'table_view'    =>  $this->createPTTable()Parti
        ];

        $this->assets
                ->addJs("dashboard/js/libs/jquery.dataTables.min.js")
                ->addJs("dashboard/js/libs/dataTables.bootstrap4.min.js")
                ->addJs('dashboard/js/libs/jquery.validate.js')
                ->addJs('dashboard/js/libs/select2.min.js');
        $this->assets->setJavascript('Analysis/analysis_js');
        $this->template
                ->setPageTitle($title)
                ->setPartial('Analysis/analysis_v', $data)
                ->adminTemplate();
	}


    public function Capa()
    {   
        $data = [];
        $title = "Capa Analysis";

        $data = [
            'page_title'    => 'Capa List',
            'back_text'     => 'Back to Dashboard',
            'back_link'     => base_url('Analysis/'),
            'table_view'    =>  $this->createCapaTable()
        ];

        $this->assets
                ->addJs("dashboard/js/libs/jquery.dataTables.min.js")
                ->addJs("dashboard/js/libs/dataTables.bootstrap4.min.js")
                ->addJs('dashboard/js/libs/jquery.validate.js')
                ->addJs('dashboard/js/libs/select2.min.js');
        $this->assets->setJavascript('Analysis/analysis_js');
        $this->template
                ->setPageTitle($title)
                ->setPartial('Analysis/analysis_v', $data)
                ->adminTemplate();
    }


    public function createCapaTable(){
        $template = $this->config->item('default');

        $heading = [
            "No.",
            "Participant",
            "Date Submitted",
            "Reviewed",
            "Actions"
        ];
        $tabledata = [];
        $this->db->where('approved',1);
        $capas = $this->db->get('capa_review')->result();
        
        if($capas){
            $counter = 0;
            foreach($capas as $capa){
                // echo "<pre>";print_r($capa);echo "</pre>";die();
                $counter ++;

                $participant_id = $this->db->get_where('participant_readiness_v', ['uuid' => $capa->participant_uuid])->row()->username;
                
                if($capa->status == 0){
                    $status = "<label class = 'tag tag-warning tag-sm'>Not Reviewed</label>"; 
                }else{
                    $status = "<label class = 'tag tag-success tag-sm'>Reviewed</label>";
                }

                $date = date('dS F, Y', strtotime($capa->date_of_submission));

                $view = "<a class = 'btn btn-info btn-sm dropdown-item' href = '".base_url('Analysis/CapaView/' . $capa->participant_uuid .'/'. $capa->round_uuid)."'><i class = 'icon-eye'></i>&nbsp;View</a>";

                if($capa->status == 0){
                    $review = "<a class = 'btn btn-success btn-sm dropdown-item' href = '".base_url('Analysis/MarkReview/1/' . $capa->id)."'><i class = 'icon-eye'></i>&nbsp;Mark as Reviewed</a>";
                }else{
                    $review = "<a class = 'btn btn-danger btn-sm dropdown-item' href = '".base_url('Analysis/MarkReview/0/' . $capa->id)."'><i class = 'icon-eye'></i>&nbsp;Mark as not Reviewed</a>";
                }

                $dropdown = "<div class = 'dropdown'>
                            <button class = 'btn btn-secondary dropdown-toggle' type = 'button' id = 'dropdownMenuButton1' data-toggle = 'dropdown' aria-haspopup='true' aria-expanded = 'true'>
                                Quick Actions
                            </button>
                            <div class = 'dropdown-menu' aria-labelledby= = 'dropdownMenuButton'>
                                $view
                                $review
                            </div>
                        </div>";
                
                $tabledata[] = [
                    $counter,
                    $participant_id,
                    $date,
                    $status,
                    $dropdown
                ];
            }
        }
        $this->table->set_heading($heading);
        $this->table->set_template($template);

        return $this->table->generate($tabledata);
    }

    public function CapaView($participant_uuid, $round_uuid){
        $data = [];
        $title = "Capa View";

        // echo "<pre>";print_r($rounds);echo "</pre>";die();

        $data = [
            'page_title'    => 'Capa View',
            'back_text'     => 'Back to CAPA Participants',
            'back_link'     => base_url('Analysis/CAPA/'.$round_uuid),
            'capa_view'    =>  $this->createCapaView($participant_uuid, $round_uuid)
        ];

        $this->assets
                ->addJs("dashboard/js/libs/jquery.dataTables.min.js")
                ->addJs("dashboard/js/libs/dataTables.bootstrap4.min.js")
                ->addJs('dashboard/js/libs/jquery.validate.js')
                ->addJs('dashboard/js/libs/select2.min.js');
        $this->assets->setJavascript('Analysis/analysis_js');
        $this->template
                ->setPageTitle($title)
                ->setPartial('Analysis/capa_view', $data)
                ->adminTemplate();
    }

    function createCapaView($participant_uuid, $round_uuid){
        $capa_view = '';

        $this->db->where('approved',1);
        $this->db->where('round_uuid',$round_uuid);
        $this->db->where('participant_uuid',$participant_uuid);

        $capa = $this->db->get('capa_review')->row();

        $date = date('dS F, Y', strtotime($capa->date_of_submission));

        $participant_id = $this->db->get_where('participant_readiness_v', ['uuid' => $participant_uuid])->row()->username;

        if($capa->effective){
            $effect = "Yes";
        }else{
            $effect = "No";
        }

        // echo "<pre>";print_r($capa);echo "</pre>";die();

        $capa_view .= '<div class = "card">
                            <div class="card-header">
                                Occurrence Details
                            </div>

                            <div class = "card-block">
                                <div class="col-sm-4"><strong>Participant ID</strong></div>
                                <div class="col-sm-8">' . $participant_id . '</div>
                                <br/>&nbsp;<br/>
                                <div class="col-sm-4"><strong>Description of the occurrence</strong></div>
                                <div class="col-sm-8">' . $capa->occurrence . '</div>
                            </div>
                        </div>';

        $capa_view .= '<div class = "card">
                            <div class="card-header">
                                Root Cause
                            </div>
                        <div class = "card-block">
                            <div class="col-sm-4"><strong>Selected applicable testing phase(s)</strong></div>
                            <div class="col-sm-8">';

        $tests = $this->db->get_where('capa_tests', ['capa_test_id' => $capa->id])->result();

        foreach ($tests as $test) {
            $capa_view .= $test->applied_test . '<br/>';
        }

        $capa_view .= '</div>
                            <br/>&nbsp;<br/>
                            <div class="col-sm-4"><strong>Description of root cause</strong></div>
                            <div class="col-sm-8">' . $capa->cause . '</div>
                            <br/>&nbsp;<br/>
                            <div class="col-sm-4"><strong>Attributing factor(s)</strong></div>
                            <div class="col-sm-4">';

        $attributes = $this->db->get_where('capa_attributes', ['capa_attribute_id' => $capa->id])->result();

        foreach ($attributes as $attribute) {
            if($attribute->attribute_factor == "Other"){
                $capa_view .= $attribute->attribute_factor . ' - ';
                $capa_view .= $attribute->specific_other . '<br/>';
            }else{
                $capa_view .= $attribute->attribute_factor . '<br/>';
            }
            
        }

        $capa_view .= '</div>
                        </div>
                        </div>

                        <div class = "card">
                            <div class="card-header">
                                Corrective Action
                            </div>

                            <div class = "card-block">

                                <div class="col-sm-4"><strong>Describe corrective measures taken</strong></div>
                                <div class="col-sm-8">' . $capa->correction . '</div>
                                <br/>&nbsp;<br/>
                                <div class="col-sm-4"><strong>Was the corrective action effective ?</strong></div>
                                <div class="col-sm-8">' . $effect . '</div>
                            </div>
                        </div>';

        $capa_view .= '<div class = "card">
                            <div class="card-header">
                                Preventive Action
                            </div>

                            <div class = "card-block">

                                <div class="col-sm-4"><strong>Describe action(s) taken to prevent recurrence</strong></div>
                                <div class="col-sm-8">' . $capa->prevention . '</div>
                            </div>
                        </div>';

        $supervisor = $this->db->get_where('participant_readiness_v', ['facility_id' => $capa->facility_id, 'user_type' => 'qareviewer'])->row();

        $capa_view .= '<div class = "card">
                            <div class="card-header">
                                Resolution
                            </div>

                            <div class = "card-block">
                                <div class="col-sm-4"><strong>QA / Supervisor</strong></div>
                                <div class="col-sm-8">' . $supervisor->firstname . '  ' . $supervisor->lastname . '</div>
                                <br/>&nbsp;<br/>
                                <div class="col-sm-4"><strong>Date of Completion</strong></div>
                                <div class="col-sm-8">' . $date . '</div>
                            </div>
                        </div>

                        <a href="'. base_url('Analysis/MarkReview/1/' . $capa->id) .'"><button id="submit-capa" type="submit" class="btn btn-block btn-primary">Mark as Reviewed</button></a>';
        

        return $capa_view;
    }

    function MarkReview($type, $capa_id){
        $response = [];

            $update_data = [];

            if($type == 1){
                $update_data = ['status'  =>  1];
            }else{
                $update_data = ['status'  =>  0];
            }

            $this->db->where('id', $capa_id);
            if($this->db->update('capa_response', $update_data)){
                $response = [
                    'status'    =>  TRUE,
                    'message'   =>  "Successfully Marked CAPA as Reviewed"
                ];
            }else{
                $response = [
                    'status'    =>  FALSE,
                    'message'   =>  "There was a problem marking the CAPA"
                ];
            }
        $this->Capa();
    }

	public function createPTTable()
	{

		$template = $this->config->item('default');

        $heading = [
            "No.",
            "PT Round No.",
            "From",
            "To",
            "Tag",
            "Lab Unit",
            "Status",
            "Actions"
        ];
        $tabledata = [];

        
        $rounds = $this->db->get('pt_round_v')->result();
        // echo "<pre>";print_r($rounds);echo "</pre>";die();

        if($rounds){
            $counter = 0;
            foreach($rounds as $round){
                $counter ++;
                $round_uuid = $round->uuid;

                if($round->type == "ongoing"){
                    $status = "<label class = 'tag tag-warning tag-sm'>Ongoing</label>"; 
                }else{
                    $status = "<label class = 'tag tag-success tag-sm'>Done</label>";
                }

                $from = date('dS F, Y', strtotime($round->from));
                $to = date('dS F, Y', strtotime($round->to));

                $view = "<a class = 'btn btn-info btn-sm dropdown-item' href = '".base_url('Analysis/Results/' . $round_uuid)."'><i class = 'icon-eye'></i>&nbsp;Data Results</a>";

                $graph = "<a class = 'btn btn-danger btn-sm dropdown-item' href = '".base_url('Analysis/Graphs/' . $round_uuid)."'><i class = 'icon-eye'></i>&nbsp;Graph Results</a>";

                $capa = "<a class = 'btn btn-danger btn-sm dropdown-item' href = '".base_url('Analysis/Capa/' . $round->uuid)."'><i class = 'icon-eye'></i>&nbsp;CAPA</a>";

                $dropdown = "<div class = 'dropdown'>
                            <button class = 'btn btn-secondary dropdown-toggle' type = 'button' id = 'dropdownMenuButton1' data-toggle = 'dropdown' aria-haspopup='true' aria-expanded = 'true'>
                                Quick Actions
                            </button>
                            <div class = 'dropdown-menu' aria-labelledby= = 'dropdownMenuButton'>
                                $graph
                                $view
                                $capa
                            </div>
                        </div>";
                
                $tabledata[] = [
                    $counter,
                    $round->pt_round_no,
                    $from,
                    $to,
                    $round->tag,
                    $round->lab_unit,
                    $status,
                    $dropdown
                ];
            }
        }
        $this->table->set_heading($heading);
        $this->table->set_template($template);

        return $this->table->generate($tabledata);
	}



    public function createParticipantResultsAbsolute($type, $round_id,$equipment_id,$sample_id,$cdtype)
    {        
        // echo'<pre>';print_r($cdtype);echo'</pre>';die();

        $template = $this->config->item('default');
        $column_data = $row_data = array();

        $html_body = '
        <div class="centered">
            <div>
                <p> 
                    <img height="50px" width="50px" src="'. $this->config->item("server_url") . '"assets/frontend/images/files/gok.png";?>" alt="Ministry of Health" />
                </p>
            </div> 
            <div>
                <th>
                     MINISTRY OF HEALTH <br/>
                     NATIONAL PUBLIC HEALTH LABORATORY SERVICES <br/>
                     NATIONAL HIV REFERENCE LABORATORY <br/>
                     P. O. BOX 20750-00202, NAIROBI <br/>
                </th>
            </div><br/><br/>

            <div><th>
                Round No : ' .$round_name. ' <br/> 
                Equipment Name : ' . $equipment_name . '
                </th>
            </div>
            <br/><br/>

        </div>

        <table>
        <thead>
        <tr>
            <th>No.</th>
            <th>Facility Name</th>
            <th>'.$cdtype.' Absolute Result</th>
        </tr> 
        </thead>
        <tbody>
        <ol type="a">';

        $heading = [
            "No.",
            "Facility",
            $cdtype." Absolute Result"
        ];
        $tabledata = [];
  
        $part_results = $this->db->get_where('pt_participant_review_v',['round_id'=> $round_id, 'equipment_id' => $equipment_id, 'sample_id' => $sample_id])->result();
         
        if($part_results){
            $counter = 0;
            foreach($part_results as $part_result){
                $counter ++;

                $participant_id = $this->db->get_where('participant_readiness_v', ['p_id' => $part_result->participant_id])->row();


                if($participant_id){
                    $pid = $participant_id->username;

                    $facilityid = $this->db->get_where('participant_readiness_v', ['username' => $pid])->row();

                    if($facilityid){
                        $facility_id = $facilityid->facility_id;

                        $facility_name = $this->db->get_where('facility_v', ['facility_id' =>  $facility_id])->row()->facility_name;
                    }else{
                        $facility_name = "No Facility";
                    }
                }else{
                    $facility_name = "No Facility";
                }

                $type_absolute = $cdtype.'_absolute';

                // echo'<pre>';print_r($type_absolute);echo'</pre>';die();

                switch ($type) {
                    case 'table':
                        $tabledata[] = [
                    $counter,
                    $facility_name,
                    $part_result->$type_absolute
                ];
                        break;

                    case 'excel':
                        array_push($row_data, array($counter, $facility_name, $part_result->$type_absolute));
                     
                        break;

                    case 'pdf':
                        $html_body .= '<tr>';
                        $html_body .= '<td class="spacings">'.$counter.'</td>';
                        $html_body .= '<td class="spacings">'.$facility_name.'</td>';
                        $html_body .= '<td class="spacings">'.$part_result->$type_absolute.'</td>';
                        $html_body .= "</tr></ol>";
                        break;
                    
                    default:
                        echo "<pre>";print_r("Something went wrong... Please contact your administrator");echo "</pre>";die();
                        break;
                }
            }
        }

        if($type == 'table'){

            $this->table->set_heading($heading);
            $this->table->set_template($template);

            return $this->table->generate($tabledata);

        }else if($type == 'excel'){

            $excel_data = array();
            $excel_data = array('doc_creator' => 'External_Quality_Assurance', 'doc_title' => 'Participant_Sample_Report_for_'.$cdtype.'_absolute', 'file_name' => 'Sample_Report_for_'.$cdtype.'_absolute', 'excel_topic' => 'Sample_Report_for_'.$cdtype.'_absolute');

            $column_data = array('No.','Facility Name',$cdtype.' Absolute Result');
            $excel_data['column_data'] = $column_data;
            $excel_data['row_data'] = $row_data;

            // echo'<pre>';print_r($excel_data);echo'</pre>';die();

            $this->export->create_excel($excel_data);

        }else if($type == 'pdf'){

            $html_body .= '</tbody></table>';
            $pdf_data = array("pdf_title" => "Participant_Sample_Report_for_".$cdtype."_absolute", 'pdf_html_body' => $html_body, 'pdf_view_option' => 'download', 'file_name' => 'Sample_Report_for_'.$cdtype.'_absolute', 'pdf_topic' => 'Sample_Report_for_'.$cdtype.'_absolute');

            $this->export->create_pdf($html_body,$pdf_data);
            // $this->export->create_pdf($pdf_data);

        }
        
    }



	public function createParticipantResultsPercent($type, $round_id,$equipment_id,$sample_id,$cdtype)
	{
        
        // echo'<pre>';print_r($cdtype);echo'</pre>';die();

        // $this->auth->check();

        $round = $this->db->get_where('pt_round', ['id' => $round_id])->row();
        $round_name = $round->pt_round_no;

        $equipment = $this->db->get_where('equipments_v', ['id' => $equipment_id])->row();
        $equipment_name = $equipment->equipment_name;

		$template = $this->config->item('default');
        $column_data = $row_data = array();

        $html_body = '
        <div class="centered">
            <div>
                <p> 
                    <img height="50px" width="50px" src="'. $this->config->item("server_url") . '"assets/frontend/images/files/gok.png";?>" alt="Ministry of Health" />
                </p>
            </div> 
            <div>
                <th>
                     MINISTRY OF HEALTH <br/>
                     NATIONAL PUBLIC HEALTH LABORATORY SERVICES <br/>
                     NATIONAL HIV REFERENCE LABORATORY <br/>
                     P. O. BOX 20750-00202, NAIROBI <br/>
                </th>
            </div><br/><br/>

            <div><th>
                Round No : ' .$round_name. ' <br/> 
                Equipment Name : ' . $equipment_name . '
                </th>
            </div>
            <br/><br/>

        </div>

        <table>
        <thead>
        <tr>
            <th>No.</th>
            <th>Facility Name</th>
            <th>'.$cdtype.' Percent Result</th>
        </tr> 
        </thead>
        <tbody>
        <ol type="a">';

        $heading = [
            "No.",
            "Facility",
            $cdtype." Percent Result"
        ];
		$tabledata = [];

        
        $part_results = $this->db->get_where('pt_participant_review_v',['round_id'=> $round_id, 'equipment_id' => $equipment_id, 'sample_id' => $sample_id])->result();
         

        if($part_results){
            $counter = 0;
            foreach($part_results as $part_result){
                $counter ++;

                $participant_id = $this->db->get_where('participant_readiness_v', ['p_id' => $part_result->participant_id])->row();


                if($participant_id){
                    $pid = $participant_id->username;

                    $facilityid = $this->db->get_where('participant_readiness_v', ['username' => $pid])->row();

                    if($facilityid){
                        $facility_id = $facilityid->facility_id;

                        $facility_name = $this->db->get_where('facility_v', ['facility_id' =>  $facility_id])->row()->facility_name;
                    }else{
                        $facility_name = "No Facility";
                    }
                }else{
                    $facility_name = "No Facility";
                }

                $type_percent = $cdtype.'_percent';

                // echo'<pre>';print_r($type_absolute);echo'</pre>';die();

                switch ($type) {
                    case 'table':
                        $tabledata[] = [
                    $counter,
                    $facility_name,
                    $part_result->$type_percent
                ];
                        break;

                    case 'excel':
                        array_push($row_data, array($counter, $facility_name, $part_result->$type_percent));

                        
                        break;

                    case 'pdf':
                        $html_body .= '<tr>';
                        $html_body .= '<td class="spacings">'.$counter.'</td>';
                        $html_body .= '<td class="spacings">'.$facility_name.'</td>';
                        $html_body .= '<td class="spacings">'.$part_result->$type_percent.'</td>';
                        $html_body .= "</tr></ol>";
                        break;
                    
                    default:
                        echo "<pre>";print_r("Something went wrong... Please contact your administrator");echo "</pre>";die();
                        break;
                }
                
                
            }
        }

        if($type == 'table'){

            $this->table->set_heading($heading);
            $this->table->set_template($template);

            return $this->table->generate($tabledata);

        }else if($type == 'excel'){

            $excel_data = array();
            $excel_data = array('doc_creator' => 'External_Quality_Assurance', 'doc_title' => 'Participant_Sample_Report_for_'.$cdtype.'_percent', 'file_name' => 'Sample_Report_for_'.$cdtype.'_percent', 'excel_topic' => 'Sample_Report_for_'.$cdtype.'_percent');

            $column_data = array('No.','Facility Name',$cdtype.' Percent Result');
            $excel_data['column_data'] = $column_data;
            $excel_data['row_data'] = $row_data;

            // echo'<pre>';print_r($excel_data);echo'</pre>';die();

            $this->export->create_excel($excel_data);

        }else if($type == 'pdf'){

            $html_body .= '</tbody></table>';
            $pdf_data = array("pdf_title" => "Participant_Sample_Report_for_".$cdtype."_percent", 'pdf_html_body' => $html_body, 'pdf_view_option' => 'download', 'file_name' => 'Sample_Report_for_'.$cdtype.'_percent', 'pdf_topic' => 'Sample_Report_for_'.$cdtype.'_percent');

            $this->export->create_pdf($html_body,$pdf_data);
            // $this->export->create_pdf($pdf_data);

        }
        
	}

   


	public function Results($round_uuid){
		$data = [];
        $title = "Analysis";

        $pt_id = $this->db->get_where('pt_round', ['uuid'   => $round_uuid])->row()->id;
		// $equipments = $this->db->get_where('equipment', ['equipment_status'=>1])->result();
		//echo "<pre>";print_r($equipments);echo "</pre>";die();

		
			$data = [
				'sample_tabs' => $this->createTabs($pt_id)
			];
            
		$this->assets->addCss('css/main.css');
        $this->assets
                ->addJs("dashboard/js/libs/jquery.dataTables.min.js")
                ->addJs("dashboard/js/libs/dataTables.bootstrap4.min.js")
                        ->addJs('dashboard/js/libs/moment.min.js');
        $this->assets->setJavascript('Analysis/analysis_js');
        $this->template
                ->setPageTitle($title)
                ->setPartial('Analysis/nhrl_peer_results_v', $data)
                ->adminTemplate();
	}


    public function Graphs($round_uuid){
        $data = [];
        $title = "Graph Analysis";
        // echo "<pre>";print_r($round_uuid);echo "</pre>";die();

        
            $data = [
                'round' => $round_uuid,
                'round_name' => $this->db->get_where('pt_round_v', ['uuid'   => $round_uuid])->row()->pt_round_no
            ];
            
        $this->assets->addCss('css/main.css');
        $this->assets
                ->addJs("js/Chart.min.js")
                ->addJs("dashboard/js/libs/jquery.dataTables.min.js")
                ->addJs("dashboard/js/libs/dataTables.bootstrap4.min.js")
                        ->addJs('dashboard/js/libs/moment.min.js');
        $this->assets->setJavascript('Analysis/chart_js');
        $this->template
                ->setPageTitle($title)
                ->setPartial('Analysis/analysis_graphs', $data)
                ->adminTemplate();
    }


    //  public function graphExample(){
    //     $labels = $graph_data = $datasets = $data = array();

    //     // $colors = [
    //     //     'rgba(220,220,220,0.5)', 'rgba(151,187,205,0.5)'
    //     // ];

    //     $sql = "SELECT ps.sample_name, avg(per.cd3_absolute) as cd3, avg(per.cd4_absolute) as cd4 FROM pt_equipment_results per JOIN pt_samples ps ON ps.id = per.sample_id GROUP BY per.sample_id;";

    //     $results = $this->db->query($sql)->result();
    //     $cd3datasets = [
    //         'label'         =>  'CD3',
    //         'backgroundColor' => 'rgba(220,220,220,0.5)',
    //         'borderColor' => 'rgba(220,220,220,0.8)',
    //         'highlightFill' => 'rgba(220,220,220,0.75)',
    //         'highlightStroke' => 'rgba(220,220,220,1)'
    //     ];
    //     $cd4datasets = [
    //         'label'         =>  'CD4',
    //         'backgroundColor' => 'rgba(151,187,205,0.5)',
    //         'borderColor' => 'rgba(151,187,205,0.8)',
    //         'highlightFill' => 'rgba(151,187,205,0.75)',
    //         'highlightStroke' => 'rgba(151,187,205,1)'
    //     ];
    //     // echo "<pre>";print_r($results);die;
    //     if($results){
    //         $counter = 0;
    //         foreach ($results as $key => $value) {
    //             $data = [];
    //             // echo "<pre>";print_r($value);echo "</pre>";die();
    //             $cd3datasets['data'][] = $value->cd3;
    //             $cd4datasets['data'][] = $value->cd4;

    //             $labels[] = $value->sample_name;
    //             $tabledata[] = [];
    //             $counter++;
    //         }
    //     }

    //     $graph_data['labels'] = $labels;
    //     $graph_data['datasets'] = [$cd3datasets, $cd4datasets];

    //     return $this->output->set_content_type('application/json')->set_output(json_encode($graph_data));
    // }



	public function createNHRLTable($form, $round_id, $equipment_id){
		$template = $this->config->item('default');

        $tabledata = [];

        $column_data = $row_data = array();

        $counter = 0;

		$where = ['pt_round_id' =>  $round_id];
        $samples = $this->db->get_where('pt_samples', $where)->result();
        $testers = $this->db->get_where('pt_testers', $where)->result();
        $labs = $this->db->get_where('pt_labs', $where)->result();

        $rounds = $this->db->get_where('pt_round_v', ['id'=>$round_id])->row();
        $round_name = str_replace(' ', '_', $rounds->pt_round_no);

        $equipments = $this->db->get_where('equipment', ['id'=>$equipment_id,'equipment_status'=>1])->row();
        $equipment_name = str_replace(' ', '_', $equipments->equipment_name);


        $html_body = '
        <div class="centered">
            <div>
                <p> 
                    <img height="50px" width="50px" src="'. $this->config->item("server_url") . '"assets/frontend/images/files/gok.png";?>" alt="Ministry of Health" />
                </p>
            </div> 
            <div>
                <th>
                     MINISTRY OF HEALTH <br/>
                     NATIONAL PUBLIC HEALTH LABORATORY SERVICES <br/>
                     NATIONAL HIV REFERENCE LABORATORY <br/>
                     P. O. BOX 20750-00202, NAIROBI <br/><br/>
                </th>
            </div><br/><br/>

            <div><th>
                Round No : ' .$round_name. ' <br/> 
                Equipment Name : ' . $equipment_name . '
                </th>
            </div>
            <br/><br/>

        </div>

                    <table>
                    <thead>
                    <tr>
                        <th>No.</th>
                        <th>Sample ID</th>
                        <th>Mean</th>
                        <th>SD</th>
                        <th>Double SD</th>
                        <th>Upper Limit</th>
                        <th>Lower Limit</th>
                    </tr> 
                    </thead>
                    <tbody>
                    <ol type="a">';

        $heading = [
            "Sample ID",
            "Mean",
            "SD",
            "2SD",
            "Upper Limit",
            "Lower Limit"
        ];

        foreach($samples as $sample){
            $counter++;

                $table_body = [];
                $table_body[] = $sample->sample_name;
                

                $calculated_values = $this->db->get_where('pt_testers_calculated_v', ['pt_round_id' =>  $round_id, 'equipment_id'   =>  $equipment_id, 'pt_sample_id'  =>  $sample->id])->row(); 

                $mean = ($calculated_values) ? $calculated_values->mean : 0;
                $sd = ($calculated_values) ? $calculated_values->sd : 0;
                $sd2 = ($calculated_values) ? $calculated_values->doublesd : 0;
                $upper_limit = ($calculated_values) ? $calculated_values->upper_limit : 0;
                $lower_limit = ($calculated_values) ? $calculated_values->lower_limit : 0;


            switch ($form) {
                case 'table':

                    $tabledata[] = [
                        $sample->sample_name,
                        $mean,
                        $sd,
                        $sd2,
                        $upper_limit,
                        $lower_limit
                    ];
                break;

                case 'excel':
                    array_push($row_data, array($counter, $sample->sample_name, $mean, $sd, $sd2,$upper_limit, $lower_limit));
                break;

                case 'pdf':
                

                    $html_body .= '<tr>';
                    $html_body .= '<td class="spacings">'.$counter.'</td>';
                    $html_body .= '<td class="spacings">'.$sample->sample_name.'</td>';
                    $html_body .= '<td class="spacings">'.$mean.'</td>';
                    $html_body .= '<td class="spacings">'.$sd.'</td>';
                    $html_body .= '<td class="spacings">'.$sd2.'</td>';
                    $html_body .= '<td class="spacings">'.$upper_limit.'</td>';
                    $html_body .= '<td class="spacings">'.$lower_limit.'</td>';
                    $html_body .= "</tr></ol>";
                break;
                    
                
                default:
                    echo "<pre>";print_r("Something went wrong... PLease contact the administrator");echo "</pre>";die();
                break;
            }
        }
                // echo "<pre>";print_r($tabledata);echo "</pre>";die();

        if($form == 'table'){

            $this->table->set_template($template);
            $this->table->set_heading($heading);

            return $this->table->generate($tabledata);

        }else if($form == 'excel'){

            $excel_data = array();
            $excel_data = array('doc_creator' => 'External_Quality_Assurance', 'doc_title' => 'NHRL_'.$round_name.'_'.$equipment_name.'_Results', 'file_name' => 'NHRL_'.$round_name.'_'.$equipment_name.'_Results', 'excel_topic' => 'NHRL_'.$equipment_name.'_Results');
            // $excel_data = array('doc_creator' => 'External_Quality_Assurance', 'doc_title' => $round_name.'_'.$equipment_name.'_'.$type.'_absolute', 'file_name' => $round_name.'_'.$equipment_name.'_'.$type.'_absolute', 'excel_topic' => $round_name.'_'.$equipment_name.'_'.$type.'_absolute');

            $column_data = array('No.','Sample ID','Mean','SD','Double SD','Upper Limit','Lower Limit');
            $excel_data['column_data'] = $column_data;
            $excel_data['row_data'] = $row_data;

            $this->export->create_excel($excel_data);

        }else if($form == 'pdf'){

            $html_body .= '</tbody></table>';
            $pdf_data = array("pdf_title" => 'NHRL_'.$round_name.'_'.$equipment_name.'_Results', 'pdf_html_body' => $html_body, 'pdf_view_option' => 'download', 'file_name' => 'NHRL_'.$round_name.'_'.$equipment_name.'_Results', 'pdf_topic' => 'NHRL_'.$round_name.'_'.$equipment_name.'_Results');

            $this->export->create_pdf($html_body,$pdf_data);

        }
	}



    public function ParticipantAbsoluteInfo($round_id,$equipment_id,$sample_id, $type)
    {

        $round_id_name = $this->db->get_where('pt_round', ['id' =>  $round_id])->row()->pt_round_no;
        $equipment_name = $this->db->get_where('equipment', ['id' =>  $equipment_id])->row()->equipment_name;
        $sample_name = $this->db->get_where('pt_samples', ['id' =>  $sample_id])->row()->sample_name;

        

        $cd4_calculated_values = $this->db->get_where('pt_participants_calculated_v', ['round_id' =>  $round_id, 'equipment_id'   =>  $equipment_id, 'sample_id'  =>  $sample_id])->row();

        switch ($type) {
            case 'cd3':
                $mean = ($cd4_calculated_values) ? $cd4_calculated_values->cd3_absolute_mean : 0;
        $sd = ($cd4_calculated_values) ? $cd4_calculated_values->cd3_absolute_sd : 0;
        $sd2 = ($cd4_calculated_values) ? $cd4_calculated_values->double_cd3_absolute_sd : 0;
        $upper = ($cd4_calculated_values) ? $cd4_calculated_values->cd3_absolute_upper_limit : 0;
        $lower = ($cd4_calculated_values) ? $cd4_calculated_values->cd3_absolute_lower_limit : 0;
                break;

            case 'cd4':
                $mean = ($cd4_calculated_values) ? $cd4_calculated_values->cd4_absolute_mean : 0;
        $sd = ($cd4_calculated_values) ? $cd4_calculated_values->cd4_absolute_sd : 0;
        $sd2 = ($cd4_calculated_values) ? $cd4_calculated_values->double_cd4_absolute_sd : 0;
        $upper = ($cd4_calculated_values) ? $cd4_calculated_values->cd4_absolute_upper_limit : 0;
        $lower = ($cd4_calculated_values) ? $cd4_calculated_values->cd4_absolute_lower_limit : 0;
                break;

            case 'other':
                $mean = ($cd4_calculated_values) ? $cd4_calculated_values->other_absolute_mean : 0;
        $sd = ($cd4_calculated_values) ? $cd4_calculated_values->other_absolute_sd : 0;
        $sd2 = ($cd4_calculated_values) ? $cd4_calculated_values->double_other_absolute_sd : 0;
        $upper = ($cd4_calculated_values) ? $cd4_calculated_values->other_absolute_upper_limit : 0;
        $lower = ($cd4_calculated_values) ? $cd4_calculated_values->other_absolute_lower_limit : 0;
                break;
            
            default:
                echo "<pre>";print_r("Something went wrong...Please contact the administrator");echo "</pre>";die();
                break;
        }

        


        // echo "<pre>";print_r($round_id_name);echo "</pre>";die();
        $part_info = '';

        $part_info .= '<div class = "row">
                                    <div class="col-md-6">
                                        <div class = "card card-outline-danger">
                                            <div class="card-header col-4">
                                                <i class = "icon-chart"></i>
                                                &nbsp;

                                                    General Details

                                            </div>

                                            <div class = "card-block">
                                            Round ID : <strong>';

                                            $part_info .= $round_id_name;

                                            $part_info .= '</strong> <br/> Equipment : <strong>';

                                            $part_info .= $equipment_name;

                                            $part_info .= '</strong> <br/> Sample : <strong>';

                                            $part_info .= $sample_name;

        $part_info .= ' </strong> <br/></div>
                       </div>
                    </div>

                    <div class="col-md-6">
                                        <div class = "card card-outline-info">
                                            <div class="card-header col-4">
                                                <i class = "icon-chart"></i>
                                                &nbsp;

                                                    Participants Information

                                            </div>
                                            <div class = "card-block">
                                                <div class="col-md-6">
                                                Mean : <strong>';

                                                $part_info .= $mean;

                                                $part_info .= '</strong> <br/> SD : <strong>';

                                                $part_info .= $sd;

                                                $part_info .= '</strong> <br/> 2SD : <strong>';

                                                $part_info .= $sd2;

        $part_info .= ' </strong> <br/>
                        </div>
                        <div class="col-md-6">
                            Upper Limit: <strong>';

                            $part_info .= $upper;

                            $part_info .= '</strong> <br/> Lower Limit : <strong>';

                            $part_info .= $lower;

                            $part_info .= ' </strong> <br/> 
                        </div>
                       </div>
                    </div>
                  </div>
                  </div>';
        
        return $part_info;
    }




	public function ParticipantPercentInfo($round_id,$equipment_id,$sample_id, $type)
	{

		$round_id_name = $this->db->get_where('pt_round', ['id' =>  $round_id])->row()->pt_round_no;
		$equipment_name = $this->db->get_where('equipment', ['id' =>  $equipment_id])->row()->equipment_name;
		$sample_name = $this->db->get_where('pt_samples', ['id' =>  $sample_id])->row()->sample_name;

		

		$cd4_calculated_values = $this->db->get_where('pt_participants_calculated_v', ['round_id' =>  $round_id, 'equipment_id'   =>  $equipment_id, 'sample_id'  =>  $sample_id])->row();

        switch ($type) {
            case 'cd3':
                $mean = ($cd4_calculated_values) ? $cd4_calculated_values->cd3_percent_mean : 0;
        $sd = ($cd4_calculated_values) ? $cd4_calculated_values->cd3_percent_sd : 0;
        $sd2 = ($cd4_calculated_values) ? $cd4_calculated_values->double_cd3_percent_sd : 0;
        $upper = ($cd4_calculated_values) ? $cd4_calculated_values->cd3_percent_upper_limit : 0;
        $lower = ($cd4_calculated_values) ? $cd4_calculated_values->cd3_percent_lower_limit : 0;
                break;

            case 'cd4':
                $mean = ($cd4_calculated_values) ? $cd4_calculated_values->cd4_percent_mean : 0;
        $sd = ($cd4_calculated_values) ? $cd4_calculated_values->cd4_percent_sd : 0;
        $sd2 = ($cd4_calculated_values) ? $cd4_calculated_values->double_cd4_percent_sd : 0;
        $upper = ($cd4_calculated_values) ? $cd4_calculated_values->cd4_percent_upper_limit : 0;
        $lower = ($cd4_calculated_values) ? $cd4_calculated_values->cd4_percent_lower_limit : 0;
                break;

            case 'other':
                $mean = ($cd4_calculated_values) ? $cd4_calculated_values->other_percent_mean : 0;
        $sd = ($cd4_calculated_values) ? $cd4_calculated_values->other_percent_sd : 0;
        $sd2 = ($cd4_calculated_values) ? $cd4_calculated_values->double_other_percent_sd : 0;
        $upper = ($cd4_calculated_values) ? $cd4_calculated_values->other_percent_upper_limit : 0;
        $lower = ($cd4_calculated_values) ? $cd4_calculated_values->other_percent_lower_limit : 0;
                break;
            
            default:
                echo "<pre>";print_r("Something went wrong...Please contact the administrator");echo "</pre>";die();
                break;
        }

		


		// echo "<pre>";print_r($round_id_name);echo "</pre>";die();
		$part_info = '';

		$part_info .= '<div class = "row">
								    <div class="col-md-6">
								        <div class = "card card-outline-danger">
								            <div class="card-header col-4">
								                <i class = "icon-chart"></i>
								                &nbsp;

								                    General Details

								            </div>

								            <div class = "card-block">
								            Round ID : <strong>';

									        $part_info .= $round_id_name;

									        $part_info .= '</strong> <br/> Equipment : <strong>';

									        $part_info .= $equipment_name;

									        $part_info .= '</strong> <br/> Sample : <strong>';

									        $part_info .= $sample_name;

        $part_info .= '	</strong> <br/></div>
				       </div>
				    </div>

				    <div class="col-md-6">
								        <div class = "card card-outline-info">
								            <div class="card-header col-4">
								                <i class = "icon-chart"></i>
								                &nbsp;

								                    Participants Information

								            </div>
								            <div class = "card-block">
									            <div class="col-md-6">
									            Mean : <strong>';

									            $part_info .= $mean;

										        $part_info .= '</strong> <br/> SD : <strong>';

										        $part_info .= $sd;

										        $part_info .= '</strong> <br/> 2SD : <strong>';

										        $part_info .= $sd2;

		$part_info .= '	</strong> <br/>
						</div>
						<div class="col-md-6">
							Upper Limit: <strong>';

							$part_info .= $upper;

					        $part_info .= '</strong> <br/> Lower Limit : <strong>';

					        $part_info .= $lower;

							$part_info .= '	</strong> <br/> 
						</div>
				       </div>
				    </div>
				  </div>
				  </div>';
  		
        return $part_info;
	}



	public function ParticipantResults($round_id,$equipment_id,$sample_id,$cdtype,$resulttype)
	{	
		$data = [];
        $title = "Participant Results";
        

        $round_uuid = $this->db->get_where('pt_round', ['id' =>  $round_id])->row()->uuid;
// echo'<pre>';print_r($cdtype);echo'</pre>';die();

        switch ($resulttype) {
            case 'absolute':
                $data = [
                    'round_uuid' => $round_uuid,
                    'participants_info' => $this->ParticipantAbsoluteInfo($round_id,$equipment_id,$sample_id,$cdtype),
                    'results_table'    =>  $this->createParticipantResultsAbsolute('table',$round_id,$equipment_id,$sample_id,$cdtype),
                    'excel_link'    =>  base_url('Analysis/createParticipantResultsAbsolute/excel/' . $round_id . '/' . $equipment_id . '/' . $sample_id .'/'. $cdtype),
                    'pdf_link'    =>  base_url('Analysis/createParticipantResultsAbsolute/pdf/' . $round_id . '/' . $equipment_id . '/' . $sample_id .'/'. $cdtype)
                ];
                break;

            case 'percent':
                $data = [
                    'round_uuid' => $round_uuid,
                    'participants_info' => $this->ParticipantPercentInfo($round_id,$equipment_id,$sample_id,$cdtype),
                    'results_table'    =>  $this->createParticipantResultsPercent('table',$round_id,$equipment_id,$sample_id,$cdtype),
                    'excel_link'    =>  base_url('Analysis/createParticipantResultsPercent/excel/' . $round_id . '/' . $equipment_id . '/' . $sample_id .'/'. $cdtype),
                    'pdf_link'    =>  base_url('Analysis/createParticipantResultsPercent/pdf/' . $round_id . '/' . $equipment_id . '/' . $sample_id .'/'. $cdtype)
                ];
                break;
            
            default:
                echo "<pre>";print_r("Something went wrong...Please contact the administrator");echo "</pre>";die();
                break;
        }   

        $this->assets
                ->addJs("dashboard/js/libs/jquery.dataTables.min.js")
                ->addJs("dashboard/js/libs/dataTables.bootstrap4.min.js")
                ->addJs('dashboard/js/libs/jquery.validate.js')
                ->addJs('dashboard/js/libs/select2.min.js');
        $this->assets->setJavascript('Analysis/analysis_js');
        $this->template
                ->setPageTitle($title)
                ->setPartial('Analysis/participant_analysis_v', $data)
                ->adminTemplate();
	}



    public function createAbsolutePeerTable($form, $round_id, $equipment_id, $type){
        $template = $this->config->item('default');

        $column_data = $row_data = array();

        $mean = $sd = $sd2 = $upper_limit = $lower_limit = $counter = 0;

        $samples = $this->db->get_where('pt_samples', ['pt_round_id' =>  $round_id])->result();

        $rounds = $this->db->get_where('pt_round_v', ['id'=>$round_id])->row();
        $round_name = str_replace(' ', '_', $rounds->pt_round_no);

        $equipments = $this->db->get_where('equipment', ['id'=>$equipment_id,'equipment_status'=>1])->row();
        $equipment_name = str_replace(' ', '_', $equipments->equipment_name);

        // echo "<pre>";print_r($equipment_name);echo "</pre>";die();


        $html_body = '
        <div class="centered">
            <div>
                <p> 
                    <img height="50px" width="50px" src="'. $this->config->item("server_url") . '"assets/frontend/images/files/gok.png";?>" alt="Ministry of Health" />
                </p>
            </div> 
            <div>
                <th>
                     MINISTRY OF HEALTH <br/>
                     NATIONAL PUBLIC HEALTH LABORATORY SERVICES <br/>
                     NATIONAL HIV REFERENCE LABORATORY <br/>
                     P. O. BOX 20750-00202, NAIROBI <br/>
                </th>
            </div><br/><br/>

            <div><th>
                Round No : ' .$round_name. ' <br/> 
                Equipment Name : ' . $equipment_name . '
                </th>
            </div>
            <br/><br/>

        </div>
        <table>
        <thead>
        <tr>
            <th>No.</th>
            <th>Sample</th>
            <th>Mean</th>
            <th>SD</th>
            <th>Double SD</th>
            <th>Upper Limit</th>
            <th>Lower Limit</th>
        </tr> 
        </thead>
        <tbody>
        <ol type="a">';

    
        $heading = [
            "Sample",
            "Mean",
            "SD",
            "2SD",
            "Upper Limit",
            "Lower Limit",
            "Actions"
        ];
        $tabledata = [];

        foreach($samples as $sample){
            $counter++;
            $table_body = [];
            $table_body[] = $sample->sample_name;

            // $calculated_values = $this->db->get_where('pt_participants_calculated_v', ['round_id' =>  $round_id, 'equipment_id'   =>  $equipment_id, 'sample_id'  =>  $sample->id])->row(); 

            $calculated_values = $this->Analysis_m->getRoundResults($round_id, $equipment_id, $sample->id);

            switch ($type) {
                case 'cd3':

                    // echo "<pre>";print_r($calculated_values);echo "</pre>";die();
                    if($calculated_values){
                        $mean = ($calculated_values->cd3_absolute_mean) ? $calculated_values->cd3_absolute_mean : 0;
                        $sd = ($calculated_values->cd3_absolute_sd) ? $calculated_values->cd3_absolute_sd : 0;
                        $sd2 = ($calculated_values->double_cd3_absolute_sd) ? $calculated_values->double_cd3_absolute_sd : 0;
                        $upper_limit = ($calculated_values->cd3_absolute_upper_limit) ? $calculated_values->cd3_absolute_upper_limit : 0;
                        $lower_limit = ($calculated_values->cd3_absolute_lower_limit) ? $calculated_values->cd3_absolute_lower_limit : 0;
                    }else{
                        $mean = 0;
                        $sd = 0;
                        $sd2 = 0;
                        $upper_limit = 0;
                        $lower_limit = 0;
                    }
                    
                break;

                case 'cd4':
                    // echo "<pre>";print_r($calculated_values);echo "</pre>";die();
                    if($calculated_values){
                        $mean = ($calculated_values->cd4_absolute_mean) ? $calculated_values->cd4_absolute_mean : 0;
                        $sd = ($calculated_values->cd4_absolute_sd) ? $calculated_values->cd4_absolute_sd : 0;
                        $sd2 = ($calculated_values->double_cd4_absolute_sd) ? $calculated_values->double_cd4_absolute_sd : 0;
                        $upper_limit = ($calculated_values->cd4_absolute_upper_limit) ? $calculated_values->cd4_absolute_upper_limit : 0;
                        $lower_limit = ($calculated_values->cd4_absolute_lower_limit) ? $calculated_values->cd4_absolute_lower_limit : 0;
                    }else{
                        $mean = 0;
                        $sd = 0;
                        $sd2 = 0;
                        $upper_limit = 0;
                        $lower_limit = 0;
                    }
                    
                break;

                case 'other':
                    // echo "<pre>";print_r($calculated_values->other_absolute_mean);echo "</pre>";die();

                    if($calculated_values){
                        $mean = ($calculated_values->other_absolute_mean) ? $calculated_values->other_absolute_mean : 0;
                        $sd = ($calculated_values->other_absolute_sd) ? $calculated_values->other_absolute_sd : 0;
                        $sd2 = ($calculated_values->double_other_absolute_sd) ? $calculated_values->double_other_absolute_sd : 0;
                        $upper_limit = ($calculated_values->other_absolute_upper_limit) ? $calculated_values->other_absolute_upper_limit : 0;
                        $lower_limit = ($calculated_values->other_absolute_lower_limit) ? $calculated_values->other_absolute_lower_limit : 0;
                    }else{
                        $mean = 0;
                        $sd = 0;
                        $sd2 = 0;
                        $upper_limit = 0;
                        $lower_limit = 0;
                    }

                    
                break;
                
                default:
                    echo "<pre>";print_r("Something went wrong");echo "</pre>";die();
                break;
            }

            switch ($form) {
                case 'table':

                $view = "<a class = 'btn btn-success btn-sm dropdown-item' href = '".base_url('Analysis/ParticipantResults/' . $round_id . '/' . $equipment_id . '/' . $sample->id . '/'.$type.'/absolute')."'><i class = 'fa fa-eye'></i>&nbsp;View Log</a>";

                    $tabledata[] = [
                                $sample->sample_name,
                                $mean,
                                $sd,
                                $sd2,
                                $upper_limit,
                                $lower_limit,
                                "<div class = 'dropdown'>
                                    <button class = 'btn btn-secondary dropdown-toggle' type = 'button' id = 'dropdownMenuButton1' data-toggle = 'dropdown' aria-haspopup='true' aria-expanded = 'true'>
                                        Act
                                    </button>
                                    <div class = 'dropdown-menu' aria-labelledby= = 'dropdownMenuButton'>
                                        $view
                                    </div>
                                </div>"
                            ];
                break;

                case 'excel':
                    array_push($row_data, array($counter, $sample->sample_name, $mean, $sd, $sd2,$upper_limit, $lower_limit));
                break;

                case 'pdf':
                    $html_body .= '<tr>';
                    $html_body .= '<td class="spacings">'.$counter.'</td>';
                    $html_body .= '<td class="spacings">'.$sample->sample_name.'</td>';
                    $html_body .= '<td class="spacings">'.$mean.'</td>';
                    $html_body .= '<td class="spacings">'.$sd.'</td>';
                    $html_body .= '<td class="spacings">'.$sd2.'</td>';
                    $html_body .= '<td class="spacings">'.$upper_limit.'</td>';
                    $html_body .= '<td class="spacings">'.$lower_limit.'</td>';
                    $html_body .= "</tr></ol>";
                break;
                    
                
                default:
                    echo "<pre>";print_r("Something went wrong... PLease contact the administrator");echo "</pre>";die();
                break;
            }
        }

        if($form == 'table'){

            $this->table->set_template($template);
            $this->table->set_heading($heading);

            return $this->table->generate($tabledata);

        }else if($form == 'excel'){

            $excel_data = array();
            $excel_data = array('doc_creator' => 'External_Quality_Assurance', 'doc_title' => $round_name.'_'.$equipment_name.'_'.$type.'_absolute', 'file_name' => $round_name.'_'.$equipment_name.'_'.$type.'_absolute', 'excel_topic' => $equipment_name.'_'.$type.'_absolute');
            // $excel_data = array('doc_creator' => 'External_Quality_Assurance', 'doc_title' => $round_name.'_'.$equipment_name.'_'.$type.'_absolute', 'file_name' => $round_name.'_'.$equipment_name.'_'.$type.'_absolute', 'excel_topic' => $round_name.'_'.$equipment_name.'_'.$type.'_absolute');

            $column_data = array('No.','Sample','Mean','SD','Double SD','Upper Limit','Lower Limit');
            $excel_data['column_data'] = $column_data;
            $excel_data['row_data'] = $row_data;

            $this->export->create_excel($excel_data);

        }else if($form == 'pdf'){

            $html_body .= '</tbody></table>';
            $pdf_data = array("pdf_title" => $round_name.'_'.$equipment_name.'_'.$type.'_absolute', 'pdf_html_body' => $html_body, 'pdf_view_option' => 'download', 'file_name' => $round_name.'_'.$equipment_name.'_'.$type.'_absolute', 'pdf_topic' => $round_name.'_'.$equipment_name.'_'.$type.'_absolute');

            $this->export->create_pdf($html_body,$pdf_data);

        }              
    }



	public function createPercentPeerTable($form, $round_id, $equipment_id, $type){
		$template = $this->config->item('default');

        $column_data = $row_data = array();

        $mean = $sd = $sd2 = $upper_limit = $lower_limit = $counter = 0;

        $samples = $this->db->get_where('pt_samples', ['pt_round_id' =>  $round_id])->result();

        $rounds = $this->db->get_where('pt_round_v', ['id'=>$round_id])->row();
        $round_name = str_replace(' ', '_', $rounds->pt_round_no);

        $equipments = $this->db->get_where('equipment', ['id'=>$equipment_id,'equipment_status'=>1])->row();
        $equipment_name = str_replace(' ', '_', $equipments->equipment_name);

        // echo "<pre>";print_r($equipment_name);echo "</pre>";die();


        $html_body = '
        <div class="centered">
            <div>
                <p> 
                    <img height="50px" width="50px" src="'. $this->config->item("server_url") . '"assets/frontend/images/files/gok.png";?>" alt="Ministry of Health" />
                </p>
            </div> 
            <div>
                <th>
                     MINISTRY OF HEALTH <br/>
                     NATIONAL PUBLIC HEALTH LABORATORY SERVICES <br/>
                     NATIONAL HIV REFERENCE LABORATORY <br/>
                     P. O. BOX 20750-00202, NAIROBI <br/>
                </th>
            </div><br/><br/>

            <div><th>
                Round No : ' .$round_name. ' <br/> 
                Equipment Name : ' . $equipment_name . '
                </th>
            </div>
            <br/><br/>

        </div>
        <table>
        <thead>
        <tr>
            <th>No.</th>
            <th>Sample</th>
            <th>Mean</th>
            <th>SD</th>
            <th>Double SD</th>
            <th>Upper Limit</th>
            <th>Lower Limit</th>
        </tr> 
        </thead>
        <tbody>
        <ol type="a">';

	
        $heading = [
            "Sample",
            "Mean",
            "SD",
            "2SD",
            "Upper Limit",
            "Lower Limit",
            "Actions"
        ];
        $tabledata = [];

        foreach($samples as $sample){
            $counter++;
            $table_body = [];
            $table_body[] = $sample->sample_name;

            // $calculated_values = $this->db->get_where('pt_participants_calculated_v', ['round_id' =>  $round_id, 'equipment_id'   =>  $equipment_id, 'sample_id'  =>  $sample->id])->row(); 

            $calculated_values = $this->Analysis_m->getRoundResults($round_id, $equipment_id, $sample->id);

            switch ($type) {
                case 'cd3':

                    // echo "<pre>";print_r($calculated_values);echo "</pre>";die();
                    if($calculated_values){
                        $mean = ($calculated_values->cd3_percent_mean) ? $calculated_values->cd3_percent_mean : 0;
                        $sd = ($calculated_values->cd3_percent_sd) ? $calculated_values->cd3_percent_sd : 0;
                        $sd2 = ($calculated_values->double_cd3_percent_sd) ? $calculated_values->double_cd3_percent_sd : 0;
                        $upper_limit = ($calculated_values->cd3_percent_upper_limit) ? $calculated_values->cd3_percent_upper_limit : 0;
                        $lower_limit = ($calculated_values->cd3_percent_lower_limit) ? $calculated_values->cd3_percent_lower_limit : 0;

                    }else{
                        $mean = 0;
                        $sd = 0;
                        $sd2 = 0;
                        $upper_limit = 0;
                        $lower_limit = 0;
                    }
                    
                break;

                case 'cd4':
                    // echo "<pre>";print_r($calculated_values);echo "</pre>";die();
                    if($calculated_values){
                        $mean = ($calculated_values->cd4_percent_mean) ? $calculated_values->cd4_percent_mean : 0;
                        $sd = ($calculated_values->cd4_percent_sd) ? $calculated_values->cd4_percent_sd : 0;
                        $sd2 = ($calculated_values->double_cd4_percent_sd) ? $calculated_values->double_cd4_percent_sd : 0;
                        $upper_limit = ($calculated_values->cd4_percent_upper_limit) ? $calculated_values->cd4_percent_upper_limit : 0;
                        $lower_limit = ($calculated_values->cd4_percent_lower_limit) ? $calculated_values->cd4_percent_lower_limit : 0;
                    }else{
                        $mean = 0;
                        $sd = 0;
                        $sd2 = 0;
                        $upper_limit = 0;
                        $lower_limit = 0;
                    }
                    
                break;

                case 'other':
                    if($calculated_values){
                        $mean = ($calculated_values->other_percent_mean) ? $calculated_values->other_percent_mean : 0;
                        $sd = ($calculated_values->other_percent_sd) ? $calculated_values->other_percent_sd : 0;
                        $sd2 = ($calculated_values->double_other_percent_sd) ? $calculated_values->double_other_percent_sd : 0;
                        $upper_limit = ($calculated_values->other_percent_upper_limit) ? $calculated_values->other_percent_upper_limit : 0;
                        $lower_limit = ($calculated_values->other_percent_lower_limit) ? $calculated_values->other_percent_lower_limit : 0;
                    }else{
                        $mean = 0;
                        $sd = 0;
                        $sd2 = 0;
                        $upper_limit = 0;
                        $lower_limit = 0;
                    }
                    
                break;
                
                default:
                    echo "<pre>";print_r("Something went wrong");echo "</pre>";die();
                break;
            }

            switch ($form) {
                case 'table':

                $view = "<a class = 'btn btn-success btn-sm dropdown-item' href = '".base_url('Analysis/ParticipantResults/' . $round_id . '/' . $equipment_id . '/' . $sample->id . '/'.$type.'/percent')."'><i class = 'fa fa-eye'></i>&nbsp;View Log</a>";

                    $tabledata[] = [
                                $sample->sample_name,
                                $mean,
                                $sd,
                                $sd2,
                                $upper_limit,
                                $lower_limit,
                                "<div class = 'dropdown'>
                                    <button class = 'btn btn-secondary dropdown-toggle' type = 'button' id = 'dropdownMenuButton1' data-toggle = 'dropdown' aria-haspopup='true' aria-expanded = 'true'>
                                        Act
                                    </button>
                                    <div class = 'dropdown-menu' aria-labelledby= = 'dropdownMenuButton'>
                                        $view
                                    </div>
                                </div>"
                            ];
                break;

                case 'excel':
                    array_push($row_data, array($counter, $sample->sample_name, $mean, $sd, $sd2,$upper_limit, $lower_limit));
                break;

                case 'pdf':
                    $html_body .= '<tr>';
                    $html_body .= '<td class="spacings">'.$counter.'</td>';
                    $html_body .= '<td class="spacings">'.$sample->sample_name.'</td>';
                    $html_body .= '<td class="spacings">'.$mean.'</td>';
                    $html_body .= '<td class="spacings">'.$sd.'</td>';
                    $html_body .= '<td class="spacings">'.$sd2.'</td>';
                    $html_body .= '<td class="spacings">'.$upper_limit.'</td>';
                    $html_body .= '<td class="spacings">'.$lower_limit.'</td>';
                    $html_body .= "</tr></ol>";
                break;
                    
                
                default:
                    echo "<pre>";print_r("Something went wrong... PLease contact the administrator");echo "</pre>";die();
                break;
            }
        }

        if($form == 'table'){

            $this->table->set_template($template);
            $this->table->set_heading($heading);

            return $this->table->generate($tabledata);

        }else if($form == 'excel'){

            $excel_data = array();
            $excel_data = array('doc_creator' => 'External_Quality_Assurance', 'doc_title' => $round_name.'_'.$equipment_name.'_'.$type.'_percent', 'file_name' => $round_name.'_'.$equipment_name.'_'.$type.'_percent', 'excel_topic' => $equipment_name.'_'.$type.'_percent');

            $column_data = array('No.','Sample','Mean','SD','Double SD','Upper Limit','Lower Limit');
            $excel_data['column_data'] = $column_data;
            $excel_data['row_data'] = $row_data;

            $this->export->create_excel($excel_data);

        }else if($form == 'pdf'){

            $html_body .= '</tbody></table>';
            $pdf_data = array("pdf_title" => $round_name.'_'.$equipment_name.'_'.$type.'_percent', 'pdf_html_body' => $html_body, 'pdf_view_option' => 'download', 'file_name' => $round_name.'_'.$equipment_name.'_'.$type.'_percent', 'pdf_topic' => $round_name.'_'.$equipment_name.'_'.$type.'_percent');

            $this->export->create_pdf($html_body,$pdf_data);

        }              
	}


    function failedList($round_id,$equipment_id){
        $data = [];
        $title = "Failed Participant";

        $round = $this->db->get_where('pt_round_v', ['id' => $round_id])->row();
        $equipment = $this->db->get_where('equipments_v', ['id' => $equipment_id])->row();
        
            $data = [
                'title' => 'Failed Participant List for <strong>'.$equipment->equipment_name. '</strong> in PT Round <strong>'.$round->pt_round_no .'</strong>',
                'round_uuid' => $round->uuid,
                'table_view' => $this->createdFailedParticipants('table',$round_id,$equipment_id)
            ];

        $this->assets
                ->addJs("dashboard/js/libs/jquery.dataTables.min.js")
                ->addJs("dashboard/js/libs/dataTables.bootstrap4.min.js")
                ->addJs('dashboard/js/libs/jquery.validate.js')
                ->addJs('dashboard/js/libs/select2.min.js');
        $this->assets->setJavascript('Analysis/failed_participant_js');
        $this->template
                ->setModal("Analysis/capa_message_v", "Add New Message")
                ->setPageTitle($title)
                ->setPartial('Analysis/participant_failed_v', $data)
                ->adminTemplate();
    }


    function newCAPAMessage($round_uuid,$email){
        $data = [];
        $title = "CAPA Message";

        $data = [
            'round_uuid' => $round_uuid,
            'email' => $email
        ];

        $this->assets
                ->addJs("dashboard/js/libs/jquery.dataTables.min.js")
                ->addJs("dashboard/js/libs/dataTables.bootstrap4.min.js")
                ->addJs('dashboard/js/libs/jquery.validate.js')
                ->addJs('dashboard/js/libs/select2.min.js');
        $this->assets->setJavascript('Analysis/failed_participant_js');
        $this->template
                ->setPageTitle($title)
                ->setPartial('Analysis/capa_message_v', $data)
                ->adminTemplate();
    }


    function sendCAPAMessage($round_uuid){
        if($this->input->post()){
            $email = $this->input->post('email');
            $subject = $this->input->post('subject');
            $message = $this->input->post('message');

            // echo "<pre>";print_r($email);echo "</pre>";die();
            if($email == null || $email == '' || is_numeric($email)){
                $this->session->set_flashdata('error', "There was no email address added. Please add an email address to send to first");
            }else{
                if($subject == null || $subject == ''){
                    $subject = 'CAPA Message from NHRL';
                }

                $insertdata = [
                    'from'          =>  'nhrlCD4eqa@nphls.or.ke',
                    'email'         =>  $email,
                    'subject'       =>  $subject,
                    'message'       =>  $message
                ];

                $data = [
                    'round_uuid'    =>  $round_uuid,
                    'subject'       =>  $subject,
                    'message'       =>  $message
                ];

                if($this->db->insert('messages', $insertdata)){
                    $this->session->set_flashdata('success', "Successfully sent the message");

                    $body = $this->load->view('Template/email/capa_message', $data, TRUE);
                    $this->load->library('Mailer');
                    $sent = $this->mailer->sendMail($email, $subject, $body);
                    if ($sent == FALSE) {
                        log_message('error', "The system could not send an email to {$user->participant_email}. Names: $user->participant_lname $user->participant_fname at " . date('Y-m-d H:i:s'));
                    }

                }else{
                    echo "<pre>";print_r("no email");echo "</pre>";die();
                    $this->session->set_flashdata('error', "There was a problem sending the message. Please try again");
                }
            }
            
            redirect('Analysis/Results/'.$round_uuid, 'refresh');
        }
    }


    public function createdFailedParticipants($form, $round_id, $equipment_id){
        $template = $this->config->item('default');
        $column_data = $row_data = $tablevalues = $tablebody = $table = [];
        $count = $zerocount = $sub_counter = 0;

        $rounds = $this->db->get_where('pt_round_v', ['id'=>$round_id])->row();
        $round_name = str_replace(' ', '_', $rounds->pt_round_no);
        $round_uuid = $rounds->uuid;

        $equipments = $this->db->get_where('equipment', ['id'=>$equipment_id,'equipment_status'=>1])->row();
        $equipment_name = str_replace(' ', '_', $equipments->equipment_name);

        $html_body = '
        <div class="centered">
            <div>
                <p> 
                    <img height="50px" width="50px" src="'. $this->config->item("server_url") . '"assets/frontend/images/files/gok.png";?>" alt="Ministry of Health" />
                </p>
            </div> 
            <div>
                <th>
                     MINISTRY OF HEALTH <br/>
                     NATIONAL PUBLIC HEALTH LABORATORY SERVICES <br/>
                     NATIONAL HIV REFERENCE LABORATORY <br/>
                     P. O. BOX 20750-00202, NAIROBI <br/>
                </th>
            </div><br/><br/>

            <div><th>
                Round No : ' .$round_name. ' <br/> 
                Equipment Name : ' . $equipment_name . '
                </th>
            </div>
            <br/><br/>

        </div>
        <table>
        <thead>
        <tr>
            <th>No.</th>
            <th>Facility</th>
            <th>Batch</th>';
            
        $heading = [
            "No.",
            "Facility",
            "Batch"
        ];

        $column_data = array('No.','Facility','Batch');
        
        $samples = $this->db->get_where('pt_samples', ['pt_round_id' =>  $round_id])->result();

        foreach ($samples as $sample) {
            array_push($heading, $sample->sample_name,"Comment");
            array_push($column_data, $sample->sample_name,"Comment");
            $html_body .= '<th>'.$sample->sample_name.'</th> <th>Comment</th>';
        }

        array_push($heading, 'Overall Grade', "Review Comment",'Participant','Cell','Email', 'Send');
        array_push($column_data, 'Overall Grade', "Review Comment",'Participant','Cell','Email');

        $html_body .= ' 
        <th>Overall Grade</th>
            <th>Review Comment</th>
            <th>Participant</th>
            <th>Cell</th>
            <th>Email</th>
            </tr> 
        </thead>
        <tbody>
        <ol type="a">';


        $submissions = $this->db->get_where('pt_data_submission', ['round_id' =>  $round_id, 'equipment_id' => $equipment_id])->result();


        foreach ($submissions as $submission) {
            $sub_counter++;
            $samp_counter = $acceptable = $unacceptable = 0;
            $tabledata = [];
 

            $facilityid = $this->db->get_where('participant_readiness_v', ['p_id' => $submission->participant_id])->row();

            if($facilityid){
                $facility_id = $facilityid->facility_id;

                $facility_name = $this->db->get_where('facility_v', ['facility_id' =>  $facility_id])->row()->facility_name;
            }else{
                $facility_name = "No Facility";
            }

            
            $batch = $this->db->get_where('pt_ready_participants', ['p_id' => $submission->participant_id, 'pt_round_uuid' => $round_uuid])->row();

            array_push($tabledata, $sub_counter, $facility_name, $batch->batch);

            $html_body .= '<tr><td class="spacings">'.$sub_counter.'</td>';
            $html_body .= '<td class="spacings">'.$facility_name.'</td>';
            $html_body .= '<td class="spacings">'.$batch->batch.'</td>';

            foreach ($samples as $sample) {
                $samp_counter++;
                
                $cd4_values = $this->db->get_where('pt_participants_calculated_v', ['round_id' =>  $round_id, 'equipment_id'   =>  $equipment_id, 'sample_id'  =>  $sample->id])->row();

                if($cd4_values){
                    $upper_limit = $cd4_values->cd4_absolute_upper_limit;
                    $lower_limit = $cd4_values->cd4_absolute_lower_limit;
                }else{
                    $upper_limit = 0;
                    $lower_limit = 0;
                } 

                
                $part_cd4 = $this->Analysis_m->absoluteValue($round_id,$equipment_id,$sample->id,$submission->participant_id);

               
                if($part_cd4){

                    $html_body .= '<td class="spacings">'.$part_cd4->cd4_absolute.'</td>';

                    if($part_cd4->cd4_absolute >= $lower_limit && $part_cd4->cd4_absolute <= $upper_limit){
                        $acceptable++;
                        $comment = "Acceptable";

                    }else{
                        $unacceptable++;
                        $comment = "Unacceptable";
                    }   

                    if($part_cd4->cd4_absolute == 0 || $part_cd4->cd4_absolute == null){
                        $zerocount++;
                    }

                    array_push($tabledata, $part_cd4->cd4_absolute, $comment);
                    
                }else{
                    array_push($tabledata, 0, "Unacceptable");
                }   
                $html_body .= '<td class="spacings">'.$comment.'</td>'; 
            }

            

            $grade = (($acceptable / $samp_counter) * 100);


            $overall_grade = round($grade, 2) . ' %';

            if($grade == 100){
                $review = "Satisfactory Performance";
            }else if($grade > 0 && $grade < 100){
                $review = "Unsatisfactory Performance";
            }else{
                $review = "Non-responsive";
            }

            $username = $this->db->get_where('pt_ready_participants', ['p_id' =>  $submission->participant_id])->row()->participant_id;
            // echo "<pre>";print_r($part_cd4);echo "</pre>";die();
            $part_details = $this->db->get_where('users_v', ['username' =>  $username])->row();
            
            $name = $part_details->firstname . ' ' . $part_details->lastname;

            $capa = '<a href = ' . base_url("Analysis/newCAPAMessage/$round_uuid/$part_details->email_address") . ' class = "btn btn-warning btn-sm"><i class = "icon-envelope"></i>&nbsp;Send Capa </a>';

            array_push($tabledata, $overall_grade,$review,$name,$part_details->phone,$part_details->email_address, $capa);
    
            switch ($form) {
                case 'table':
                    if($review == "Unsatisfactory Performance"){
                        $table[$count] = $tabledata;
                    }

                break;

                case 'excel':

                    array_push($row_data, $tabledata);
                break;

                case 'pdf':
                  
                    $html_body .= '<td class="spacings">'.$overall_grade.' %</td>';
                    $html_body .= '<td class="spacings">'.$review.'</td>';
                    $html_body .= '<td class="spacings">'.$name.'</td>';
                    $html_body .= '<td class="spacings">'.$part_details->phone.'</td>';
                    $html_body .= '<td class="spacings">'.$part_details->email_address.'</td>';
                    $html_body .= "</tr></ol>";
                break;
                    
                
                default:
                    echo "<pre>";print_r("Something went wrong... PLease contact the administrator");echo "</pre>";die();
                break;
            }

            $count++;
                      
        }

        if($form == 'table'){

            $this->table->set_template($template);
            $this->table->set_heading($heading);

            return $this->table->generate($table);

        }else if($form == 'excel'){

            $excel_data = array();
            $excel_data = array('doc_creator' => 'External_Quality_Assurance', 'doc_title' => 'Participants_'.$round_name.'_'.$equipment_name, 'file_name' => 'Participants_'.$round_name.'_'.$equipment_name, 'excel_topic' => 'Participants_'.$equipment_name);

            
            $excel_data['column_data'] = $column_data;
            $excel_data['row_data'] = $row_data;

            $this->export->create_excel($excel_data);

        }else if($form == 'pdf'){

            $html_body .= '</tbody></table>';
            $pdf_data = array("pdf_title" => 'Participants_'.$round_name.'_'.$equipment_name, 'pdf_html_body' => $html_body, 'pdf_view_option' => 'download', 'file_name' => 'Participants_'.$round_name.'_'.$equipment_name, 'pdf_topic' => 'Participants_'.$round_name.'_'.$equipment_name);

            $this->export->create_pdf($html_body,$pdf_data);

        }
    }


    public function createParticipantTable($form, $round_id, $equipment_id){
        $template = $this->config->item('default');
        $column_data = $row_data = $tablevalues = $tablebody = $table = [];
        $count = $zerocount = $sub_counter = 0;

        $rounds = $this->db->get_where('pt_round_v', ['id'=>$round_id])->row();
        $round_name = str_replace(' ', '_', $rounds->pt_round_no);
        $round_uuid = $rounds->uuid;

        $equipments = $this->db->get_where('equipment', ['id'=>$equipment_id,'equipment_status'=>1])->row();
        $equipment_name = str_replace(' ', '_', $equipments->equipment_name);

        $html_body = '
        <div class="centered">
            <div>
                <p> 
                    <img height="50px" width="50px" src="'. $this->config->item("server_url") . '"assets/frontend/images/files/gok.png";?>" alt="Ministry of Health" />
                </p>
            </div> 
            <div>
                <th>
                     MINISTRY OF HEALTH <br/>
                     NATIONAL PUBLIC HEALTH LABORATORY SERVICES <br/>
                     NATIONAL HIV REFERENCE LABORATORY <br/>
                     P. O. BOX 20750-00202, NAIROBI <br/>
                </th>
            </div><br/><br/>

            <div><th>
                Round No : ' .$round_name. ' <br/> 
                Equipment Name : ' . $equipment_name . '
                </th>
            </div>
            <br/><br/>

        </div>
        <table>
        <thead>
        <tr>
            <th>No.</th>
            <th>Facility</th>
            <th>Batch</th>';
            
        $heading = [
            "No.",
            "Facility",
            "Batch"
        ];

        $column_data = array('No.','Facility','Batch');
        
        $samples = $this->db->get_where('pt_samples', ['pt_round_id' =>  $round_id])->result();

        foreach ($samples as $sample) {
            array_push($heading, $sample->sample_name,"Comment");
            array_push($column_data, $sample->sample_name,"Comment");
            $html_body .= '<th>'.$sample->sample_name.'</th> <th>Comment</th>';
        }

        array_push($heading, 'Overall Grade', "Review Comment",'Participant','Cell','Email');
        array_push($column_data, 'Overall Grade', "Review Comment",'Participant','Cell','Email');

        $html_body .= ' 
        <th>Overall Grade</th>
            <th>Review Comment</th>
            <th>Participant</th>
            <th>Cell</th>
            <th>Email</th>
            </tr> 
        </thead>
        <tbody>
        <ol type="a">';


        $submissions = $this->db->get_where('pt_data_submission', ['round_id' =>  $round_id, 'equipment_id' => $equipment_id])->result();


        foreach ($submissions as $submission) {
            $sub_counter++;
            $samp_counter = $acceptable = $unacceptable = 0;
            $tabledata = [];
 

            $facilityid = $this->db->get_where('participant_readiness_v', ['p_id' => $submission->participant_id])->row();

            if($facilityid){
                $facility_id = $facilityid->facility_id;

                $facility_name = $this->db->get_where('facility_v', ['facility_id' =>  $facility_id])->row()->facility_name;
            }else{
                $facility_name = "No Facility";
            }

            
            $batch = $this->db->get_where('pt_ready_participants', ['p_id' => $submission->participant_id, 'pt_round_uuid' => $round_uuid])->row();

            

            array_push($tabledata, $sub_counter, $facility_name, $batch->batch);

            $html_body .= '<tr>
                            <td class="spacings">'.$sub_counter.'</td>';
            $html_body .= '<td class="spacings">'.$facility_name.'</td>';
            $html_body .= '<td class="spacings">'.$batch->batch.'</td>';

            foreach ($samples as $sample) {
                $samp_counter++;
                
                $cd4_values = $this->db->get_where('pt_participants_calculated_v', ['round_id' =>  $round_id, 'equipment_id'   =>  $equipment_id, 'sample_id'  =>  $sample->id])->row();

                


                if($cd4_values){
                    $upper_limit = $cd4_values->cd4_absolute_upper_limit;
                    $lower_limit = $cd4_values->cd4_absolute_lower_limit;
                }else{
                    $upper_limit = 0;
                    $lower_limit = 0;
                } 

                
                $part_cd4 = $this->Analysis_m->absoluteValue($round_id,$equipment_id,$sample->id,$submission->participant_id);

               
                if($part_cd4){

                    $html_body .= '<td class="spacings">'.$part_cd4->cd4_absolute.'</td>';

                    if($part_cd4->cd4_absolute >= $lower_limit && $part_cd4->cd4_absolute <= $upper_limit){
                        $acceptable++;
                        $comment = "Acceptable";

                    }else{
                        $unacceptable++;
                        $comment = "Unacceptable";
                    }   

                    if($part_cd4->cd4_absolute == 0 || $part_cd4->cd4_absolute == null){
                        $zerocount++;
                    }

                    array_push($tabledata, $part_cd4->cd4_absolute, $comment);
                    
                }else{
                    array_push($tabledata, 0, "Unacceptable");
                }   
                $html_body .= '<td class="spacings">'.$comment.'</td>'; 
            }

            

            $grade = (($acceptable / $samp_counter) * 100);


            $overall_grade = round($grade, 2) . ' %';

            if($grade == 100){
                $review = "Satisfactory Performance";
            }else if($grade > 0 && $grade < 100){
                $review = "Unsatisfactory Performance";
            }else{
                $review = "Non-responsive";
            }

            $username = $this->db->get_where('pt_ready_participants', ['p_id' =>  $submission->participant_id])->row()->participant_id;
            // echo "<pre>";print_r($part_cd4);echo "</pre>";die();
            $part_details = $this->db->get_where('users_v', ['username' =>  $username])->row();
            
            $name = $part_details->firstname . ' ' . $part_details->lastname;

            array_push($tabledata, $overall_grade,$review,$name,$part_details->phone,$part_details->email_address);


            
            switch ($form) {
                case 'table':
                    
                    $table[$count] = $tabledata;

                break;

                case 'excel':
                    array_push($row_data, $tabledata);
                break;

                case 'pdf':
                 
                    
                    $html_body .= '<td class="spacings">'.$overall_grade.' %</td>';
                    $html_body .= '<td class="spacings">'.$review.'</td>';
                    $html_body .= '<td class="spacings">'.$name.'</td>';
                    $html_body .= '<td class="spacings">'.$part_details->phone.'</td>';
                    $html_body .= '<td class="spacings">'.$part_details->email_address.'</td>';
                    $html_body .= "</tr></ol>";
                break;
                    
                
                default:
                    echo "<pre>";print_r("Something went wrong... PLease contact the administrator");echo "</pre>";die();
                break;
            }


           
            $count++;
                      
        }

        if($form == 'table'){

            $this->table->set_template($template);
            $this->table->set_heading($heading);

            return $this->table->generate($table);

        }else if($form == 'excel'){

            $excel_data = array();
            $excel_data = array('doc_creator' => 'External_Quality_Assurance', 'doc_title' => 'Participants_'.$round_name.'_'.$equipment_name, 'file_name' => 'Participants_'.$round_name.'_'.$equipment_name, 'excel_topic' => 'Participants_'.$equipment_name);

            
            $excel_data['column_data'] = $column_data;
            $excel_data['row_data'] = $row_data;

            $this->export->create_excel($excel_data);

        }else if($form == 'pdf'){

            $html_body .= '</tbody></table>';
            $pdf_data = array("pdf_title" => 'Participants_'.$round_name.'_'.$equipment_name, 'pdf_html_body' => $html_body, 'pdf_view_option' => 'download', 'file_name' => 'Participants_'.$round_name.'_'.$equipment_name, 'pdf_topic' => 'Participants_'.$round_name.'_'.$equipment_name);

            $this->export->create_pdf($html_body,$pdf_data);

        }
    }


     public function HistoricalGraph(){
        $labels = $graph_data = $datasets = $data = array();
        $participants = $pass = $fail = 0;
        $counter = $unsatisfactory = $satisfactory = $disqualified = $unable = $non_responsive = $partcount = $accept = $unaccept = $passed = $failed = 0;

        $backgroundColor = ['rgba(52,152,219,0.5)','rgba(46,204,113,0.5)','rgba(211,84,0,0.5)','rgba(231,76,60,0.5)','rgba(127,140,141,0.5)','rgba(241,196,15,0.5)','rgba(52,73,94,0.5)'
        ];

        $borderColor = ['rgba(52,152,219,0.8)','rgba(46,204,113,0.8)','rgba(211,84,0,0.8)','rgba(231,76,60,0.8)','rgba(127,140,141,0.8)','rgba(241,196,15,0.8)','rgba(52,73,94,0.8)'
        ];

        $highlightFill = ['rgba(52,152,219,0.75)','rgba(46,204,113,0.75)','rgba(211,84,0,0.75)','rgba(231,76,60,0.75)','rgba(127,140,141,0.75)','rgba(241,196,15,0.75)','rgba(52,73,94,0.75)'
        ];

        $highlightStroke = ['rgba(52,152,219,1)','rgba(46,204,113,1)','rgba(211,84,0,1)','rgba(231,76,60,1)','rgba(127,140,141,1)','rgba(241,196,15,1)','rgba(52,73,94,1)'
        ];

        $rounds = $this->Analysis_m->getLatestRounds();

        if($rounds){
            foreach ($rounds as $round) {
                $data = [];
                $counter = 0;

                $no_participants = [
                    'label'         =>  'NO. OF PARTICIPANTS',
                    'backgroundColor' => $backgroundColor[$counter],
                    'borderColor' => $borderColor[$counter],
                    'highlightFill' => $highlightFill[$counter],
                    'highlightStroke' => $highlightStroke[$counter]
                ];

                $counter++;

                $pass = [
                    'label'         =>  'PASS',
                    'backgroundColor' => $backgroundColor[$counter],
                    'borderColor' => $borderColor[$counter],
                    'highlightFill' => $highlightFill[$counter],
                    'highlightStroke' => $highlightStroke[$counter]
                ];

                $counter++;

                $fail = [
                    'label'         =>  'FAIL',
                    'backgroundColor' => $backgroundColor[$counter],
                    'borderColor' => $borderColor[$counter],
                    'highlightFill' => $highlightFill[$counter],
                    'highlightStroke' => $highlightStroke[$counter]
                ];


                $round_id = $this->db->get_where('pt_round', ['uuid' => $round->uuid])->row()->id;
                $samples = $this->db->get_where('pt_samples', ['pt_round_id' =>  $round_id])->result();
                $participants = $this->Analysis_m->getReadyParticipants($round_id);
                $equipments = $this->Analysis_m->Equipments();

                foreach ($equipments as $key => $equipment) {
                    
                    
                    $equipment_id = $equipment->id;

                    foreach ($participants as $participant) {
                        $partcount ++;
                        $novalue = $sampcount = $acceptable = $unacceptable = 0;

                        foreach ($samples as $sample) {
                            $sampcount++;

                            $cd4_values = $this->Analysis_m->getRoundResults($round_id, $equipment_id, $sample->id);

                            if($cd4_values){

                                $upper_limit = $cd4_values->cd4_absolute_upper_limit;
                                $lower_limit = $cd4_values->cd4_absolute_lower_limit;
                            }else{
                                $upper_limit = 0;
                                $lower_limit = 0;
                            } 

                            $part_cd4 = $this->Analysis_m->absoluteValue($round_id,$equipment_id,$sample->id,$participant->participant_id);

                            if($part_cd4){
                                
                                if($part_cd4->cd4_absolute >= $lower_limit && $part_cd4->cd4_absolute <= $upper_limit){
                                    $acceptable++;    
                                } else{
                                    $unacceptable++;    
                                } 

                                if($part_cd4->cd4_absolute == 0){
                                    $novalue++;
                                }
                            } 
                        } 

                        if($novalue == $sampcount){
                            $non_responsive++;
                        }

                        if($acceptable == $sampcount) {
                            $passed++;
                        }

                    }
                }

                
                $labels[] = $round->pt_round_no;

                $no_of_participants = $this->Analysis_m->ParticipatingParticipants($round->uuid)->participants;
                $failed = $no_of_participants - $passed;

                $no_participants['data'][] = $no_of_participants;
                $pass['data'][] = $passed;
                $fail['data'][] = $failed;

                
            }
        }

        $graph_data['labels'] = $labels;
        $graph_data['datasets'] = [$no_participants, $pass, $fail];

        // echo "<pre>";print_r($graph_data);die;

        return $this->output->set_content_type('application/json')->set_output(json_encode($graph_data));
    }


    public function RemedialGraph($round_uuid){
        $labels = $graph_data = $datasets = $data = array();
        $facscalibur = $facspresto = $facscount = $alere_pima = $partec_cyflow = $guava_easycyte = $other = 0;

        // $equipment_breakdown = $this->Analysis_m->getEquipmentBreakdown($round_uuid)->equipments;
        // $reagent_stock_out = $this->Analysis_m->getReagentStock($round_uuid)->reagents;
        // $analyst_unavailable = $this->Analysis_m->getUnavailableAnalyst($round_uuid)->analysts;
        // $pending_capa = $this->Analysis_m->getPendingCapa($round_uuid)->capas;

        // echo "<pre>";print_r($pending_capa);echo "</pre>";die();

        $datasets1 = [
            'label'         =>  'FACSCALIBUR',
            'backgroundColor' => 'rgba(211,84,0,0.5)',
            'borderColor' => 'rgba(211,84,0,0.8)',
            'highlightFill' => 'rgba(211,84,0,0.75)',
            'highlightStroke' => 'rgba(211,84,0,1)',
            'data' => [$facscalibur]
        ];
        $datasets2 = [
            'label'         =>  'FACSPRESTO',
            'backgroundColor' => 'rgba(52,152,219,0.5)',
            'borderColor' => 'rgba(52,152,219,0.8)',
            'highlightFill' => 'rgba(52,152,219,0.75)',
            'highlightStroke' => 'rgba(52,152,219,1)',
            'data' => [$facspresto]
        ];
        $datasets3 = [
            'label'         =>  'FACSCOUNT',
            'backgroundColor' => 'rgba(46,204,113,0.5)',
            'borderColor' => 'rgba(46,204,113,0.8)',
            'highlightFill' => 'rgba(46,204,113,0.75)',
            'highlightStroke' => 'rgba(46,204,113,1)',
            'data' => [$facscount]
        ];
        $datasets4 = [
            'label'         =>  'ALERE PIMA',
            'backgroundColor' => 'rgba(231,76,60,0.5)',
            'borderColor' => 'rgba(231,76,60,0.8)',
            'highlightFill' => 'rgba(231,76,60,0.75)',
            'highlightStroke' => 'rgba(231,76,60,1)',
            'data' => [$alere_pima]
        ];
        $datasets5 = [
            'label'         =>  'PARTEC CYFLOW',
            'backgroundColor' => 'rgba(127,140,141,0.5)',
            'borderColor' => 'rgba(127,140,141,0.8)',
            'highlightFill' => 'rgba(127,140,141,0.75)',
            'highlightStroke' => 'rgba(127,140,141,1)',
            'data' => [$partec_cyflow]
        ];
        $datasets6 = [
            'label'         =>  'GUAVA EASYCYTE',
            'backgroundColor' => 'rgba(241,196,15,0.5)',
            'borderColor' => 'rgba(241,196,15,0.8)',
            'highlightFill' => 'rgba(241,196,15,0.75)',
            'highlightStroke' => 'rgba(241,196,15,1)',
            'data' => [$guava_easycyte]
        ];
        $datasets7 = [
            'label'         =>  'OTHER',
            'backgroundColor' => 'rgba(52,73,94,0.5)',
            'borderColor' => 'rgba(52,73,94,0.8)',
            'highlightFill' => 'rgba(52,73,94,0.75)',
            'highlightStroke' => 'rgba(52,73,94,1)',
            'data' => [$other]
        ];

        

        $graph_data['labels'] = $labels;
        $graph_data['datasets'] = [$datasets1, $datasets2, $datasets3, $datasets4, $datasets5, $datasets6, $datasets7];

        return $this->output->set_content_type('application/json')->set_output(json_encode($graph_data));
    }


    public function JustificationGraph($round_uuid){
        $labels = $graph_data = $datasets = $data = array();
        $equipment_breakdown = $equipment_readerror = $reagent_stock_out = $panel_integrity = $failed_login = $no_justification = 0;

        // $equipment_breakdown = $this->Analysis_m->getEquipmentBreakdown($round_uuid)->equipments;
        // $reagent_stock_out = $this->Analysis_m->getReagentStock($round_uuid)->reagents;
        // $analyst_unavailable = $this->Analysis_m->getUnavailableAnalyst($round_uuid)->analysts;
        // $pending_capa = $this->Analysis_m->getPendingCapa($round_uuid)->capas;

        // echo "<pre>";print_r($pending_capa);echo "</pre>";die();

        $datasets1 = [
            'label'         =>  'EQUIPMENT BREAKDOWN',
            'backgroundColor' => 'rgba(211,84,0,0.5)',
            'borderColor' => 'rgba(211,84,0,0.8)',
            'highlightFill' => 'rgba(211,84,0,0.75)',
            'highlightStroke' => 'rgba(211,84,0,1)',
            'data' => [$equipment_breakdown]
        ];
        $datasets2 = [
            'label'         =>  'EQUIPMENT READ ERROR',
            'backgroundColor' => 'rgba(52,152,219,0.5)',
            'borderColor' => 'rgba(52,152,219,0.8)',
            'highlightFill' => 'rgba(52,152,219,0.75)',
            'highlightStroke' => 'rgba(52,152,219,1)',
            'data' => [$equipment_readerror]
        ];
        $datasets3 = [
            'label'         =>  'REAGENT STOCK-OUT',
            'backgroundColor' => 'rgba(46,204,113,0.5)',
            'borderColor' => 'rgba(46,204,113,0.8)',
            'highlightFill' => 'rgba(46,204,113,0.75)',
            'highlightStroke' => 'rgba(46,204,113,1)',
            'data' => [$reagent_stock_out]
        ];
        $datasets4 = [
            'label'         =>  'PANEL INTEGRITY',
            'backgroundColor' => 'rgba(231,76,60,0.5)',
            'borderColor' => 'rgba(231,76,60,0.8)',
            'highlightFill' => 'rgba(231,76,60,0.75)',
            'highlightStroke' => 'rgba(231,76,60,1)',
            'data' => [$panel_integrity]
        ];
        $datasets5 = [
            'label'         =>  'FAILED LOG-IN',
            'backgroundColor' => 'rgba(127,140,141,0.5)',
            'borderColor' => 'rgba(127,140,141,0.8)',
            'highlightFill' => 'rgba(127,140,141,0.75)',
            'highlightStroke' => 'rgba(127,140,141,1)',
            'data' => [$failed_login]
        ];
        $datasets6 = [
            'label'         =>  'NO JUSTIFICATION',
            'backgroundColor' => 'rgba(241,196,15,0.5)',
            'borderColor' => 'rgba(241,196,15,0.8)',
            'highlightFill' => 'rgba(241,196,15,0.75)',
            'highlightStroke' => 'rgba(241,196,15,1)',
            'data' => [$no_justification]
        ];

        

        $graph_data['labels'] = $labels;
        $graph_data['datasets'] = [$datasets1, $datasets2, $datasets3, $datasets4, $datasets5, $datasets6];

        return $this->output->set_content_type('application/json')->set_output(json_encode($graph_data));
    }


    public function DisqualificationGraph($round_uuid){
        $labels = $graph_data = $datasets = $data = array();
        $equipment_breakdown = $reagent_stock_out = $analyst_unavailable = $pending_capa = 0;

        $equipment_breakdown = $this->Analysis_m->getEquipmentBreakdown($round_uuid)->equipments;
        $reagent_stock_out = $this->Analysis_m->getReagentStock($round_uuid)->reagents;
        // $analyst_unavailable = $this->Analysis_m->getUnavailableAnalyst($round_uuid)->analysts;
        $pending_capa = $this->Analysis_m->getPendingCapa($round_uuid)->capas;

        // echo "<pre>";print_r($pending_capa);echo "</pre>";die();

        $datasets1 = [
            'label'         =>  'EQUIPMENT BREAKDOWN',
            'backgroundColor' => 'rgba(211,84,0,0.5)',
            'borderColor' => 'rgba(211,84,0,0.8)',
            'highlightFill' => 'rgba(211,84,0,0.75)',
            'highlightStroke' => 'rgba(211,84,0,1)',
            'data' => [$equipment_breakdown]
        ];
        $datasets2 = [
            'label'         =>  'REAGENT STOCK-OUT',
            'backgroundColor' => 'rgba(52,152,219,0.5)',
            'borderColor' => 'rgba(52,152,219,0.8)',
            'highlightFill' => 'rgba(52,152,219,0.75)',
            'highlightStroke' => 'rgba(52,152,219,1)',
            'data' => [$reagent_stock_out]
        ];
        $datasets3 = [
            'label'         =>  'ANALYST UNAVAILABLE',
            'backgroundColor' => 'rgba(46,204,113,0.5)',
            'borderColor' => 'rgba(46,204,113,0.8)',
            'highlightFill' => 'rgba(46,204,113,0.75)',
            'highlightStroke' => 'rgba(46,204,113,1)',
            'data' => [$analyst_unavailable]
        ];
        $datasets4 = [
            'label'         =>  'PENDING CAPA',
            'backgroundColor' => 'rgba(231,76,60,0.5)',
            'borderColor' => 'rgba(231,76,60,0.8)',
            'highlightFill' => 'rgba(231,76,60,0.75)',
            'highlightStroke' => 'rgba(231,76,60,1)',
            'data' => [$pending_capa]
        ];

        

        $graph_data['labels'] = $labels;
        $graph_data['datasets'] = [$datasets1, $datasets2, $datasets3, $datasets4];

        return $this->output->set_content_type('application/json')->set_output(json_encode($graph_data));
    }


    public function ParticipationGraph($round_uuid){
        $labels = $graph_data = $datasets = $data = array();
        $counter = $unsatisfactory = $satisfactory = $disqualified = $unable = $non_responsive = $partcount = $accept = $unaccept = $passed = $failed = 0;

        $round_id = $this->db->get_where('pt_round', ['uuid' => $round_uuid])->row()->id;
        $samples = $this->db->get_where('pt_samples', ['pt_round_id' =>  $round_id])->result();
        $participants = $this->Analysis_m->getReadyParticipants($round_id);
        $equipments = $this->Analysis_m->Equipments();

        foreach ($equipments as $key => $equipment) {
            $counter++;
            
            $equipment_id = $equipment->id;

            foreach ($participants as $participant) {
                $partcount ++;
                $novalue = $sampcount = $acceptable = $unacceptable = 0;

                foreach ($samples as $sample) {
                    $sampcount++;

                    $cd4_values = $this->Analysis_m->getRoundResults($round_id, $equipment_id, $sample->id);

                    if($cd4_values){

                        $upper_limit = $cd4_values->cd4_absolute_upper_limit;
                        $lower_limit = $cd4_values->cd4_absolute_lower_limit;
                    }else{
                        $upper_limit = 0;
                        $lower_limit = 0;
                    } 

                    $part_cd4 = $this->Analysis_m->absoluteValue($round_id,$equipment_id,$sample->id,$participant->participant_id);

                    if($part_cd4){
                        
                        if($part_cd4->cd4_absolute >= $lower_limit && $part_cd4->cd4_absolute <= $upper_limit){
                            $acceptable++;    
                        } else{
                            $unacceptable++;    
                        } 

                        if($part_cd4->cd4_absolute == 0){
                            $novalue++;
                        }
                    } 
                } 

                if($novalue == $sampcount){
                    $non_responsive++;
                }

                if($acceptable == $sampcount) {
                    $passed++;
                }

            }
        }

        $unable = $this->Analysis_m->getUnableParticipants($round_uuid)->participants;
        $disqualified = $this->Analysis_m->getRoundVerdict($round_uuid)->participants;
        $total_facilities = $this->Analysis_m->TotalFacilities()->facilities;
        $no_of_participants = $this->Analysis_m->ParticipatingParticipants($round_uuid)->participants;
        $failed = $no_of_participants - $passed;

        $datasets1 = [
            'label'         =>  'TOTAL N0. OF FACILITIES ENROLLED',
            'backgroundColor' => 'rgba(211,84,0,0.5)',
            'borderColor' => 'rgba(211,84,0,0.8)',
            'highlightFill' => 'rgba(211,84,0,0.75)',
            'highlightStroke' => 'rgba(211,84,0,1)',
            'data' => [$total_facilities]
        ];
        $datasets2 = [
            'label'         =>  'N0. OF PARTICIPANTS (CURRENT ROUND)',
            'backgroundColor' => 'rgba(52,152,219,0.5)',
            'borderColor' => 'rgba(52,152,219,0.8)',
            'highlightFill' => 'rgba(52,152,219,0.75)',
            'highlightStroke' => 'rgba(52,152,219,1)',
            'data' => [$no_of_participants]
        ];
        $datasets3 = [
            'label'         =>  'PASSED',
            'backgroundColor' => 'rgba(46,204,113,0.5)',
            'borderColor' => 'rgba(46,204,113,0.8)',
            'highlightFill' => 'rgba(46,204,113,0.75)',
            'highlightStroke' => 'rgba(46,204,113,1)',
            'data' => [$passed]
        ];
        $datasets4 = [
            'label'         =>  'FAILED',
            'backgroundColor' => 'rgba(231,76,60,0.5)',
            'borderColor' => 'rgba(231,76,60,0.8)',
            'highlightFill' => 'rgba(231,76,60,0.75)',
            'highlightStroke' => 'rgba(231,76,60,1)',
            'data' => [$failed]
        ];
        $datasets5 = [
            'label'         =>  'NON-RESPONSIVE',
            'backgroundColor' => 'rgba(127,140,141,0.5)',
            'borderColor' => 'rgba(127,140,141,0.8)',
            'highlightFill' => 'rgba(127,140,141,0.75)',
            'highlightStroke' => 'rgba(127,140,141,1)',
            'data' => [$non_responsive]
        ];
        $datasets6 = [
            'label'         =>  'UNABLE TO REPORT',
            'backgroundColor' => 'rgba(241,196,15,0.5)',
            'borderColor' => 'rgba(241,196,15,0.8)',
            'highlightFill' => 'rgba(241,196,15,0.75)',
            'highlightStroke' => 'rgba(241,196,15,1)',
            'data' => [$unable]
        ];
        $datasets7 = [
            'label'         =>  'DISQUALIFIED',
            'backgroundColor' => 'rgba(52,73,94,0.5)',
            'borderColor' => 'rgba(52,73,94,0.8)',
            'highlightFill' => 'rgba(52,73,94,0.75)',
            'highlightStroke' => 'rgba(52,73,94,1)',
            'data' => [$disqualified]
        ];

        // echo "<pre>";print_r($unable);echo "</pre>";die();

        $graph_data['labels'] = $labels;
        $graph_data['datasets'] = [$datasets1, $datasets2, $datasets7, $datasets6, $datasets5, $datasets3, $datasets4];

        return $this->output->set_content_type('application/json')->set_output(json_encode($graph_data));
    }



	public function createTabs($round_id){
        
        $datas=[];

        $counter = $tab = 0;
        
        $samples = $this->db->get_where('pt_samples', ['pt_round_id' =>  $round_id])->result();

        
        $equipments = $this->Analysis_m->Equipments();
        
        $equipment_tabs = '';

        $equipment_tabs .= "<ul class='nav nav-tabs' role='tablist'>";

        foreach ($equipments as $key => $equipment) {
            $tab++;

            $equipment_tabs .= "<li class='nav-item'>";
            if($tab == 1){
                $equipment_tabs .= "<a class='nav-link active' data-toggle='tab'";
            }else{
                $equipment_tabs .= "<a class='nav-link' data-toggle='tab'";
            }

            $equipmentname = $equipment->equipment_name;
            $equipmentname = str_replace(' ', '_', $equipmentname);
            
            $equipment_tabs .= " href='#".$equipmentname."' role='tab' aria-controls='home'><i class='icon-calculator'></i>&nbsp;";
            $equipment_tabs .= $equipment->equipment_name;
            $equipment_tabs .= "&nbsp;";
            // $equipment_tabs .= "<span class='tag tag-success'>Complete</span>";
            $equipment_tabs .= "</a>
                                </li>";
        }

        $equipment_tabs .= "</ul>

                            <div class='tab-content'>";

        
        foreach ($equipments as $key => $equipment) {
            

            $counter++;
            
            $equipment_id = $equipment->id;
            $equipmentname = $equipment->equipment_name;
            $equipmentname = str_replace(' ', '_', $equipmentname);

            if($counter == 1){
            	
                $equipment_tabs .= "<div class='tab-pane active' id='". $equipmentname ."' role='tabpanel'>";
            }else{

                $equipment_tabs .= "<div class='tab-pane' id='". $equipmentname ."' role='tabpanel'>";
            }

            $round_uuid = $this->db->get_where('pt_round', ['id' => $round_id])->row()->uuid;

            $submissions = $this->Analysis_m->getSubmissionsNumber($round_id, $equipment_id);
            $registrations = $this->Analysis_m->getRegistrationsNumber($equipment_id);
            $participants = $this->Analysis_m->getReadyParticipants($round_id, $equipment_id);

            
            $partcount = $passed = $failed = 0;

            foreach ($participants as $participant) {
                $partcount ++;
                $sampcount = $acceptable = $unacceptable =0;



                foreach ($samples as $sample) {
                    $sampcount++;

                    $cd4_values = $this->Analysis_m->getRoundResults($round_id, $equipment_id, $sample->id);

                    // $cd4_values = $this->db->get_where('pt_participants_calculated_v', ['round_id' =>  $round_id, 'equipment_id'   =>  $equipment_id, 'sample_id'  =>  $sample->id])->row();


                    if($cd4_values){

                        $upper_limit = $cd4_values->cd4_absolute_upper_limit;
                        $lower_limit = $cd4_values->cd4_absolute_lower_limit;
                    }else{
                        $upper_limit = 0;
                        $lower_limit = 0;
                    } 

                    $part_cd4 = $this->Analysis_m->absoluteValue($round_id,$equipment_id,$sample->id,$participant->participant_id);
                    if($part_cd4){
                        
                        if($part_cd4->cd4_absolute >= $lower_limit && $part_cd4->cd4_absolute <= $upper_limit){
                            $acceptable++;
                            
                        } else{
                            $unacceptable++;
                            
                        } 
                    }  
                } 

                if($acceptable == $sampcount) {
                    $passed++;
                }else{
                    $failed++;
                }
            }

            $equipment_tabs .= '<div class = "row">
								    <div class="col-md-12">
								        <div class = "card card-outline-info">
								            <div class="card-header col-4">
								                <i class = "icon-chart"></i>
								                &nbsp;

								                    Equipment Info

								            </div>

								            <div class = "card-block">
								            No. of Registrations : <strong>';

            if($registrations){
            	$equipment_tabs .= $registrations->register_count;
            }else{
            	$equipment_tabs .= 0;
            }

            $equipment_tabs .= ' </strong><br/>
                No. of Submissions : <strong>';

                if($submissions){
                	$equipment_tabs .= $submissions->submissions_count;
                }else{
                	$equipment_tabs .= 0;
                }

            
            $equipment_tabs .= ' </strong><br/>
                No. of Passes : <strong>';

            $equipment_tabs .= $passed;

            $equipment_tabs .= ' </strong><br/><a href="'.base_url("Analysis/failedList/$round_id/$equipment_id/").'">
                No. of Failed : <strong>';

            $equipment_tabs .= $failed;

            $equipment_tabs .= ' </a></strong><br/>
				            </div>
				        </div>
				    </div>
				</div>';
            
            $equipment_tabs .= '<div class = "row">
				  
						<div class="col-md-12">
							<div class = "card">
					            <div class="card-header col-6">
					            	<i class = "icon-chart"></i>
				                &nbsp;

				                    NHRL Results

                                    <div class = "pull-right">
                                        <a href = "'.base_url("Analysis/createNHRLTable/excel/$round_id/$equipment_id/").'"> <button class = "btn btn-success btn-sm"><i class = "fa fa-arrow-down"></i> Excel</button></a>

                                        <a href = "'.base_url("Analysis/createNHRLTable/pdf/$round_id/$equipment_id/").'"> <button class = "btn btn-danger btn-sm"><i class = "fa fa-arrow-down"></i> PDF</button></a>    
                                    </div>
					            </div>
					            <div class = "card-block"><div class="table-responsive">';

            $equipment_tabs .= $this->createNHRLTable('table', $round_id, $equipment_id);

            $equipment_tabs .= '</div></div>
						    </div>
					    </div>

				</div>';


            $equipment_tabs .= '<div class = "row">
                  
                        <div class="col-md-6">
                            <div class = "card">
                                <div class="card-header col-6">
                                    <i class = "icon-chart"></i>
                                &nbsp;

                                    CD4 Absolute Peer Results
                                    <div class = "pull-right">
                                        <a href = "'.base_url("Analysis/createAbsolutePeerTable/excel/$round_id/$equipment_id/cd4").'"> <button class = "btn btn-success btn-sm"><i class = "fa fa-arrow-down"></i> Excel</button></a>

                                        <a href = "'.base_url("Analysis/createAbsolutePeerTable/pdf/$round_id/$equipment_id/cd4").'"> <button class = "btn btn-danger btn-sm"><i class = "fa fa-arrow-down"></i> PDF</button></a>    
                                    </div>
                                </div>
                                <div class = "card-block"><div class="table-responsive">';

            $equipment_tabs .= $this->createAbsolutePeerTable('table', $round_id, $equipment_id,'cd4');

            $equipment_tabs .= '</div></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class = "card ">
                                <div class="card-header col-6">
                                    <i class = "icon-chart"></i>
                                &nbsp;
                                CD4 Percent Peer Results

                                    <div class = "pull-right">
                                        <a href = "'.base_url("Analysis/createPercentPeerTable/excel/$round_id/$equipment_id/cd4").'"> <button class = "btn btn-success btn-sm"><i class = "fa fa-arrow-down"></i> Excel</button></a>

                                        <a href = "'.base_url("Analysis/createPercentPeerTable/pdf/$round_id/$equipment_id/cd4").'"> <button class = "btn btn-danger btn-sm"><i class = "fa fa-arrow-down"></i> PDF</button></a>    
                                    </div>
                                </div>

                                <div class = "card-block"><div class="table-responsive">';

            $equipment_tabs .= $this->createPercentPeerTable('table', $round_id, $equipment_id,'cd4');

            $equipment_tabs .= '</div></div>
                            </div>
                        </div>
                </div>';



            $equipment_tabs .= '<div class = "row">
                  
                        <div class="col-md-6">
                            <div class = "card">
                                <div class="card-header col-6">
                                    <i class = "icon-chart"></i>
                                &nbsp;

                                    CD3 Absolute Peer Results

                                    <div class = "pull-right">
                                        <a href = "'.base_url("Analysis/createAbsolutePeerTable/excel/$round_id/$equipment_id/cd3").'"> <button class = "btn btn-success btn-sm"><i class = "fa fa-arrow-down"></i> Excel</button></a>

                                        <a href = "'.base_url("Analysis/createAbsolutePeerTable/pdf/$round_id/$equipment_id/cd3").'"> <button class = "btn btn-danger btn-sm"><i class = "fa fa-arrow-down"></i> PDF</button></a>    
                                    </div>
                                </div>
                                <div class = "card-block"><div class="table-responsive">';

            $equipment_tabs .= $this->createAbsolutePeerTable('table', $round_id, $equipment_id,'cd3');

            $equipment_tabs .= '</div></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class = "card ">
                                <div class="card-header col-6">
                                    <i class = "icon-chart"></i>
                                &nbsp;
                                CD3 Percent Peer Results

                                <div class = "pull-right">
                                        <a href = "'.base_url("Analysis/createPercentPeerTable/excel/$round_id/$equipment_id/cd3").'"> <button class = "btn btn-success btn-sm"><i class = "fa fa-arrow-down"></i> Excel</button></a>

                                        <a href = "'.base_url("Analysis/createPercentPeerTable/pdf/$round_id/$equipment_id/cd3").'"> <button class = "btn btn-danger btn-sm"><i class = "fa fa-arrow-down"></i> PDF</button></a>    
                                    </div>
                                </div>
                                <div class = "card-block"><div class="table-responsive">';

            $equipment_tabs .= $this->createPercentPeerTable('table', $round_id, $equipment_id,'cd3');

            $equipment_tabs .= '</div></div>
                            </div>
                        </div>
                </div>';


            $equipment_tabs .= '<div class = "row">
                  
                        <div class="col-md-6">
                            <div class = "card">
                                <div class="card-header col-6">
                                    <i class = "icon-chart"></i>
                                &nbsp;

                                    Other Absolute Peer Results

                                    <div class = "pull-right">
                                        <a href = "'.base_url("Analysis/createAbsolutePeerTable/excel/$round_id/$equipment_id/other").'"> <button class = "btn btn-success btn-sm"><i class = "fa fa-arrow-down"></i> Excel</button></a>

                                        <a href = "'.base_url("Analysis/createAbsolutePeerTable/pdf/$round_id/$equipment_id/other").'"> <button class = "btn btn-danger btn-sm"><i class = "fa fa-arrow-down"></i> PDF</button></a>    
                                    </div>
                                </div>
                                <div class = "card-block"><div class="table-responsive">';

            $equipment_tabs .= $this->createAbsolutePeerTable('table', $round_id, $equipment_id,'other');

            $equipment_tabs .= '</div></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class = "card ">
                                <div class="card-header col-6">
                                    <i class = "icon-chart"></i>
                                &nbsp;
                                Other Percent Peer Results

                                <div class = "pull-right">
                                        <a href = "'.base_url("Analysis/createPercentPeerTable/excel/$round_id/$equipment_id/other").'"> <button class = "btn btn-success btn-sm"><i class = "fa fa-arrow-down"></i> Excel</button></a>

                                        <a href = "'.base_url("Analysis/createPercentPeerTable/pdf/$round_id/$equipment_id/other").'"> <button class = "btn btn-danger btn-sm"><i class = "fa fa-arrow-down"></i> PDF</button></a>    
                                    </div>
                                </div>
                                <div class = "card-block"><div class="table-responsive">';

            $equipment_tabs .= $this->createPercentPeerTable('table', $round_id, $equipment_id,'other');

            $equipment_tabs .= '</div></div>
                            </div>
                        </div>
                </div>';


			$equipment_tabs .= 	'<div class = "row">
				    <div class="col-md-12">
				        <div class = "card card-outline-danger">
				            <div class="card-header col-12">
				                <i class = "icon-chart"></i>
				                &nbsp;
				                    Participant Results


                                    <div class = "pull-right">
                                        <a href = "'.base_url("Analysis/createParticipantTable/excel/$round_id/$equipment_id/").'"> <button class = "btn btn-success btn-sm"><i class = "fa fa-arrow-down"></i> Excel</button></a>

                                        <a href = "'.base_url("Analysis/createParticipantTable/pdf/$round_id/$equipment_id/").'"> <button class = "btn btn-danger btn-sm"><i class = "fa fa-arrow-down"></i> PDF</button></a>    
                                    </div>
				            </div>

				            <div class = "card-block col-md-12"><div class="table-responsive">';

            $equipment_tabs .= $this->createParticipantTable('table', $round_id, $equipment_id);

            $equipment_tabs .= '</div></div>


				        </div>
				    
					</div>
			    </div>
			</div>';
               
        }

       
        $equipment_tabs .= "</div>";
        return $equipment_tabs;

    }

	



}

/* End of file Analysis.php */
/* Location: ./application/modules/Home/controllers/Analysis.php */
