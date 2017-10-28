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

    public function getRegistrationsNumber($equipment_id){
        
        $this->db->select("count(participant_id) AS register_count");
        $this->db->from("participant_equipment");
        $this->db->where("equipment_id",$equipment_id);
        $query = $this->db->get();
        
        return $query->row();

    }

    public function getReadyParticipants($round_id, $equipment_id){

        $this->db->select("participant_id");
        $this->db->from("pt_participant_review_v");
        $this->db->where("round_id",$round_id);
        $this->db->where("equipment_id",$equipment_id);
        $this->db->group_by("participant_id");

        $query = $this->db->get();
        
        return $query->result();
    }



    public function getstdSample(){
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
        WHERE equipment_id = 5 AND sample_id = 1
    GROUP BY `per`.`sample_id` , `pds`.`equipment_id`";

    $query = $this->db->query($sql);

    return $query->result();

    }


}