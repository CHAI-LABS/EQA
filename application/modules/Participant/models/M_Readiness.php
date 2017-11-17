<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_Readiness extends CI_Model {
	public function findParticipant($username){
        $this->db->where('email_address', $username);
        $this->db->or_where('username', $username);

        $query = $this->db->get('participant_readiness_v', 1);

        return $query->row();
    }

    public function findUserByIdentifier($identifier, $value){
        $this->db->where($identifier, $value);
        $query = $this->db->get('participant_readiness_v', 1);

        return $query->row();
    }

    public function findUserByLabResult($round_uuid, $facility_id){
        $this->db->where('round_uuid', $round_uuid);
        $this->db->where('facility_id', $facility_id);
        $this->db->where('lab_result', 1);
        $query = $this->db->get('pt_ready_participants', 1);

        return $query->row();
    }

    public function findRoundByIdentifier($identifier, $value){
        $this->db->where($identifier, $value);
        $query = $this->db->get('pt_round_v', 1);

        return $query->row();
    }
}

/* End of file M_Readiness.php */
/* Location: ./application/modules/API/models/M_Readiness.php */