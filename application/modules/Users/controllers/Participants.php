<?php

class Participants extends DashboardController{
    function __construct(){
        parent::__construct();
	$this->load->module(['API', 'Participant', 'Facilities']);
        // $this->load->model('m_participant');
        $this->load->model('API/m_facilities');
    }

    
    function listing(){
        $this->assets
                ->addCss("plugin/sweetalert/sweetalert.css");
        $this->assets
                ->addJs("dashboard/js/libs/jquery.dataTables.min.js")
                ->addJs("dashboard/js/libs/dataTables.bootstrap4.min.js")
                ->addJs("plugin/sweetalert/sweetalert.min.js")
                ->setJavascript('Users/participant_js');
        $this->template
                    ->setPartial('Users/participant_list_v')
                    ->setPageTitle('Participant List')
                    ->adminTemplate();
    }

    function details($uuid){
        $participant = $this->M_Participant->findParticipantByIdentifier('uuid', $uuid);
        if($participant){
            $data = [
                'participant'   =>  $participant,
                'facility'      =>  $this->m_facilities->get($participant->participant_facility)
            ];

            $this->template
                        ->setPartial('Users/participant_details_v', $data)
                        ->setPageTitle($participant->participant_fname . " Details")
                        ->adminTemplate();
        }else{
            redirect('Users/Participants/list');
        }
    }

    function approval($uuid){
        $participant = $this->M_Participant->findParticipantByIdentifier('uuid', $uuid);
        $response = [];
        if($participant){
            $update_data = [];
            if($participant->approved == 0){
                $update_data = ['approved'  =>  1];
            }else{
                $update_data = ['approved'  =>  0];
            }

            $this->db->where('uuid', $uuid);
            if($this->db->update('participants', $update_data)){
                $response = [
                    'status'    =>  TRUE,
                    'message'   =>  "Successfully updated participant's approval details"
                ];
            }else{
                $response = [
                    'status'    =>  FALSE,
                    'message'   =>  "There was a problem updating the details"
                ];
            }
        }else{
            $response = [
                'status'    =>  FALSE,
                'message'   =>  "There was a problem getting the participant details"
            ];
        }

        return $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }
}
