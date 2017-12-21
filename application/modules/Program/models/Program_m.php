<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Program_m extends CI_Model {

	function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }


    public function getEquipmentBreakdown($round_uuid, $county_id = null, $facility_id = null){
        $this->db->select("count(prr.response) AS equipments");
        $this->db->from("pt_readiness_responses_v prr");
        $this->db->join("facility_v fv", "fv.facility_id = prr.participant_facility");
        $this->db->where("prr.pt_round_uuid",$round_uuid);

        if($county_id){
            $this->db->where("fv.county_id", $county_id);
        }

        if($facility_id){
            $this->db->where("fv.facility_id", $facility_id);
        }
        
        $this->db->where("prr.question_id", 2);
        $this->db->where("prr.response", 1);
        $query = $this->db->get();
        
        return $query->row();
    }

    public function getReagentStock($round_uuid, $county_id = null, $facility_id = null){
        $this->db->select("count(prr.response) AS reagents");
        $this->db->from("pt_readiness_responses_v prr");
        $this->db->join("facility_v fv", "fv.facility_id = prr.participant_facility");
        $this->db->where("prr.pt_round_uuid",$round_uuid);

        if($county_id){
            $this->db->where("fv.county_id", $county_id);
        }

        if($facility_id){
            $this->db->where("fv.facility_id", $facility_id);
        }
        
        $this->db->where("prr.question_id", 3);
        $this->db->where("prr.response", 1);
        $query = $this->db->get();
        
        return $query->row();
    }

    public function getUnavailableAnalyst($round_uuid, $county_id = null, $facility_id = null){
        $this->db->select("count(prr.response) AS analysts");
        $this->db->from("pt_readiness_responses_v prr");
        $this->db->join("facility_v fv", "fv.facility_id = prr.participant_facility");
        $this->db->where("prr.pt_round_uuid",$round_uuid);

        if($county_id){
            $this->db->where("fv.county_id", $county_id);
        }

        if($facility_id){
            $this->db->where("fv.facility_id", $facility_id);
        }
        
        $this->db->where("prr.question_id", 1);
        $this->db->where("prr.response", 1);
        $query = $this->db->get();
        
        return $query->row();
    }

    public function getPendingCapa($round_uuid, $county_id = null, $facility_id = null){
        $this->db->select("count(prr.response) AS capas");
        $this->db->from("pt_readiness_responses_v prr");
        $this->db->join("facility_v fv", "fv.facility_id = prr.participant_facility");
        $this->db->where("prr.pt_round_uuid",$round_uuid);

        if($county_id){
            $this->db->where("fv.county_id", $county_id);
        }

        if($facility_id){
            $this->db->where("fv.facility_id", $facility_id);
        }
        
        $this->db->where("prr.question_id", 7);
        $this->db->where("prr.response", 1);
        $query = $this->db->get();
        
        return $query->row();
    }

    public function getLatestRounds(){
        $this->db->select("*");
        $this->db->from("pt_round_v");
        $this->db->where('type', 'previous');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(6);

        $query = $this->db->get();
        
        return $query->result();
    }


    public function TotalFacilities($round_uuid, $county_id = null, $facility_id = null){
        $this->db->select('COUNT(DISTINCT(prv.facility_code)) AS facilities');
        $this->db->from('participant_readiness_v prv');
        $this->db->join("facility_v fv", "fv.facility_id = prv.facility_id", "left");
        $this->db->join("pt_ready_participants prp", "prp.p_id = prv.p_id", "left");

        if($county_id){
            $this->db->where('prv.county_id', $county_id);
        }

        if($facility_id){
            $this->db->where('prv.facility_id', $facility_id);
        }
        
        $this->db->where('prp.pt_round_uuid', $round_uuid);
        $this->db->where('prv.status', 1);
        $this->db->where('prv.user_type', 'participant');
        
        $query = $this->db->get();

        return $query->row();
    }

    public function getCounties(){

        $this->db->select("cv.id AS county_id,cv.county_name AS county_name,fv.facility_id AS facility_id,fv.facility_name AS facility_name");
        $this->db->from("county_v cv");
        $this->db->join("facility_v fv", "fv.county_id = cv.id");
        $this->db->join("pt_participant_review_v prv", "prv.facility_id = fv.facility_id");
        $this->db->where("fv.cd4", 1);
        // $this->db->limit(6);
        $this->db->group_by("cv.county_name");
        $this->db->order_by("cv.county_name");

        $query = $this->db->get();
        
        return $query->result();
    }


    public function getFacilities($county_id){

        $this->db->select("*");
        $this->db->from("facility_v fv");
        $this->db->join("participant_readiness pr", "fv.facility_id = pr.participant_facility");
        $this->db->where("fv.county_id", $county_id);
        $this->db->group_by("fv.facility_name");
        $this->db->order_by("fv.facility_name");

        $query = $this->db->get();
        
        return $query->result();
    }


    public function ParticipatingParticipants($round_uuid, $county_id = null, $facility_id = null){
        $facility = $county = '';

        if($county_id){
        	$county = " AND county_id = ".$county_id." " ;
        }

        if($facility_id){
            $facility = " AND facility_id = ".$facility_id." " ;
        }

        $sql = "SELECT COUNT(participant_id) AS participants 
        FROM pt_ready_participants 
        WHERE pt_round_uuid = '".$round_uuid."' 
        $county
        $facility
        AND verdict = 1; ";

        $query = $this->db->query($sql);

        // $query = $this->db->get();
        
        return $query->row();
    }

    public function getRoundVerdict($round_uuid, $county_id = null, $facility_id = null){
        $this->db->select('COUNT(pr.participant_id) AS participants');
        $this->db->from('participant_readiness pr');
        $this->db->join('facility_v fv', 'pr.participant_facility = fv.facility_id');
        $this->db->where('pr.pt_round_no', $round_uuid);

        if($county_id){
            $this->db->where('fv.county_id', $county_id);
        }

        if($facility_id){
            $this->db->where('fv.facility_id', $facility_id);
        }
        
        $this->db->where('pr.verdict', 0);
        $query = $this->db->get();

        return $query->row();
    }

    public function getUnableParticipants($round_uuid, $county_id = null, $facility_id = null){
        $county = $facility = '';
        
        if($county_id){
            $county = " AND fv.county_id = ".$county_id. " ";
        }

        if($facility_id){
            $facility = " AND fv.facility_id = ".$facility_id. " ";
        }

        $sql = "SELECT COUNT(prv.p_id) AS participants
                FROM participant_readiness_v prv
                JOIN facility_v fv ON prv.facility_id = fv.facility_id
                WHERE NOT EXISTS 
                    (SELECT * 
                     FROM participant_readiness pr
                     WHERE prv.uuid = pr.participant_id 
                     AND pr.pt_round_no = '".$round_uuid."')
                $county
                $facility
                AND prv.user_type = 'participant'";

        $query = $this->db->query($sql);

        return $query->row();
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


    public function getReadyParticipants($round_id, $county_id = null, $facility_id = null){

        $this->db->select("ppr.participant_id");
        $this->db->from("pt_participant_review_v ppr");
        $this->db->where("ppr.round_id",$round_id);

        if($county_id){
            $this->db->where("ppr.county_id", $county_id);
        }

        if($facility_id){   
            $this->db->join('participant_readiness pr', 'pr.participant_facility = ppr.facility_id');
            $this->db->where("pr.lab_result", 1);
            $this->db->where("ppr.facility_id", $facility_id);
        }
        
        $this->db->group_by("ppr.participant_id");

        $query = $this->db->get();
        
        return $query->result();
    }


    public function getRoundResults($round_id, $equipment_id = null, $sample_id){

        if($equipment_id){
            $equip_where = 'AND equipment_id = '. $equipment_id;
        }else{
            $equip_where = null;
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
        AND sample_id = $sample_id
    GROUP BY `per`.`sample_id` , `pds`.`equipment_id`";

    $query = $this->db->query($sql);

    return $query->row();

    }


}