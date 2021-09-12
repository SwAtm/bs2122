<?php
class Trns_details_model extends CI_Model{
	public function __construct()
		{		
		$this->load->database();
	}

	public function add($data){
	//called by trns_details/sales_complete_details, trns_details/edit_purchase_add, trns_details/edit_sales_add, trns_details/purch_complete_details
	if($this->db->insert('trns_details',$data)):
		return true;
	else:
		return false;
	endif;
		
	}

	public function get_details($pk){
		//called by Trns_summary/view_details, trns_details/check_editable
		$sql = $this->db->select('td.id, td.item_id,item.title,item.code,td.rate,td.quantity,td.discount,td.cash_disc,td.hsn,td.gst_rate,((rate-cash_disc)*quantity)-((rate-cash_disc)*quantity)*discount/100 as amount, td.trns_summary_id, td.inventory_id');
		$sql = $this->db->from('trns_details as td');
		$sql = $this->db->join('item','item.id = td.item_id');
		$sql = $this->db->where('td.trns_summary_id',$pk);
		$sql = $this->db->get();
		return $sql->result_array();

	}


		public function get_details_to_delete_purchase($pk){
		//called by trns_details/check_editable
		$sql = $this->db->select('td.id, td.item_id,item.title,item.code,td.rate,td.quantity,td.discount,td.cash_disc,td.hsn,td.gst_rate,((td.rate-cash_disc)*td.quantity)-((td.rate-cash_disc)*td.quantity)*discount/100 as amount, td.trns_summary_id, td.inventory_id');
		$sql = $this->db->from('trns_details as td');
		$sql = $this->db->join('item','item.id = td.item_id');
		$sql = $this->db->join('inventory','td.inventory_id = inventory.id');
		$sql = $this->db->where('td.trns_summary_id',$pk);
		$sql = $this->db->where('inventory.out_qty',0);
		$sql = $this->db->get();
		return $sql->result_array();
}

	public function confirm_one_entry($field, $value){
		//called by trns_details/check_editable
		$sql = $this->db->where($field,$value);
		$sql = $this->db->from('trns_details');
		$sql = $this->db->count_all_results();
		if ($sql>1):
			return false;
		else:
			return true;
		endif;


	}


	public function delete($id){
		//called by trns_details/edit_purchase_add, trns_details/edit_sales_add
		$sql = $this->db->where('id',$id);
		if ($sql = $this->db->delete('trns_details')):
			return true;
		else:
			return false;
		endif;
	}

		public function get_trans($id, $rate){
		//called by item/det_stck
		$sql = $this->db->select('series.payment_mode_name, series.tran_type_name, trns_summary.series, trns_summary.date, trns_details.trns_summary_id, trns_details.item_id, trns_details.rate, trns_details.quantity, item.title ');
		$sql = $this->db->from('trns_details');
		$sql = $this->db->join('trns_summary', 'trns_details.trns_summary_id=trns_summary.id');
		$sql = $this->db->join('series', 'trns_summary.series = series.series');
		$sql = $this->db->join('item', 'trns_details.item_id=item.id');
		$sql = $this->db->join('inventory', 'trns_details.inventory_id=inventory.id');
		$sql = $this->db->where('trns_details.item_id', $id);
		$sql = $this->db->where('trns_details.rate', $rate);
		$sql = $this->db->where('inventory.location_id', $this->session->loc_id);
		$sql = $this->db->get();
		return $sql->result_array();
		}
}
