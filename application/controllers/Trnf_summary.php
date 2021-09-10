<?php
class Trnf_summary extends CI_Controller{
	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->helper('url');
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->load->library('table');
		//$this->load->helper('security');
		$this->load->library('grocery_CRUD');
		//$this->load->model('Tran_type_model');
		//$this->load->model('Party_model');
		//$this->load->model('Trns_summary_model');
		//$this->load->model('Temp_details_model');
		$this->output->enable_profiler(TRUE);
		$this->load->library('user_agent');
		//$this->load->model('Company_model');
		$this->load->library('session');
		//$this->load->model('Series_model');
		//$this->load->model('Party_model');
		$this->load->model('Trnf_details_model');
		$this->load->model('Inventory_model');
		$this->load->model('Location_model');		
		$this->load->model('Trnf_summary_model');		
		$this->load->helper('pdf_helper');

}



		public function summary()
		
	{
			$crud = new grocery_CRUD();
			
			
			$crud->set_table('trnf_summary')
				->set_subject('Transfer')
				->order_by('id','desc')
				->columns('id','date','from_id','to_id')
				->display_as('id','ID')
				->display_as('date','Date')
				->display_as('from_id','From')
				->display_as('to_id','To')
				->unset_print()
				->set_relation('from_id','locations','{name}')
				->set_relation('to_id','locations','{name}')
				->set_rules('date', 'Date', 'required')
				->unset_delete()
				->unset_add()
				->unset_edit()
				->add_action('View Details',base_url('application/view_details.png'),'trnf_summary/view_details');
				//better not to have facility to edit the summary, since that will involve changing loc id in inventory as well. Hence edit is unset above.
				$state = $crud->getState();
				$stateInfo = $crud->getStateInfo();
				if ('edit' == $state || 'update' == $state || 'update_validation' == $state):
					
					$details = $this->Trnf_summary_model->get_details_by_id($stateInfo->primary_key);
					if ($details['to_id'] == $this->session->loc_id):
						$crud->field_type('to_id','readonly')
						->set_rules('from_id', 'Location From', 'required|callback_check_location');
					else:
						$crud->field_type('from_id','readonly')
						->set_rules('to_id', 'Location To', 'required|callback_check_location');
					endif;
					
				endif;
				$output = $crud->render();
				$output->extra ='';
				$this->_example_output($output);                

	
	}
		
		function check_location($str){
	
			if ($str==$this->session->loc_id):
				$this->form_validation->set_message('check_location', 'Both locations cannot be same');
				return false;
			else:
				return true;
			endif;		
		}
		

		function _example_output($output = null)
	{
		$this->load->view('templates/header');
		$this->load->view('templates/trans_template.php',$output);    
		$this->load->view('templates/footer');
	}   


		public function view_details($pk){
			$data['trnf_details'] = $this->Trnf_details_model->trnf_details_per_id($pk);
			$data['trnf_summary'] = $this->Trnf_summary_model->trnf_summary_per_id($pk);
			
			//$this->load->view('templates/header');
			$this->load->view('trnf_details/view_details',$data);
			//$this->load->view('templates/footer');

		}




