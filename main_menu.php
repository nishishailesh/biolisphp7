<?php 
session_start();

echo '<html>';
echo '<head>';
echo '<title></title>';
echo '</head>';
echo '<body>';


//echo '<pre>';
//print_r($GLOBALS);
//echo '</pre>';

if(isset($_POST['login']))
{
$_SESSION['login']=$_POST['login'];
}

if(isset($_POST['password']))
{
$_SESSION['password']=$_POST['password'];
}

include 'common.php';

if(!login_varify())
{
exit();
}

$remote_user=array(
'Dr.Shailesh',
'Dr.Manmeet',
'Dr.Saritamam',
'Dr.Sarita',
'Dr.Piyush',
'Dr.Shilpi',
'doctor'
);

if($_SERVER['REMOTE_ADDR']=='12.207.3.241')
{
	if(!in_array($_SESSION['login'],$remote_user))
	{
		echo 'You are not authorized to access outside LAN';
		exit(0);
	}
}


if($_SESSION['login']=='doctor')
{
	echo '<table><tr><td bgcolor=lightblue><a href=print_report_doctor.php>Biochemistry Report</a></td>';
	echo '<td bgcolor=lightblue><a href=logout.php>Logout</a></td>';
	echo '<td bgcolor=lightpink><a href=doctor_help.html target=_blank>HELP</a></td></tr></table>';
}
else
{
main_menu();
}

?>
