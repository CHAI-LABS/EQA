<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Template extends MX_Controller {
	protected $asset_url;

	protected $pageTitle;
	protected $pageDescription;
	protected $contentView;
	protected $contentViewData;
	protected $metaData;
	protected $modalView;
	protected $modalData;
	protected $modalTitle;

	function __construct(){
		$this->asset_url = $this->config->item('assets_url');
		$this->load->library('Assets', $this->config);
	}

	function getCurrentSOP(){
		$this->db->where('current', 1);
		$sop = $this->db->get('sops',1)->row();

		if($sop){
			$sop_path = $sop->sop_path;
		}else{
			$sop_path = "Home";
		}

		// echo "<pre>";print_r($sop->sop_path);echo "</pre>";die();
		return $sop_path;
	}

	function adminTemplate(){
		$data['page_css'] = $this->assets->css;
		$data['page_js'] = $this->assets->js;

		// $this->load->model('Auth/auth_m');
		$this->load->module('Auth');
		$user_details = $this->auth_m->findUserByIdentifier('uuid', $this->session->userdata('uuid'));

		if($user_details){
			$data['javascript_file'] = $this->assets->javascript_file;
			$data['javascript_data'] = $this->assets->javascript_data;
			$data['user_details'] = $user_details;
			 // echo "</pre>";print_r($this->viewNewMessages($this->session->userdata('uuid')));echo "</pre>";die();
			// $data['new_messages'] = $this->viewNewMessages($this->session->userdata('uuid'));
			$data['menu'] = $this->createSideBar();
			$data['pagetitle'] = $this->pageTitle;
			$data['pagedescription'] = $this->pageDescription;
			$data['modalView'] = $this->modalView;
			$data['modalData'] = $this->modalData;
			$data['modalTitle'] = $this->modalTitle;
			$data['partial'] = $this->contentView;
			$data['partialData'] = $this->contentViewData;
		}else{
			$this->load->module('Auth');
			$this->auth->logout();
		}

		$this->load->view('Template/backend_template_v', $data);
	}

	function readinessTemplate(){
		$data['page_css'] = $this->assets->css;
		$data['page_js'] = $this->assets->js;

		$this->load->model('Auth/auth_m');
		$user_details = $this->auth_m->findUserByIdentifier('uuid', $this->session->userdata('uuid'));
		if($user_details){
			$data['javascript_file'] = $this->assets->javascript_file;
			$data['javascript_data'] = $this->assets->javascript_data;
			$data['user_details'] = $user_details;
			// $data['menu'] = $this->createSideBar();
			$data['pagetitle'] = $this->pageTitle;
			$data['pagedescription'] = $this->pageDescription;
			$data['modalView'] = $this->modalView;
			$data['modalData'] = $this->modalData;
			$data['modalTitle'] = $this->modalTitle;
			$data['partial'] = $this->contentView;
			$data['partialData'] = $this->contentViewData;
		}else{
			$this->load->module('Auth');
			$this->auth->logout();
		}

		$this->load->view('Template/readiness_template_v', $data);
	}


	function authTemplate(){
		$data['page_css'] = $this->assets->css;
		$data['page_js'] = $this->assets->js;


		$data['javascript_file'] = $this->assets->javascript_file;
		$data['javascript_data'] = $this->assets->javascript_data;
		
		$data['pagetitle'] = $this->pageTitle;
		$data['pagedescription'] = $this->pageDescription;
		$data['partial'] = $this->contentView;
		$data['partialData'] = $this->contentViewData;

		$this->load->view('Template/auth_template_v', $data);
	}

	function frontEndTemplate(){
		$data['metadata'] = $this->metaData;

		$data['page_css'] = $this->assets->css;
		$data['page_js'] = $this->assets->js;

		$data['pagetitle'] = $this->pageTitle;
		$data['pagedescription'] = $this->pageDescription;

		$data['partial'] = $this->contentView;
		$data['partialData'] = $this->contentViewData;

		$data['javascript_file'] = $this->assets->javascript_file;
		$data['javascript_data'] = $this->assets->javascript_data;
		$this->load->view('Template/frontend_template_v', $data);
	}

	function frontEndTemplate2(){
		$data['metadata'] = $this->metaData;

		$data['page_css'] = $this->assets->css;
		$data['page_js'] = $this->assets->js;

		$data['pagetitle'] = $this->pageTitle;
		$data['pagedescription'] = $this->pageDescription;

		$data['partial'] = $this->contentView;
		$data['partialData'] = $this->contentViewData;

		$data['javascript_file'] = $this->assets->javascript_file;
		$data['javascript_data'] = $this->assets->javascript_data;
		$data['sop_path'] = $this->getCurrentSOP();
		$this->load->view('Template/frontend_template_v2', $data);
	}

	function createSideBar($selected = null){

		$class = $this->router->class;
		$menus = [];
		$menu_list = "";
		$menus = [
			'reports' => [
				'icon'	=>	'fa fa-book',
				'text'	=>	'Reports',
				'link'	=>	'Dashboard',
				'users'	=>	['admin']
			],
			'participants' => [
				'icon'	=>	'icon-people',
				'text'	=>	'Participants',
				'link'	=>	'Users/Participants/listing',
				'users'	=>	['admin']
			],
			'ptrounds'	=>	[
				'icon'	=>	'icon-chemistry',
				'text'	=>	'PT Rounds',
				'link'	=>	'PTRounds',
				'users'	=>	['admin']
			],
			'facilities' => [
				'icon' => 'fa fa-hospital-o',
				'text' => 'Facilities',
				'sublist' => [
					[
						'icon' => 'fa fa-table',
						'link' => 'Facilities/listing/',
						'text' => 'All Facilities'
					],
					[
						'icon' => 'fa fa-table',
						'link' => 'Facilities/listing/CD4',
						'text' => 'CD4 Sites'
					]
				],
				'users' => ['admin']
			],
			'users'		=>	[
				'icon'	=>	'icon-user-follow',
				'text'	=>	'User Accounts',
				'link'	=>	'Users/userlist',
				'users'	=>	['admin']
			],
			'equipments'		=>	[
				'icon'	=>	'icon-wrench',
				'text'	=>	'Equipment',
				'link'	=>	'Equipments/equipmentlist',
				'users'	=>	['admin']
			],
			'libraries' => [
				'icon' => 'fa fa-book',
				'text' => 'Libraries',
				'sublist' => [
					[
						'icon' => 'fa fa-bookmark',
						'link' => 'FAQ/faqlist',
						'text' => 'FAQs'
					],
					[
						'icon' => 'fa fa-bookmark',
						'link' => 'SOP/soplist',
						'text' => 'SOPs'
					]
				],
				'users' => ['admin']
			],
			'analysis'		=>	[
				'icon'	=>	'icon-chart',
				'text'	=>	'Analysis',
				'link'	=>	'Analysis/',
				'users'	=>	['admin']
			],
			'program'		=>	[
				'icon'	=>	'icon-graph',
				'text'	=>	'Dashboard: Graphs',
				'link'	=>	'Analysis/Dashboard',
				'users'	=>	['admin']
			],
			'participantrounds'	=>	[
				'icon'	=>	'icon-chemistry',
				'text'	=>	'PT Rounds',
				'link'	=>	'Participant/PTRound/',
				'users'	=>	['participant']
			],

			'calendar'	=>	[
				'icon'	=>	'icon-calendar',
				'text'	=>	'Calendar',
				'link'	=>	'Participant/Calendar/',
				'users'	=>	['participant']
			],
			'qarounds'	=>	[
				'icon'	=>	'icon-chemistry',
				'text'	=>	'PT Rounds',
				'link'	=>	'QAReviewer/PTRound/',
				'users'	=>	['qareviewer']
			],
			'users'		=>	[
				'icon'	=>	'icon-user-follow',
				'text'	=>	'User Accounts',
				'link'	=>	'QAReviewer/PTRound/userlist',
				'users'	=>	['qareviewer']
			]
		];

		if (count($menus) > 0) {
			foreach ($menus as $key => $item) {
				if(in_array($this->session->userdata('type'), $item['users'])){
					$active = "";
					if ($key == strtolower($class)) {
						$active = "active";
					}

					if(isset($item['sublist']) && is_array($item['sublist'])){
						$menu_list .= "<li class = 'nav-item nav-dropdown'>
							<a class = 'nav-link nav-dropdown-toggle' href = '#'>
								<i class = '{$item['icon']}'></i> {$item['text']}
							</a>
							<ul class = 'nav-dropdown-items'>";
						foreach($item['sublist'] as $sub_item){
							$menu_list .= "
								<li class = 'nav-item'>
									<a class = 'nav-link' href = '".base_url($sub_item['link'])."'><i class = '{$sub_item['icon']}'></i> {$sub_item['text']}</a>
								</li>
							";
						}
						$menu_list .= "</ul></li>";
					}
					else{
					$menu_list .= "<li class = 'nav-item'>
						<a class = 'nav-link' href = '".base_url($item['link'])."'><i class = '{$item['icon']}'></i> {$item['text']}</a>
					</li>";
					}
				}
			}
		}

		return $menu_list;
	}

	function setPageTitle($page_title = ""){
		$this->pageTitle = $page_title;

		return $this;
	}

	function setPageDescription($pageDescription){
		$this->pageDescription = $pageDescription;

		return $this;
	}

	function setPartial($view, $data = []){
		$this->contentView = $view;
		$this->contentViewData = $data;

		return $this;
	}

	function setModal($view, $title ,$data =[]){
		$this->modalView = $view;
		$this->modalData = $data;
		$this->modalTitle = $title;

		return $this;
	}

	function setMetaData($key, $value){
		$metadata_string = "";

		$metadata_string = "<meta property = '{$key}' content = '{$value}'>\r\n";

		$this->metaData .= $metadata_string;

		return $this;
	}
}

/* End of file Template.php */
/* Location: ./application/modules/Template/controllers/Template.php */