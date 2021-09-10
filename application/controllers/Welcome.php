<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	
	public function __construct()
	{
		parent::__construct();

		$this->load->database();
		$this->load->helper('url');
		$this->load->model('Location_model');
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->load->library('session');
		}
	
	
	
	public function index()
	{
			$loc = $this->Location_model->getall();
			$data['location']=$loc;
			//$this->load->view('templates/header');
			$this->load->view('welcome/index',$data);
		
	}
	
	public function verify(){
	$this->form_validation->set_rules('user','User Name','required');
	
		if ($this->form_validation->run()==false):
		$this->index();
		else:
		//submitted and ok
		$id=$_POST['user'];
		$user=$this->Location_model->getdetails($id);
		//$GLOBALS['loc_id'] = $user['id'];
		//$GLOBALS['loc_name'] = $user['name'];
		//$GLOBALS['loc_auto_bill_no'] = $user['auto_bill_no'];
		//define("LOC_ID", $user['id']);
		$this->session->loc_id=$user['id'];
		//define("LOC_NAME", $user['name']);
		$this->session->loc_name=$user['name'];
		//define("LOC_AUTO_BILL_NO", $user['auto_bill_no']);
		$this->session->loc_auto_bill_no=$user['auto_bill_no'];
		//$this->load->view('templates/header');
		//$this->load->view('welcome/start');
		//setcookie('admin', 'abc', time()+600); 
		$this->home();
		//print_r($_SESSION);
		//echo "<a href=".site_url('Welcome/index').">Home</a href>";
		endif;
	}
		//$this->load->view('welcome_message');
	
	public function home(){
		//if ((!null == LOC_NAME)||!empty(LOC_NAME)):
		if (isset($this->session->loc_id)||!empty($this->session->loc_id)):
		$this->load->view('templates/header');
		$this->load->view('welcome/home');
		else:
		$this->index();
		endif;	
	}


	public function logout(){
		unset ($_SESSION);
		$this->session->sess_destroy();
		//unset(LOC_NAME);
		//unset(LOC_ID);
		//unset(LOC_AUTO_BILL_NO);
		//unset ($GLOBALS['loc_id']);
		//unset ($GLOBALS['loc_name']);
		//unset ($GLOBALS['loc_auto_bill_no']);
		$this->index();
	}
	
}
