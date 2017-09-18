<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Import extends MY_Controller {
	public function __construct()
	{
		parent::__construct();
		$this->load->library('Excel');
	}
	function importCounties(){
		$file_path = './uploads/data/FCSCMapping.xlsx';
		$data = $this->excel->readExcel($file_path);
		if (count($data) > 0) {
			foreach ($data as $item => $itemData) {
				$headers = $itemData[0];
				$dbColumns = $this->getDbColumnNames($item);

				$cleaned_headers = array_replace($headers, $dbColumns);
				
				$insertData = [];
				for ($i=1; $i < count($itemData); $i++) { 
					if ($itemData[$i][0] != "" && is_numeric($itemData[$i][0])) {
						$rowData = array_combine($cleaned_headers, $itemData[$i]);
						array_push($insertData, $rowData);
					}
				}
				
				if (count($insertData)) {
					if ($item == "Facilities") {
						$this->db->insert_batch('facility', $insertData);
					}
				}
				
			}
		}
	}

	function getDbColumnNames($table = 'Counties'){
		$dbColumns = [];
		switch ($table) {
			case 'Counties':
				$dbColumns = [
					0	=>	'id',
					1	=>	'county_name',
					2	=>	'county_dhis_code',
					3	=>	'county_mfl_code',
					4	=>	'county_letter',
					5	=>	'county_coordinates'
				];
				break;
			case 'Sub Counties':
				$dbColumns = [
					0	=>	'id',
					1	=>	'sub_county_name',
					2	=>	'sub_county_dhis_code',
					3	=>	'sub_county_mfl_code',
					4	=>	'county_id',
					5	=>	'sub_county_coordinates'
				];
				break;
			case 'Partners':
				$dbColumns = [
					0	=>	'id',
					1	=>	'partner_name'
				];
				break;
			case 'Facilities':
				$dbColumns = [
					'id',
					'facility_code',
					'sub_county_id',
					'facility_name',
					'partner_id',
					'facility_type',
					'facility_dhis_code',
					'latitude',
					'longitude',
					'telephone',
					'alt_telephone',
					'email',
					'postal_address',
					'contact_person',
					'contact_telephone',
					'contact_alt_telephone',
					'physical_address',
					'contact_email',
					'sub_county_email',
					'county_email',
					'partner_email',
					'ART',
					'PMTC',
					'G4S_branch_name',
					'G4S_location',
					'G4S_phone_1',
					'G4S_phone_2',
					'G4S_phone_3',
					'G4S_fax'
				];
				break;
			default:
				# code...
				break;
		}

		return $dbColumns;
	}

	function importEquipmentMapping(){
		$file_path = './uploads/data/EquipmentMapping.xlsx';
		$data = $this->excel->readExcel($file_path);

		$insertData = [];
		foreach ($data as $equipment_id => $itemData) {
			$headers = $itemData[0];
			for ($i=1; $i < count($itemData); $i++) { 
				$insertData[] = [
					'equipment_id'	=>	$equipment_id,
					'facility_code'	=>	$itemData[$i][0]
				];
			}
		}

		// $this->db->insert_batch('facility_equipment_mapping', $insertData);
	}


	function importR17DataSubmissions(){
		$file_path = './uploads/data/R17_Results.xlsx';
		$participant = 0;

		$data = $this->excel->readExcel($file_path);
		if (count($data) > 0) {
			foreach ($data as $item => $itemData) {

				// echo "<pre>"; print_r($itemData);echo "</pre>";die();
				$headers = $itemData[0];
				
				$insertData = $datas = [];
				
				
				for ($i=4; $i < 72; $i++) {

					if($itemData[$i][7] != '' && $itemData[$i][7] != null && $itemData[$i][7] != 'not indicated'){
						
						$equip = $this->db->get_where('equipment', ['equipment_name'=>$itemData[$i][7]])->row();
						if($equip){
							$equip_id = $equip->id;
						}else{
							$equip_id = 0;
						}


						// echo "<pre>"; print_r($itemData[$i]);echo "</pre>";die();
						
						$facility = $this->db->get_where('facility_v', ['facility_name'=>$itemData[$i][0]])->row();


						if($facility){
							$facility_id = $facility->facility_id;
						}else{
							$facility_id = 0;
						}
						
						$participant++;

						$insertdata4 = [
				                'participant_id'    =>  $participant,
				                'participant_lname'    =>  $itemData[$i][23] ? $itemData[$i][23] : 'No name',
				                'participant_phonenumber'    =>  $itemData[$i][24] ? $itemData[$i][24] : 0,
				                'participant_facility'    =>  $facility_id ? $facility_id : 0,
				                'participant_email'    =>  $itemData[$i][25] ? $itemData[$i][25] : 0,
				                'participant_sex'    =>  '',
				                'participant_age'    =>  0,
				                'participant_education'    =>  '',
				                'participant_experience'    =>  '',
				                'user_type'    =>  'participant',
				                'participant_password'    =>  '$2y$10$kHEgvCOIRVePKcwc00n0puvWsrCXN6ab2HIxwvKsNsCbvt8UK49au',
				                'avatar'    =>  '',
				                'approved'    =>  1,
				                'status'    =>  1,
				                'date_registered'    =>  '',
				                'confirm_token'    =>  null
			            	];

			            	$this->db->insert('participants', $insertdata4);
						
						$insertdata = [
								'round_id'    =>  1,
				                'participant_id'    =>  $participant,
				                'equipment_id'    =>  $equip_id,
				                'status'    =>  1,
				                'verdict'    =>  2
			            ];

			            // $this->db->insert('pt_data_submission', $insertdata);
						$sample_counter = 1;
						$batch_counter = 1;

			            if($this->db->insert('pt_data_submission', $insertdata)){
                        $submission_id = $this->db->insert_id();

                        $insertdata1 = [
								'equip_result_id'    =>  $submission_id,
				                'sample_id'    =>  $sample_counter,
				                'cd3_absolute'    =>  $itemData[$i][10] ? $itemData[$i][10] : 0,
				                'cd3_percent'    =>  $itemData[$i][11] ? $itemData[$i][11] : 0,
				                'cd4_absolute'    =>  $itemData[$i][12] ? $itemData[$i][12] : 0,
				                'cd4_percent'    =>  $itemData[$i][13] ? $itemData[$i][13] : 0,
				                'other_absolute'    =>  0,
				                'other_percent'    =>  0
			            	];

			            	$this->db->insert('pt_equipment_results', $insertdata1);
			            	$sample_counter ++;
			            	

			            	$insertdata2 = [
			            		'equip_result_id'    =>  $submission_id,
				                'sample_id'    =>  $sample_counter,
				                'cd3_absolute'    =>  $itemData[$i][14] ? $itemData[$i][14] : 0,
				                'cd3_percent'    =>  $itemData[$i][15] ? $itemData[$i][15] : 0,
				                'cd4_absolute'    =>  $itemData[$i][16] ? $itemData[$i][16] : 0,
				                'cd4_percent'    =>  $itemData[$i][17] ? $itemData[$i][17] : 0,
				                'other_absolute'    =>  0,
				                'other_percent'    =>  0
			            	];

			            	$this->db->insert('pt_equipment_results', $insertdata2);
			            	$sample_counter ++;
			            	

			            	$insertdata3 = [
			            		'equip_result_id'    =>  $submission_id,
				                'sample_id'    =>  $sample_counter,
				                'cd3_absolute'    =>  $itemData[$i][18] ? $itemData[$i][18] : 0,
				                'cd3_percent'    =>  $itemData[$i][19] ? $itemData[$i][19] : 0,
				                'cd4_absolute'    =>  $itemData[$i][20] ? $itemData[$i][20] : 0,
				                'cd4_percent'    =>  $itemData[$i][21] ? $itemData[$i][21] : 0,
				                'other_absolute'    =>  0,
				                'other_percent'    =>  0
			            	];

			            	$this->db->insert('pt_equipment_results', $insertdata3);


			            	$equipment = $this->db->get_where('equipment', ['equipment_name'=>$itemData[$i][7]])->row(); 


			            	$insertdata4 = [
								'participant_id'    =>  $submission_id,
				                'equipment_id'    =>  $equipment->id
			            	];

			            	$this->db->insert('participant_equipment', $insertdata4);


			            	$insertdata5 = [
			            		'batch_name'    =>  "Batch_".$submission_id,
				                'description'    => "Testing for batch for Participant ID ".$submission_id,
				                'pt_round_id'    =>  1
			            	];

			            	$this->db->insert('pt_batches', $insertdata5);



			            	$round_uuid = $this->db->get_where('pt_round', ['id'=>1])->row()->uuid;
			            	$participant_det = $this->db->get_where('participants', ['participant_id'=>$submission_id])->row();

			            	$insertdata6 = [
			            		'pt_round_no'    =>  $round_uuid,
				                'participant_id'    => $participant_det->uuid,
				                'participant_facility'    =>  $participant_det->participant_facility,
				                'status'    =>  1,
				                'verdict'    =>  1,
				                'lab_result'    =>  1
			            	];

			            	$this->db->insert('participant_readiness', $insertdata6);

							$date1 = date("Y-m-d",strtotime("2017-08-08"));
							$date2 = date("Y-m-d",strtotime("2017-08-09"));
							$date3 = date("Y-m-d",strtotime("2017-08-10"));


			            	$insertdata7 = [
			            		'pt_batch_id'    =>  $submission_id,
				                'pt_readiness_id'    => $submission_id,
				                'panel_preparation_date'    =>  $date1,
				                'panel_preparation_notes'    => "They are finished, they are done",
				                'courier_collection_date'    =>  $date2,
				                'courier_company'    => "G4S Courier",
				                'courier_official'    =>  "Mr. Mareka",
				                'courier_dispatch_notes'    => "They are on their way",
				                'participant_received_date'    =>  $date3,
				                'panel_condition_comment'    => "OK we guess",
				                'panel_received_entered'    =>  $submission_id,
				                'receipt'    => 1,
				                'panel_condition'    =>  1
			            	];

			            	$this->db->insert('pt_panel_tracking', $insertdata7);


			            	$insertdata7 = [
			            		'batch_id'    =>  $submission_id,
				                'tube_id'    => $batch_counter,
				                'sample_id'    =>  $batch_counter
			            	];

			            	$this->db->insert('pt_batch_tube', $insertdata7);
			            	$batch_counter++;


			            	$insertdata7 = [
			            		'batch_id'    =>  $submission_id,
				                'tube_id'    => $batch_counter,
				                'sample_id'    =>  $batch_counter
			            	];

			            	$this->db->insert('pt_batch_tube', $insertdata7);
			            	$batch_counter++;


			            	$insertdata7 = [
			            		'batch_id'    =>  $submission_id,
				                'tube_id'    => $batch_counter,
				                'sample_id'    =>  $batch_counter
			            	];

			            	$this->db->insert('pt_batch_tube', $insertdata7);

                   		}
					}

					
				}

				//A QA / Supervisor for testing purposes

				$insertdata4 = [
				                'participant_id'    =>  '12881_001',
				                'participant_fname'    =>  'Willy',
				                'participant_lname'    =>  'Mareka',
				                'participant_phonenumber'    =>  '0714135480',
				                'participant_facility'    =>  3277,
				                'participant_email'    =>  'marekawilly@gmail.com',
				                'participant_sex'    =>  'Male',
				                'participant_age'    =>  23,
				                'participant_education'    =>  'Higher Diploma',
				                'participant_experience'    =>  'Very Little',
				                'user_type'    =>  'qareviewer',
				                'participant_password'    =>  '$2y$10$kHEgvCOIRVePKcwc00n0puvWsrCXN6ab2HIxwvKsNsCbvt8UK49au',
				                'avatar'    =>  '',
				                'approved'    =>  1,
				                'status'    =>  1,
				                'date_registered'    =>  '',
				                'confirm_token'    =>  null
			            	];

			            	$this->db->insert('participants', $insertdata4);

				echo "<pre>"; print_r("Check your DB to view TABLE -> pt_batch_tube, pt_panel_tracking, participant_readiness, pt_batches, pt_data_submission, participants, participant_equipment and pt_equipment_results");echo "</pre>";die();	
			}
		}
	}



}

/* End of file Import.php */
/* Location: ./application/modules/Data/controllers/Import.php */