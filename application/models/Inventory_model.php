<?php
class Inventory_model extends CI_Model{
	public function __construct()
		{		
		$this->load->database();
	}

	public function add($data){
	//called by trns_details/edit_purchase_add, trns_details/purch_complete_details, trnf_details/send_complete
	if($this->db->insert('inventory',$data)):
		return true;
	else:
		return false;
	endif;
		
	}

	public function get_max_id(){
	//called by trns_details/purch_complete_details, trns_details/edit_purchase_add
	$sql=$this->db->select_max('id');
	$sql=$this->db->from('inventory');
	$sql=$this->db->get();
	return $sql->row_array();	
	}

	public function get_list_per_loc(){
	//called by trns_details/other_add_details/ trns_details/edit_other_add, trnf_details/send
		$sql = $this->db->select('inventory.*, item.title');
		$sql = $this->db->from('inventory');
		$sql = $this->db->join('item','item.id = inventory.item_id');
		$sql = $this->db->where('location_id',$this->session->loc_id);
		$sql=$this->db->get();
		if ($sql and $sql->num_rows()>0):
			return $sql->result_array();	
		else:
			return false;
		endif;
	
	}

	public function update_transaction($tran_type_name, $tinventory_id, $tquantity){
		//called by trns_details/other_complete_details, trns_details/edit_other_add
		if ('Sale Return' == $tran_type_name):
			$this->db->set('in_qty','in_qty+'.$tquantity,false);
		else:
			$this->db->set('out_qty', 'out_qty+'.$tquantity,false);
		endif;
		$this->db->set('clbal','opbal+in_qty-out_qty',false);
		$this->db->where('id',$tinventory_id);
		$this->db->update('inventory');
		}

	public function edit_transaction_delete_purchase($inventory_id, $quantity)	{
		//called by trns_details/edit_purchase_add
		/*
		$this->db->set('in_qty','in_qty-'.$quantity,false);
		$this->db->set('clbal','opbal+in_qty-out_qty',false);
		$this->db->where('id',$inventory_id);
		$this->db->update('inventory');
		*/
		$sql = $this->db->where('id',$inventory_id);
		if ($sql = $this->db->delete('inventory')):
			return true;
		else:
			return false;
		endif;

	}

	public function edit_transaction_delete_other($tran_type_name, $tinventory_id, $tquantity){
		//called by trns_details/edit_other_add,
		if ('Sale Return' == $tran_type_name):
			$this->db->set('in_qty','in_qty-'.$tquantity,false);
		else:
			$this->db->set('out_qty', 'out_qty-'.$tquantity,false);
		endif;
		$this->db->set('clbal','opbal+in_qty-out_qty',false);
		$this->db->where('id',$tinventory_id);
		$this->db->update('inventory');
		}

	public function itemwise_locationwise_stock($id){
		//called by item/get_sto
		$this->db->select('item.id, item.title, invent.id as iid, invent.rate, sum(invent.clbal) as clbal');
		$this->db->from('item');
		$this->db->join ('inventory invent', 'item.id=invent.item_id');
		$this->db->where('item.id',$id);
		$this->db->where('invent.location_id', $this->session->loc_id);
		$this->db->group_by('invent.rate');
		$sql = $this->db->get();
		return $sql->result_array();
		//return $stock;

	}	

	public function update_transfer_send($key){
		//called by trnf_details/send_complete
		$this->db->set('out_qty', 'out_qty+'.$key['quantity'],false);
		$this->db->set('clbal','opbal+in_qty-out_qty',false);
		$this->db->where('id',$key['inventory_id']);
		$this->db->update('inventory');
	}

	public function get_details($id){
		//called by trnf_details/send_complete
		$this->db->select('*');
		$this->db->from('inventory');
		$this->db->where('id',$id);
		$sql = $this->db->get();
		return $sql->row_array();
	}

	public function get_opbal($id, $rate){
		//called by item/det_stck
		$this->db->select_sum('opbal');
		$this->db->from('inventory');
		$this->db->where('item_id',$id);
		$this->db->where('rate',$rate);
		$this->db->where('location_id',$this->session->loc_id);
		$sql = $this->db->get();
		return $sql->row_array();
		
	}
}
