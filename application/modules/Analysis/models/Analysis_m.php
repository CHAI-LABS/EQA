<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Analysis_m extends CI_Model {

	function __construct()
    {
    	
        // Call the Model constructor
        parent::__construct();

    }


    public function Equipments(){
        $sql = "
            SELECT e.id, e.uuid, e.equipment_name FROM equipment e
            WHERE e.equipment_status = 1
        ";

        $query = $this->db->query($sql);

        return $query->result();
    }

    public function absoluteValue($round_id,$equipment_id,$sample_id,$participant_id){

        $this->db->select("cd4_absolute");
        $this->db->from("pt_participant_review_v");
        $this->db->where("round_id",$round_id);
        $this->db->where("equipment_id",$equipment_id);
        $this->db->where("sample_id",$sample_id);
        $this->db->where("participant_id",$participant_id);
        $query = $this->db->get();
        
        return $query->row();
    }


    // public function absoluteValue($round_id,$equipment_id,$sample_id){

    //     $this->db->select("cd4_absolute");
    //     $this->db->from("pt_participant_review_v");
    //     $this->db->where("round_id",$round_id);
    //     $this->db->where("equipment_id",$equipment_id);
    //     $this->db->where("sample_id",$sample_id);
    //     $query = $this->db->get();
        
    //     return $query->row();
    // }



    public function getSubmissionsNumber($round_id,$equipment_id){

        $this->db->select("count(equipment_id) AS submissions_count");
        $this->db->from("pt_participant_result_v");
        $this->db->where("round_id",$round_id);
        $this->db->where("equipment_id",$equipment_id);
        $this->db->group_by("equipment_id");
        $query = $this->db->get();
        
        return $query->row();
    }

    public function getRegistrationsNumber($round_uuid,$equipment_id){
        
        $this->db->select("count(prp.participant_id) AS register_count");
        $this->db->from("pt_ready_participants prp");
        $this->db->join('participant_equipment pe', 'prp.p_id = pe.participant_id');
        $this->db->where("pt_round_uuid",$round_uuid);
        $this->db->where("equipment_id",$equipment_id);
        $this->db->group_by("equipment_id");
        $query = $this->db->get();
        
        return $query->row();

    }


    public function getReadyParticipants($round_id, $equipment_id){

        $this->db->select("prp.p_id");
        $this->db->from("pt_participant_review_v ppr");
        $this->db->join('pt_ready_participants prp', 'prp.p_id = ppr.participant_id');
        $this->db->where("round_id",$round_id);
        $this->db->where("equipment_id",$equipment_id);
        $this->db->group_by("equipment_id");

        $query = $this->db->get();
        
        return $query->result();
    }


}