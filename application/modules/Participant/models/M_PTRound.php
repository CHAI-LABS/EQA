<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_PTRound extends CI_Model {
	public function findRound($rounduuid){
        $this->db->where('email_address', $username);
        $this->db->or_where('username', $username);

        $query = $this->db->get('participant_readiness_v', 1);

        return $query->row();
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

    public function resultEquipments($participant_id){
        $sql = "
            SELECT e.id, e.uuid, e.equipment_name FROM equipment e
            JOIN participant_equipment pe ON pe.participant_id = $participant_id
            WHERE e.equipment_status = 1 AND e.id = pe.equipment_id
        ";

        $query = $this->db->query($sql);

        return $query->result();
    }


    public function allowPTRound($ongoing_pt_uuid, $participant_uuid){
        $this->db->select('ppt.uuid, ppt.receipt');
        $this->db->from('pt_panel_tracking ppt');
        $this->db->join('participant_readiness pr', 'pr.readiness_id = ppt.pt_readiness_id');
        $this->db->join('pt_round_v prv', 'prv.uuid = pr.pt_round_no');

        $this->db->where('pr.pt_round_no', $ongoing_pt_uuid);
        $this->db->where('pr.participant_id', $participant_uuid);

        $query = $this->db->get();

        return $query->row();
    }

    public function Equipments($participant_uuid){
        $sql = "
            SELECT e.id, e.uuid, e.equipment_name FROM participant_equipment pe
            JOIN equipment e ON e.id = pe.equipment_id
            JOIN participants p ON p.id = pe.participant_id
            WHERE e.equipment_status = 1
            AND p.uuid = '{$participant_uuid}'
        ";

        $query = $this->db->query($sql);

        return $query->result();
    }

    public function getSamples($round_uuid,$participant_uuid,$type=null){

        $round_id = $this->db->get_where('pt_round_v', ['uuid' => $round_uuid])->row()->id;

        if($type == 'nopart'){
            $this->db->select('ps.id AS sample_id, ps.uuid AS sample_uuid, ps.sample_name AS sample_name');
            $this->db->from('pt_samples ps');
            $this->db->where('ps.pt_round_id', $round_id);

        }else{
            $this->db->select('ps.id AS sample_id, ps.uuid AS sample_uuid, ps.sample_name AS sample_name');
            $this->db->from('pt_panel_tracking ppt');
            $this->db->join('pt_batches pb', 'pb.id = ppt.pt_batch_id');
            $this->db->join('pt_batch_tube pbt', 'pbt.batch_id = pb.id');
            $this->db->join('pt_samples ps', 'ps.id = pbt.sample_id');
            $this->db->join('participant_readiness par', 'par.readiness_id = ppt.pt_readiness_id');
            $this->db->join('pt_round pr', 'pr.uuid = par.pt_round_no');

            $this->db->where('pr.uuid', $round_uuid);
            $this->db->where('par.participant_id', $participant_uuid);
        }

    	

    	// $this->db->group_by('ps.id');

        $query = $this->db->get();

        // echo $this->db->last_query();die;

		return $query->result();
    }


    public function getDataSubmission($round,$participant,$equipment){
    	$this->db->where('round_id', $round);
    	$this->db->where('participant_id', $participant);
    	$this->db->where('equipment_id', $equipment);
    	$query = $this->db->get('pt_data_submission',1);

    	return $query->row();
    }

    public function getReagents($submission_id,$equipment_id){
        $this->db->where('submission_id',$submission_id);
        $this->db->where('equipment_id',$equipment_id);

        $data = $this->db->get('pt_data_submission_reagent')->result();

        return $data;
    }

    public function getDataLog($round_id,$part_id,$equip_id){
        $this->db->where('round_id', $round_id);
        $this->db->where('participant_id', $part_id);
        $this->db->where('equipment_id', $equip_id);
        $this->db->order_by('id','desc');
        $query = $this->db->get('pt_data_log');

        return $query->result();
    }

    public function getPTUuid($panel_tracking_uuid){
        $this->db->select('prp.pt_round_uuid,prp.participant_uuid');
        $this->db->where('ppt.uuid', $panel_tracking_uuid);
        $this->db->join('pt_ready_participants prp', 'prp.readiness_id = ppt.pt_readiness_id');
        $query = $this->db->get('pt_panel_tracking ppt',1);

        return $query->row();
    }
}

/* End of file M_Participant.php */
/* Location: ./application/modules/Participant/models/M_PTRound.php */