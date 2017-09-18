<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends MY_Controller {
	public function __construct(){
		parent::__construct();

		$this->load->model('Home_m');
	}

	public function index()
	{
		$this->template->setPageTitle('External Quality Assurance Programme')->setPartial('home_v2')->frontEndTemplate2();
	}

    public function FAQ()
    {

        $faq_view = '';

        $this->db->where('status', 1);
        $faqs = $this->db->get('faqs')->result();
        $counter = 1;
        $color_counter = 0;
        foreach($faqs as $faq){
            $faq_view .= '<div class="col-sm-12 col-md-6">
                    <div class="post-row mb30">
                        <div class="post-header">
                            <div class="post-feature">';

            if ($color_counter == 0) {
                $faq_view .= '<img src="'.base_url("assets/frontend/images/files/FAQS/background_blue.png").'" alt="'.$faq->title.'" />';
                $faq_view .= '<div style="left: 0;position:absolute;text-align:center;top: 45%;width: 100%;font-size: 36px;color: #FFFFFF;font-weight: bold;">';
                $color_counter++;
            }else{
                $faq_view .= '<img src="'.base_url("assets/frontend/images/files/FAQS/background_white.png").'" alt="'.$faq->title.'" />';
                $faq_view .= '<div style="left: 0;position:absolute;text-align:center;top: 45%;width: 100%;font-size: 36px;color: #029ce7;font-weight: bold;">';
                $color_counter = 0;
            }

            $faq_view .= $faq->title;
            
            $faq_view .=  '</div>
                            </div>
                                <div class="post-sticker"><small>FAQ</small>
                                    <p class="month">';


            $faq_view .= $counter;

            $faq_view .= '</p>
                            </div>
                        </div>
                        <div class="post-body">
                            <div class="post-caption">
                                <h2 class="post-heading"><a href="#">';

            $faq_view .= $faq->question;

            $faq_view .= '</a></h2>
                            </div>
                            <p class="post-text">';

            $faq_view .= $faq->answer;

            $faq_view .= '</p>
                        </div>
                        <div class="post-footer">
                        </div>
                    </div>
                </div>';



            $counter ++;
        }

        $data = [
            'faq_view'          =>  $faq_view
        ];


        $this->template->setPageTitle('External Quality Assurance Programme')->setPartial('faq', $data)->frontEndTemplate2();
    }


	function sendMessage($round_uuid,$particapant_uuid){
        if($this->input->post()){
            
            $fname = $this->input->post('fname');
            $email = $this->input->post('email');
            $subject = $this->input->post('subject');
            $message = $this->input->post('message');

            $email_to = "nhrlCD4eqa@nphls.or.ke";

            $data = [
                'names'  	=>  $fname,
                'email'  	=>  $email,
                'subject'  	=>  $subject,
                'message'  	=>  $message,
                'to'		=>  $email_to,
                'cc'        =>  'ruth3mumo@gmail.com'
            ];

            $body = $this->load->view('Template/email/message_v', $data, TRUE);
            $this->load->library('Mailer');
            $sent = $this->mailer->sendMail($email_to, $subject, $body);
            if ($sent == FALSE) {
                log_message('error', "The system could not send an email to {$email_to}, from: $fname at " . date('Y-m-d H:i:s'));
            }
                      
            redirect('Home/', 'refresh');
        }
    }


    function downloadSOP(){

        $pdf_view = '/uploads/docs/NHRL-PT-SOP-19042017.pdf';

        // $data['document_title'] = "STANDARD OPERATING PROCEDURE - " . date('d-m-Y');

        // $this->load->library('pdf');

        // $pdf = $this->pdf->load($pdf_view);
        // $pdf->AddPage("L");

        // $pdf->output('STANDARD OPERATING PROCEDURE ' . date('d-m-Y') . '.pdf', 'D');

        $this->load->helper('download');
        $data = file_get_contents(APPPATH . 'uploads/docs/NHRL-PT-SOP-19042017.pdf'); // Read the file's contents
        $name = 'NHRL-PT-SOP-19042017.pdf';
        force_download($name, $data);
    }



}

/* End of file Home.php */
/* Location: ./application/modules/Home/controllers/Home.php */
