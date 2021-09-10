<script>
	window.onload = function(){
	let title = document.querySelector('#title');
	title.focus();
	let ratehandle = document.querySelector('#rate');
	let hsnhandle = document.querySelector('#hsn');
	let gstratehandle = document.querySelector('#gstrate');
	let cbhandle = document.querySelector("#cb");
	let btnhandle = document.querySelector('#complete');
	function fillvalues(){
		cbhandle.innerHTML = '';
		let	details = title.options[title.selectedIndex].value;
		if ('' === details){
			btnhandle.disabled = false;
		} else {
			details1 = JSON.parse(details);
			ratehandle.value = details1.rate;
			hsnhandle.value = details1.hsn;
			gstratehandle.value = details1.grate;
			cbhandle.innerHTML ='Closig Balance: '+ details1.clbal + ' HSN: ' + details1.hsn + ' GST Rate: ' + details1.grate + '%';
			console.log(details1);
		
			btnhandle.disabled = true;
			
		}
}
	title.addEventListener('change',fillvalues);
	

}

</script>
<style>
.tb {

width: 100%;
border: 1px solid black;	
}
.tb tr, .tb th, .tb td {
border: 1px solid black;	
}
</style>

<table class = "tb">
<!--
<tr><th>Title</th><th style="width: 11%">Rate</th><th style="width: 11%">Quantity</th><th style="width: 11%">Discount</th><th style="width: 11%">Cash Disc</th><th style="width: 11%">HSN</th><th style="width: 11%">GST Rate</th><th style="width: 5%"></th></tr><tr>
-->
	<tr><th>Title</th><th style="width: 11%">Quantity</th><th style="width: 5%"></th></tr><tr>

<?php
$items = array();
foreach ($invent as $key) {
	$items[] = array ('title' => $key['title']. "-".$key['rate'], 'value' => json_encode($key));
}
if (isset($calling_proc) and 'edit' == $calling_proc):
	echo "<form method = POST action = ".site_url("trnf_details/edit_send/").">";
else:
	echo "<form method = POST action = ".site_url("trnf_details/send/").">";
endif;
echo "<td><Select name = item required id = title>";
echo "<option value=''>Title</option>";
foreach ($items as $key) {
	echo "<option value = '$key[value]'>$key[title]</option>";
}
echo "</select>";
echo "</td>";
?>
<td><input type = number size = 15 name = quantity value = 1 min="1"></td>
<input type = hidden id = hsn>
<input type = hidden id = gstrate>
<input type = hidden id = rate>
<td><input type = submit name = add value = Add></td></tr>
<tr><td id = cb></td>
<td align = center colspan="4"><input type = submit name =  complete id = complete formnovalidate="formnovalidate" value = 'Transfer Over'></td></tr>
<?php
//<input type="hidden" name = details value= <?php echo $invent;
echo "</form>";
echo "</table>";
/*
if (isset($details)):
echo "<table width = 100% border = 1><tr><td>Item id</td><td>Title</td><td>Rate</td><td>Quantity</td><td>Discount</td><td>Cash Disc</td><td>Amount</td></tr></tr>";
$total = 0;
foreach ($details as $key):
	$amt = (($key['rate']-$key['cash_disc'])*$key['quantity'])-((($key['rate']-$key['cash_disc'])*$key['quantity'])*$key['discount']/100);
	echo "<td>$key[item_id]</td><td>$key[title]</td><td>$key[rate]</td><td>$key[quantity]</td><td>$key[discount]</td><td>$key[cash_disc]</td><td>".number_format($amt,2)."</td></tr><tr>";
	$total += $amt;
endforeach;
echo "<tr><td colspan = 7 align = center>Total: ".number_format($total,2)."</td></tr>";
echo "</table>";
endif;*/
//echo "<input type = hidden name = amount value = $total>";

?>

