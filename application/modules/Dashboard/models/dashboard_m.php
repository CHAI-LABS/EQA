<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Dashboard_m extends CI_Model {

	function __construct()
    {
    	
        // Call the Model constructor
        parent::__construct();

    }


    public function pendingParticipants(){
    	$this->db->from('participants');
        $this->db->where('approved', 0);
    	$result = $this->db->count_all_results();

    	return $result;
    }


    public function getCurrentRound(){
        $sql = "SELECT *
                FROM pt_round_v
                WHERE status = 'active'
                AND (type = 'future' OR type = 'ongoing')
                ORDER BY id DESC
                LIMIT 1;";

        return $this->db->query($sql)->row();
    }

    public function newEquipments(){
        $this->db->select('equipment_id')->from('participant_equipment')->where('`equipment_id` NOT IN (SELECT id FROM equipment)', NULL, FALSE);

    	$result = $this->db->count_all_results();
    	
    	return $result;
    }

    public function getDashboardStats($round_uuid){
        $sql = "SELECT 
                    count(pr.readiness_id) as readiness_submitted,
                    count(a.id) as panels_received,
                    count(b.id) as panels_not_received 
                FROM participant_readiness pr
                LEFT JOIN (SELECT id, pt_readiness_id FROM pt_panel_tracking WHERE participant_received_date IS NOT NULL) a
                        ON pr.readiness_id = a.pt_readiness_id
                LEFT JOIN (SELECT id, pt_readiness_id FROM pt_panel_tracking WHERE participant_received_date IS NULL) b
                        ON pr.readiness_id = b.pt_readiness_id
                WHERE pr.pt_round_no = '$round_uuid';";

        return $this->db->query($sql)->row();
    }

}