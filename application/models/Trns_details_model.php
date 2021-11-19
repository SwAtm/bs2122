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
		//called by Trns_summary/view_details, trns_details/check_editable, reports/print_bill
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

		public function update_purchase_quantity($id, $quantity){
		
		//called by trns_details/edit_purchase_add
		$this->db->set('quantity',$quantity);
		$this->db->where('id',$id);
		$this->db->update('trns_details');
	}	
		
		public function get_billwise_details($frdate, $todate, $s){
		//called by reports/tran_report
		$sql = $this->db->select('ts.series, ts.no, ts.date, ts.party_id, ts.party_status, ts.expenses, party.name, party.city,  party.state_io,
		round(sum(if (item_cat.name = "Books",
		(((td.rate-cash_disc)*td.quantity)-(((td.rate-cash_disc)*td.quantity)*discount/100))/(100+td.gst_rate)*100,0)),2)  
		as bamount,
		round(sum(if (item_cat.name = "Articles",
		(((td.rate-cash_disc)*td.quantity)-(((td.rate-cash_disc)*td.quantity)*discount/100))/(100+td.gst_rate)*100,0)),2)    
		as ramount,
		round  (sum((((td.rate-cash_disc)*td.quantity)-(((td.rate-cash_disc)*td.quantity)*discount/100))/(100+td.gst_rate)*td.gst_rate),2) as gst');
		$sql = $this->db->from('trns_summary as ts');
		$sql = $this->db->join('trns_details as td', 'ts.id = td.trns_summary_id');
		$sql = $this->db->join('party', 'ts.party_id = party.id');
		$sql = $this->db->join('item', 'td.item_id = item.id');
		$sql = $this->db->join('item_cat', 'item.cat_id = item_cat.id');
		$sql = $this->db->where('ts.date>=',$frdate);
		$sql = $this->db->where('ts.date<=',$todate);
		$sql = $this->db->where('ts.series=',$s); 
		$sql = $this->db->group_by('ts.series, ts.no');
		$sql = $this->db->get();
		return $sql->result_array();	
		}
		
		
		public function get_datewise_details($frdate, $todate, $s){
		//called by reports/tran_report
		/*
		$sql = $this->db->select('ts.date, round(sum(ts.expenses),2) as expenses,   
		round(sum(if (item_cat.name = "Books",	(((td.rate-cash_disc)*td.quantity)-(((td.rate-cash_disc)*td.quantity)*discount/100))/(100+td.gst_rate)*100,0)),2)  
		as bamount,
		round(sum(if (item_cat.name = "Articles",		(((td.rate-cash_disc)*td.quantity)-(((td.rate-cash_disc)*td.quantity)*discount/100))/(100+td.gst_rate)*100,0)),2)    
		as ramount,
		round  (sum((((td.rate-cash_disc)*td.quantity)-(((td.rate-cash_disc)*td.quantity)*discount/100))/(100+td.gst_rate)*td.gst_rate),2) as gst');
		$sql = $this->db->from('trns_summary as ts');
		$sql = $this->db->join('trns_details as td', 'ts.id = td.trns_summary_id', 'right outer');
		//$sql = $this->db->join('party', 'ts.party_id = party.id');
		$sql = $this->db->join('item', 'td.item_id = item.id');
		$sql = $this->db->join('item_cat', 'item.cat_id = item_cat.id');
		$sql = $this->db->where('ts.date>=',$frdate);
		$sql = $this->db->where('ts.date<=',$todate);
		$sql = $this->db->where('ts.series=',$s); 
		$sql = $this->db->group_by('ts.date');
		$sql = $this->db->get();
		*/
		$sql = "select t.date, sum(t.expenses) as texpenses, sum(d.bamount) as tbamount, sum(d.ramount) as tramount, sum(d.gst) as tgst 
		from 
		(select trns_summary.id, trns_summary.series, trns_summary.date, trns_summary.expenses from trns_summary ) as t
		join
		(select trs.id, round(sum(if (item_cat.name = \"Books\", (((td.rate-cash_disc)*td.quantity)-(((td.rate-cash_disc)*td.quantity)*discount/100))/(100+td.gst_rate)*100,0)),2) as bamount, 
		round(sum(if (item_cat.name = \"Articles\", (((td.rate-cash_disc)*td.quantity)-(((td.rate-cash_disc)*td.quantity)*discount/100))/(100+td.gst_rate)*100,0)),2) as ramount, 
		round (sum((((td.rate-cash_disc)*td.quantity)-(((td.rate-cash_disc)*td.quantity)*discount/100))/(100+td.gst_rate)*td.gst_rate),2) as gst
		from trns_summary as trs
		join trns_details as td on trs.id = td.trns_summary_id
		join item on td.item_id = item.id 
		join item_cat on item_cat.id = item.cat_id 
		group by trs.id) as d
		on t.id = d.id 
		where t.date>=? and t.date<=? and t.series  = ?
		group by t.date";
		//  and t.series = $s";	
		//where t.date>=\"$frdate\" and t.date<=\"$todate\" and t.series  = \"$s\"
		return $this->db->query($sql, array ($frdate, $todate, $s))->result_array();	
		}
		
		}
