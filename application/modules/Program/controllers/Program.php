<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Program extends MY_Controller {
    public function __construct()
    {
        parent::__construct();

        $this->load->model('Program_m');
    }

    public function index(){
        
            $data = [
                $title = "Program Graphs"
            ];

            $this->assets->addJs('js/Chart.min.js');
        $this->assets->setJavascript('Program/program_js');
        $this->template
                ->setPageTitle($title)
                ->setPartial('Program/program_view', $data)
                ->adminTemplate();
    }

}

/* End of file Program.php */
/* Location: ./application/modules/QAReviewer/controllers/PTRound.php */