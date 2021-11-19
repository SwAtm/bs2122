<?php
	class Reports extends CI_Controller{
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
		$this->load->helper('pdf_helper');
		$this->output->enable_profiler(TRUE);
		$this->load->library('Qrcodeg');
		$this->load->helper('form');
	}

	public function print_bill($id){
		$id = $this->uri->segment(3);
		$data['summary']= $this->Trns_summary_model->get_details_by_id($id);
		$data['party'] = $this->Party_model->get_details($data['summary']['party_id']);
		$data['details'] = $this->Trns_details_model->get_details($data['summary']['id']);
		$data['location'] = $this->session->loc_name;
		$gst = 0;
		$data['taxamt'] = 0;
		$data['notaxamt'] = 0;
		$data['totamount']= 0;
		foreach ($data['details'] as $det):
			if ($det['gst_rate']>0):
			$data['taxamt']+=$det['amount']/(100+$det['gst_rate'])*100;
			$gst+=$det['amount']/(100+$det['gst_rate'])*$det['gst_rate'];
			else:
			$data['notaxamt']+=$det['amount'];
			endif;
		$data['totamount']+=$det['amount'];
		endforeach;
		if ($data['party']['state']=='KARNATAKA'):
			$data['cgst'] = $data['sgst'] = $gst/2;
			$data['igst'] = 0;
		else:
			$data['igst'] = $gst;
			$data['cgst'] = $data['sgst'] = 0;
		endif;
		$data['totamount']+=$data['summary']['expenses'];
		//generate QR Code for UPI Sales
		if ($data['summary']['payment_mode_name'] == "UPI" and $data['summary']['tran_type_name'] == "Sales"):
		$text = "upi://pay?pa=".   			 // payment method.
                "8197374808@paytm".          // VPA number.
                "&am=".number_format($data['totamount'],2,".",",").       // this param is for fixed amount (non editable).
                "&pn=Yellappa%20Sunadolli".      // to showing your name in app.
                "&cu=INR".                  // Currency code.
                "&mode=02";                 // mode O2 for Secure QR Code.
                //"&trxnID=".$data['summary']['payment_mode_name'].' - '.$data['summary']['tran_type_name']. ' - '.$data['summary']['no'];
                //"&orgid=189999" +            //If the transaction is initiated by any PSP app then the respective orgID needs to be passed.
                //"&sign=MEYCIQC8bLDdRbDhpsPAt9wR1a0pcEssDaV".   // Base 64 encoded Digital signature needs to be passed in this tag
                //"Q7lugo8mfJhDk6wIhANZkbXOWWR2lhJOH2Qs/OQRaRFD2oBuPCGtrMaVFR23t"
		$file = SAVEPATH.'/qrc.png';
		$ecc = 'L';
		$pixel_Size = 40;
		$frame_Size = 0;
		QRcode::png($text, $file, $ecc, $pixel_Size, $frame_Size);
		endif;
		
		$this->load->view('reports/print_bill',$data);
		
		
		}
		
		public function tran_report(){
		//set validation rules
		$this->form_validation->set_rules('frdate', 'From Date', 'required');
		$this->form_validation->set_rules('todate', 'To Date', 'required');
		$this->form_validation->set_rules('rtype', 'Type of Report', 'required');
		$this->form_validation->set_rules('ttype[]', 'Type of Transaction', 'required');
		$data['series'] = $this->Series_model->get_all_series_by_location();
		//first pass
		if ($this->form_validation->run()==false):
			$this->load->view('templates/header');
			$this->load->view('reports/tran_report',$data);
			$this->load->view('templates/footer');
		//submitted, validated
		else:
			//print_r($_POST);
			//var_dump($_POST);
			$series=$this->input->post('ttype');
			//$frdate = DateTime::createFromFormat('Y-m-d',$_POST['frdate'])->format('Y-m-d');
			$frdate=date('Y-m-d',strtotime($this->input->post('frdate')));
			$todate=date('Y-m-d',strtotime($this->input->post('todate')));
			//var_dump($frdate);
			if ('bill' == $this->input->post('rtype')):
			foreach ($series as $s):
			$details[$s]['det'] = $this->Trns_details_model->get_billwise_details($frdate, $todate, $s);
			$ser = $this->Series_model->get_details_from_series($s);
			$details[$s]['name'] = $ser['payment_mode_name']." - ".$ser['tran_type_name'];
			endforeach;
			$data['details']=$details;
			$this->load->view('reports/billwise', $data);
			else:
			foreach ($series as $s):
			$details[$s]['det'] = $this->Trns_details_model->get_datewise_details($frdate, $todate, $s);
			$ser = $this->Series_model->get_details_from_series($s);
			$details[$s]['name'] = $ser['payment_mode_name']." - ".$ser['tran_type_name'];
			endforeach;
			$data['details']=$details;
			$this->load->view('reports/datewise', $data);
			endif;
			$this->load->view('templates/footer');
				
		endif;
			
		
		
		
		}


}
?>