//All below not be required.
/*
				public function send(){

			//unsubmitted
			if (!isset($_POST)||empty($_POST)):			
			
				if (!$inventory = $this->Inventory_model->get_list_per_loc()):
					//nothing in the inventory	
					echo $this->load->view('templates/header','',true);
					die("Sorry, Inventory is empty<br> <a href = ".site_url('welcome/home').">Go Home</a href><a href = ".site_url('trnf_summary/summary').">Or Go to List</a href>");
						
				endif;
				$data['invent'] = $inventory;
				$this->session->invent = $inventory;
				$this->load->view('templates/header');
				$this->load->view('trnf_details/add_details',$data);
				$this->load->view('templates/footer');	
			
			elseif(isset($_POST['add'])):
				print_r($_POST);
				$this->load->view('templates/footer');	
			endif;	




		}


		/*function check_location($str){
	
			$from_id=$this->input->post('from_id');
			if ($str==$from_id):
				$this->form_validation->set_message('check_location', 'Both locations cannot be same');
				return false;
			else:
				return true;
			endif;

		}
		function check_editable($pk, $row){
		//check whether a transaction is editable
		$editable=1;
		if ($row->remark=='Cancelled'):
		$editable=0;
		endif;
		$payment_mode_name=$this->Series_model->get_payment_mode_name($row->series)->payment_mode_name;
		$dt=date_create_from_format('d/m/Y', $row->date);
		$date = date_format($dt,'Y-m-d');
		if ((ucfirst($payment_mode_name)=="Cash" and $date!=date("Y-m-d")) OR (ucfirst($payment_mode_name)!=="Cash" and Date("m",strtotime($date))!==Date("m"))):
		$editable=0;
		endif;
		
		if ($editable):
		return site_url('trns_summary/summary1/edit/'.$pk);
		else:
		return site_url('trns_summary/not_editable');
		endif;
		
		}

		

		public function not_editable(){
			$this->load->view('templates/header');
			$this->load->view('trns_summary/not_editable');

		}	




		public function summary1($pk)
	{
		//for editing. In summary() edit is unset. As such summary/edit is not allowed.
			//unsubmitted
			if (!isset($_POST) || empty($_POST)):	
				$pk = $this->uri->segment(4);
				/*
				$series_id = $this->Trns_summary_model->get_details_by_id($pk)['series_id'];
				$series_details = $this->Series_model->get_series_details($series_id);
				$tran_type = $series_details['tran_type_name'];
				
				$tran_details = $this->Trns_summary_model->get_details_by_id($pk);
				$tran_type = $tran_details['tran_type_name'];
				$party_status = $tran_details['party_status'];
				//$party_status = $this->Trns_summary_model->get_details_by_id($pk)['party_status'];
				
				if($tran_type == 'Sales' || $tran_type == 'Sale Return'):
				//sale/sale return from a regd party - party cannot be changed
				  	if(strtoupper($party_status) == 'REGD'):			
						$data['partychange'] = 'No';
					else:
				//sale/sale return from an unrd party - party can be changed only to another unrd
						$data['partychange'] = 'Yes';
						$data['party'] = $this->Party_model->getall_unregd();
					endif;
				else:
				//other transactions
						$data['partychange'] = 'Yes';
						$data['party'] = $this->Party_model->getall();
				endif;
				//$ser_det = $this->Trns_summary_model->get_details_by_id($pk);
				foreach ($tran_details as $k => $v):
					$data[$k] = $v;
				endforeach;	
				$p_id = $tran_details['party_id'];
				$p_details = $this->Party_model->get_details($p_id);
				$data['party_name'] = $p_details['name'].' - '.$p_details['city'];
				$data['pk'] = $pk;
				$this->load->view('templates/header');
				$this->load->view('trns_summary/summary_edit',$data);
				$this->load->view('templates/footer');
			//submitted	
			else:	
			//print_r($_POST);
			$party_id = $_POST['party'];
			$party_details = $this->Party_model->get_details($party_id);
			$series_id = $_POST['series_id'];
			//$data['series'] = $this->Series_model->get_series_details($series_id)['series'];
			$data['series'] = $_POST['series'];
			$data['no'] = $_POST['no'];
			$id = $_POST['id'];
			$data['series_id'] = $series_id;
			$data['date'] = $_POST['date'];
			$data['party_id'] = $party_id;
			$data['party_status'] = $party_details['status'];
			$data['party_gstno'] = $party_details['gstno'];
			$data['expenses'] = $_POST['expenses'];
			$data['remark'] = $_POST['remark'];
			//print_r($data);
				if ($this->Trns_summary_model->update($data,$id)):
					$mess = "Data Updated";
				else:
					$mess = "Error, Could not update";
				endif;	
			$this->load->view('templates/header');
			$this->output->append_output("$mess<a href =".site_url('trns_summary/summary'."> GO to List</a>"));
			$this->load->view('templates/footer');
			endif;		

}

		/*		$crud = new grocery_CRUD();
				$crud->set_table('trns_summary')
				->set_subject('Transaction')
				->display_as('series_id','Series')
				->display_as('no','Trn Number')
				->display_as('date','Date')
				->display_as('party_id','Party')
				->display_as('expenses','Expenses')
				->display_as('remark','Remark')
				->unset_add()
				->unset_back_to_list()
				->set_relation('series_id','series','{location_name}-{payment_mode_name}-{tran_type_name}')
				->set_relation('party_id','party','{name}--{city}')
				->edit_fields('series_id', 'no', 'date', 'party_id', 'expenses',  'remark', 'party_status', 'party_gstno');
				$series_id = $this->Trns_summary_model->get_details_by_id($pk)['series_id'];
				$series_details = $this->Series_model->get_series_details($series_id);
				$tran_type = $series_details['tran_type_name'];
				$party_status = $this->Trns_summary_model->get_details_by_id($pk)['party_status'];
				if(($tran_type == 'Sales' || $tran_type == 'Sale Return') AND strtoupper($party_status) == 'REGD'):
				$crud->field_type('party_id','readonly');
				endif;
				$crud->field_type('series_id','readonly')
				->field_type('no','readonly')
				->field_type('date','readonly')
				->field_type('party_gstno','invisible')
				->field_type('party_status','invisible')
				->callback_before_update(array($this,'update_party_details'));
				$output = $crud->render();
				$output->extra="<table align=center bgcolor=lightblue width=100%><tr><td align=center><a href=".site_url('trns_summary/summary').">Go to List</a></td></tr></table>";
				$this->_example_output($output);                

			}

			public function update_party_details($post_array, $primary_key){
			//if there is no change in party, party status and gst no should not be updated
			$p_id = $this->Trns_summary_model->get_details_by_id($primary_key)['party_id'];	
			$party_id = $post_array['party_id'];
			if ($p_id == $party_id):
			//party is same	
				return $post_array;
			else:
				$party_details = $this->Party_model->get_details($party_id);
				$post_array['party_status'] = $party_details['status'];
				$post_array['party_gstno'] = $party_details['gstno'];
				return $post_array;
			endif;

		}
		
		public function view_details($pk){
			$data['trns_details'] = $this->Trns_details_model->get_details($pk);
			$this->load->view('templates/header');
			$this->load->view('trns_details/view_details',$data);
			$this->load->view('templates/footer');

		}


		
		public function _callback_date($dt, $row)
		{
		return date('d/m/Y', strtotime($dt));
		}
*/	
		

		/*
		public function _callback_tr_no($id, $row)
		{
		return wordwrap($id,10);
		}
		*/
		
		 

	/*	
	function get_trcode_etc($post){
	
	//party status may change over time. Need to get the present status and add it to summary row.
		$party=$this->Party_model->getdetails($post['party_id']);
		if (!$party->status or null==$party->status):
			$party->status='UNRD';
		endif;
		$post['p_status']=$party->status;
		
		//get tr_code for this tr_type_id
		$tid=$post['tran_type_id'];
		$trcode=$this->Tran_type_model->gettrcode($tid);
		$post['tr_code']=$trcode->tr_code;
		
		//get tr_no for this tr_code
		$trno=$this->Summary_model->gettranno($post['tr_code']);
		$post['tr_no']=$trno;
		
		//add today's date
		$post['date']=date("Y-m-d");
		
		return $post;
	
	}
		
		
		
		/*
		function check_addable($pk, $row){
		//check whether details can be added to a transaction
		$addable=1;
		if ($row->remark=='Cancelled'):
		$addable=0;
		endif;
		$trantype=$this->Summary_model->getdescr($pk);
		$descr=$trantype->descrip_1;
		$date=$trantype->date;
		if ((ucfirst($descr)=="Cash" and $date!=date("Y-m-d")) OR (ucfirst($descr)!=="Cash" and Date("m",strtotime($date))!=Date("m"))):
		$addable=0;
		endif;
		if ($addable):
		return site_url('Details/details/add/'.$pk);
		else:
		return 'javascript:void()';
		endif;
		}

		/*
		function check_det_editable($pk, $row){
		//check whether details can be edited
		$det_edtable=1;
		if ($row->remark=='Cancelled'):
		$det_edtable=0;
		endif;
		$trantype=$this->Summary_model->getdescr($pk);
		$descr=$trantype->descrip_1;
		$date=$trantype->date;
		if ((ucfirst($descr)=="Cash" and $date!=date("Y-m-d")) OR (ucfirst($descr)!=="Cash" and Date("m",strtotime($date))!=Date("m"))):
		$det_edtable=0;
		endif;
		if ($det_edtable):
		return site_url('Details/id_details/'.$pk);
		else:
		return 'javascript:void()';
		endif;
		}
		/*
		public function chkdt($dt)
		{
		$dt=date('Y-m-d');
		$sdt=strtotime($dt);
		$cmp=$this->Company_model->getall();
		$frdate=$cmp->from_date;
		$todate=$cmp->to_date;
		$frdate=strtotime($frdate);
		$todate=strtotime($todate);
		if ($sdt<=$todate and $sdt>=$frdate):
		return true;
		else:
		$this->form_validation->set_message('chkdt','Date must be in the current year between '.date('d/m/Y',$frdate).' and '.date('d/m/Y',$todate).'. Instead your date is '.date('d/m/Y',$sdt));
		return false;
		endif;
		
		}	

	/*	public function checkedit($id)
		{
		$sql=$this->db->select('tran_type.descrip1');
		$sql=$this->db->from('tran_type');
		$sql=$this->db->join('summary', 'summary.tr_code=tran_type.tr_code');
		$sql=$this->db->where('sumamry.id',$id);
		$res=$this->db->get();
		$trtype=$res->row()->descrip1;
		$sql=$this->db->select('summary.date');
		$sql=$this->db->from('summary');
		$sql=$this->db->where('sumamry.id',$id);
		$res=$this->db->get();
		$dt=$res->row()->date;
	if ($trantype=='cash' AND $data!=date()):
		return false;
	else:
		return true;
	endif;
		}

	function _add_default_date_value(){
        $value = !empty($value) ? $value : date("d/m/Y");
        $return = '<input type="text" name="date" value="'.$value.'">';
        $return .= "(dd/mm/yyyy)";
        return $return;
	}



*/			

}
?>
