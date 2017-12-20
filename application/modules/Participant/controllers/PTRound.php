<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PTRound extends MY_Controller {
    protected $row_blueprint;
    public function __construct()
    {
        parent::__construct();

        $this->load->library('table');
        $this->load->config('table');
        $this->load->module('Export');
        $this->load->module('Participant');
        $this->load->model('M_PTRound');
        $this->load->model('M_Readiness');
        $this->load->library('Mailer');

        $this->row_blueprint = "<tr class = 'reagent_row'><td colspan = '2'><label style='text-align: center;'>Reagent Name: </label> <input type = 'text' class = 'page-signup-form-control form-control' name = 'reagent_name[]' value = '|reagent_name|' required |disabled|/> </td><td colspan = '3'><label style='text-align: center;'>Lot Number: </label><input type = 'text' class = 'page-signup-form-control form-control' name = 'lot_number[]' value = '|lot_number|' required |disabled|/></td><td colspan = '3'><label style='text-align: center;'>Expiry Date: (YYYY-MM-DD)</label><input type = 'text' class = 'page-signup-form-control form-control' name = 'expiry_date[]' value = '|expiry_date|' required |disabled|/> </td></tr>";
    }

    public function index(){
        
            $data = [
                'pt_rounds'    =>  $this->createPTRoundTable()
            ];

            $this->template->setPageTitle('EQA Dashboard')->setPartial("pt_view",$data)->adminTemplate();
    }

    function createPTRoundTable(){
        // $this->db->where_not_in('type','future');
        $rounds = $this->db->get('pt_round_v')->result();
        // echo "<pre>";print_r($rounds);echo "</pre>";die();

        $view = $ongoing = $prev = $fut = '';
        $round_array = [];
        if ($rounds) {
            foreach ($rounds as $round) {
                $created = date('dS F, Y', strtotime($round->date_of_entry));

                $this->db->where('status','active');
                $get = $this->db->get('pt_round_v')->row();

            
                if($get == null){
                    $locking = 0;
                }else{

                    $this->db->where('status','active');
                    $this->db->where_not_in('type','future');
                    
                    $ongoing_check = $this->db->get('pt_round_v')->row();
                 
                    if($ongoing_check){
                        $ongoing_pt = $ongoing_check->uuid;
                    }else{
                        $ongoing_pt = 0;
                    }

                    if($ongoing_pt){
                        $checklocking = $this->M_PTRound->allowPTRound($ongoing_pt, $this->session->userdata('uuid'));

                    // echo "<pre>";print_r($this->session->userdata('uuid'));echo "</pre>";die();


                        if($checklocking == null){
                            $view = "";
                        }else{
                            if($checklocking->receipt){

                                $view = "<a class = 'btn btn-success btn-sm' href = '".base_url('Participant/PTRound/Round/' . $round->uuid)."'><i class = 'fa fa-eye'></i>&nbsp;View</a>&nbsp;";

                                if($round->type == 'previous'){
                                    $view .= "<a class = 'btn btn-info btn-sm' href = '".base_url('Participant/PTRound/Results/' . $round->uuid)."'><i class = 'fa fa-eye'></i>&nbsp;Results</a>";
                                }
             
                            }else{

                                $view = "<a class = 'btn btn-success btn-sm' href = '".base_url('Participant/PanelTracking/confirm/' . $checklocking->uuid)."'><i class = 'fa fa-eye'></i>&nbsp;Confirm Receipt</a>";

                            }
                        }
                    }else{
                        $view = "";
                    }
                }

                
                $panel_tracking = "<a class = 'btn btn-danger btn-sm' href = '".base_url('Participant/PTRound/Report/' . $round->uuid)."'><i class = 'fa fa-line-chart'></i>&nbsp;Report</a>";
                $status = ($round->status == "active") ? '<span class = "tag tag-success">Active</span>' : '<span class = "tag tag-danger">Inactive</span>';

                if ($round->type == "ongoing") {
                    $ongoing .= "<tr>
                    <td>{$round->pt_round_no}</td>
                    <td>{$created}</td>
                    <td>{$status}</td>
                    <td>{$view}</td>
                    </tr>";
                }else if ($round->type == "future"){
                    $fut .= "<tr>
                    <td>{$round->pt_round_no}</td>
                    <td>{$created}</td>
                    <td>{$status}</td>
                    <td>{$view}</td>
                    </tr>";
                }else if ($round->type == "previous"){
                    $prev .= "<tr>
                    <td>{$round->pt_round_no}</td>
                    <td>{$created}</td>
                    <td>{$status}</td>
                    <td>{$view}</td>
                    </tr>";
                }
            }
        }

        $round_array = [
            'ongoing'   =>  $ongoing,
            'previous'   =>  $prev,
            'future'   =>  $fut
        ];

        return $round_array;
    }


    public function Results($round_uuid){
        $data = [];
        $title = "Analysis";


        $pt_id = $this->db->get_where('pt_round', ['uuid'   => $round_uuid])->row()->id;

        //echo "<pre>";print_r($equipments);echo "</pre>";die();

        
        $data = [
            'table_results' => $this->createTables($pt_id)
        ]; 

        $this->assets->addCss('css/main.css');
        $this->assets
                ->addJs("dashboard/js/libs/jquery.dataTables.min.js")
                ->addJs("dashboard/js/libs/dataTables.bootstrap4.min.js")
                ->addJs('dashboard/js/libs/moment.min.js');
        $this->assets->setJavascript('Participant/results_js');
        $this->template
                ->setPageTitle($title)
                ->setPartial('Participant/participant_result', $data)
                ->adminTemplate();
    }



    public function createTables($round_id){
        
        $datas=[];

        $participant_uuid = $this->session->userdata('uuid');

        $participant = $this->db->get_where('participant_readiness_v', ['uuid' =>  $participant_uuid])->row();

        $counter = $tab = 0;
        
        $samples = $this->db->get_where('pt_samples', ['pt_round_id' =>  $round_id])->result();

        
        $equipments = $this->M_PTRound->resultEquipments($participant->p_id);
        
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

            
            $samp_counter = $acceptable = $unacceptable = $sampcount = $partcount = $passed = $failed = 0;

                foreach ($samples as $sample) {
                    $sampcount++;

                    $cd4_values = $this->db->get_where('pt_participants_calculated_v', ['round_id' =>  $round_id, 'equipment_id'   =>  $equipment_id, 'sample_id'  =>  $sample->id])->row();

                    


                    if($cd4_values){
                        $upper_limit = $cd4_values->cd4_absolute_upper_limit;
                        $lower_limit = $cd4_values->cd4_absolute_lower_limit;
                    }else{
                        $upper_limit = 0;
                        $lower_limit = 0;
                    } 



                    $part_cd4 = $this->M_PTRound->absoluteValue($round_id,$equipment_id,$sample->id,$participant->p_id);
                    if($part_cd4){
                        // echo "<pre>";print_r("Upper ".$upper_limit);echo "</pre>";
                        
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
            

            $equipment_tabs .= '<div class = "row">

                        <div class="col-md-12">
                            <div class = "card">
                                <div class="card-header col-6">
                                    <i class = "icon-chart"></i>
                                &nbsp;

                                    Facility CD4 Results
                                    <div class = "pull-right">
                                        <a href = "'.base_url("Participant/PTRound/createFacilityTable/excel/$round_id/$equipment_id/cd4").'"> <button class = "btn btn-success btn-sm"><i class = "fa fa-arrow-down"></i> Excel</button></a>

                                        <a href = "'.base_url("Participant/PTRound/createFacilityTable/pdf/$round_id/$equipment_id/cd4").'"> <button class = "btn btn-danger btn-sm"><i class = "fa fa-arrow-down"></i> PDF</button></a>    
                                    </div>
                                </div>
                                <div class = "card-block">';

            $equipment_tabs .= $this->createFacilityTable('table', $round_id, $equipment_id,'cd4');

            $equipment_tabs .= '</div>
                            </div>
                        </div>
                  
                        <div class="col-md-6">
                            <div class = "card">
                                <div class="card-header col-6">
                                    <i class = "icon-chart"></i>
                                &nbsp;

                                    CD4 Absolute Peer Results
                                    <div class = "pull-right">
                                        <a href = "'.base_url("Participant/PTRound/createAbsolutePeerTable/excel/$round_id/$equipment_id/cd4").'"> <button class = "btn btn-success btn-sm"><i class = "fa fa-arrow-down"></i> Excel</button></a>

                                        <a href = "'.base_url("Participant/PTRound/createAbsolutePeerTable/pdf/$round_id/$equipment_id/cd4").'"> <button class = "btn btn-danger btn-sm"><i class = "fa fa-arrow-down"></i> PDF</button></a>    
                                    </div>
                                </div>
                                <div class = "card-block">';

            $equipment_tabs .= $this->createAbsolutePeerTable('table', $round_id, $equipment_id,'cd4');

            $equipment_tabs .= '</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class = "card ">
                                <div class="card-header col-6">
                                    <i class = "icon-chart"></i>
                                &nbsp;
                                CD4 Percent Peer Results

                                    <div class = "pull-right">
                                        <a href = "'.base_url("Participant/PTRound/createPercentPeerTable/excel/$round_id/$equipment_id/cd4").'"> <button class = "btn btn-success btn-sm"><i class = "fa fa-arrow-down"></i> Excel</button></a>

                                        <a href = "'.base_url("Participant/PTRound/createPercentPeerTable/pdf/$round_id/$equipment_id/cd4").'"> <button class = "btn btn-danger btn-sm"><i class = "fa fa-arrow-down"></i> PDF</button></a>    
                                    </div>
                                </div>

                                <div class = "card-block">';

            $equipment_tabs .= $this->createPercentPeerTable('table', $round_id, $equipment_id,'cd4');

            $equipment_tabs .= '</div>
                            </div>
                        </div>
                </div>';


            $equipment_tabs .=  '<div class = "row">
                    <div class="col-md-12">
                        <div class = "card card-outline-danger">
                            <div class="card-header col-12">
                                <i class = "icon-chart"></i>
                                &nbsp;
                                    Participant Results


                                    <div class = "pull-right">
                                        <a href = "'.base_url("Participant/PTRound/createParticipantTable/excel/$round_id/$equipment_id/").'"> <button class = "btn btn-success btn-sm"><i class = "fa fa-arrow-down"></i> Excel</button></a>

                                        <a href = "'.base_url("Participant/PTRound/createParticipantTable/pdf/$round_id/$equipment_id/").'"> <button class = "btn btn-danger btn-sm"><i class = "fa fa-arrow-down"></i> PDF</button></a>    
                                    </div>
                            </div>

                            <div class = "card-block col-md-12">';

            $equipment_tabs .= $this->createParticipantTable('table', $round_id, $equipment_id);

            $equipment_tabs .= '</div>


                        </div>
                    
                    </div>
                </div>
            </div>';
               
        }

       
        $equipment_tabs .= "</div>";
        return $equipment_tabs;

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
            "Lower Limit"
        ];
        $tabledata = [];

        foreach($samples as $sample){
            $counter++;
            $table_body = [];
            $table_body[] = $sample->sample_name;

            $calculated_values = $this->db->get_where('pt_participants_calculated_v', ['round_id' =>  $round_id, 'equipment_id'   =>  $equipment_id, 'sample_id'  =>  $sample->id])->row(); 

            switch ($type) {
                case 'cd3':

                    // echo "<pre>";print_r($calculated_values);echo "</pre>";die();

                        $mean = ($calculated_values) ? $calculated_values->cd3_percent_mean : 0;
                        $sd = ($calculated_values) ? $calculated_values->cd3_percent_sd : 0;
                        $sd2 = ($calculated_values) ? $calculated_values->double_cd3_percent_sd : 0;
                        $upper_limit = ($calculated_values) ? $calculated_values->cd3_percent_upper_limit : 0;
                        $lower_limit = ($calculated_values) ? $calculated_values->cd3_percent_lower_limit : 0;
                    
                break;

                case 'cd4':
                    // echo "<pre>";print_r($calculated_values);echo "</pre>";die();

                        $mean = ($calculated_values) ? $calculated_values->cd4_percent_mean : 0;
                        $sd = ($calculated_values) ? $calculated_values->cd4_percent_sd : 0;
                        $sd2 = ($calculated_values) ? $calculated_values->double_cd4_percent_sd : 0;
                        $upper_limit = ($calculated_values) ? $calculated_values->cd4_percent_upper_limit : 0;
                        $lower_limit = ($calculated_values) ? $calculated_values->cd4_percent_lower_limit : 0;
                    
                break;

                case 'other':
                    // echo "<pre>";print_r($calculated_values);echo "</pre>";die();

                        $mean = ($calculated_values) ? $calculated_values->other_percent_mean : 0;
                        $sd = ($calculated_values) ? $calculated_values->other_percent_sd : 0;
                        $sd2 = ($calculated_values) ? $calculated_values->double_other_percent_sd : 0;
                        $upper_limit = ($calculated_values) ? $calculated_values->other_percent_upper_limit : 0;
                        $lower_limit = ($calculated_values) ? $calculated_values->other_percent_lower_limit : 0;
                    
                break;
                
                default:
                    echo "<pre>";print_r("Something went wrong");echo "</pre>";die();
                break;
            }

            switch ($form) {
                case 'table':

                // $view = "<a class = 'btn btn-success btn-sm dropdown-item' href = '".base_url('Analysis/ParticipantResults/' . $round_id . '/' . $equipment_id . '/' . $sample->id . '/'.$type.'/percent')."'><i class = 'fa fa-eye'></i>&nbsp;View Log</a>";

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

        if($form == 'table'){

            $this->table->set_template($template);
            $this->table->set_heading($heading);

            return $this->table->generate($tabledata);

        }else if($form == 'excel'){

            $excel_data = array();
            $excel_data = array('doc_creator' => 'External_Quality_Assurance', 'doc_title' => $round_name.'_'.$equipment_name.'_'.$type.'_percent', 'file_name' => $round_name.'_'.$equipment_name.'_'.$type.'_percent', 'excel_topic' => $equipment_name.'_'.$type.'_percent');
            // $excel_data = array('doc_creator' => 'External_Quality_Assurance', 'doc_title' => $round_name.'_'.$equipment_name.'_'.$type.'_absolute', 'file_name' => $round_name.'_'.$equipment_name.'_'.$type.'_absolute', 'excel_topic' => $round_name.'_'.$equipment_name.'_'.$type.'_absolute');

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


    public function createFacilityTable($form, $round_id, $equipment_id, $type){
        $template = $this->config->item('default');

        $column_data = $row_data = array();

        $mean = $sd = $sd2 = $upper_limit = $lower_limit = $counter = 0;

        $samples = $this->db->get_where('pt_samples', ['pt_round_id' =>  $round_id])->result();

        $rounds = $this->db->get_where('pt_round_v', ['id'=>$round_id])->row();
        $round_name = str_replace(' ', '_', $rounds->pt_round_no);

        $equipments = $this->db->get_where('equipment', ['id'=>$equipment_id,'equipment_status'=>1])->row();
        $equipment_name = str_replace(' ', '_', $equipments->equipment_name);

        $participant_uuid = $this->session->userdata('uuid');
        $participant = $this->db->get_where('participant_readiness_v', ['uuid' =>  $participant_uuid])->row();

        // echo "<pre>";print_r($participant->facility_id);echo "</pre>";die();


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
            "Lower Limit"
        ];
        $tabledata = [];


        foreach($samples as $sample){
            $counter++;
            $table_body = [];
            $table_body[] = $sample->sample_name;

            $calculated_values = $this->db->get_where('pt_facility_calculated_v', ['round_id' =>  $round_id, 'equipment_id'   =>  $equipment_id, 'sample_id'  =>  $sample->id, 'facility_id'  =>  $participant->facility_id])->row(); 

            // echo "<pre>";print_r($calculated_values);echo "</pre>";die();

            switch ($type) {
                case 'cd3':

                        $mean = ($calculated_values) ? $calculated_values->cd3_absolute_mean : 0;
                        $sd = ($calculated_values) ? $calculated_values->cd3_absolute_sd : 0;
                        $sd2 = ($calculated_values) ? $calculated_values->double_cd3_absolute_sd : 0;
                        $upper_limit = ($calculated_values) ? $calculated_values->cd3_absolute_upper_limit : 0;
                        $lower_limit = ($calculated_values) ? $calculated_values->cd3_absolute_lower_limit : 0;
                    
                break;

                case 'cd4':
                    // echo "<pre>";print_r($calculated_values);echo "</pre>";die();

                        $mean = ($calculated_values) ? $calculated_values->cd4_absolute_mean : 0;
                        $sd = ($calculated_values) ? $calculated_values->cd4_absolute_sd : 0;
                        $sd2 = ($calculated_values) ? $calculated_values->double_cd4_absolute_sd : 0;
                        $upper_limit = ($calculated_values) ? $calculated_values->cd4_absolute_upper_limit : 0;
                        $lower_limit = ($calculated_values) ? $calculated_values->cd4_absolute_lower_limit : 0;
                    
                break;

                case 'other':
                    // echo "<pre>";print_r($calculated_values);echo "</pre>";die();

                        $mean = ($calculated_values) ? $calculated_values->other_absolute_mean : 0;
                        $sd = ($calculated_values) ? $calculated_values->other_absolute_sd : 0;
                        $sd2 = ($calculated_values) ? $calculated_values->double_other_absolute_sd : 0;
                        $upper_limit = ($calculated_values) ? $calculated_values->other_absolute_upper_limit : 0;
                        $lower_limit = ($calculated_values) ? $calculated_values->other_absolute_lower_limit : 0;
                    
                break;
                
                default:
                    echo "<pre>";print_r("Something went wrong");echo "</pre>";die();
                break;
            }

            switch ($form) {
                case 'table':

                // $view = "<a class = 'btn btn-success btn-sm dropdown-item' href = '".base_url('Analysis/ParticipantResults/' . $round_id . '/' . $equipment_id . '/' . $sample->id . '/'.$type.'/absolute')."'><i class = 'fa fa-eye'></i>&nbsp;View Log</a>";

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

        if($form == 'table'){

            $this->table->set_template($template);
            $this->table->set_heading($heading);

            return $this->table->generate($tabledata);

        }else if($form == 'excel'){

            $excel_data = array();
            $excel_data = array('doc_creator' => 'External_Quality_Assurance', 'doc_title' => $round_name.'_'.$equipment_name.'_'.$type.'_absolute', 'file_name' => $round_name.'_'.$equipment_name.'_'.$type.'_absolute', 'excel_topic' => $equipment_name.'_'.$type.'_absolute');

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
            "Lower Limit"
        ];
        $tabledata = [];

        foreach($samples as $sample){
            $counter++;
            $table_body = [];
            $table_body[] = $sample->sample_name;

            $calculated_values = $this->db->get_where('pt_participants_calculated_v', ['round_id' =>  $round_id, 'equipment_id'   =>  $equipment_id, 'sample_id'  =>  $sample->id])->row(); 

            switch ($type) {
                case 'cd3':

                    // echo "<pre>";print_r($calculated_values);echo "</pre>";die();

                        $mean = ($calculated_values) ? $calculated_values->cd3_absolute_mean : 0;
                        $sd = ($calculated_values) ? $calculated_values->cd3_absolute_sd : 0;
                        $sd2 = ($calculated_values) ? $calculated_values->double_cd3_absolute_sd : 0;
                        $upper_limit = ($calculated_values) ? $calculated_values->cd3_absolute_upper_limit : 0;
                        $lower_limit = ($calculated_values) ? $calculated_values->cd3_absolute_lower_limit : 0;
                    
                break;

                case 'cd4':
                    // echo "<pre>";print_r($calculated_values);echo "</pre>";die();

                        $mean = ($calculated_values) ? $calculated_values->cd4_absolute_mean : 0;
                        $sd = ($calculated_values) ? $calculated_values->cd4_absolute_sd : 0;
                        $sd2 = ($calculated_values) ? $calculated_values->double_cd4_absolute_sd : 0;
                        $upper_limit = ($calculated_values) ? $calculated_values->cd4_absolute_upper_limit : 0;
                        $lower_limit = ($calculated_values) ? $calculated_values->cd4_absolute_lower_limit : 0;
                    
                break;

                case 'other':
                    // echo "<pre>";print_r($calculated_values);echo "</pre>";die();

                        $mean = ($calculated_values) ? $calculated_values->other_absolute_mean : 0;
                        $sd = ($calculated_values) ? $calculated_values->other_absolute_sd : 0;
                        $sd2 = ($calculated_values) ? $calculated_values->double_other_absolute_sd : 0;
                        $upper_limit = ($calculated_values) ? $calculated_values->other_absolute_upper_limit : 0;
                        $lower_limit = ($calculated_values) ? $calculated_values->other_absolute_lower_limit : 0;
                    
                break;
                
                default:
                    echo "<pre>";print_r("Something went wrong");echo "</pre>";die();
                break;
            }

            switch ($form) {
                case 'table':

                // $view = "<a class = 'btn btn-success btn-sm dropdown-item' href = '".base_url('Analysis/ParticipantResults/' . $round_id . '/' . $equipment_id . '/' . $sample->id . '/'.$type.'/absolute')."'><i class = 'fa fa-eye'></i>&nbsp;View Log</a>";

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


    public function createParticipantTable($form, $round_id, $equipment_id){
        $template = $this->config->item('default');
        $column_data = $row_data = $tablevalues = $tablebody = $table = [];
        $samp_counter = $count = $zerocount = $sub_counter = $acceptable = $unacceptable = 0;

        $tabledata = [];

        $rounds = $this->db->get_where('pt_round_v', ['id'=>$round_id])->row();
        $round_name = str_replace(' ', '_', $rounds->pt_round_no);
        $round_uuid = $rounds->uuid;

        $equipments = $this->db->get_where('equipment', ['id'=>$equipment_id,'equipment_status'=>1])->row();
        $equipment_name = str_replace(' ', '_', $equipments->equipment_name);

        $participant_uuid = $this->session->userdata('uuid');
        $participant = $this->db->get_where('participant_readiness_v', ['uuid' =>  $participant_uuid])->row();

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
            <th>Batch</th>';
            
        $heading = [
            "Batch"
        ];

        $column_data = array('Batch');
        
        $samples = $this->db->get_where('pt_samples', ['pt_round_id' =>  $round_id])->result();

        foreach ($samples as $sample) {
            array_push($heading, $sample->sample_name,"Comment");
            array_push($column_data, $sample->sample_name,"Comment");
            $html_body .= '<th>'.$sample->sample_name.'</th> <th>Comment</th>';
        }

        array_push($heading, 'Overall Grade', "Review Comment");
        array_push($column_data, 'Overall Grade', "Review Comment");

        $html_body .= ' 
        <th>Overall Grade</th>
            <th>Review Comment</th>
            </tr> 
        </thead>
        <tbody>
        <ol type="a">';


        $submissions = $this->db->get_where('pt_data_submission', ['round_id' =>  $round_id, 'equipment_id' => $equipment_id])->result();

            
            $batch = $this->db->get_where('pt_ready_participants', ['p_id' => $participant->p_id, 'pt_round_uuid' => $round_uuid])->row();

            array_push($tabledata, $batch->batch);

            // $html_body .= '<tr><td class="spacings">'.$sub_counter.'</td>';
            // $html_body .= '<td class="spacings">'.$facility_name.'</td>';
            $html_body .= '<tr><td class="spacings">'.$batch->batch.'</td>';

            

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

                
                $part_cd4 = $this->M_PTRound->absoluteValue($round_id,$equipment_id,$sample->id,$participant->p_id);

               
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
            }else if($zerocount == $samp_counter){
                $review = "Non-responsive";
            }else{
                $review = "Incomplete Submission";
            }

            
            $part_details = $this->db->get_where('users_v', ['username' =>  $participant->username])->row();
            
            $name = $part_details->firstname . ' ' . $part_details->lastname;

            array_push($tabledata, $overall_grade,$review);


            // echo "<pre>";print_r($tabledata);echo "</pre>";die();
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
                    $html_body .= "</tr></ol>";
                break;
                    
                
                default:
                    echo "<pre>";print_r("Something went wrong... PLease contact the administrator");echo "</pre>";die();
                break;
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
    

    public function Round($round_uuid){
        $user = $this->M_Readiness->findUserByIdentifier('uuid', $this->session->userdata('uuid'));
        

        $participant_uuid = $user->uuid;
        $equipment_tabs = $this->createTabs($round_uuid,$participant_uuid);
        
        $pt_round_to = $this->M_Readiness->findRoundByIdentifier('uuid', $round_uuid)->to;

        $data = [
                'pt_round_to' => $pt_round_to,
                'pt_uuid'    =>  $round_uuid,
                'participant'    =>  $participant_uuid,
                'equipment_tabs'    =>  $equipment_tabs,
                'data_submission' => 'data_submission'
            ];

        $js_data['row_blueprint'] = $this->cleanReagentRowTemplate("");


              
        $this->assets
                ->addCss('plugin/bootstrap-datepicker/css/bootstrap-datepicker3.min.css')
                ->addCss("plugin/sweetalert/sweetalert.css")
                ->addCss('css/signin.css');  
        $this->assets
                ->addJs("dashboard/js/libs/jquery.dataTables.min.js")
                ->addJs("dashboard/js/libs/dataTables.bootstrap4.min.js")
                ->addJs('dashboard/js/libs/moment.min.js')
                ->addJs('plugin/bootstrap-datepicker/js/bootstrap-datepicker.min.js')
                ->addJs('dashboard/js/libs/jquery.validate.js')
                ->addJs('dashboard/js/libs/select2.min.js')
                ->addJs("plugin/sweetalert/sweetalert.min.js");
        $this->assets
                ->setJavascript('Participant/data_submission_js', $js_data);
        
        $this->template->setPageTitle('PT Forms')->setPartial('pt_form_v', $data)->adminTemplate();
    }

    public function Report($round_uuid){
        $user = $this->M_Readiness->findUserByIdentifier('uuid', $this->session->userdata('uuid'));
        $round = $this->M_Readiness->findRoundByIdentifier('uuid',$round_uuid);
        // echo "<pre>";print_r($round);echo "</pre>";die();
        $data = [
                'pt_uuid'    =>  $round_uuid,
                'user'    =>  $user,
                'round'    =>  $round
            ];
        $this->assets
            ->addJs('dashboard/js/libs/jquery.validate.js')
            ->addJs("plugin/sweetalert/sweetalert.min.js");
        $this->assets->setJavascript('Participant/participant_login_js');
        $this->assets
                ->addCss("plugin/sweetalert/sweetalert.css")
                ->addCss('css/signin.css');
        $this->template->setPageTitle('PT Forms')->setPartial('pt_report_v',$data)->adminTemplate();
    }


    public function dataSubmission($equipmentid,$round){
        if($this->input->post()){
            $user = $this->M_Readiness->findUserByIdentifier('uuid', $this->session->userdata('uuid'));

            $no_reagents = count($this->input->post('reagent_name'));
            $round_id = $this->M_Readiness->findRoundByIdentifier('uuid', $round)->id;
            $participant_uuid = $user->uuid;
            $participant_id = $user->p_id;

            $samples = $this->M_PTRound->getSamples($round,$participant_uuid);
             
            $counter2 = 0;
            $submission = $this->M_PTRound->getDataSubmission($round_id,$participant_id,$equipmentid);

            $lot_number = $this->input->post('lot_number');
            $reagent_name = $this->input->post('reagent_name');
            $expiry_date = $this->input->post('expiry_date');
            // echo "<pre>";print_r($sample);echo "</pre>";die();

            // Uploading file
            $file_upload_errors = [];
            $file_path = NULL;
            
            if($_FILES){
                $config['upload_path'] = './uploads/participant_data/';
                $config['allowed_types'] = 'gif|jpg|png|xlsx|xls|pdf|csv';
                $config['max_size'] = 10000000;
                $this->load->library('upload', $config);

                $this->upload->initialize($config); 
                $docCheck = $this->upload->do_upload('data_uploaded_form');
 


                if (!$docCheck) {
                    $file_upload_errors = $this->upload->display_errors();
                    echo "<pre>";print_r($file_upload_errors);echo "</pre>";die();
                }else{
                    $data =$this->upload->data();
                    $file_path = substr($config['upload_path'], 1) . $data['file_name'];
                }
            }
            if(!$file_upload_errors){
                if(!($submission)){

                    $insertsampledata = [
                            'round_id'    =>  $round_id,
                            'participant_id'    =>  $participant_id,
                            'equipment_id'    =>  $equipmentid,
                            'status'    =>  0,
                            'verdict'    =>  2,
                            'doc_path'  =>  $file_path
                        ];



                    if($this->db->insert('pt_data_submission', $insertsampledata)){
                        $submission_id = $this->db->insert_id();

                            foreach ($samples as $key => $sample) {

                                $sample_id = $this->input->post('sample_'.$counter2);
                                $cd3_abs = $this->input->post('cd3_abs_'.$counter2);
                                $cd3_per = $this->input->post('cd3_per_'.$counter2);
                                $cd4_abs = $this->input->post('cd4_abs_'.$counter2);
                                $cd4_per = $this->input->post('cd4_per_'.$counter2);
                                $other_abs = $this->input->post('other_abs_'.$counter2);
                                $other_per = $this->input->post('other_per_'.$counter2);

                                $insertequipmentdata = [
                                'equip_result_id'    =>  $submission_id,
                                'sample_id'    =>  $sample_id,
                                'cd3_absolute'    =>  $cd3_abs,
                                'cd3_percent'    =>  $cd3_per,
                                'cd4_absolute'    =>  $cd4_abs,
                                'cd4_percent'    =>  $cd4_per,
                                'other_absolute'    =>  $other_abs,
                                'other_percent'    =>  $other_per
                                ];

                                try {
                                    if($this->db->insert('pt_equipment_results', $insertequipmentdata)){
                                        $this->session->set_flashdata('success', "Successfully saved new data");
                                    }else{
                                        $this->session->set_flashdata('error', "There was a problem saving the new data. Please try again");
                                    }
                                    
                                } catch (Exception $e) {
                                    echo $e->getMessage();
                                }
                                $counter2 ++;
                            }

                            $reagent_insert = [];

                            for ($i=0; $i < $no_reagents; $i++) { 
                                $reagent_insert[] = [
                                    'submission_id' =>  $submission_id,
                                    'equipment_id'  =>  $equipmentid,
                                    'reagent_name'  =>  $this->input->post('reagent_name')[$i],
                                    'lot_number'    =>  $this->input->post('lot_number')[$i],
                                    'expiry_date'   =>  date('Y-m-d', strtotime($this->input->post('expiry_date')[$i]))
                                ];
                            }

                            $this->db->insert_batch('pt_data_submission_reagent', $reagent_insert);

                    }else{
                        //echo "submission_error";
                        $this->session->set_flashdata('error', "A problem was encountered while saving data. Please try again...");
                    }

                    echo "submission_save";
                    $this->session->set_flashdata('success', "Successfully saved new data");

                }else{
                    $reagent_insert = [];
                    
                    $submission_id = $submission->id;

                        $this->db->where('equip_result_id', $submission_id);
                        $this->db->delete('pt_equipment_results');
                    
                    foreach ($samples as $key => $sample) {     

                        $sample_id = $this->input->post('sample_'.$counter2);
                        $cd3_abs = $this->input->post('cd3_abs_'.$counter2);
                        $cd3_per = $this->input->post('cd3_per_'.$counter2);
                        $cd4_abs = $this->input->post('cd4_abs_'.$counter2);
                        $cd4_per = $this->input->post('cd4_per_'.$counter2);
                        $other_abs = $this->input->post('other_abs_'.$counter2);
                        $other_per = $this->input->post('other_per_'.$counter2);

                        $insertequipmentdata = [
                                'equip_result_id'    =>  $submission_id,
                                'sample_id'    =>  $sample_id,
                                'cd3_absolute'    =>  $cd3_abs,
                                'cd3_percent'    =>  $cd3_per,
                                'cd4_absolute'    =>  $cd4_abs,
                                'cd4_percent'    =>  $cd4_per,
                                'other_absolute'    =>  $other_abs,
                                'other_percent'    =>  $other_per
                                ];

                        if($this->db->insert('pt_equipment_results', $insertequipmentdata)){
                            
                        }

                        $counter2 ++;
                    }

                    $this->db->where('submission_id', $submission_id);
                    $this->db->where('equipment_id', $equipmentid);
                    $this->db->delete('pt_data_submission_reagent');

                    for ($i=0; $i < $no_reagents; $i++) { 
                        $reagent_insert[] = [
                            'submission_id' =>  $submission_id,
                            'equipment_id'  =>  $equipmentid,
                            'reagent_name'  =>  $this->input->post('reagent_name')[$i],
                            'lot_number'    =>  $this->input->post('lot_number')[$i],
                            'expiry_date'   =>  date('Y-m-d', strtotime($this->input->post('expiry_date')[$i]))
                        ];
                    }

                    $this->db->insert_batch('pt_data_submission_reagent', $reagent_insert);



                    $this->session->set_flashdata('success', "Successfully updated data");
                    echo "submission_update";
                }
            }else{
                echo "error3";
                $this->session->set_flashdata('error', $file_upload_errors);
            }
        }else{
            //echo "no_post";
            echo "error4";
          $this->session->set_flashdata('error', "No data was received");
        }
    }

    


    public function createTabs($round_uuid, $participant_uuid){
        
        $datas=[];
        $tab = 0;
        $zero = '0';
        
        if($participant_uuid == 0){
            $samples = $this->M_PTRound->getSamples($round_uuid,$participant_uuid,'nopart');
        }else{
            $samples = $this->M_PTRound->getSamples($round_uuid,$participant_uuid);
        }

        // echo "<pre>";print_r($samples);echo "</pre>";die();


        $round_id = $this->M_Readiness->findRoundByIdentifier('uuid', $round_uuid)->id;
        $user = $this->M_Readiness->findUserByIdentifier('uuid', $this->session->userdata('uuid'));

        if($user){
            $participant_id = $user->p_id;
        }else{
            $participant_id = 0;
        }


        // $unable = $this->db->checkInability();
        

        if($participant_id){
            $equipments = $this->M_PTRound->Equipments($participant_uuid);
        }else{
            $equipments = $this->db->get("equipments_v")->result();
        }
        
        
        $equipment_tabs = '';

        $equipment_tabs .= "<ul class='nav nav-tabs' role='tablist'>";

        foreach ($equipments as $key => $equipment) {
            $tab++;
            $equipment_tabs .= "";

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

        $counter = 0;
        $counter3 = 0;
        $lotcounter = 0;

        foreach ($equipments as $key => $equipment) {
            $counter++;

            

            $equipmentname = $equipment->equipment_name;
            $equipmentname = str_replace(' ', '_', $equipmentname);

            if($counter == 1){
                $equipment_tabs .= "<div class='tab-pane active' id='". $equipmentname ."' role='tabpanel'>";
            }else{
                $equipment_tabs .= "<div class='tab-pane' id='". $equipmentname ."' role='tabpanel'>";
            }
            
            $this->db->where('round_uuid',$round_uuid);
            $this->db->where('participant_id',$participant_id);
            $this->db->where('equipment_id',$equipment->id);

            $datas = $this->db->get('data_entry_v')->result();

            $this->db->where('round_id',$round_id);
            $this->db->where('participant_id',$participant_id);
            $this->db->where('equipment_id',$equipment->id);
            $new_m_count = $this->db->count_all_results('pt_data_log');

            if($new_m_count){
               $qa_m_count = $new_m_count; 
            }else{
                $qa_m_count = 0;
            }

            // echo "<pre>";print_r($new_m_count);echo "</pre>";die();

            $equipment_tabs .= "<div class='row'>
        <div class='col-sm-12'>
        <div class='card'>
            <div class='card-header'>
                

            <div class='form-group row'>
                <div class='col-md-6'>

                <label class='checkbox-inline'>
                <strong>RESULTS FOR ". $equipment->equipment_name ."</strong>
                </label>";


            $this->db->where('round_uuid', $round_uuid);
            $this->db->where('participant_uuid', $participant_uuid);
            $this->db->where('equipment_id',$equipment->id);
            $inability = $this->db->get('unable_response')->row();

            
            // echo "<pre>";print_r($reason);echo "</pre>";die();

            if($inability){
                $reason = "Reason: ".$inability->reason." Detailed Reason: ".$inability->detail. " ";

                $equipment_tabs .= "
                <div>
                    <a data-type='unable' data-value='".$reason."' class='nav-link nav-link unable' role='button'>
                        <i class='icon-envelope-letter'></i>
                        <span class='tag tag-pill tag-danger'>Click here if unable to Report for this Equipment</span>
                    </a>
                </div>";
            }else{
                $reason = "";

                $equipment_tabs .= "
                <div>
                    <a class='nav-link nav-link'  href='".base_url('Participant/PTRound/Unable/'.$round_uuid.'/'.$round_id.'/'.$participant_id.'/'.$equipment->id)."' role='button'>
                        <i class='icon-envelope-letter'></i>
                        <span class='tag tag-pill tag-danger'>Click here if unable to Report for this Equipment</span>
                    </a>
                </div>";
            }


            

            $equipment_tabs .= "</div>
                <div class='col-md-6'>
                    <a class='nav-link nav-link'  href='".base_url('Participant/PTRound/QAMessage/'.$round_uuid.'/'.$round_id.'/'.$participant_id.'/'.$equipment->id)."' role='button'>
                    Message(s) from QA on ". $equipment->equipment_name ."
                        <i class='icon-envelope-letter'></i>
                        <span class='tag tag-pill tag-danger'>". $new_m_count ."</span>
                    </a>
                </div>
                
                <div class='col-md-6'>
                    
            <label class='checkbox-inline' for='check-complete'>";

            if($datas){
                $getCheck = $this->M_PTRound->getDataSubmission($round_id,$participant_id,$equipment->id)->status;
                // $submission_id = $this->db->get_where('pt_data_submission', );
            }else{
                $getCheck = 0; 
            }
            

            
            $disabled = "";

            if($getCheck == 1 || $inability){
                $disabled = "disabled='' ";

                if($getCheck){
                    $equipment_tabs .= "<p><strong><span class='text-danger'>Further entry disabled. The Supervisor has submitted your data for review by the NHRL</span></strong></p>";
                }elseif($inability){
                    $equipment_tabs .= "<p><strong><span class='text-danger'>You checked this equipment as unable to respond</span></strong></p>";
                }
                
                // $equipment_tabs .= "<input type='checkbox' data-type = '". $equipment->equipment_name ."' class='check-complete' checked='checked' $disabled name='check-complete' value='". $equipment->id ."'>&nbsp;&nbsp; Mark Equipment as Complete";
            }else{
                $disabled = "";
                // $equipment_tabs .= "<input type='checkbox' class='check-complete' $disabled name='check-complete' value='". $equipment->id ."'>&nbsp;&nbsp; Mark Equipment as Complete";
            }

            $equipment_tabs .= "</label>
                    </div>
                </div>


            </div>
            <div class='card-block'>
            <form method='POST' class='p-a-4' id='".$equipment->id."' enctype='multipart/form-data'>
                <input type='hidden' class='page-signup-form-control form-control ptround' value='".$round_uuid."'>
                <div>
                ";

                $equipment_tabs .= "
                </div>


                <div class='row'>
                    <table class='table table-bordered'>
                        <tr>
                            <td colspan = '8'>
                                <button id = 'add-reagent' href = '#' class = 'btn btn-primary btn-sm pull-right' $disabled> <a>Add Reagent</a> </button>
                            </td>
                        </tr>";

                
                $submission_id = ($datas) ? $datas[0]->equip_result_id : NULL;
                // echo "<pre>";print_r($submission_id);echo "</pre>";die();


                $equipment_tabs .= $this->generateReagentRow($submission_id, $equipment->id, $disabled);
    

                       $equipment_tabs .= "<tr>
                            <th style='text-align: center; width:20%;' rowspan='3'>
                                PANEL
                            </th>
                            <th style='text-align: center;' colspan='6'>
                                RESULT
                            </th>
                        </tr>
                        <tr>
                            <th style='text-align: center;' colspan='2'>
                                CD3
                            </th>
                            <th style='text-align: center;' colspan='2'>
                                CD4
                            </th>
                            <th style='text-align: center;' colspan='2'>
                                Other (Specify)
                            </th>
                        </tr>
                        <tr>
                            <th style='text-align: center;'>
                                Absolute
                            </th>
                            <th style='text-align: center;'>
                                Percent
                            </th>
                            <th style='text-align: center;'>
                                Absolute
                            </th>
                            <th style='text-align: center;'>
                                Percent
                            </th>
                            <th style='text-align: center;'>
                                Absolute
                            </th>
                            <th style='text-align: center;'>
                                Percent
                            </th>
                        </tr>";                    
                    $counter2 = 0;


                    // echo "<pre>";print_r($samples);echo "</pre>";die();
                    foreach ($samples as $key => $sample) {
                        
                        

                        $value = 0;
                        $equipment_tabs .= "
                                        <tr>
                                            <th style='text-align: center;'>";
                        $equipment_tabs .= $sample->sample_name;

                        $equipment_tabs .= "</th>
                            <td>
                                <input type='hidden' name='sample_".$counter2."' value='".$sample->sample_id."' />
                                <input type='text' data-type='". $equipment->equipment_name ."' class='page-signup-form-control form-control' $disabled placeholder='' value = '";
                                
                        //echo "<pre>";print_r($datas[$counter2]->equipment_id);echo "</pre>";die();
                            if($datas){
                                if($equipment->id == $datas[$counter2]->equipment_id){
                                    $value = $datas[$counter2]->cd3_absolute;
                                }else{
                                    $value = 0;
                                }
                            }else{
                                $value = 0;
                            }

                        $equipment_tabs .= $value ."' name = 'cd3_abs_$counter2'>
                            </td>
                            <td>
                                <input type='text' data-type='". $equipment->equipment_name ."' class='page-signup-form-control form-control' $disabled placeholder='' value = '";

                        if($datas){
                                if($equipment->id == $datas[$counter2]->equipment_id){
                                    $value = $datas[$counter2]->cd3_percent;
                                }else{
                                    $value = 0;
                                }
                        }else{
                            $value = 0;
                        }

                        $equipment_tabs .= $value."' name = 'cd3_per_$counter2'>
                            </td>
                            <td>
                                <input type='text' data-type='". $equipment->equipment_name ."'  class='page-signup-form-control form-control' $disabled placeholder='' value = '";

                        if($datas){
                                if($equipment->id == $datas[$counter2]->equipment_id){
                                    $value = $datas[$counter2]->cd4_absolute;
                                }else{
                                    $value = 0;
                                }
                        }else{
                            $value = 0;
                        }

                        $equipment_tabs .= $value."'  name = 'cd4_abs_$counter2'>
                            </td>
                            <td>
                                <input type='text' data-type='". $equipment->equipment_name ."'  class='page-signup-form-control form-control' $disabled placeholder='' value = '";

                        if($datas){
                                if($equipment->id == $datas[$counter2]->equipment_id){
                                    $value = $datas[$counter2]->cd4_percent;
                                }else{
                                    $value = 0;
                                }
                        }else{
                            $value = 0;
                        }

                        $equipment_tabs .= $value."' name = 'cd4_per_$counter2'>
                            </td>
                            <td>
                                <input type='text' data-type='". $equipment->equipment_name ."'  class='page-signup-form-control form-control' $disabled placeholder='' value = '";

                        if($datas){
                                if($equipment->id == $datas[$counter2]->equipment_id){
                                    $value = $datas[$counter2]->other_absolute;
                                }else{
                                    $value = 0;
                                }
                        }else{
                            $value = 0;
                        }

                        $equipment_tabs .= $value."' name = 'other_abs_$counter2'>
                            </td>
                            <td>
                                <input type='text' data-type='". $equipment->equipment_name ."'  class='page-signup-form-control form-control' $disabled placeholder='' value = '";

                        if($datas){
                                if($equipment->id == $datas[$counter2]->equipment_id){
                                    $value = $datas[$counter2]->other_percent;
                                }else{
                                    $value = 0;
                                }
                        }else{
                            $value = 0;
                        }

                        $equipment_tabs .= $value."' name = 'other_per_$counter2'>
                            </td>
                        </tr>";
                        $counter2++;
                    }

                    $this->db->where('round_id', $round_id);
                    $this->db->where('participant_id', $participant_id);
                    $this->db->where('equipment_id', $equipment->id);
                    $entry = $this->db->get('pt_data_submission')->row();
                    if($entry){
                        if($entry->doc_path){
                            $uploader = "<div class = 'form-control'>
                                <h5>File uploaded</h5>
                                <a href = '".base_url($entry->doc_path)."'>Click to Download File</a>
                            </div>";
                        }else{
                            $uploader = "<div class = 'form-group'>
                                            <label class = 'control-label'>Please upload the data received from the machine ( jpg / png / excel / pdf / csv )</label>
                                            <input type = 'file' name = 'data_uploaded_form' required = 'true' class = 'form-control'/>
                                        </div>";
                        }
                    }else{
                        $uploader = "<div class = 'form-group'>
                                            <label class = 'control-label'>Please upload the data received from the machine ( jpg / png / excel / pdf / csv )</label>
                                            <input type = 'file' name = 'data_uploaded_form' required = 'true' class = 'form-control'/>
                                        </div>";
                    }


                    $equipment_tabs .= "</table>
                                        </div>

                                        {$uploader}
                                        <button $disabled type='submit' class='btn btn-block btn-lg btn-primary m-t-3 submit'>
                                            Save
                                        </button>

                                        </form>

                                        </div>   
                                        </div>
                                        </div>
                                        </div>
                                        </div>";

                    $equipment_tabs .= "";
                    $lotcounter++;
        }

        $equipment_tabs .= "</div>";

        return $equipment_tabs;

    }

    public function generateReagentRow($submission_id = NULL, $equipment_id, $disabled){
        

        $reagent_row = "";

        if ($submission_id != NULL) {
            $this->db->where('submission_id', $submission_id);
            $this->db->where('equipment_id', $equipment_id);
            $reagents = $this->db->get('pt_data_submission_reagent')->result();

            if($reagents){
                foreach ($reagents as $reagent) {
                    $reagent_row .= $this->cleanReagentRowTemplate($disabled, $reagent->reagent_name, $reagent->lot_number, $reagent->expiry_date);
                }
            }
        }

        if ($reagent_row == "") {
            $reagent_row = $this->cleanReagentRowTemplate($disabled);
        }

        return $reagent_row;
    }

    function cleanReagentRowTemplate($disabled, $reagent_name = NULL, $lot_number = NULL, $expiry_date = NULL){
        $row_blueprint = $this->row_blueprint;

        $search = ['|reagent_name|', '|lot_number|', '|expiry_date|', '|disabled|'];
        $replace = [$reagent_name, $lot_number, $expiry_date, $disabled];

        $row = str_replace($search, $replace, $row_blueprint);

        return $row;
    }


    public function QAMessage($round_uuid,$round_id,$part_id,$equip_id){
        $message_view = '';

        $messages = $this->M_PTRound->getDataLog($round_id,$part_id,$equip_id);
         // echo "<pre>";print_r($messages);echo "</pre>";die();

        $counter = 1;
        foreach ($messages as $key => $message) {
            $message_view .= "<div class='container-fluid pt-2'>
                                <div class='animated fadeIn'>
                                    <div class='row'>
                                        <div class='col-sm-12'>";

            if($message->verdict == 'Accepted'){
                $message_view .= "<div class='card card-outline-success'>";
            }else if($message->verdict == 'Rejected'){
                $message_view .= "<div class='card card-outline-danger'>";
            }else{
                $message_view .= "<div class='card'>";
            }


            $message_view .= "<div class='card-header'>
                                        <strong> Message ";
            $message_view .= $counter;

            $message_view .= "</strong>
                            </div>
                            <div class='card-block'>
                                <div class='row'>

                                    <div class='col-sm-2'>

                                        <div class='form-group'>
                                            <label for='name'>Verdict</label>
                                            <p>";

            $message_view .= $message->verdict;

            $message_view .= "</p>
                                        </div>

                                    </div>
                                    <div class='col-sm-8'>

                                        <div class='form-group'>
                                            <label for='ccnumber'>Message</label>
                                            <p>";

            $message_view .= $message->comments;

            $message_view .= "</p>
                                </div>

                                </div>
                                <div class='col-sm-2'>

                                    <div class='form-group'>
                                        <label for='ccnumber'>Date Sent</label>
                                        <p>";
            $message_view .= date('dS F, Y', strtotime($message->date_of_log));

            $message_view .= "</p></div></div></div></div></div></div></div></div></div>";

            $counter++;

        }

        $title = "QA/Supervisor Message";

        $data = [
            'pt_uuid'    =>  $round_uuid,
            'message_view' => $message_view 
            ];

        $this->assets
                ->addJs("dashboard/js/libs/jquery.dataTables.min.js")
                ->addJs("dashboard/js/libs/dataTables.bootstrap4.min.js")
                ->addJs('dashboard/js/libs/jquery.validate.js')
                ->addJs('dashboard/js/libs/select2.min.js');
        // $this->assets->setJavascript('Participant/notifications_js');
        $this->template
                ->setPageTitle($title)
                ->setPartial('Participant/qa_messages', $data)
                ->adminTemplate();
    }



    public function Unable($round_uuid,$round_id,$part_id,$equip_id){
        // $message_view = '';

        $round = $this->db->get_where('pt_round_v', ['uuid' => $round_uuid])->row();

        $equipment = $this->db->get_where('equipments_v', ['id' => $equip_id])->row();


        // $counter = 1;
        // foreach ($messages as $key => $message) {
            

        //     $counter++;

        // }

        $title = "Response Unable";

        $data = [
            'round_name' => $round->pt_round_no,
            'equipment_name' => $equipment->equipment_name,
            'round_id' => $round_id,
            'round_uuid' => $round_uuid,
            'equipment_id' => $equip_id,
            'participant_id' => $part_id
            ];

        $this->assets
                ->addJs("dashboard/js/libs/jquery.dataTables.min.js")
                ->addJs("dashboard/js/libs/dataTables.bootstrap4.min.js")
                ->addJs('dashboard/js/libs/jquery.validate.js')
                ->addJs('dashboard/js/libs/select2.min.js');
        // $this->assets->setJavascript('Participant/notifications_js');
        $this->template
                ->setPageTitle($title)
                ->setPartial('Participant/unable_response', $data)
                ->adminTemplate();
    }


    public function submitReason(){
        $response_array = [];
        if($this->input->post()){

            $round_uuid = $this->input->post('round_uuid');
            $equipment_id = $this->input->post('equipment_id');
            $participantuuid  =   $this->session->userdata('uuid');

            $participant = $this->db->get_where('participant_readiness_v', ['uuid' => $participantuuid])->row();
            $facilityid  =   $participant->facility_id;

            $reason = $this->input->post('reason');
            $detail = $this->input->post('detail');

            // echo "<pre>";print_r($tests);echo "</pre>";die();

            $insertdata = [
                'round_uuid'        =>  $round_uuid,
                'equipment_id'                =>  $equipment_id,
                'participant_uuid'          =>  $participantuuid,
                'facility_id'               =>  $facilityid,
                'reason'             =>  $reason,
                'detail'  =>  $detail
            ];


            if($this->db->insert('unable_response', $insertdata)){
                $this->session->set_flashdata('success', "Successfully sent reason for inability to carry out Round");
                redirect('Participant/PTRound/Round/'.$round_uuid, 'refresh');
            }else{
                $this->session->set_flashdata('error', "Unable to send...Please contact the NHRL");
                redirect('Participant/PTRound/Unable/'.$round_uuid, 'refresh');
            }


            
            
        }
    }

    
    

}

/* End of file PTRound.php */
/* Location: ./application/modules/Participant/controllers/Participant/PTRound.php */