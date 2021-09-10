<?php
class Trnf_details extends CI_Controller{
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
		$this->load->model('Party_model');
		$this->load->model('Trnf_details_model');
		$this->load->model('Inventory_model');
		$this->load->model('Location_model');		
		$this->load->model('Trnf_summary_model');		
		$this->load->model('Item_model');		

}


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


				//submitted to add
				$item = json_decode($_POST['item']);
				//currently submitted data
				$details = array('inventory_id' => $item->id, 'quantity' => $_POST['quantity'],'item_id' => $item->item_id, 'rate' => $item->rate);
				// firts transaction - session is empty
				if (!isset($this->session->send_details)||empty($this->session->send_details)):
					$det[] = $details;
				else:
				//pull frm session
					$det = $this->session->send_details;
					$det[] = $details;
				endif;
				$this->session->send_details = $det;
				//now everything is in det and session
				//$data['details'] = $det;
				
				//need to reduce the last trnf from clbal in inventory
				$inventory = $this->session->invent;
				foreach ($inventory as $key => $value):
					if ($value['id'] == $details['inventory_id']):
					$inventory[$key]['clbal']-=$details['quantity'];
					//print_r($inventory[$key]['clbal']);
					endif;
				endforeach;
				//put chgd inventory in session
				$this->session->invent = $inventory;
				$data['invent'] = $this->session->invent;
				$this->load->view('templates/header');
				$this->load->view('trnf_details/add_details',$data);
				$this->load->view('templates/footer');	
			else:
			//submitted to complete, no currently submitted data
			//if a joker submits empty bill:
			if (!isset($this->session->send_details)||empty($this->session->send_details)):
				echo $this->load->view('templates/header','',true);
				
				unset($_SESSION['invent']);
				if (isset($this->session->send_details)):
					unset($_SESSION['send_details']);
				endif;
				die("Sorry, You cannt create an empty transfer<br> <a href = ".site_url('welcome/home').">Go Home</a href>");
			endif;
			//unset($_POST);
			$_POST = array();
			$this->send_complete();

			endif;	
		}

		public function send_complete(){

			//unsubmitted
			if (!isset($_POST)||empty($_POST)):			
				$loc = array();
				$locations = $this->Location_model->get_list_except_loggedin($this->session->loc_id);
				foreach ($locations as $k=>$v):
					//$loc[]=array($v['id'] => $v['name']);
					$loc[$v['id']] = $v['name'];

				endforeach;
				$data['loc'] = $loc;
				$this->load->view('templates/header');
				$this->load->view('trnf_details/send_complete',$data);
				$this->load->view('templates/footer');
			//submitted
			else:
				//$this->load->view('templates/header');
				//print_r($_POST);
				//trnf summary
				$ts['date'] = date('Y-m-d');
				$ts['from_id'] = $this->session->loc_id;
				$ts['to_id'] = $_POST['to_id'];
				//trnf details
				$td = $this->session->send_details;
				//start transaction
				$this->db->trans_start();
				//add to trnf summary
				$this->Trnf_summary_model->add($ts);
				//get the max id
				$trnf_summary_id = $this->Trnf_summary_model->get_max_id()['id'];
				//add trnf summary id to each detail row and add to trnf details
				foreach ($td as $k):
					$k['trnf_summ_id'] = $trnf_summary_id;
					//$tds[] = $k;
				//endforeach;
				//foreach ($tds as $k):
					$this->Trnf_details_model->add($k);
				endforeach;
				//update inventory
				foreach($td as $key):
					//sending loc
					$this->Inventory_model->update_transfer_send($key);
					//receiving loc
					//pull inventory row based on $key['inventory_id']
					$inventr = $this->Inventory_model->get_details($key['inventory_id']);
					//unset id, replace loc_id, in_qty, out_qty, opbal, clbal
					unset ($inventr['id']);
					$inventr['location_id']=$ts['to_id'];
					$inventr['in_qty'] = $key['quantity'];
					$inventr['out_qty'] = 0;
					$inventr['opbal'] = 0;
					$inventr['clbal'] = $key['quantity'];
					//add to inventory table
					$this->Inventory_model->add($inventr);
				endforeach;
				unset($_SESSION['invent']);
				unset($_SESSION['send_details']);
				$this->db->trans_complete();
				$this->load->view('templates/header');				
				//print_r($item_code);
				//echo "File ".$trnf_summary_id.".csv is saved at".SAVEPATH."<br>";
				$this->load->view('templates/footer');
			endif;
		}
				
	/*			
				//creat data for exporting
				foreach ($td as $k):
					//add trnf_summary_id key to trnf_details
					$k['trnf_summary_id'] = $trnf_summary_id;
					$tds[] = $k;
					//build inventory 
					$inventr = $this->Inventory_model->get_details($k['inventory_id']);
					unset($inventr['id']);
					$inventr['location_id'] = $ts['to_id'];
					$inventr['opbal'] = 0;
					$inventr['in_qty'] = $k['quantity'];
					$inventr['out_qty'] = 0;
					$inventr['clbal'] = $k['quantity'];
					$inventr['stock'] = 0;
					$invent_ds[]=$inventr;

					//item
					$it = $this->Item_model->get_details_with_partycode($k['item_id']);
					unset($it['id']);
					$item[] = $it;


				endforeach;
				//make item unique
				$item_code = array_unique(array_column($item, 'code'));
				$item = array_intersect_key($item, $item_code);
				//party unique
				$party = array_unique(array_column($item, 'party_id'));
				$party_string = implode(',', $party);
				$party_a = $this->Party_model->getall_in_array($party_string);
				foreach ($party_a as $p):
					unset($p['id']);
					$party_array[] = $p;
				endforeach;
				/*
				echo "<pre>";
				print_r($ts);
				print_r($tds);
				print_r($invent_ds);
				print_r($item);
				print_r($party_array);
				var_export($invent_ds);
				$to_export = array_merge($ts,$tds,$invent_ds,$item,$party_array);
				print_r($to_export);
				echo "</pre>";
				
				//file_put_contents(SAVEPATH.$trnf_summary_id.'.txt', '$trnf_summary=', FILE_APPEND);
				
				file_put_contents(SAVEPATH.'tr_'.$trnf_summary_id.'.php', '<?php $trnf_summary='.var_export($ts,true).';',FILE_APPEND);
				//file_put_contents(SAVEPATH.$trnf_summary_id.'.txt', '$trnf_details=', FILE_APPEND);
				file_put_contents(SAVEPATH.'tr_'.$trnf_summary_id.'.php', '$trnf_details='.var_export($tds,true).';',FILE_APPEND);
				//file_put_contents(SAVEPATH.$trnf_summary_id.'.txt', '$inventory=', FILE_APPEND);
				file_put_contents(SAVEPATH.'tr_'.$trnf_summary_id.'.php', '$inventory='.var_export($invent_ds,true).';',FILE_APPEND);
				//file_put_contents(SAVEPATH.$trnf_summary_id.'.txt', '$item=', FILE_APPEND);
				file_put_contents(SAVEPATH.'tr_'.$trnf_summary_id.'.php', '$item='.var_export($item,true).';',FILE_APPEND);
				//file_put_contents(SAVEPATH.$trnf_summary_id.'.txt', '$party=', FILE_APPEND);
				file_put_contents(SAVEPATH.'tr_'.$trnf_summary_id.'.php', '$party='.var_export($party_array,true).';?>',FILE_APPEND);
				//header("Location: ".SAVEPATH.$trnf_summary_id.".php");
				//header('Content-Type: application/txt');

				
				//header('Content-Disposition: attachment; filename='.$trnf_summary_id.'.txt');

				
				//readfile(SAVEPATH.$trnf_summary_id.'.txt');
				//exit;
				/*
				$handle = fopen(SAVEPATH.$trnf_summary_id.'.csv', 'a');
				if ($handle):
					foreach($party_array as $p):
					fputcsv($handle, $p);
					endforeach;
					foreach($item as $i):
					fputcsv($handle, $i);
					endforeach;
					//fputcsv($handle, $item);
					foreach($invent_ds as $is):
					fputcsv($handle, $is);
					endforeach;
					//fputcsv($handle, $invent_ds);
					fputcsv($handle, $ts);
					foreach($tds as $t):
					fputcsv($handle, $t);
					endforeach;	
					//fputcsv($handle, $tds);
				endif;
				fclose($handle);
				

				$this->db->trans_complete();
				$this->load->view('templates/header');				
				//print_r($item_code);
				//echo "File ".$trnf_summary_id.".csv is saved at".SAVEPATH."<br>";
				$this->load->view('templates/footer');
			endif;



		}


		public function receive(){
			//include (SAVEPATH.'up_file.php');
			
			//not submitted
			if (!$_POST || empty($_POST)):
				//$data['error'] = '';
				$this->load->view('templates/header');
				$this->load->view('trnf_details/receive');
				$this->load->view('templates/footer');

			//submitted
			else:
				/*
				$config['upload_path'] = SAVEPATH.'Downloads/';
				$config['allowed_types'] = 'php|txt';
				$config['max_size'] = '2000';
				$this->load->library('upload',$config);
				if (! $this->upload->do_upload('file_upload')):
					//$data['error'] = array ('error' => $this->upload->display_errors());
					print_r($this->upload->display_errors());
					$this->load->view('trnf_details/receive');
					$this->load->view('templates/footer');
				else:
					include(SAVEPATH.'Downloads/'.$this->upload->data()['file_name']);
					echo "</pre>";
					print_r($party);
					print_r($this->upload->data());
					echo "</pre>";
					$this->load->view('templates/footer');
				endif;					
				
				include (SAVEPATH.$_POST['file_upload'].'.php');
				
				echo "<pre>";
				//check if party exists, else add
				foreach ($party as $p):
					if (!$party_to_add = $this->Party_model->get_details_by_code($p['code'])):
						$this->Party_model->add($p);
					echo "New Party<br>";
					else:
					echo "Old party<br>";
					endif;

				endforeach;
				//check if item exists, else add
				foreach ($item as $i):
					if(!$this->Item_model->check_if_exists($i['code'])):
						$pid = $this->Party_model->get_id_from_code($i['pcode']);
						$i['party_id'] = $pid;
						unset($i['pcode']);
						$this->Item_model->add($i);
					endif;
				endforeach;
				print_r($party);
				print_r($inventory);
				print_r($item);
				print_r($trnf_summary);
				print_r($trnf_details);
				echo "</pre>";
			
			$this->load->view('templates/footer');	
			endif;	
			





		}

*/


//All below not be required.

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

		function _example_output($output = null)
	{
		$this->load->view('templates/header');
		$this->load->view('templates/trans_template.php',$output);    
		$this->load->view('templates/footer');
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


/*		
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
