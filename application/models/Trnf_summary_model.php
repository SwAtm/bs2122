<?php
class Trnf_summary_model extends CI_Model{
	public function __construct()
		{		
		$this->load->database();
	}

	public function get_details_by_id($pk){
	//called by trnf_summary/summary
	$sql=$this->db->select('*');
	$sql=$this->db->from('trnf_summary');
	//$sql=$this->db->join('series','trns_summary.series = series.series');
	$sql=$this->db->where('id',$pk);
	$sql=$this->db->get();
	return $sql->row_array();	
	}

	public function add($data){
	//called by trnf_details/send_complete	
	if($this->db->insert('trnf_summary',$data)):
		return true;
	else:
		return false;
	endif;

	}


	public function get_max_id(){
	//called by trnf_details/send_complete	
	$sql=$this->db->select_max('id');
	$sql=$this->db->from('trnf_summary');
	$sql=$this->db->get();
	return $sql->row_array();
	}


	public function trnf_summary_per_id($id){
		//called by Trnf_summary/view_details
		$this->db->select('ts.id, ts.date, lfr.name as from, lto.name as to');
		$this->db->from('trnf_summary ts');
		$this->db->join('locations lfr', 'ts.from_id = lfr.id');
		$this->db->join('locations lto', 'ts.to_id = lto.id');
		$this->db->where('ts.id',$id);
		$sql = $this->db->get();
		return $sql->row_array();
	}



//Useless since copied from elsewhere
	/*
	public function get_max_no($sr){
	//called by trns_details/other_complete_details, trns_details/purch_complete_details
	$sql=$this->db->select_max('no');
	$sql=$this->db->from('trns_summary');
	$sql=$this->db->where('series',$sr);
	$sql=$this->db->get();
	return $sql->row_array();
	}

	public function add($data){
	//called by trns_details/other_complete_details, trns_details/purch_complete_details
	if($this->db->insert('trns_summary',$data)):
		return true;
	else:
		return false;
	endif;
		
	}

	public function get_max_id(){
	//called by trns_details/other_complete_details, trns_details/purch_complete_details
	$sql=$this->db->select_max('id');
	$sql=$this->db->from('trns_summary');
	$sql=$this->db->get();
	return $sql->row_array();	
	}

	public function get_details_by_id($pk){
		//called by trns_summary/summary1, trns_details/check_editable
	$sql=$this->db->select('trns_summary.*, series.tran_type_name, series.payment_mode_name');
	$sql=$this->db->from('trns_summary');
	$sql=$this->db->join('series','trns_summary.series = series.series');
	$sql=$this->db->where('trns_summary.id',$pk);
	$sql=$this->db->get();
	return $sql->row_array();	
	}

	public function update($data, $id){
	//called by trns_summary/summary1
	$sql=$this->db->where('id',$id);
	
	if($sql=$this->db->update('trns_summary',$data)):
		return true;
	else:
		return false;
	endif;
}

*/	


}
