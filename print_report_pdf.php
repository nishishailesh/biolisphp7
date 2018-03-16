<?php
session_start();

/*
echo '<html>';
echo '<head>';
echo '</head>';
echo '<body>';
*/
/*
echo '<pre>';
print_r($GLOBALS);
echo '</pre>';
*/

include 'common.php';
require_once('tcpdf/config/lang/eng.php');
require_once('tcpdf/tcpdf.php');

class MYPDF_NABL extends TCPDF {
	public $lab_name='New Civil Hospital Surat Laboratory Services';
	public $section_name='Biochemistry Section';
	public $address_phone='2nd Floor, Near Blood Bank, NCH Surat(Guj) Ph: 2224445 Ext:317,366';
	public $nabl_symbol='nabl.jpg';
	public $blank_symbol='blank.jpg';
	public $nabl_cert_no='M-0450';
	public $blank_cert_no='';
	public $bypass_autoverification='no';		//if 'yes'=>it will bypass autoverification
	public $sample_id_array;
	public $sample_id;
	public $doctor;
	public $login;
	//Page header W=210 H=148
	//$this->Write(0,$this->getPageWidth());
	public function Header() 
	{
		$this->Rect(10, 10,190,30, $style='', $border_style=array(), $fill_color=array());
		//write six lines in header
		//all header things XY between 10,10 and 200,40
		//public function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='', $stretch=0, 
		//						$ignore_min_height=false, $calign='T', $valign='M')
		$this->SetFont('courier', 'B', 10);
		$this->SetXY(10,10);
		$this->Cell(190, $h=0, $txt=$this->lab_name.' ('.$this->section_name.')', $border=0, $ln=0, $align='C', $fill=false, $link='', $stretch=1, $ignore_min_height=false, $calign='T', $valign='M');

		$this->SetFont('courier', '', 10);
		$this->SetXY(10,15);
		$this->Cell(190, $h=0, $txt=$this->address_phone, $border=0, $ln=0, $align='C', $fill=false, $link='', $stretch=1, $ignore_min_height=false, $calign='T', $valign='M');

///////////Print NABL symbol if any one is accredited
		$linkk=start_nchsls();
		$sql_sample_data='select * from sample where sample_id='.$this->sample_id;
		$sql_examination_data='select * from examination where sample_id=\''.$this->sample_id.'\' order by name_of_examination';
		$NABL_acc_counter=0;
		$result_examination_data_for_accr=mysql_query($sql_examination_data,$linkk);
		while($acc_array=mysql_fetch_assoc($result_examination_data_for_accr))
		{
			if($acc_array['NABL_Accredited']=='Yes')
			{
				$NABL_acc_counter++;
			}
		}
		if($NABL_acc_counter>0)
		{
			$image_file = $this->nabl_symbol;
			$this->Image($image_file, 10, 10,275/12, 320/12, 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
	
			$this->SetXY(10,36);//(Y=10+(320/12)=36)
			$this->Cell(275/12, $h=0, $txt=$this->nabl_cert_no, $border=0, $ln=0, $align='C', $fill=false, $link='', $stretch=1, $ignore_min_height=false, $calign='T', $valign='M');
		}

//////////////////Print Sample data
		if(mysql_num_rows($result_sample_data=mysql_query($sql_sample_data,$linkk))>0)
		{
			$border=0;
			$sample_array=mysql_fetch_assoc($result_sample_data);
			////line 1
			$this->SetFont('courier', 'B', 10);
			$this->SetXY(35,20); //275/12=22 22+10=32
			//210-10-35=165 remaining//55 name,mrd,sampleif=165 
			$this->Cell(54, $h=0, $txt='Patient Name: '.$sample_array['patient_name'], $border, $ln=0, $align='L', $fill=false, $link='', $stretch=1, $ignore_min_height=false, $calign='T', $valign='M');

			//35+55=90
			$this->SetXY(90,20); //275/12=22 22+10=32
			$this->Cell(54, $h=0, $txt='MRD: '.$sample_array['patient_id'], $border, $ln=0, $align='L', $fill=false, $link='', $stretch=1, $ignore_min_height=false, $calign='T', $valign='M');

			//90+55=145
			$this->SetXY(145,20); //275/12=22 22+10=32
			//210-35=175 remaining//55 name,mrd,sampleif=165+ 5,5 space 
			$this->SetFont('courier', 'B', 16);
			$this->Cell(54, $h=0, $txt='Sample ID: '.$sample_array['sample_id'], $border, $ln=0, $align='L', $fill=false, $link='', $stretch=1, $ignore_min_height=false, $calign='T', $valign='M');
			
			$this->SetFont('courier', '', 10);
			////line 2
			$this->SetXY(35,25); //275/12=22 22+10=32
			//210-10-35=165 remaining//55 name,mrd,sampleif=165 
			$this->Cell(54, $h=0, $txt='Received: '.$sample_array['sample_receipt_time'], $border, $ln=0, $align='L', $fill=false, $link='', $stretch=1, $ignore_min_height=false, $calign='T', $valign='M');

			//35+55=90
			$this->SetXY(90,25); //275/12=22 22+10=32
			$this->Cell(54, $h=0, $txt='Reported: '.strftime('%Y-%m-%d %H:%M:%S'), $border, $ln=0, $align='L', $fill=false, $link='', $stretch=1, $ignore_min_height=false, $calign='T', $valign='M');

			//90+55=145
			$this->SetXY(145,25); //275/12=22 22+10=32
			//210-35=175 remaining//55 name,mrd,sampleif=165+ 5,5 space 
			$this->Cell(54, $h=0, $txt=$sample_array['clinician'].' Unit:'.$sample_array['unit'].' '.$sample_array['location'], $border, $ln=0, $align='L', $fill=false, $link='', $stretch=1, $ignore_min_height=false, $calign='T', $valign='M');


			////line 3
			$this->SetXY(35,30); //275/12=22 22+10=32
			//210-10-35=165 remaining//55 name,mrd,sampleif=165 
			$this->Cell(54, $h=0, $txt='Sample Type: '.$sample_array['sample_type'], $border, $ln=0, $align='L', $fill=false, $link='', $stretch=1, $ignore_min_height=false, $calign='T', $valign='M');

			//35+55=90
			$this->SetXY(90,30); //275/12=22 22+10=32
			$this->Cell(54, $h=0, $txt='Preservative: '.$sample_array['preservative'], $border, $ln=0, $align='L', $fill=false, $link='', $stretch=1, $ignore_min_height=false, $calign='T', $valign='M');

			//90+55=145
			$this->SetXY(145,30); //275/12=22 22+10=32
			//210-35=175 remaining//55 name,mrd,sampleif=165+ 5,5 space 
			$this->Cell(54, $h=0, $txt=$sample_array['sample_details'], $border, $ln=0, $align='L', $fill=false, $link='', $stretch=1, $ignore_min_height=false, $calign='T', $valign='M');


			////line 4
			$this->SetXY(35,35); //275/12=22 22+10=32
			//210-10-35=165 remaining//55 name,mrd,sampleif=165 
			$this->Cell(109, $h=0, $txt='Collection Time/Age/Sex/Dx: '.$sample_array['details'], $border, $ln=0, $align='L', $fill=false, $link='', $stretch=1, $ignore_min_height=false, $calign='T', $valign='M');

			//90+55=145
			$this->SetFont('courier', 'B', 10);
			$this->SetXY(145,35); //275/12=22 22+10=32
			//210-35=175 remaining//55 name,mrd,sampleif=165+ 5,5 space 
			$this->Cell(54, $h=0, $txt='Status: '.$sample_array['status'], $border, $ln=0, $align='L', $fill=false, $link='', $stretch=1, $ignore_min_height=false, $calign='T', $valign='M');
			$this->SetFont('courier', '', 10);
		}
				$border=1;
				$counter=45;
				$this->SetFont('courier', 'B', 10);
				$this->SetXY(10,$counter);
				$this->Cell($w=10, $h=0, 'NABL Accr.',$border, $ln=0, $align='', $fill=false, $link='', 
					$stretch=1, $ignore_min_height=false, $calign='T', $valign='M');
				
				$this->SetXY(20,$counter);
				$this->Cell($w=40, $h=0, 'Examination',$border, $ln=0, $align='', $fill=false, $link='', 
					$stretch=1, $ignore_min_height=false, $calign='T', $valign='M');

				$this->SetXY(60,$counter);
				$this->Cell($w=40, $h=0, 'Result',$border, $ln=0, $align='', $fill=false, $link='', 
					$stretch=1, $ignore_min_height=false, $calign='T', $valign='M');

				$this->SetXY(100,$counter);
				$this->Cell($w=40, $h=0, 'Referance range',$border, $ln=0, $align='', $fill=false, $link='', 
					$stretch=1, $ignore_min_height=false, $calign='T', $valign='M');

				$this->SetXY(140,$counter);
				$this->Cell($w=20, $h=0,'Alert',$border, $ln=0, $align='', $fill=false, $link='', 
					$stretch=1, $ignore_min_height=false, $calign='T', $valign='M');
				
				$this->SetXY(160,$counter);					
				$this->Cell($w=40, $h=0,'Method',$border, $ln=0, $align='', $fill=false, $link='', 
					$stretch=1, $ignore_min_height=false, $calign='T', $valign='M');		
		
	}

	// Page footer
	public function Footer() 
	{
		$border=1;
		$this->SetFont('courier', 'B', 10);
		$this->SetXY(10,-10);
		$this->Cell(95, $h=0, $txt='Examinations marked \'No\' are not NABL Accredited.', $border, $ln=0, $align='L', $fill=false, $link='', $stretch=1, $ignore_min_height=false, $calign='T', $valign='M');
		$this->SetFont('courier', '', 10);
		$this->SetXY(105,-10);
		$this->Cell(95, $h=0, $txt='Page:'.$this->getPageNumGroupAlias().'/'.$this->getPageGroupAlias(), $border, $ln=0, $align='R', $fill=false, $link='', $stretch=1, $ignore_min_height=false, $calign='T', $valign='M');

		//this start at Y=127
		//210/4=52 50-20=30,70   130,170
		$border=1;
		$this->SetFont('courier', '', 10);
		$this->SetXY(10,-20);
		$this->Cell(95, $h=10, $txt=$this->login, $border, $ln=0, $align='L', $fill=false, $link='', $stretch=1, $ignore_min_height=false, $calign='T', $valign='M');
		$this->SetXY(105,-20);
		$this->Cell(95, $h=10, $txt=$this->doctor, $border, $ln=0, $align='L', $fill=false, $link='', $stretch=1, $ignore_min_height=false, $calign='T', $valign='M');


	}
}

function print_report_pdf_A5($sample_id_array,$doctor)
{
	$acr_check_code=array(	'-1'=>'',
					'-2'=>'',
					'-3'=>'',
					'0'=>'',
					'1'=>'low absurd',
					'2'=>'high absurd',					
					'3'=>'low critical',
					'4'=>'high critical',
					'5'=>'',
					'6'=>'');
	//A5=210,148
	$pdf = new MYPDF_NABL('L', 'mm', 'A5', true, 'UTF-8', false);
	$pdf->sample_id_array=$sample_id_array;
	$pdf->doctor=$doctor;
	$pdf->login=$_SESSION['login'];
	
	// set default courierspaced font
	//$pdf->SetDefaultcourierspacedFont(PDF_FONT_courierSPACED);
	//set margins
	$pdf->SetMargins(10, 50);
	//set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, 10);

	//set image scale factor
	//$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

	//$pdf->SetFont('times', '', 10);
	$pdf->SetFont('courier', '', 8);
	
	
	foreach($sample_id_array as $value)
	{
		$pdf->sample_id=$value;
		$pdf->startPageGroup();
		$pdf->AddPage();
		
		$linkk=start_nchsls();
		$sql_examination_data='select * from examination where sample_id=\''.$pdf->sample_id.'\' order by name_of_examination';
		$result_examination_data=mysql_query($sql_examination_data,$linkk);
		$counter=45;
		$pdf->SetFont('courier','',10);
		$border=0;
		while($examination_array=mysql_fetch_assoc($result_examination_data))
		{
			$counter=$counter+5;
			if($examination_array['id']<1000)
			{	//available 190 mm
				//10+40+40+40+20+40
				$pdf->SetFont('courier','',10);
				$pdf->SetXY(10,$counter);
				$pdf->Cell($w=10, $h=0, $examination_array['NABL_Accredited'],$border, $ln=0, $align='', $fill=false, $link='', 
					$stretch=1, $ignore_min_height=false, $calign='T', $valign='M');
				
				$pdf->SetXY(20,$counter);
				$pdf->Cell($w=40, $h=0, $examination_array['name_of_examination'],$border, $ln=0, $align='', $fill=false, $link='', 
					$stretch=1, $ignore_min_height=false, $calign='T', $valign='M');

				$pdf->SetXY(60,$counter);
				$pdf->Cell($w=40, $h=0, $examination_array['result'],$border, $ln=0, $align='', $fill=false, $link='', 
					$stretch=1, $ignore_min_height=false, $calign='T', $valign='M');

				$pdf->SetXY(100,$counter);
				$pdf->Cell($w=40, $h=0, $examination_array['referance_range'].' '.$examination_array['unit'],$border, $ln=0, $align='', $fill=false, $link='', 
					$stretch=1, $ignore_min_height=false, $calign='T', $valign='M');

				$pdf->SetXY(140,$counter);
				$acr=$acr_check_code[check_critical_abnormal_reportable($examination_array['sample_id'],$examination_array['code'])];
				$pdf->Cell($w=20, $h=0,$acr,$border, $ln=0, $align='', $fill=false, $link='', 
					$stretch=1, $ignore_min_height=false, $calign='T', $valign='M');
				
				$pdf->SetXY(160,$counter);					
				$pdf->Cell($w=40, $h=0,$examination_array['method_of_analysis'],$border, $ln=0, $align='', $fill=false, $link='', 
					$stretch=1, $ignore_min_height=false, $calign='T', $valign='M');
				if($counter>=120){$counter=45;$pdf->AddPage();}
			}
			else
			{
				$pdf->SetFont('courier', 'B', 10);
				$pdf->SetXY(10,$counter);
				$pdf->Cell($w=50, $h=0, trim($examination_array['name_of_examination'],'Z_'),$border, $ln=0, $align='', $fill=false, $link='', 
				$stretch=1, $ignore_min_height=false, $calign='T', $valign='M');
				$pdf->SetFont('courier','',10);
				$pdf->SetXY(60,$counter);
				$pdf->Cell($w=140, $h=0, $examination_array['result'],$border, $ln=0, $align='', $fill=false, $link='', 
				$stretch=1, $ignore_min_height=false, $calign='T', $valign='M');
				if($counter>=120){$counter=45;$pdf->AddPage();}
			}
		}
	
	}
	
	$pdf->Output('report.pdf', 'I');
}

/////////// Report specific data////////////////
/*
$lab_name='New Civil Hospital Surat Laboratory Services';
$section_name='Biochemistry Section';
$address_phone='2nd Floor, Near Blood Bank, NCH Surat(Guj) Ph: 2224445 Ext:317,366';
$nabl_symbol='nabl.jpg';
$blank_symbol='blank.jpg';
$nabl_cert_no='Cert. No:X-1234';
$blank_cert_no='';
$bypass_autoverification='no';		//if 'yes'=>it will bypass autoverification
*/


if(!login_varify())
{
exit();
}

function search_form($filename)
{
	$link=start_nchsls();
	$sql='desc sample';
	if(!$result=mysql_query($sql,$link)){echo mysql_error();}
	$tr=1;
	echo '<table border=1><form action=\''.$filename.'\' target=_blank method=post>';
	echo '	<tr>
				<td title=\'1) Tickmark to include the field for search. 2) Use % as wildcard. e.g. [%esh = Mahesh,Jignesh] [Mahesh%=Mahesh,Maheshbhai, Maheshkumar]\'><input type=submit name=submit value=print></td>';
	/*echo '			<td>Technician:</td><td>';
					mk_select_from_table('technician','','');
		  echo '</td>';
	*/
		echo   '<td>Autorized Signatory:</td><td>';
					mk_select_from_table('authorized_signatory','','');
		  echo '</td>';
		  echo '</tr>';
	while($ar=mysql_fetch_assoc($result))
	{
		if($tr%3==1){echo '<tr>';}
		
		if($ar['Field']=='sample_id')
		{
			echo '<td><input type=checkbox checked name=\'chk_from_'.$ar['Field'].'\' ></td><td>from_'.$ar['Field'].'</td>';
			echo '<td><input type=text name=\'from_'.$ar['Field'].'\' ></td>';
			echo '<td><input type=checkbox name=\'chk_to_'.$ar['Field'].'\' ></td><td>to_'.$ar['Field'].'</td>';
			echo '<td><input type=text name=\'to_'.$ar['Field'].'\' >';
			$tr++;
		}
		
		else
		{		
			echo '<td><input type=checkbox name=\'chk_'.$ar['Field'].'\' ></td><td>'.$ar['Field'].'</td><td>';
			if(!mk_select_from_table($ar['Field'],'',''))
			{
				  echo '<input type=text name=\''.$ar['Field'].'\' >';
			}
		}
		echo '</td>';
		if($tr%3==0){echo '</tr>';}
		$tr++;
	}
	echo '</form></table>';
}






	


$search_str='select sample_id from sample '; 
$where=array();

if(isset($_POST['submit']))
{
	foreach($_POST as $key=>$value)
	{
		if(substr($key,0,4)=='chk_' && $value=='on')
		{
			//echo substr($key,4).'='.$_POST[substr($key,4)].'<br>';
			$where[substr($key,4)]=$_POST[substr($key,4)];
		}
	}
}

//print_r($where);

$sample_id_where='';
if(isset($where['from_sample_id']) && isset($where['to_sample_id']) )
{
$sample_id_where='sample_id between  \''.$where['from_sample_id'].'\' and \''.$where['to_sample_id'].'\' ';
}
elseif(isset($where['from_sample_id']))
{
$sample_id_where=' sample_id=\''.$where['from_sample_id'].'\' ';
}
elseif(isset($where['to_sample_id']))
{
$sample_id_where=' sample_id=\''.$where['to_sample_id'].'\' ';
}

$other_wheree='';
foreach($where as $key=>$value)
{
	if($key!='from_sample_id' && $key!='to_sample_id' )
	{
		$other_wheree=$other_wheree.' '.$key.' like \''.$value.'\' and';
	}
}
$other_where=substr($other_wheree,0,-3);


if(strlen($sample_id_where)>0 && strlen($other_where)>0)
{
$search_str=$search_str.' where '.$sample_id_where.' and '.$other_where;
}
elseif(strlen($sample_id_where)>0 && strlen($other_where)==0)
{
$search_str=$search_str.' where '.$sample_id_where;
}
elseif(strlen($sample_id_where)==0 && strlen($other_where)>0)
{
$search_str=$search_str.' where '.$other_where;
}

$printed=array();

if(isset($_POST['submit']) && substr($search_str,-7)!='sample ')
{
	$link=start_nchsls();
	if(!$search_result=mysql_query($search_str,$link)){echo mysql_error();}
	while($ar=mysql_fetch_assoc($search_result))
	{
		$printed[]=$ar['sample_id'];
	}
	foreach($printed as $value)
	{
		if(get_sample_status($value)!='verified')
		{
			echo $value.' is not verified. PDF report can not be printed<br>';
		}
	}
	print_report_pdf_A5($printed,$_POST['authorized_signatory']);
}
else
{
	main_menu();
	search_form('print_report_pdf.php');
	echo '<h1>No coditions are given for selecting records</h1>';
}


?>
