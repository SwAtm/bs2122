<?php
class Trns_details extends CI_Controller{
	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->helper('url');
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->load->library('table');
		$this->load->model('Item_model');
		$this->load->model('Party_model');
		$this->load->model('Trns_summary_model');
		$this->load->model('Series_model');
		$this->load->model('Inventory_model');
		$this->load->model('Trns_details_model');
		$this->load->model('Trnf_details_model');
		$this->load->model('Profo_details_model');
		$this->load->library('session');
		$this->output->enable_profiler(TRUE);
	}


public function purch_add_details(){
	
	
	if (!isset($_POST)||empty($_POST)):
		//unsubmitted
		$item = $this->Item_model->getall();
		$data['item'] = $item;
		$this->session->item = $item;
		$this->load->view('templates/header');
		$this->load->view('trns_details/purch_add_details',$data);
		//$this->load->view('templates/footer');
		
	elseif (isset($_POST['add'])):
		//for adding
		unset($_POST['add']);
		//first entry
		if (!isset($this->session->purchase_details)||empty($this->session->purchase_details)):
		$det[] = $_POST;
		else:
		//pull out from session
		$det = $this->session->purchase_details;
		//add latest row to session
		$det[] = $_POST;
		endif;
		//save to session
		$this->session->purchase_details = $det;
		//endif;
		$data['item'] = $this->session->item;
		$data['details'] = $det;
		$this->load->view('templates/header');
		$this->load->view('trns_details/purch_add_details',$data);
		//$this->load->view('templates/footer');

	elseif (isset($_POST['cancel'])):
		unset($_SESSION['purchase_details']);
		unset($_SESSION['item']);
		redirect (site_url('Welcome/home'));
	else:
		//completed bill
		//if a joker submits empty bill:
		if (!isset($this->session->purchase_details)||empty($this->session->purchase_details)):
			unset($_SESSION['item']);
			echo $this->load->view('templates/header','',true);
				die("Sorry, You cannt create an empty bill<br> <a href = ".site_url('welcome/home').">Go Home</a href>&nbsp&nbsp&nbsp<a href = ".site_url('trns_summary/summary').">Or Go to List</a href>");
		endif;
		//unset($_POST);
		$_POST = array();
		$this->purch_complete_details();
	endif;

}

	public function purch_complete_details(){

		if (!isset($_POST)||empty($_POST)):
		//unsubmitted
			$data['party'] = $this->Party_model->getall();
			$this->load->view('templates/header');
			$this->load->view('trns_details/purch_complete_details', $data);
			$this->load->view('templates/footer');
		//cancel bill
		elseif (isset($_POST['cancel'])):
		unset($_SESSION['purchase_details']);
		unset($_SESSION['item']);
		redirect (site_url('Welcome/home'));
		
		else:	
		//submitted	
			//print_r($_POST);
			$series = $this->Series_model->get_series('Fort Ashrama','Credit','Purchase');
			$data1['series'] = $series['series'];
			$data1['series_id'] = $series['id'];
			$no_arr = $this->Trns_summary_model->get_max_no($data1['series']);
			$data1['no'] = $no_arr['no']+1;
			$party_id = $_POST['party'];
			$party = $this->Party_model->get_details($party_id);
			$data1['date'] = date('Y-m-d');
			$data1['party_id'] = $party['id'];
			$data1['party_status'] = $party['status'];
			$data1['party_gstno'] = $party['gstno'];
			$data1['expenses'] = $_POST['expenses'];
			$data1['remark'] = $_POST['remark'];
			$details = $this->session->purchase_details;
			//start adding data. Add to summary, get the id
			$this->db->trans_start();
			$this->Trns_summary_model->add($data1);
			$trns_summary_id_arr = $this->Trns_summary_model->get_max_id();
			$trns_summary_id = $trns_summary_id_arr['id'];
			
			// add to inventory, simultaneously build trans_details and add
			foreach ($details as $key => $value) {
			$invent['id'] = '';
			$invent['location_id'] = $this->session->loc_id;
			$invent['item_id'] = $value['item_id'];
			$invent['rate'] = $value['rate'];
			$invent['cost'] = round(($value['rate']-$value['cash_disc'])-(($value['rate']-$value['cash_disc'])*$value['discount']/100 ),2);
			$invent['hsn'] = $value['hsn'];
			$invent['grate'] = $value['gst_rate'];
			$invent['opbal'] = 0;
			$invent['in_qty'] = $value['quantity'];
			$invent['out_qty'] = 0;
			$invent['clbal'] = $value['quantity'];
			$this->Inventory_model->add($invent);
			$inventory_id_arr = $this->Inventory_model->get_max_id();
			$inventory_id = $inventory_id_arr['id'];
			//build trns_details using summary_id and inventory_id
			$trns_details['item_id'] =  $value['item_id'];
			$trns_details['rate'] =  $value['rate'];
			$trns_details['quantity'] =  $value['quantity'];
			$trns_details['discount'] =  $value['discount'];
			$trns_details['cash_disc'] =  $value['cash_disc'];
			$trns_details['hsn'] =  $value['hsn'];
			$trns_details['gst_rate'] =  $value['gst_rate'];
			$trns_details['trns_summary_id'] = $trns_summary_id;
			$trns_details['inventory_id'] = $inventory_id;
			$this->Trns_details_model->add($trns_details);
			}
		
			$this->db->trans_complete();
			unset($_SESSION['purchase_details']);
			unset($_SESSION['item']);
			$this->load->view('templates/header');
			$this->output->append_output("<a href = ".site_url('trns_summary/summary').">Go to List</a hre><br>");
			$this->output->append_output("<a href =".site_url('welcome/home').">Home</a href>");
			$this->load->view('templates/footer');
		endif;	

	}


		public function sales_add_details(){
		//unsubmitted
		if (!isset($_POST)||empty($_POST)):			
			
			if (!$inventory = $this->Inventory_model->get_list_per_loc()):
					//nothing in the inventory	
				echo $this->load->view('templates/header','',true);
				die("Sorry, Inventory is empty<br> <a href = ".site_url('welcome/home').">Go Home</a href>&nbsp&nbsp&nbsp<a href = ".site_url('trns_summary/summary').">Or Go to List</a href>");
						
			endif;
			$data['invent'] = $inventory;
			$this->session->invent = $inventory;
			$this->load->view('templates/header');
			$this->load->view('trns_details/sales_add_details',$data);
			$this->load->view('templates/footer');	
		//cancelled	
		elseif (isset($_POST['cancel'])):
			
			unset($_SESSION['sales_details']);
			unset($_SESSION['invent']);
			redirect (site_url('Welcome/home'));
		
		elseif(isset($_POST['add'])):
		//submitted to add
			$item = json_decode($_POST['item']);
		//currently submitted data
			$details = array('inventory_id' => $item->id, 'rate' => $item->rate, 'quantity' => $_POST['quantity'], 'discount' => $_POST['discount'], 'cash_disc' => $_POST['cash_disc'], 'hsn' => $item->hsn, 'gst_rate' => $item->grate, 'title' => $item->title, 'item_id' => $item->item_id);
		// firts transaction - session is empty
			if (!isset($this->session->sales_details)||empty($this->session->sales_details)):
			$det[] = $details;
			else:
		//pull frm session
			$det = $this->session->sales_details;
			$det[] = $details;
			endif;
			$this->session->sales_details = $det;
		//need to reduce the last sale from clbal in inventory
			$inventory = $this->session->invent;
			foreach ($inventory as $key => $value):
				if ($value['id'] == $details['inventory_id']):
				$inventory[$key]['clbal']-=$details['quantity'];
				//print_r($inventory[$key]['clbal']);
				endif;
			endforeach;
		//put chgd inventory in session
			$this->session->invent = $inventory;
		//now everything is in det and session
			$data['details'] = $det;
			$data['invent'] = $this->session->invent;	
			
			$this->load->view('templates/header');
			$this->load->view('trns_details/sales_add_details',$data);
			$this->load->view('templates/footer');	
		
		else:
			//submitted to complete, no currently submitted data
			//if a joker submits empty bill:
			if (!isset($this->session->sales_details)||empty($this->session->sales_details)):
				unset($_SESSION['invent']);
				echo $this->load->view('templates/header','',true);
				die("Sorry, You cannt create an empty bill<br> <a href = ".site_url('welcome/home').">Go Home</a href>&nbsp&nbsp&nbsp<a href = ".site_url('trns_summary/summary').">Or Go to List</a href>");
			endif;
			//unset($_POST);
			$_POST = array();
			$this->sales_complete_details();
		endif;	
		}

		public function sales_complete_details(){
		
			if (!isset($_POST)||empty($_POST)):			
			//unsubmitted	
				if (!$series = $this->Series_model->get_series_by_location()):
			//this query returns all sales for present location
				echo $this->load->view('templates/header','',true);
				die("Sorry, No Series defined for this location<br> <a href = ".site_url('welcome/home').">Go Home</a href>&nbsp&nbsp&nbsp<a href = ".site_url('trns_summary/summary').">Or Go to List</a href>".$this->session->location_name);
				endif;
			
				$data['series'] = $series;
				$data['party'] = $this->Party_model->getall();
				$this->load->view('templates/header');
				$this->load->view('trns_details/sales_complete_details',$data);
				$this->load->view('templates/footer');		

			//cancelled	
			elseif (isset($_POST['cancel'])):
				unset($_SESSION['sales_details']);
				unset($_SESSION['invent']);
				redirect (site_url('Welcome/home'));
			
			else:
			//submitted	
			//for trns_summary
				$series_details = $this->Series_model->get_series_details($_POST['series']);
				$data['series_id'] = $series_details['id'];
				$data['series'] = $series_details['series'];
				$no_array = $this->Trns_summary_model->get_max_no($series_details['series']);
				$data['no'] = $no_array['no']+1;
				$data['date'] = date('Y-m-d');
				$party_details = $this->Party_model->get_details($_POST['party']);
				$data['party_id'] = $party_details['id'];
				$data['party_status'] = $party_details['status'];
				$data['party_gstno'] = $party_details['gstno'];
				$data['expenses'] = $_POST['expenses'];
				$data['remark'] = $_POST['remark'];
				$tran_type_name = $series_details['tran_type_name'];
				
				$this->db->trans_start();
				$this->Trns_summary_model->add($data);
			
				//for trns_details and inventory
				$trns_summary_id = $this->Trns_summary_model->get_max_id()['id'];
				//get details from session
				$det = $this->session->sales_details;
				foreach ($det as $d):
					$d['trns_summary_id'] = $trns_summary_id;
					unset($d['title']);
					$td[] = $d;
				endforeach;
				
				foreach ($td as $t):
					$this->Trns_details_model->add($t);
					$this->Inventory_model->update_transaction($tran_type_name,$t['inventory_id'], $t['quantity']);
				endforeach;
				$this->db->trans_complete();
				unset($_SESSION['invent']);
				unset($_SESSION['sales_details']);
				$this->load->view('templates/header');
				$this->output->append_output("<a href =".site_url('trns_summary/summary').">Go to List</a href>");
				$this->load->view('templates/footer');	
			endif;				
		}

		public function check_editable(){

			$id = $this->uri->segment(3);
			$tran = $this->Trns_summary_model->get_details_by_id($id);
			$tran_type_name = $tran['tran_type_name'];
			$party_status = $tran['party_status'];
			$date = $tran['date'];
			$payment_mode_name = $tran['payment_mode_name'];
			$mess = '';
			//B2B sales/sale return not allowed:
			if (($tran_type_name == 'Sales' || $tran_type_name == 'Sale Return') AND ($party_status == 'REGD')):
				$mess = "This is a B2B Sales or Sale Return, cannot be edited";
			//Earlier month's transactions not allowed:
			elseif (date('m',strtotime($date))!=Date('m')):
				$mess = "This transaction belongs to earlier month, cannot be edited";
			//Cash transactions of earlier day not allowed:
			elseif ($payment_mode_name == 'Cash' and date('Y-m-d',strtotime($date)) != Date('Y-m-d')):
				$mess = "This is a cash transaction of an earlier day, cannot be edited";
			endif;
			if (''!=$mess):
				$this->load->view('templates/header');	
				$this->output->append_output($mess."<br>");
				$this->output->append_output("<a href =".site_url('trns_summary/summary').">Go to List</a href>");
				$this->load->view('templates/footer');	
			else:
				//editable
				//get details
				//if it is purchase, exclude inventory items with >0 out_qty or having >1 entry in trns_details/ >0 entry in trnf_details/profo_details
				$details = $this->Trns_details_model->get_details($id);
				if($tran_type_name == 'Purchase'){
					//$details = $this->Trns_details_model->get_details_to_delete_purchase($id);	
					foreach ($details as $k=>$d) {
						if (!$this->Trns_details_model->confirm_one_entry('inventory_id',$d['inventory_id'])||!$this->Trnf_details_model->confirm_zero_entry('inventory_id',$d['inventory_id'])||!$this->Profo_details_model->confirm_zero_entry('inventory_id',$d['inventory_id'])||!$this->Inventory_model->confirm_zero_out_qty($d['inventory_id'])):
							$details[$k]['delet']=0;
						else:
							$details[$k]['delet']=1;
						endif;
					}
				}
					
				//add to session
				$this->session->details = $details;
				$this->session->tran_type_name = $tran_type_name;
				$this->session->trns_summary_id = $id;

				$this->edit_delet();
			endif;

		}	

			public function edit_delet(){
				//unsubmitted:
			if (!isset($_POST) or empty($_POST)):
				$data['details'] = $this->session->details;
				$data['tran_type'] = $this->session->tran_type_name;
				$this->load->view('templates/header');	
				$this->load->view('trns_details/edit_delet',$data);	
				$this->load->view('templates/footer');	
			//cancel
			elseif (isset($_POST['cancel'])):
				unset($_SESSION['details']['tran_type_name']['trns_summary_id']);
				redirect (site_url('Welcome/home'));
			else:
				//submitted
				$deleted = array();
				$retained = array();
				if (isset($_POST['det']) and !empty($_POST['det'])):
				$det = $_POST['det'];
					foreach ($det as $d):
						if(isset($d['delete']) and $d['delete'] == 1):
							$deleted[] = $d;
						else:
							$retained[] = $d;	
						endif;
					endforeach;
				endif;
				$this->session->retained = $retained;
				$this->session->deleted = $deleted;
				//now we have to send to add
				$tran_type_name = $this->session->tran_type_name;
				//unset($_POST);
				$_POST = array();
				if ('Purchase' == $tran_type_name):
					$this->edit_purchase_add();
				else:
					$this->edit_sales_add();
				endif;	
					
			endif;
}
			
		public function edit_purchase_add(){
			if (!isset($_POST) or empty($_POST)):
			//using the same view files that are used while adding. Need to identify the calling process in the view file.
				$data['calling_proc'] = 'edit';
				$data['item']= $this->Item_model->getall();
				$data['retained'] = $this->session->retained;
				$this->session->item = $data['item'];
				$this->load->view('templates/header');	
				$this->load->view('trns_details/purch_add_details',$data);	
				$this->load->view('templates/footer');	
			//cancel
			elseif (isset($_POST['cancel'])):
				unset($_SESSION['details']['tran_type_name']['trns_summary_id']['retained']['deleted']['toadd']['item']);
				redirect (site_url('Welcome/home'));
			
			//to add
			elseif(isset($_POST['add'])):
				unset($_POST['add']);
				//first entery
				if(!isset($this->session->toadd) or empty($this->session->toadd)):
					$toadd[] = $_POST;
				else:
				//subsequent entries
					$toadd = $this->session->toadd;
					$toadd[] = $_POST;
				endif;		

				$this->session->toadd = $toadd;				
				$data['item']= $this->session->item;
				$data['calling_proc'] = 'edit';
				$this->load->view('templates/header');	
				$this->load->view('trns_details/purch_add_details',$data);	
				$this->load->view('templates/footer');	
			else:
			//bill is complete.
				if (isset($this->session->deleted) and !empty($this->session->deleted)):
					$deleted = $this->session->deleted;
				
				endif;
				if (isset($this->session->retained) and !empty($this->session->retained)):
						$retained = $this->session->retained;
				endif;
				if (isset($this->session->toadd) and !empty($this->session->toadd)):
					$toadd = $this->session->toadd;
				endif;
				$this->db->trans_start();
				//adding to inventory
				if (isset($toadd) and !empty($toadd)):
				foreach ($toadd as $key => $value) {
					$invent['id'] = '';
					$invent['location_id'] = $this->session->loc_id;
					$invent['item_id'] = $value['item_id'];
					$invent['rate'] = $value['rate'];
					$invent['cost'] = round(($value['rate']-$value['cash_disc'])-(($value['rate']-$value['cash_disc'])*$value['discount']/100 ),2);
					$invent['hsn'] = $value['hsn'];
					$invent['grate'] = $value['gst_rate'];
					$invent['opbal'] = 0;
					$invent['in_qty'] = $value['quantity'];
					$invent['out_qty'] = 0;
					$invent['clbal'] = $value['quantity'];
					$this->Inventory_model->add($invent);
					$inventory_id_arr = $this->Inventory_model->get_max_id();
					$inventory_id = $inventory_id_arr['id'];
				//build trns_details using summary_id and inventory_id
					$trns_details['item_id'] =  $value['item_id'];
					$trns_details['rate'] =  $value['rate'];
					$trns_details['quantity'] =  $value['quantity'];
					$trns_details['discount'] =  $value['discount'];
					$trns_details['cash_disc'] =  $value['cash_disc'];
					$trns_details['hsn'] =  $value['hsn'];
					$trns_details['gst_rate'] =  $value['gst_rate'];
					$trns_details['trns_summary_id'] = $this->session->trns_summary_id;
					$trns_details['inventory_id'] = $inventory_id;
					$this->Trns_details_model->add($trns_details);
					}
					endif;
					//to delete: trns_details- delete the entry, inventory- delet the entry
					if (isset($deleted) and !empty($deleted)):
					foreach ($deleted as $d):
						$this->Trns_details_model->delete($d['id']);
						$this->Inventory_model->edit_transaction_delete_purchase($d['inventory_id']);
					endforeach;
					endif;
					//retained: In purchase retained will have entries which could not be deleted + which were not deleted. 'which could not be deleted' - in this category, change of quantity is allowed. We will just 
					//update the quantity and clbal in inventory for each entry,
					//update the entry in trns_details
					if (isset($retained) and !empty($retained)):
					foreach ($retained as $r):
						$this->Inventory_model->update_purchase_quantity($r['inventory_id'], $r['quantity']);
						$this->Trns_details_model->update_purchase_quantity($r['id'], $r['quantity']);
					endforeach;
					endif;
				unset($_SESSION['details']);
				unset($_SESSION['retained']);
				unset($_SESSION['tran_type_name']);
				unset($_SESSION['trns_summary_id']);
				unset($_SESSION['deleted']);
				unset($_SESSION['toadd']);
				unset($_SESSION['item']);
				$this->db->trans_complete();	
				$this->load->view('templates/header');	
				$this->output->append_output("<a href = ".site_url('trns_summary/summary').">Go to List</a href>");
				$this->load->view('templates/footer');	
			endif;	
			}


		public function edit_sales_add(){
			if (!isset($_POST) or empty($_POST)):
			//using the same view files that are used while adding. Need to identify the calling process in the view file.
				$data['calling_proc'] = 'edit';
				$inventory = $this->Inventory_model->get_list_per_loc();
				$deleted = $this->session->deleted;
				//add/subtract deleted quantity to/from inventory
				foreach ($inventory as $key => $value):
					foreach ($deleted as $dkey => $dvalue):
						if ($value['id'] == $dvalue['inventory_id']):
								$inventory[$key]['clbal']+= $dvalue['quantity'];
						endif;
					endforeach;
				endforeach;
				$data['invent'] = $inventory;
				$this->session->invent = $data['invent'];
				$this->load->view('templates/header');	
				$this->load->view('trns_details/sales_add_details',$data);	
				$this->load->view('templates/footer');	
			//cancel
			elseif (isset($_POST['cancel'])):
				unset($_SESSION['details']['tran_type_name']['trns_summary_id']['retained']['deleted']['toadd']['invent']);
				redirect (site_url('Welcome/home'));
	
			//to add
			elseif(isset($_POST['add'])):
				//unset($_POST['add']);
				$item = json_decode($_POST['item']);
				//currently submitted data
				$itemtoadd = array('inventory_id' => $item->id, 'rate' => $item->rate, 'quantity' => $_POST['quantity'], 'discount' => $_POST['discount'], 'cash_disc' => $_POST['cash_disc'], 'hsn' => $item->hsn, 'gst_rate' => $item->grate, 'title' => $item->title, 'item_id' => $item->item_id);

				//first entery
				if(!isset($this->session->toadd) or empty($this->session->toadd)):
					$toadd[] = $itemtoadd;
				else:
				//subsequent entries
					$toadd = $this->session->toadd;
					$toadd[] = $itemtoadd;
				endif;		

				//need to reduce the last sale from clbal in inventory
				$inventory = $this->session->invent;
				foreach ($inventory as $key => $value):
					if ($value['id'] == $itemtoadd['inventory_id']):
						$inventory[$key]['clbal']-=$itemtoadd['quantity'];
					endif;
				endforeach;
				//put chgd inventory in session
				$this->session->invent = $inventory;
				$this->session->toadd = $toadd;				
				$data['invent']= $this->session->invent;
				$data['calling_proc'] = 'edit';
				$this->load->view('templates/header');	
				$this->load->view('trns_details/sales_add_details',$data);	
				$this->load->view('templates/footer');	
			else:
				//bill is complete.
				if (isset($this->session->deleted) and !empty($this->session->deleted)):
					$deleted = $this->session->deleted;
				endif;
				if (isset($this->session->toadd) and !empty($this->session->toadd)):
					$toadd = $this->session->toadd;
				endif;
				$tran_type_name = $this->session->tran_type_name;
				$this->db->trans_start();
				
				if (isset($toadd) and !empty($toadd)):

				foreach ($toadd as $key) {
					
					//adding to inventory
					$this->Inventory_model->update_transaction($tran_type_name, $key['inventory_id'], $key['quantity']);
					//adding to trns_details
					unset($key['title']);
					$key['trns_summary_id'] = $this->session->trns_summary_id;
					$this->Trns_details_model->add($key);
				}
				endif;

				if (isset($deleted) and !empty($deleted)):
				
					foreach ($deleted as $d) {
					//removing from trns_details
						$this->Trns_details_model->delete($d['id']);
					//updating inventory
						$this->Inventory_model->edit_transaction_delete_sales($tran_type_name, $d['inventory_id'], $d['quantity']);
					}
				endif;
				unset($_SESSION['details']);
				unset($_SESSION['retained']);
				unset($_SESSION['tran_type_name']);
				unset($_SESSION['trns_summary_id']);
				unset($_SESSION['deleted']);
				unset($_SESSION['invent']);
				unset($_SESSION['toadd']);
				$this->db->trans_complete();
				$this->load->view('templates/header');	
				$this->output->append_output("<a href =".site_url('trns_summary/summary').">Go to List</a href>");
				$this->load->view('templates/footer');	
			endif;

		}



}
?>











