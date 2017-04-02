<?php

class Equipments extends DashboardController{
    function __construct(){
        parent::__construct();
        $this->load->library('table');
        $this->load->config('table');

        //$this->load->model('Equipments/M_Equipments');
    }

    function index(){
    }

    function equipmentlist($eFacilities = NULL){
        $data = [];
        $title = "Equipments";
        $equipment_count = $this->db->count_all('equipment');

        if($eFacilities == NULL){

            $data = [
                'table_view'    =>  $this->createEquipmentTable(),
                'new_id_entry'  => $equipment_count + 1,
                'equipmentview' => 1
            ];

        }else{
            $data = [
                'table_view'   =>  $this->facilities($eFacilities),
                'new_id_entry' => '',
                'equipmentview' => 0
            ];
        }

        $this->assets
                ->addJs("dashboard/js/libs/jquery.dataTables.min.js")
                ->addJs("dashboard/js/libs/dataTables.bootstrap4.min.js")
                ->addJs('dashboard/js/libs/jquery.validate.js')
                ->addJs('dashboard/js/libs/select2.min.js');
        $this->assets->setJavascript('Equipments/equipments_js');
        $this->template
                ->setModal("Equipments/new_equipment_v", "Add New Equipment")
                ->setPageTitle($title)
                ->setPartial('Equipments/equipment_list_v', $data)
                ->adminTemplate();
    }

    function create(){
        if($this->input->post()){
            $equipmentname = $this->input->post('equipmentname');
            $kitnames = $this->input->post('kitnames');

            $insertdata = [
                'equipment_name'    =>  $equipmentname,
                'kit_name'    =>  $kitnames
            ];

            //$this->db->insert('equipment', $insertdata);

            if($this->db->insert('equipment', $insertdata)){
                $this->session->set_flashdata('success', "Successfully created new Equipment");

                $equipment_id = $this->db->insert_id();

                $this->db->where('id', $equipment_id);
                $equipment = $this->db->get('equipment')->row();

                $message = "Equipment Name : <strong>" . $equipment->equipment_name . "</strong> is registered successfully";
            }else{
                $this->session->set_flashdata('error', "There was a problem creating a new equipment. Please try again");
            }

            redirect('Equipments/equipmentlist', 'refresh');
        }
    }

    function createEquipmentTable(){



        $template = $this->config->item('default');

        $heading = [
            "No.",
            "Equipment Name",
            "Kit Names",
            "Status",
            "No. of Facilities Equipped",
            "Actions"
        ];
        $tabledata = [];

        //$equipments = $this->M_Facilities->getequipments();
        $equipments = $this->db->get('equipments_v')->result();


        if($equipments){
            $counter = 0;
            foreach($equipments as $equipment){
                $counter ++;
                $id = $equipment->uuid;
                if($equipment->equipment_status == 1){
                    $status = "<label class = 'tag tag-success tag-sm'>Active</label>";
                    $change_state = '<a href = ' . base_url("Equipments/changeState/deactivate/$id") . ' class = "btn btn-warning btn-sm"><i class = "icon-refresh"></i>&nbsp;Deactivate </a>';
                    
                }else{
                    $status = "<label class = 'tag tag-danger tag-sm'>Inactive</label>";
                    $change_state = '<a href = ' . base_url("Equipments/changeState/activate/$id") . ' class = "btn btn-warning btn-sm"><i class = "icon-refresh"></i>&nbsp;Activate </a>';
                }

                $change_state .= ' <a href = ' . base_url("Equipments/equipmentEdit/$id") . ' class = "btn btn-primary btn-sm"><i class = "icon-note"></i>&nbsp;Edit</a>';
                
                $tabledata[] = [
                    $counter,
                    $equipment->equipment_name,
                    $equipment->kit_name,
                    $status,
                    '<a class="data-toggle="tooltip" data-placement="top" title="Facilities with this equipment"" href = ' . base_url("Equipments/equipmentlist/$id") . ' >'. $equipment->facilities .'</a>',
                    $change_state
                ];
            }
        }
        $this->table->set_heading($heading);
        $this->table->set_template($template);

        return $this->table->generate($tabledata);
    }

    function changeState($type, $id){
        switch($type){
            case 'activate':
                $this->db->set('equipment_status', 1);

            break;

            case 'deactivate':
                $this->db->set('equipment_status', 0);
                
            break;
        }

        $this->db->where('uuid', $id);
        //$this->db->update('equipment');

        if($this->db->update('equipment')){
            $this->session->set_flashdata('success', "Successfully updated the equipment details");
        }else{
            $this->session->set_flashdata('error', "There was a problem updating the equipment details. Please try again");
        }

        redirect('Equipments/equipmentlist', 'refresh');

    }

    function equipmentEdit($id){
        $this->db->where('uuid', $id);
        $equipment = $this->db->get('equipment')->row();

        $data = [
            'equipment_id'  =>  $equipment->id,
            'equipment_uuid'  =>  $equipment->uuid,
            'equipment_name'  =>  $equipment->equipment_name
        ];
        $this->assets
                ->addJs('dashboard/js/libs/jquery.validate.js');
        $this->assets->setJavascript('Equipments/equipment_update_js');
        $this->template
                ->setPartial('Equipments/equipment_edit_v', $data)
                ->setPageTitle('Equipment Edit')
                ->adminTemplate();
    }

    function editEquipment(){
        if($this->input->post()){
            $equipmentuuid = $this->input->post('equipmentuuid');
            $equipmentname = $this->input->post('equipmentname');

            $this->db->set('equipment_name', $equipmentname);
            $this->db->where('uuid', $equipmentuuid);

            if($this->db->update('equipment')){
            $this->session->set_flashdata('success', "Successfully updated the equipment to " . $equipmentname);
            $message = "Equipment Name : <strong>" . $equipmentname . "</strong> has been edited successfully";
        }else{
            $this->session->set_flashdata('error', "There was a problem updating the equipment details. Please try again");
        }

            redirect('Equipments/equipmentlist', 'refresh');
        }
    }

    function facilities($equipmentId){

        $template = $this->config->item('default');

        $facilityheading = [
            "No.",
            "MFL. Code",
            "Facility Name",
            "County",
            "Sub County",
            "Actions"
        ];
        $facilitytabledata = [];

        // $this->db->get('facility');
        // $this->db->join('facility_equipment_mapping', 'facility_equipment_mapping.facility_code = facility.facility_code');
        // $this->db->join('equipment', 'facility_equipment_mapping.equipment_id = equipment.id');

        $this->db->where('e_uuid', $equipmentId);
        $efacilities = $this->db->get('equipment_facilities_v')->result();

        if($efacilities){
            $counter = 0;
            foreach($efacilities as $facility){
                $counter ++;
 
                $facilitytabledata[] = [
                    $counter,
                    $facility->facility_code,
                    $facility->facility_name,
                    $facility->county_name,
                    $facility->sub_county_name,
                    ""
                ];
            }
        }

        $this->table->set_heading($facilityheading);
        $this->table->set_template($template);

        return $this->table->generate($facilitytabledata);
    }


}