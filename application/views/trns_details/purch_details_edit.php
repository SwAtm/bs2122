<script>
window.onload = function(){
var pr = document.querySelector('#party');
pr.focus();	}
</script>
<style>
#tbb {
table-layout: fixed;
width: 100%;
border: 1px solid black;	
}
th {
	background-color: yellow;
	position: sticky; 
    top: 0px; 
    padding: 0px 0px; 
    height: 10px;
    font-size: small;
}
</style>
<?php

if (!isset($_SESSION['expenses'])){
	$_SESSION['expenses'] = 0;
}
if (!isset($_SESSION['remark'])){
	$_SESSION['remark'] = '';
}
echo "<table width=100% border=1><tr><td align = center><a href = ".site_url('trns_details/purch_add_details').">Add New Item</a href></td></tr>";
echo "<tr><td align = center colspan = 8>You can edit/delet the details below</td></tr></table>";
//echo "<td>Title</td><td style=width:10%>Rate</td><td style=width:10%>Quantity</td><td style=width:10%>Discount</td><td style=width:10%>Cash Disc</td><td style=width:10%>HSN</td><td style=width:10%>GST Rate</td><td style=width:5%>Delet</td></tr><tr>";
?>

<table id = tbb border = 1>
<th style="width: 35%">Title</th><th style="width: 10%">Rate</th><th style="width: 10%" >Quantity</th><th style="width: 10%" >Discount</th><th style="width: 10%">Cash Disc</th><th style="width: 10%">HSN</th><th style="width: 10%">GST Rate</th><th style="width: 5%">Delet</th>
<?php
echo "<form method = POST action = ".site_url('trns_details/purch_edit_details').">";
echo "<tr>";
$i = 0;
foreach ($details as $key => $value) {
echo "<td><Select name = det[$i][title] required id = title>";
echo "<option value=''>Title</option>";
foreach ($item as $key => $val) {
	echo "<option value = $val[id] ";
	if ($val['id']==$value['title']){
	echo " selected = selected";
	}
	echo ">$val[title]</option>";
}
echo "</td>";
//echo "</select>";
echo "<td style='width:10%'><input type = number size = 13 maxlength = 11 name = det[$i][rate] required step = 0.01 value = $value[rate]></td>";
echo "<td style=width:10%><input type = number size = 13 name =det[$i][quantity] required value = $value[quantity]></td>";
echo "<td style=width:10%><input type = number size = 13 name = det[$i][discount] step = 0.01 placeholder = 0.00 value = $value[discount]></td>";
echo "<td style=width:10%><input type = number size = 13 name = det[$i][cash_disc] step = 0.01 placeholder = 0.00 value = $value[cash_disc]></td>";
echo "<td style=width:10%><input type = number size = 13 maxlength = 14 name = det[$i][hsn] required value = $value[hsn]></td>";
echo "<td style=width:10%><input type = number size = 13 name = det[$i][gstrate] required step = 0.01 placeholder = 0.00 value = $value[gstrate]></td>";
echo "<td style=width:5%><input type = checkbox id = delete name = det[$i][delete] value = 1></td></tr>";
$i++;
}
echo "<tr>";
echo "<td style='width: 55%' colspan = 3><Select name = party required id = party>";
echo "<option value=''>Select Party</option>";
foreach ($party as $key => $value) {
	echo "<option value = $value[id]";

	if (isset($_SESSION['party_id']) && $_SESSION['party_id'] == $value['id'] ) {
	echo " selected = selected";
	}
	echo ">$value[name] - $value[city]</option>";
}
echo "</td>";
echo "<td style='width: 20%' colspan = 2>Expenses: <input type = number id = expenses name = expenses span = 0.01 value = $_SESSION[expenses]></td>";
echo "<td style-'width: 25%' colspan = 3>Remark: <input type = text id = remark name = remark value = $_SESSION[remark]></td></tr>";
echo "<tr><td colspan = 8 align = center>Total Bill Amount is ".number_format($amount, 2)."</td></tr>";
//echo "";
//echo "<tr><td>$_SESSION[xyz]</td></tr>";
echo "<tr><td style='width: 55%' colspan = 3 align = center><input type = submit name =  finalize value = 'Finalize'></td>";
echo "<td style='width: 45%' colspan = 5 align = center><input type = submit name =  update value = 'Update'></td></tr></table>";
echo "</form>";

?>
