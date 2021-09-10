<?php
tfpdf();

class PDF extends tFPDF
{
// Page header
function Header()
{
     // Arial bold 15
    $this->SetFont('Arial','',13);
    // Move to the right
    
    // Title
    /*
    $this->cell(30,5,'Item ID',1,0,'C');
	$this->cell(30,5,'Item Rate',1,0,'C');
	$this->cell(90,5,'Title',1,0,'C');
	$this->cell(30,5,'Quantity',1,1,'C');*/
    // Line break
	$this->Ln(50);
    $this->cell(30,5,'Item ID',1,0,'C');
	$this->cell(30,5,'Item Rate',1,0,'C');
	$this->cell(90,5,'Title',1,0,'C');
	$this->cell(30,5,'Quantity',1,1,'C');

    //$this->Ln(5);
}

// Page footer
function Footer()
{
    // Position at 1.5 cm from bottom
    $this->SetY(-15);
    // Arial italic 8
    $this->SetFont('Arial','I',8);
    // Page number
    $this->Cell(0,10,'Page '.$this->PageNo(),0,0,'C');
}
}




$pdf = new PDF('P', 'mm', array(210,296));
$pdf->setLeftMargin(25);
//$this->SetAutoPageBreak(false);
$pdf->AddPage();
/*
$this->SetFont('Arial','B',14);
$this->Image(base_url(IMGPATH.'logo.jpg'),25,10,15,'');
$this->setXY(25,10);
$this->Cell(180,5,'Ramakrishna Mission Ashrama',0,1,'C');
$this->SetFont('Arial','',14);
$this->Cell(180,5,'Fort, Belgaum, Karnataka - 590016',0,1,'C');
$this->Cell(180,5,'Ph: 0831 2432789 / 2970320 / 321 :: email: belgaum@rkmm.org',0,1,'C');
$this->ln(2);
$this->cell(180,0,'',1,1);
$this->ln(5);
$this->SetFont('Arial','B',14);
$this->cell(180,5,'STOCK TRANSFER',0,1,'C');
$this->SetFont('Arial','',14);
$this->cell(90,5,'No: '.$trnf_summary['id'],0,0,'L');
$this->cell(90,5,'Date: '.date('d-m-Y',strtotime($trnf_summary['date'])),0,1,'R');
$this->cell(90,5,'From: '.$trnf_summary['from'],0,0,'L');
$this->cell(90,5,'To: '.$trnf_summary['to'],0,1,'L');
$this->ln(5);
$this->cell(30,5,'Item ID',1,0,'C');
$this->cell(30,5,'Item Rate',1,0,'C');
$this->cell(90,5,'Title',1,0,'C');
$this->cell(30,5,'Quantity',1,1,'C');
*/
	$pdf->Image(base_url(IMGPATH.'logo.jpg'),25,10,15,'');
	$pdf->setXY(25,10);
	$pdf->Cell(180,5,'Ramakrishna Mission Ashrama',0,1,'C');
	$pdf->SetFont('Arial','',14);
	$pdf->Cell(180,5,'Fort, Belgaum, Karnataka - 590016',0,1,'C');
	$pdf->Cell(180,5,'Ph: 0831 2432789 / 2970320 / 321 :: email: belgaum@rkmm.org',0,1,'C');
	$pdf->ln(2);
	$pdf->cell(180,0,'',1,1);
	$pdf->ln(5);
	$pdf->SetFont('Arial','',12);
	$pdf->cell(180,5,'STOCK TRANSFER',0,1,'C');
	$pdf->ln(5);
	$pdf->SetFont('Arial','',14);
	$pdf->cell(90,5,'No: '.$trnf_summary['id'],0,0,'L');
	$pdf->cell(90,5,'Date: '.date('d-m-Y',strtotime($trnf_summary['date'])),0,1,'R');
	$pdf->ln(5);
	$pdf->cell(90,5,'From: '.$trnf_summary['from'],0,0,'L');
	$pdf->cell(90,5,'To: '.$trnf_summary['to'],0,1,'L');
	$pdf->ln(10);
	$pdf->SetFont('Arial','',12);
foreach ($trnf_details as $ts):
	$pdf->cell(30,5,$ts['item_id'],1,0,'C');
	$pdf->cell(30,5,$ts['rate'],1,0,'C');
	$pdf->cell(90,5,$ts['title'],1,0,'C');
	$pdf->cell(30,5,$ts['quantity'],1,1,'C');
endforeach;
$pdf->ln(5);
$pdf->cell(90,5,'Home',0,0,'C',0,site_url('welcome/home'));
$pdf->cell(90,5,'List',0,1,'C',0,site_url('trnf_summary/summary'));
$pdf->output();







/*

	$amt = 0;
	foreach ($trns_details as $key => $v) {
		echo "<tr><td>$v[item_id]</td><td>$v[code]</td><td>$v[title]</td><td>".number_format($v['rate'],2)."</td><td>$v[quantity]</td><td>".number_format($v['discount'],2)."</td><td>".number_format($v['cash_disc'],2)."</td><td>".number_format($v['amount'],2)."</td><td>$v[hsn]</td><td>".number_format($v['gst_rate'],2)."</td></tr>";
		$amt+=$v['amount'];
	}
	echo "<tr><td colspan = 10 align = center>Amount: ".number_format($amt,2)."</td></tr>";
	echo "<tr><td colspan = 10 align = center><a href = ".site_url('trns_summary/summary').">Back to List</a href></td></tr>";
	echo "</table>";
*/
?>

</body>
</html>	
