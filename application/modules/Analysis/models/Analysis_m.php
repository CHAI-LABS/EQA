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

        $this->db->select("*");
        $this->db->from("pt_participant_review_v");
        $this->db->where("round_id",$round_id);
        $this->db->where("equipment_id",$equipment_id);
        $this->db->where("sample_id",$sample_id);
        $this->db->where("participant_id",$participant_id);
        $query = $this->db->get();
        
        return $query->row();
    }


    public function getParticipatedFacilities($round_id){
        $this->db->select("DISTINCT(ppr.facility_id),f.facility_code,f.facility_name");
        $this->db->from("pt_participant_review_v ppr");
        $this->db->join("facility_v f", "f.facility_id = ppr.facility_id");
        $this->db->where("ppr.round_id", $round_id);
        $query = $this->db->get();
        
        return $query->result();
    }

    public function getUsedEquipments($facility_code){
        $this->db->select("e.id, e.equipment_name");
        $this->db->from("equipments_v e");
        $this->db->join("facility_equipment_mapping fem", "fem.equipment_id = e.id");
        $this->db->where("fem.facility_code", $facility_code);
        $query = $this->db->get();
        
        return $query->result();
    }


    public function getResult($round_id,$facility_id,$equipment_id = null,$sample_id = null){
        $this->db->select("*");
        $this->db->from("pt_participant_review_v ppr");
        $this->db->join("pt_ready_participants prp", "prp.p_id = ppr.participant_id");
        $this->db->where("prp.lab_result", 1);
        $this->db->where("ppr.round_id", $round_id);

        if($equipment_id){
            $this->db->where("ppr.equipment_id",$equipment_id);
        }

        if($sample_id){
            $this->db->where("ppr.sample_id",$sample_id);
        }

        $this->db->where("prp.facility_id", $facility_id);
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

    public function getEquipmentBreakdown($round_uuid){
        $this->db->select("count(response) AS equipments");
        $this->db->from("pt_readiness_responses_v");
        $this->db->where("pt_round_uuid",$round_uuid);
        $this->db->where("question_id", 2);
        $this->db->where("response", 1);
        $query = $this->db->get();
        
        return $query->row();
    }

    public function getReagentStock($round_uuid){
        $this->db->select("count(response) AS reagents");
        $this->db->from("pt_readiness_responses_v");
        $this->db->where("pt_round_uuid",$round_uuid);
        $this->db->where("question_id", 3);
        $this->db->where("response", 1);
        $query = $this->db->get();
        
        return $query->row();
    }

    public function getUnavailableAnalyst($round_uuid){
        $this->db->select("count(response) AS analysts");
        $this->db->from("pt_readiness_responses_v");
        $this->db->where("pt_round_uuid",$round_uuid);
        $this->db->where("question_id", 1);
        $this->db->where("response", 1);
        $query = $this->db->get();
        
        return $query->row();
    }

    public function getLatestRounds(){
        $this->db->select("*");
        $this->db->from("pt_round_v");
        $this->db->order_by('id', 'DESC');
        $this->db->limit(6);

        $query = $this->db->get();
        
        return $query->result();
    }

    public function getPendingCapa($round_uuid){
        $this->db->select("count(response) AS capas");
        $this->db->from("pt_readiness_responses_v");
        $this->db->where("pt_round_uuid",$round_uuid);
        $this->db->where("question_id", 7);
        $this->db->where("response", 1);
        $query = $this->db->get();
        
        return $query->row();
    }


    public function getSubmissionsNumber($round_id,$equipment_id){

        $this->db->select("count(equipment_id) AS submissions_count");
        $this->db->from("pt_data_submission");
        $this->db->where("round_id",$round_id);
        $this->db->where("equipment_id",$equipment_id);
        $this->db->group_by("equipment_id");
        $query = $this->db->get();
        
        return $query->row();
    }

    public function getRegistrationsNumber($equipment_id){
        
        $this->db->select("count(participant_id) AS register_count");
        $this->db->from("participant_equipment");
        $this->db->where("equipment_id",$equipment_id);
        $query = $this->db->get();
        
        return $query->row();

    }

    public function getReadyParticipants($round_id, $equipment_id = null){

        $this->db->select("participant_id");
        $this->db->from("pt_participant_review_v");
        $this->db->where("round_id",$round_id);

        if($equipment_id){
            $this->db->where("equipment_id",$equipment_id);
        }
        
        $this->db->group_by("participant_id");

        $query = $this->db->get();
        
        return $query->result();
    }


    public function TotalFacilities(){
        $this->db->select('COUNT(DISTINCT(prv.facility_code)) AS facilities');
        $this->db->from('participant_readiness_v prv');
        $this->db->where('prv.status', 1);
        $query = $this->db->get();

        return $query->row();
    }


    public function ParticipatingParticipants($round_uuid){
        $this->db->select('COUNT(participant_id) AS participants');
        $this->db->from('pt_ready_participants');
        $this->db->where('pt_round_uuid', $round_uuid);
        $this->db->where('verdict', 1);
        $query = $this->db->get();

        return $query->row();
    }

    public function getRoundVerdict($round_uuid){
        $this->db->select('COUNT(participant_id) AS participants');
        $this->db->from('participant_readiness');
        $this->db->where('pt_round_no', $round_uuid);
        $this->db->where('verdict', 0);
        $query = $this->db->get();

        return $query->row();
    }

    public function getUnableParticipants($round_uuid){

        $sql = "SELECT COUNT(prv.p_id) AS participants
                FROM participant_readiness_v prv
                WHERE NOT EXISTS 
                    (SELECT * 
                     FROM participant_readiness pr
                     WHERE prv.uuid = pr.participant_id 
                     AND pr.pt_round_no = '".$round_uuid."')
                     AND prv.user_type = 'participant'";

        $query = $this->db->query($sql);

        return $query->row();
    }




    public function getParticipantsResults($round_id, $equipment_id = null, $sample_id, $participant_ids = null){

        if($equipment_id){
            $equip_where = 'AND equipment_id = '. $equipment_id;
        }else{
            $equip_where = null;
        }

        

        if ($participant_ids) {
            $participants = 'AND participant_id IN ('.$participant_ids.')';
        }else{
            $participants = '';
        }


        $sql = "SELECT 
        `pds`.`id` AS `id`,
        `pds`.`round_id` AS `round_id`,
        `per`.`sample_id` AS `sample_id`,
        `pds`.`equipment_id` AS `equipment_id`,
        ROUND(AVG((CASE
                    WHEN (`per`.`cd3_absolute` <> 0) THEN `per`.`cd3_absolute`
                    ELSE NULL
                END)),
                1) AS `cd3_absolute_mean`,
        ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`cd3_absolute` <> 0) THEN `per`.`cd3_absolute`
                    ELSE NULL
                END)),
                1) AS `cd3_absolute_sd`,
        (2 * ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`cd3_absolute` <> 0) THEN `per`.`cd3_absolute`
                    ELSE NULL
                END)),
                1)) AS `double_cd3_absolute_sd`,
        (ROUND(AVG((CASE
                    WHEN (`per`.`cd3_absolute` <> 0) THEN `per`.`cd3_absolute`
                    ELSE NULL
                END)),
                1) + (2 * ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`cd3_absolute` <> 0) THEN `per`.`cd3_absolute`
                    ELSE NULL
                END)),
                1))) AS `cd3_absolute_upper_limit`,
        (ROUND(AVG((CASE
                    WHEN (`per`.`cd3_absolute` <> 0) THEN `per`.`cd3_absolute`
                    ELSE NULL
                END)),
                1) - (2 * ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`cd3_absolute` <> 0) THEN `per`.`cd3_absolute`
                    ELSE NULL
                END)),
                1))) AS `cd3_absolute_lower_limit`,
        CEILING(((STDDEV_SAMP((CASE
                    WHEN (`per`.`cd3_absolute` <> 0) THEN `per`.`cd3_absolute`
                    ELSE NULL
                END)) / AVG((CASE
                    WHEN (`per`.`cd3_absolute` <> 0) THEN `per`.`cd3_absolute`
                    ELSE NULL
                END))) * 100)) AS `cd3_absolute_cv`,
        (CASE
            WHEN
                (CEILING(((STDDEV_SAMP((CASE
                            WHEN (`per`.`cd3_absolute` <> 0) THEN `per`.`cd3_absolute`
                            ELSE NULL
                        END)) / AVG((CASE
                            WHEN (`per`.`cd3_absolute` <> 0) THEN `per`.`cd3_absolute`
                            ELSE NULL
                        END))) * 100)) > 28)
            THEN
                'Failed'
            ELSE 'Passed'
        END) AS `cd3_absolute_outcome`,
        ROUND(AVG((CASE
                    WHEN (`per`.`cd3_percent` <> 0) THEN `per`.`cd3_percent`
                    ELSE NULL
                END)),
                1) AS `cd3_percent_mean`,
        ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`cd3_percent` <> 0) THEN `per`.`cd3_percent`
                    ELSE NULL
                END)),
                1) AS `cd3_percent_sd`,
        (2 * ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`cd3_percent` <> 0) THEN `per`.`cd3_percent`
                    ELSE NULL
                END)),
                1)) AS `double_cd3_percent_sd`,
        (ROUND(AVG((CASE
                    WHEN (`per`.`cd3_percent` <> 0) THEN `per`.`cd3_percent`
                    ELSE NULL
                END)),
                1) + (2 * ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`cd3_percent` <> 0) THEN `per`.`cd3_percent`
                    ELSE NULL
                END)),
                1))) AS `cd3_percent_upper_limit`,
        (ROUND(AVG((CASE
                    WHEN (`per`.`cd3_percent` <> 0) THEN `per`.`cd3_percent`
                    ELSE NULL
                END)),
                1) - (2 * ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`cd3_percent` <> 0) THEN `per`.`cd3_percent`
                    ELSE NULL
                END)),
                1))) AS `cd3_percent_lower_limit`,
        CEILING(((STDDEV_SAMP((CASE
                    WHEN (`per`.`cd3_percent` <> 0) THEN `per`.`cd3_percent`
                    ELSE NULL
                END)) / AVG((CASE
                    WHEN (`per`.`cd3_percent` <> 0) THEN `per`.`cd3_percent`
                    ELSE NULL
                END))) * 100)) AS `cd3_percent_cv`,
        (CASE
            WHEN
                (CEILING(((STDDEV_SAMP((CASE
                            WHEN (`per`.`cd3_percent` <> 0) THEN `per`.`cd3_percent`
                            ELSE NULL
                        END)) / AVG((CASE
                            WHEN (`per`.`cd3_percent` <> 0) THEN `per`.`cd3_percent`
                            ELSE NULL
                        END))) * 100)) > 28)
            THEN
                'Failed'
            ELSE 'Passed'
        END) AS `cd3_percent_outcome`,
        ROUND(AVG((CASE
                    WHEN (`per`.`cd4_absolute` <> 0) THEN `per`.`cd4_absolute`
                    ELSE NULL
                END)),
                1) AS `cd4_absolute_mean`,
        ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`cd4_absolute` <> 0) THEN `per`.`cd4_absolute`
                    ELSE NULL
                END)),
                1) AS `cd4_absolute_sd`,
        (2 * ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`cd4_absolute` <> 0) THEN `per`.`cd4_absolute`
                    ELSE NULL
                END)),
                1)) AS `double_cd4_absolute_sd`,
        (ROUND(AVG((CASE
                    WHEN (`per`.`cd4_absolute` <> 0) THEN `per`.`cd4_absolute`
                    ELSE NULL
                END)),
                1) + (2 * ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`cd4_absolute` <> 0) THEN `per`.`cd4_absolute`
                    ELSE NULL
                END)),
                1))) AS `cd4_absolute_upper_limit`,
        (ROUND(AVG((CASE
                    WHEN (`per`.`cd4_absolute` <> 0) THEN `per`.`cd4_absolute`
                    ELSE NULL
                END)),
                1) - (2 * ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`cd4_absolute` <> 0) THEN `per`.`cd4_absolute`
                    ELSE NULL
                END)),
                1))) AS `cd4_absolute_lower_limit`,
        CEILING(((STDDEV_SAMP((CASE
                    WHEN (`per`.`cd4_absolute` <> 0) THEN `per`.`cd4_absolute`
                    ELSE NULL
                END)) / AVG((CASE
                    WHEN (`per`.`cd4_absolute` <> 0) THEN `per`.`cd4_absolute`
                    ELSE NULL
                END))) * 100)) AS `cd4_absolute_cv`,
        (CASE
            WHEN
                (CEILING(((STDDEV_SAMP((CASE
                            WHEN (`per`.`cd4_absolute` <> 0) THEN `per`.`cd4_absolute`
                            ELSE NULL
                        END)) / AVG((CASE
                            WHEN (`per`.`cd4_absolute` <> 0) THEN `per`.`cd4_absolute`
                            ELSE NULL
                        END))) * 100)) > 28)
            THEN
                'Failed'
            ELSE 'Passed'
        END) AS `cd4_absolute_outcome`,
        ROUND(AVG((CASE
                    WHEN (`per`.`cd4_percent` <> 0) THEN `per`.`cd4_percent`
                    ELSE NULL
                END)),
                1) AS `cd4_percent_mean`,
        ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`cd4_percent` <> 0) THEN `per`.`cd4_percent`
                    ELSE NULL
                END)),
                1) AS `cd4_percent_sd`,
        (2 * ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`cd4_percent` <> 0) THEN `per`.`cd4_percent`
                    ELSE NULL
                END)),
                1)) AS `double_cd4_percent_sd`,
        (ROUND(AVG((CASE
                    WHEN (`per`.`cd4_percent` <> 0) THEN `per`.`cd4_percent`
                    ELSE NULL
                END)),
                1) + (2 * ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`cd4_percent` <> 0) THEN `per`.`cd4_percent`
                    ELSE NULL
                END)),
                1))) AS `cd4_percent_upper_limit`,
        (ROUND(AVG((CASE
                    WHEN (`per`.`cd4_percent` <> 0) THEN `per`.`cd4_percent`
                    ELSE NULL
                END)),
                1) - (2 * ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`cd4_percent` <> 0) THEN `per`.`cd4_percent`
                    ELSE NULL
                END)),
                1))) AS `cd4_percent_lower_limit`,
        CEILING(((STDDEV_SAMP((CASE
                    WHEN (`per`.`cd4_percent` <> 0) THEN `per`.`cd4_percent`
                    ELSE NULL
                END)) / AVG((CASE
                    WHEN (`per`.`cd4_percent` <> 0) THEN `per`.`cd4_percent`
                    ELSE NULL
                END))) * 100)) AS `cd4_percent_cv`,
        (CASE
            WHEN
                (CEILING(((STDDEV_SAMP((CASE
                            WHEN (`per`.`cd4_percent` <> 0) THEN `per`.`cd4_percent`
                            ELSE NULL
                        END)) / AVG((CASE
                            WHEN (`per`.`cd4_percent` <> 0) THEN `per`.`cd4_percent`
                            ELSE NULL
                        END))) * 100)) > 28)
            THEN
                'Failed'
            ELSE 'Passed'
        END) AS `cd4_percent_outcome`,
        ROUND(AVG((CASE
                    WHEN (`per`.`other_absolute` <> 0) THEN `per`.`other_absolute`
                    ELSE NULL
                END)),
                1) AS `other_absolute_mean`,
        ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`other_absolute` <> 0) THEN `per`.`other_absolute`
                    ELSE NULL
                END)),
                1) AS `other_absolute_sd`,
        (2 * ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`other_absolute` <> 0) THEN `per`.`other_absolute`
                    ELSE NULL
                END)),
                1)) AS `double_other_absolute_sd`,
        (ROUND(AVG((CASE
                    WHEN (`per`.`other_absolute` <> 0) THEN `per`.`other_absolute`
                    ELSE NULL
                END)),
                1) + (2 * ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`other_absolute` <> 0) THEN `per`.`other_absolute`
                    ELSE NULL
                END)),
                1))) AS `other_absolute_upper_limit`,
        (ROUND(AVG((CASE
                    WHEN (`per`.`other_absolute` <> 0) THEN `per`.`other_absolute`
                    ELSE NULL
                END)),
                1) - (2 * ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`other_absolute` <> 0) THEN `per`.`other_absolute`
                    ELSE NULL
                END)),
                1))) AS `other_absolute_lower_limit`,
        CEILING(((STDDEV_SAMP((CASE
                    WHEN (`per`.`other_absolute` <> 0) THEN `per`.`other_absolute`
                    ELSE NULL
                END)) / AVG((CASE
                    WHEN (`per`.`other_absolute` <> 0) THEN `per`.`other_absolute`
                    ELSE NULL
                END))) * 100)) AS `other_absolute_cv`,
        (CASE
            WHEN
                (CEILING(((STDDEV_SAMP((CASE
                            WHEN (`per`.`other_absolute` <> 0) THEN `per`.`other_absolute`
                            ELSE NULL
                        END)) / AVG((CASE
                            WHEN (`per`.`other_absolute` <> 0) THEN `per`.`other_absolute`
                            ELSE NULL
                        END))) * 100)) > 28)
            THEN
                'Failed'
            ELSE 'Passed'
        END) AS `other_absolute_outcome`,
        ROUND(AVG((CASE
                    WHEN (`per`.`other_percent` <> 0) THEN `per`.`other_percent`
                    ELSE NULL
                END)),
                1) AS `other_percent_mean`,
        ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`other_percent` <> 0) THEN `per`.`other_percent`
                    ELSE NULL
                END)),
                1) AS `other_percent_sd`,
        (2 * ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`other_percent` <> 0) THEN `per`.`other_percent`
                    ELSE NULL
                END)),
                1)) AS `double_other_percent_sd`,
        (ROUND(AVG((CASE
                    WHEN (`per`.`other_percent` <> 0) THEN `per`.`other_percent`
                    ELSE NULL
                END)),
                1) + (2 * ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`other_percent` <> 0) THEN `per`.`other_percent`
                    ELSE NULL
                END)),
                1))) AS `other_percent_upper_limit`,
        (ROUND(AVG((CASE
                    WHEN (`per`.`other_percent` <> 0) THEN `per`.`other_percent`
                    ELSE NULL
                END)),
                1) - (2 * ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`other_percent` <> 0) THEN `per`.`other_percent`
                    ELSE NULL
                END)),
                1))) AS `other_percent_lower_limit`,
        CEILING(((STDDEV_SAMP((CASE
                    WHEN (`per`.`other_percent` <> 0) THEN `per`.`other_percent`
                    ELSE NULL
                END)) / AVG((CASE
                    WHEN (`per`.`other_percent` <> 0) THEN `per`.`other_percent`
                    ELSE NULL
                END))) * 100)) AS `other_percent_cv`,
        (CASE
            WHEN
                (CEILING(((STDDEV_SAMP((CASE
                            WHEN (`per`.`other_percent` <> 0) THEN `per`.`other_percent`
                            ELSE NULL
                        END)) / AVG((CASE
                            WHEN (`per`.`other_percent` <> 0) THEN `per`.`other_percent`
                            ELSE NULL
                        END))) * 100)) > 28)
            THEN
                'Failed'
            ELSE 'Passed'
        END) AS `other_percent_outcome`,
        `pds`.`doc_path` AS `doc_path`
    FROM
        (`pt_data_submission` `pds`
        JOIN `pt_equipment_results` `per` ON ((`pds`.`id` = `per`.`equip_result_id`)))
        WHERE round_id = $round_id
        $participants
        $equip_where
        AND sample_id = $sample_id
    GROUP BY `per`.`sample_id` , `pds`.`equipment_id`";

    $query = $this->db->query($sql);

    return $query->row();

    }





    public function getRoundResults($round_id, $equipment_id = null, $sample_id, $participant_id = null){

        if($equipment_id){
            $equip_where = 'AND equipment_id = '. $equipment_id;
        }else{
            $equip_where = null;
        }

        if($participant_id){
            $participant_where = 'AND participant_id = '. $participant_id;
        }else{
            $participant_where = null;
        }


        $sql = "SELECT 
        `pds`.`id` AS `id`,
        `pds`.`round_id` AS `round_id`,
        `per`.`sample_id` AS `sample_id`,
        `pds`.`equipment_id` AS `equipment_id`,
        ROUND(AVG((CASE
                    WHEN (`per`.`cd3_absolute` <> 0) THEN `per`.`cd3_absolute`
                    ELSE NULL
                END)),
                0) AS `cd3_absolute_mean`,
        ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`cd3_absolute` <> 0) THEN `per`.`cd3_absolute`
                    ELSE NULL
                END)),
                2) AS `cd3_absolute_sd`,
        (2 * ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`cd3_absolute` <> 0) THEN `per`.`cd3_absolute`
                    ELSE NULL
                END)),
                2)) AS `double_cd3_absolute_sd`,
        (ROUND(AVG((CASE
                    WHEN (`per`.`cd3_absolute` <> 0) THEN `per`.`cd3_absolute`
                    ELSE NULL
                END)),
                0) + (2 * ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`cd3_absolute` <> 0) THEN `per`.`cd3_absolute`
                    ELSE NULL
                END)),
                2))) AS `cd3_absolute_upper_limit`,
        (ROUND(AVG((CASE
                    WHEN (`per`.`cd3_absolute` <> 0) THEN `per`.`cd3_absolute`
                    ELSE NULL
                END)),
                0) - (2 * ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`cd3_absolute` <> 0) THEN `per`.`cd3_absolute`
                    ELSE NULL
                END)),
                2))) AS `cd3_absolute_lower_limit`,
        CEILING(((STDDEV_SAMP((CASE
                    WHEN (`per`.`cd3_absolute` <> 0) THEN `per`.`cd3_absolute`
                    ELSE NULL
                END)) / AVG((CASE
                    WHEN (`per`.`cd3_absolute` <> 0) THEN `per`.`cd3_absolute`
                    ELSE NULL
                END))) * 100)) AS `cd3_absolute_cv`,
        (CASE
            WHEN
                (CEILING(((STDDEV_SAMP((CASE
                            WHEN (`per`.`cd3_absolute` <> 0) THEN `per`.`cd3_absolute`
                            ELSE NULL
                        END)) / AVG((CASE
                            WHEN (`per`.`cd3_absolute` <> 0) THEN `per`.`cd3_absolute`
                            ELSE NULL
                        END))) * 100)) > 28)
            THEN
                'Failed'
            ELSE 'Passed'
        END) AS `cd3_absolute_outcome`,
        ROUND(AVG((CASE
                    WHEN (`per`.`cd3_percent` <> 0) THEN `per`.`cd3_percent`
                    ELSE NULL
                END)),
                0) AS `cd3_percent_mean`,
        ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`cd3_percent` <> 0) THEN `per`.`cd3_percent`
                    ELSE NULL
                END)),
                2) AS `cd3_percent_sd`,
        (2 * ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`cd3_percent` <> 0) THEN `per`.`cd3_percent`
                    ELSE NULL
                END)),
                2)) AS `double_cd3_percent_sd`,
        (ROUND(AVG((CASE
                    WHEN (`per`.`cd3_percent` <> 0) THEN `per`.`cd3_percent`
                    ELSE NULL
                END)),
                0) + (2 * ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`cd3_percent` <> 0) THEN `per`.`cd3_percent`
                    ELSE NULL
                END)),
                2))) AS `cd3_percent_upper_limit`,
        (ROUND(AVG((CASE
                    WHEN (`per`.`cd3_percent` <> 0) THEN `per`.`cd3_percent`
                    ELSE NULL
                END)),
                2) - (2 * ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`cd3_percent` <> 0) THEN `per`.`cd3_percent`
                    ELSE NULL
                END)),
                2))) AS `cd3_percent_lower_limit`,
        CEILING(((STDDEV_SAMP((CASE
                    WHEN (`per`.`cd3_percent` <> 0) THEN `per`.`cd3_percent`
                    ELSE NULL
                END)) / AVG((CASE
                    WHEN (`per`.`cd3_percent` <> 0) THEN `per`.`cd3_percent`
                    ELSE NULL
                END))) * 100)) AS `cd3_percent_cv`,
        (CASE
            WHEN
                (CEILING(((STDDEV_SAMP((CASE
                            WHEN (`per`.`cd3_percent` <> 0) THEN `per`.`cd3_percent`
                            ELSE NULL
                        END)) / AVG((CASE
                            WHEN (`per`.`cd3_percent` <> 0) THEN `per`.`cd3_percent`
                            ELSE NULL
                        END))) * 100)) > 28)
            THEN
                'Failed'
            ELSE 'Passed'
        END) AS `cd3_percent_outcome`,
        ROUND(AVG((CASE
                    WHEN (`per`.`cd4_absolute` <> 0) THEN `per`.`cd4_absolute`
                    ELSE NULL
                END)),
                0) AS `cd4_absolute_mean`,
        ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`cd4_absolute` <> 0) THEN `per`.`cd4_absolute`
                    ELSE NULL
                END)),
                2) AS `cd4_absolute_sd`,
        (2 * ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`cd4_absolute` <> 0) THEN `per`.`cd4_absolute`
                    ELSE NULL
                END)),
                2)) AS `double_cd4_absolute_sd`,
        (ROUND(AVG((CASE
                    WHEN (`per`.`cd4_absolute` <> 0) THEN `per`.`cd4_absolute`
                    ELSE NULL
                END)),
                0) + (2 * ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`cd4_absolute` <> 0) THEN `per`.`cd4_absolute`
                    ELSE NULL
                END)),
                2))) AS `cd4_absolute_upper_limit`,
        (ROUND(AVG((CASE
                    WHEN (`per`.`cd4_absolute` <> 0) THEN `per`.`cd4_absolute`
                    ELSE NULL
                END)),
                2) - (2 * ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`cd4_absolute` <> 0) THEN `per`.`cd4_absolute`
                    ELSE NULL
                END)),
                2))) AS `cd4_absolute_lower_limit`,
        CEILING(((STDDEV_SAMP((CASE
                    WHEN (`per`.`cd4_absolute` <> 0) THEN `per`.`cd4_absolute`
                    ELSE NULL
                END)) / AVG((CASE
                    WHEN (`per`.`cd4_absolute` <> 0) THEN `per`.`cd4_absolute`
                    ELSE NULL
                END))) * 100)) AS `cd4_absolute_cv`,
        (CASE
            WHEN
                (CEILING(((STDDEV_SAMP((CASE
                            WHEN (`per`.`cd4_absolute` <> 0) THEN `per`.`cd4_absolute`
                            ELSE NULL
                        END)) / AVG((CASE
                            WHEN (`per`.`cd4_absolute` <> 0) THEN `per`.`cd4_absolute`
                            ELSE NULL
                        END))) * 100)) > 28)
            THEN
                'Failed'
            ELSE 'Passed'
        END) AS `cd4_absolute_outcome`,
        ROUND(AVG((CASE
                    WHEN (`per`.`cd4_percent` <> 0) THEN `per`.`cd4_percent`
                    ELSE NULL
                END)),
                0) AS `cd4_percent_mean`,
        ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`cd4_percent` <> 0) THEN `per`.`cd4_percent`
                    ELSE NULL
                END)),
                2) AS `cd4_percent_sd`,
        (2 * ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`cd4_percent` <> 0) THEN `per`.`cd4_percent`
                    ELSE NULL
                END)),
                2)) AS `double_cd4_percent_sd`,
        (ROUND(AVG((CASE
                    WHEN (`per`.`cd4_percent` <> 0) THEN `per`.`cd4_percent`
                    ELSE NULL
                END)),
                0) + (2 * ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`cd4_percent` <> 0) THEN `per`.`cd4_percent`
                    ELSE NULL
                END)),
                2))) AS `cd4_percent_upper_limit`,
        (ROUND(AVG((CASE
                    WHEN (`per`.`cd4_percent` <> 0) THEN `per`.`cd4_percent`
                    ELSE NULL
                END)),
                2) - (2 * ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`cd4_percent` <> 0) THEN `per`.`cd4_percent`
                    ELSE NULL
                END)),
                2))) AS `cd4_percent_lower_limit`,
        CEILING(((STDDEV_SAMP((CASE
                    WHEN (`per`.`cd4_percent` <> 0) THEN `per`.`cd4_percent`
                    ELSE NULL
                END)) / AVG((CASE
                    WHEN (`per`.`cd4_percent` <> 0) THEN `per`.`cd4_percent`
                    ELSE NULL
                END))) * 100)) AS `cd4_percent_cv`,
        (CASE
            WHEN
                (CEILING(((STDDEV_SAMP((CASE
                            WHEN (`per`.`cd4_percent` <> 0) THEN `per`.`cd4_percent`
                            ELSE NULL
                        END)) / AVG((CASE
                            WHEN (`per`.`cd4_percent` <> 0) THEN `per`.`cd4_percent`
                            ELSE NULL
                        END))) * 100)) > 28)
            THEN
                'Failed'
            ELSE 'Passed'
        END) AS `cd4_percent_outcome`,
        ROUND(AVG((CASE
                    WHEN (`per`.`other_absolute` <> 0) THEN `per`.`other_absolute`
                    ELSE NULL
                END)),
                0) AS `other_absolute_mean`,
        ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`other_absolute` <> 0) THEN `per`.`other_absolute`
                    ELSE NULL
                END)),
                2) AS `other_absolute_sd`,
        (2 * ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`other_absolute` <> 0) THEN `per`.`other_absolute`
                    ELSE NULL
                END)),
                2)) AS `double_other_absolute_sd`,
        (ROUND(AVG((CASE
                    WHEN (`per`.`other_absolute` <> 0) THEN `per`.`other_absolute`
                    ELSE NULL
                END)),
                0) + (2 * ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`other_absolute` <> 0) THEN `per`.`other_absolute`
                    ELSE NULL
                END)),
                2))) AS `other_absolute_upper_limit`,
        (ROUND(AVG((CASE
                    WHEN (`per`.`other_absolute` <> 0) THEN `per`.`other_absolute`
                    ELSE NULL
                END)),
                2) - (2 * ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`other_absolute` <> 0) THEN `per`.`other_absolute`
                    ELSE NULL
                END)),
                2))) AS `other_absolute_lower_limit`,
        CEILING(((STDDEV_SAMP((CASE
                    WHEN (`per`.`other_absolute` <> 0) THEN `per`.`other_absolute`
                    ELSE NULL
                END)) / AVG((CASE
                    WHEN (`per`.`other_absolute` <> 0) THEN `per`.`other_absolute`
                    ELSE NULL
                END))) * 100)) AS `other_absolute_cv`,
        (CASE
            WHEN
                (CEILING(((STDDEV_SAMP((CASE
                            WHEN (`per`.`other_absolute` <> 0) THEN `per`.`other_absolute`
                            ELSE NULL
                        END)) / AVG((CASE
                            WHEN (`per`.`other_absolute` <> 0) THEN `per`.`other_absolute`
                            ELSE NULL
                        END))) * 100)) > 28)
            THEN
                'Failed'
            ELSE 'Passed'
        END) AS `other_absolute_outcome`,
        ROUND(AVG((CASE
                    WHEN (`per`.`other_percent` <> 0) THEN `per`.`other_percent`
                    ELSE NULL
                END)),
                0) AS `other_percent_mean`,
        ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`other_percent` <> 0) THEN `per`.`other_percent`
                    ELSE NULL
                END)),
                2) AS `other_percent_sd`,
        (2 * ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`other_percent` <> 0) THEN `per`.`other_percent`
                    ELSE NULL
                END)),
                2)) AS `double_other_percent_sd`,
        (ROUND(AVG((CASE
                    WHEN (`per`.`other_percent` <> 0) THEN `per`.`other_percent`
                    ELSE NULL
                END)),
                0) + (2 * ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`other_percent` <> 0) THEN `per`.`other_percent`
                    ELSE NULL
                END)),
                2))) AS `other_percent_upper_limit`,
        (ROUND(AVG((CASE
                    WHEN (`per`.`other_percent` <> 0) THEN `per`.`other_percent`
                    ELSE NULL
                END)),
                2) - (2 * ROUND(STDDEV_SAMP((CASE
                    WHEN (`per`.`other_percent` <> 0) THEN `per`.`other_percent`
                    ELSE NULL
                END)),
                2))) AS `other_percent_lower_limit`,
        CEILING(((STDDEV_SAMP((CASE
                    WHEN (`per`.`other_percent` <> 0) THEN `per`.`other_percent`
                    ELSE NULL
                END)) / AVG((CASE
                    WHEN (`per`.`other_percent` <> 0) THEN `per`.`other_percent`
                    ELSE NULL
                END))) * 100)) AS `other_percent_cv`,
        (CASE
            WHEN
                (CEILING(((STDDEV_SAMP((CASE
                            WHEN (`per`.`other_percent` <> 0) THEN `per`.`other_percent`
                            ELSE NULL
                        END)) / AVG((CASE
                            WHEN (`per`.`other_percent` <> 0) THEN `per`.`other_percent`
                            ELSE NULL
                        END))) * 100)) > 28)
            THEN
                'Failed'
            ELSE 'Passed'
        END) AS `other_percent_outcome`,
        `pds`.`doc_path` AS `doc_path`
    FROM
        (`pt_data_submission` `pds`
        JOIN `pt_equipment_results` `per` ON ((`pds`.`id` = `per`.`equip_result_id`)))
        WHERE round_id = $round_id
        $equip_where
        $participant_where
        AND sample_id = $sample_id
    GROUP BY `per`.`sample_id` , `pds`.`equipment_id`";

    $query = $this->db->query($sql);

    return $query->row();

    }



    // public function getTestersResults($round_id, $equipment_id = null, $sample_id){

    //     if($equipment_id){
    //         $equip_where = 'AND equipment_id = '. $equipment_id;
    //     }else{
    //         $equip_where = null;
    //     }

    //     $sql = "SELECT 
    //     `pt_testers_result`.`pt_sample_id` AS `pt_sample_id`,
    //     `pt_testers_result`.`equipment_id` AS `equipment_id`,
    //     `pt_testers_result`.`pt_round_id` AS `pt_round_id`,
    //     ROUND(AVG(`pt_testers_result`.`result`), 0) AS `mean`,
    //     ROUND(STDDEV_SAMP(`pt_testers_result`.`result`), 2) AS `sd`,
    //     (2 * ROUND(STDDEV_SAMP(`pt_testers_result`.`result`), 2)) AS `doublesd`,
    //     (ROUND(AVG(`pt_testers_result`.`result`), 0) + (2 * ROUND(STDDEV_SAMP(`pt_testers_result`.`result`), 2))) AS `upper_limit`,
    //     (ROUND(AVG(`pt_testers_result`.`result`), 2) - (2 * ROUND(STDDEV_SAMP(`pt_testers_result`.`result`), 2))) AS `lower_limit`,
    //     CEILING(((STDDEV_SAMP(`pt_testers_result`.`result`) / AVG(`pt_testers_result`.`result`)) * 100)) AS `cv`,
    //     (CASE
    //         WHEN (CEILING(((STDDEV_SAMP(`pt_testers_result`.`result`) / AVG(`pt_testers_result`.`result`)) * 100)) > 28) THEN 'Failed'
    //         ELSE 'Passed'
    //     END) AS `outcome`
    // FROM
    //     `pt_testers_result`
    // WHERE pt_round_id = $round_id
    // $equip_where
    // AND pt_sample_id = $sample_id
    // GROUP BY `pt_testers_result`.`pt_sample_id` , `pt_testers_result`.`equipment_id`";

    // $query = $this->db->query($sql);

    // return $query->row();

    // }


}