<?php
session_start();
  // error_reporting(E_ALL);
  // ini_set("display_errors", 1);
// functions
function randomNumber($length) {
    $result = '';
    for($i = 0; $i < $length; $i++) {
        $result .= mt_rand(0, 9);
    }
    return $result;
}
//

$dob="0000-00-00"; //if not captured on form set to zero
//$conn = mysqli_connect("localhost", "root", "", "vend"); 
// for local db	 	
	 	//$conn = mysqli_connect('localhost','trialurgdz_2','KeaSWaS6kb8', 'trialurgdz_db2') or die(mysqli_error());
//
//LIVE DB deatils on 129.232.168.188 
$conn = mysqli_connect('129.232.168.188','vend','4nqBRh@jPE', 'vend') or die(mysqli_error());
// for big brother
$conn1 = mysqli_connect('129.232.136.190', 'bigbrother','bigbrother123','bigbrother') or die(mysqli_error());


//
// POST variables
// $campaign = $_POST['campaign'];
// $client = $_POST['client'];
// $brand = $_POST['brand'];
// $frm = $_POST['frm'];
// $stdq = $_POST['stdq'];
//
$campaign = '19';
$client = '9';
$brand = '19';
 
//
$sql1 = "SELECT ca_client FROM campaigns WHERE ca_id = '$campaign'";
$result1 = mysqli_query($conn, $sql1) or die(mysqli_error($conn));
$number_of_rows1 = mysqli_num_rows($result1);
if ( $number_of_rows1 != 0)
{
	$newArray1 = mysqli_fetch_assoc($result1);
	$ca_client = $newArray1['ca_client'];
}
//
$zone=10;
//============ check if person exists 
$sqla = "SELECT * FROM sign_ups WHERE su_claim_machine > '0' AND (su_campaign = '15' OR su_campaign = '16' OR su_campaign = '17' OR su_campaign = '18') GROUP BY su_person";
$resulta = mysqli_query($conn, $sqla) or die(mysqli_error($conn));
$number_of_rowsa = mysqli_num_rows($resulta);
if ( $number_of_rowsa != 0)
{
	$no_sms = 0;
	while ($newArraya = mysqli_fetch_assoc($resulta))
	{
	$su_person = $newArraya['su_person'];
	$coffee = $newArraya['su_campaign'];
    if($coffee == '15')
    {
        $who = 'Americano';
    }
    if($coffee == '16')
    {
        $who = 'Cappuccino';
    }
    if($coffee == '17')
    {
        $who = 'Latte';
    }
    if($coffee == '18')
    {
        $who = 'Espresso';
    }	
	//============= now check if he has registered for this campaign
	$sqlx = "SELECT * FROM person WHERE pe_id = '$su_person'";
	$resultx = mysqli_query($conn, $sqlx) or die(mysqli_error($conn));
	$number_of_rowsx = mysqli_num_rows($resultx);
	if ( $number_of_rowsx != 0)  // NB we check if nothing there **
	{
		$newArrayx = mysqli_fetch_assoc($resultx);
		$pe_id = $newArrayx['pe_id'];
        $mobile = $newArrayx['pe_mobile'];
        $pe_fname = $newArrayx['pe_fname'];
		// Not Found so can INSERT records - first get entry code 8 digits and format to 12
		$length=6;
		$entry = randomNumber($length);
		$entry = str_pad($entry, 12, '0', STR_PAD_LEFT);
		// make sure it does not exists in the db yet
		$try_again=1;
		while($try_again==1)
		{
			$sql1 = "SELECT su_id FROM sign_ups WHERE su_entry_code = '$entry'";
			$result1 = mysqli_query($conn, $sql1) or die(mysqli_error($conn));
			$number_of_rows1 = mysqli_num_rows($result1);
			if ( $number_of_rows1 == 0)  // NB we check if nothing there **
			{
				$sql2 = "INSERT INTO sign_ups VALUES ('', '$ca_client', '$brand', '$coffee', '$pe_id', '$entry', now(), '0', '0000-00-00 00:00:00', '0', '0')";
			//	echo "*$sql2*<br />";
				$result2 = mysqli_query($conn, $sql2) or die(mysqli_error($conn));
				$sign_up = mysqli_insert_id($conn);	
				$try_again=9;
				$no_sms=9;
			}
			else
			{
				$length=6;
				$entry = randomNumber($length);
				$entry = str_pad($entry, 12, '0', STR_PAD_LEFT);
			}		
		}
		// now add the campaign offerrings
		$sql1 = "SELECT of_id FROM offerrings WHERE of_campaign = '$campaign'";
		$result1 = mysqli_query($conn, $sql1) or die(mysqli_error($conn));
		$number_of_rows1 = mysqli_num_rows($result1);
		if ( $number_of_rows1 != 0)
		{
			while ($newArray1 = mysqli_fetch_assoc($result1))
			{
				$of_id = $newArray1['of_id'];
				$sql2 = "INSERT INTO sign_up_samples VALUES ('',  '$ca_client', '$brand', '$coffee', '$pe_id', '$sign_up', '$of_id', '0', '', '0')";
				$result2 = mysqli_query($conn, $sql2) or die(mysqli_error($conn));
			}
        } 
        $zone++;    
        if($who = 'Americano')
        {
            if($zone < 31 || $zone > 39)
            {
                $zone = 31;
            }
            if($zone == 30 || $zone == 32 || $zone == 34 || $zone == 36 || $zone == 38 )
            {
                $zone++;
            }
        }
        if($who = 'Cappuccino')
        {
            if($zone < 11 || $zone > 29)
            {
                $zone = 11;
            }
            if($zone == 10 || $zone == 12 || $zone == 14 || $zone == 16 || $zone == 18 || $zone == 20 || $zone == 22 || $zone == 24 || $zone == 26 || $zone == 28)
            {
                $zone++;
            }
        }
        if($who = 'Latte')
        {
            if($zone < 45 || $zone > 49)
            {
                $zone = 45;
            }
            if($zone == 44 || $zone == 46 || $zone == 48 )
            {
                $zone++;
            }
        }
        if($who = 'Espresso')
        {
            if($zone < 41 || $zone > 43)
            {
                $zone = 41;
            }
            if($zone == 40 || $zone == 42)
            {
                $zone++;
            }
        }	

// 		// and now store all his answers for this form/campaign
// 		$sql2 = "INSERT INTO f_answers VALUES ('', '$client', '$brand', '$campaign', '$pe_id', '$frm', '$stdq', '$fname', '$sname', '$dob', '$gender', '$mobile', '$email', now())";
// 		$result2 = mysqli_query($conn, $sql2) or die(mysqli_error($conn));		
	}
	else
	{
		// SORRY you cannot enter again
		$error = "Already registered for this campaign";	
//		echo $error;		
	}	

    
    //$cl_cell = "0".substr($mobile, 1,9);//format in db
    //$cl_cell1 = "27".substr($mobile, 1,9);//sms out format
    $cl_cell = "0623961536";
    $cl_cell1 = "27623961536";
    //Select first name from table 
    $sqlxx = "SELECT * FROM person WHERE pe_mobile = '$cl_cell'";
	$resultxx = mysqli_query($conn, $sqlxx) or die(mysqli_error($conn));
	$number_of_rowsxx = mysqli_num_rows($resultxx);
    $newArrayxx = mysqli_fetch_assoc($resultxx);
    $pe_fname = $newArrayxx['pe_fname'];


    $sms_entry = ltrim($entry, '0');
    // Hi Espresso. U like different things and so do we! Use the code  #12345 at the Redefine Stand tomorrow to find out what spoil we got for U. Opt out reply stop
    $message = "Hi $pe_fname. Use code $sms_entry#$zone to grab a coffee at the Redefine Stand. Opt out reply stop";
    //$message = "Hi. U like different things and so do we! Use code $sms_entry#69 at the Redefine Stand tomorrow to find out what spoil we got for U. Opt out reply stop";
    $message=mysqli_real_escape_string($conn1, $message);
    
        //
        $sql9 = "INSERT INTO bigbrother.sentmessages_pending VALUES( '',
                                                                        '30',
                                                                        '0',
                                                                        '$sign_up',
                                                                        '0',
                                                                        '$message',
                                                                        now(),
                                                                        now(),
                                                                        '$cl_cell1',
                                                                        'connet',
                                                                        '0',
                                                                        '00:00:00',
                                                                        '',
                                                                        '999',
                                                                        '0' )";
                                                                        echo "$sql9<br />";
                                                                        
        $result9 = mysqli_query($conn1,  $sql9) or die(mysqli_error($conn1));
        
        //$cl_cell = "0".substr($mobile, 1,9);//format in db
        //$cl_cell1 = "27".substr($mobile, 1,9);//sms out format
        $cl_cell2 = "0744436580";
        $cl_cell3 = "27744436580";
        //Select first name from table 
        $sqlxxx = "SELECT * FROM person WHERE pe_mobile = '$cl_cell2'";
        $resultxxx = mysqli_query($conn, $sqlxxx) or die(mysqli_error($conn));
        $number_of_rowsxxx = mysqli_num_rows($resultxxx);
        $newArrayxxx = mysqli_fetch_assoc($resultxxx);
        $pe_fname1 = $newArrayxxx['pe_fname'];
    
    
        $sms_entry = ltrim($entry, '0');
        // Hi Espresso. U like different things and so do we! Use the code  #12345 at the Redefine Stand tomorrow to find out what spoil we got for U. Opt out reply stop
        $message = "Hi $pe_fname1. Use code $sms_entry#$zone to grab a coffee at the Redefine Stand. Opt out reply stop";
        //$message = "Hi. U like different things and so do we! Use code $sms_entry#69 at the Redefine Stand tomorrow to find out what spoil we got for U. Opt out reply stop";
        $message=mysqli_real_escape_string($conn1, $message);
        
            //
            $sql10 = "INSERT INTO bigbrother.sentmessages_pending VALUES( '',
                                                                            '30',
                                                                            '0',
                                                                            '$sign_up',
                                                                            '0',
                                                                            '$message',
                                                                            now(),
                                                                            now(),
                                                                            '$cl_cell3',
                                                                            'connet',
                                                                            '0',
                                                                            '00:00:00',
                                                                            '',
                                                                            '999',
                                                                            '0' )";
                                                                            echo "$sql10<br />";
                                                                            
            $result10 = mysqli_query($conn1,  $sql10) or die(mysqli_error($conn1));
        break;  
    	
	//header("Location: http://trialrun.media/c$campaign/confirmation.php"); 
	//header("Location: c$campaign/confirmation.php"); 
}
}

echo "done";

  
    ?>