<?php
//echo $error;
echo form_open('trnf_details/receive');
echo form_label('Please Copy file at '.SAVEPATH.' and Enter the File Name <br><br>','file_upload');
//echo form_dropdown('to_id',$loc,'',array('id'=>'to_id'));
echo "<input type = text name = file_upload size = 20>";
echo form_submit('upload','Upload');
echo form_close();
?>