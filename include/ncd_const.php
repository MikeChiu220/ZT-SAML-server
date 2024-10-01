<?php
/*
 * --- Ver: 1.09 ---
 * Mike[2022/06/24] Add Leave bed status: Set Up & On Bed side
 *
 */
const   C_FEATURE_MULTI_PANEL	= 1;		// Mike[2023/07/31]

const   C_IP_NCD		= 2;              // 2=IP-NCD
const   C_IP_IPHONE		= 3;              // 3=iPhone APP
const   C_IP_AtPHONE	= 4;              // 4=@Phone APP
const   C_CHINESE_PANEL	= 1;              // Mike[2022/03/29]
const   C_ENGLISH_PANEL	= 2;              // Mike[2022/03/24]

// ---- His Type define ----				// Mike[2023/06/21]
const   C_HIS_VGHTP         = 1;			// 1= 北榮
const	C_SINLAU_HOSPITAL	= 2;			// 台南新樓醫院		// Mike[2023/02/29] add ;

// ---- NCD Event Key code define ----              // Mike[2020/05/15]
const   C_NCD_CALL_BUTTON         = 1;              // 1= Call from NCD6/PC Call Button
const   C_NCD_PATIENT_LINE_CALL   = 3;              // 3= Call from NCD6/PC Pull Cord Button
const   C_NCD_CALL_LINE_PLUG_OUT  = 4;              // 4= NCD6/PC Pull Cord plugged out
const   C_NCD_EMERGENCY_CALL      = 6;              // 6= Call from NCD6 SOS button/EC Call Button
const   C_NCD_NP_CALL_BUTTON      = 8;              // 8= NP status on (Nurse Presence Call Button pressed)
const   C_NCD_BATH_BUTTON         = 10;             // 10= Call from BE Call Button
const   C_NCD_LEAVE_BED_ACTIVE    = 12;             // 12= BW Leave Bed status on
const   C_NCD_WET_BED_CANCEL      = 13;             // 13= BW Cancel button
const   C_NCD_WET_BED_ACTIVE      = 14;             // 14= BW Wet status on
const   C_NCD_WET_LINE_PLUG_OUT   = 15;             // 15= BW Wet Pull Cord plugged out
const   C_NCD_BED_LINE_PLUG_OUT   = 17;             // 17= BW Leave Bed Pull Cord plugged out
const   C_NCD_STAFF_CALL_BUTTON   = 21;             // 21= Call from SC Call Button
const   C_NCD_DOCTOR_PRESENCE     = 35;             // 35= Docter Presence	Mike[2021/07/19]
const   C_NCD_SET_UP	  	  	  = 36;				// 36= Set Up			Mike[2022/06/24]
const	C_NCD_SET_BED_SIDE   	  = 37;				// 37= Set bed Side		/
const   C_NCD_CANCEL_CALL_BUTTON  = 2;              // Cancel from NCD6/PC Cancel Button
const   C_NCD_CALL_LINE_PLUG_IN   = 5;              // NCD6/PC Pull Cord plugged in (after Pull Cord plugged out)
const   C_NCD_CANCEL_EMERGENCY    = 7;				// Cancel from NCD6/EC Cancel Button
const   C_NCD_NP_CANCEL_CALL      = 9;              // 9= NP status off (Nurse Presence Cancel Button pressed)
const   C_NCD_CANCEL_BATH_BUTTON  = 11;             // Cancel from BE Cancel Button
const   C_NCD_CANCEL_BED_BUTTON   = 13;             // Cancel from BW Cancel Button
const   C_NCD_WET_LINE_PLUG_IN    = 16;             // BW Wet Pull Cord plugged in (after Pull Cord plugged out)
const   C_NCD_BED_LINE_PLUG_IN    = 18;             // BW Leave Bed Pull Cord plugged in (after Pull Cord plugged out)
const   C_NCD_WET_BED_DEACTIVE    = 19;             // BW Wet status off (after Wet status on)
const   C_NCD_LEAVE_BED_DEACTIVE  = 20;             // BW Leave Bed status off (after Leave Bed status on)
const   C_NCD_CANCEL_STAFF_CALL   = 22;             // Cancel from SC Cancel Button
const   C_NCD_CLEAN_ROOM		  = 45;				// Clean Room			Mike[2022/01/24]
const   C_NCD_DRIP_LOW			  = 47;				// Drip Low detect
const   C_NCD_CODE_BLUE_CALL	  = 50;				// Code Blue detect
const   C_NCD_CODE_BLUE_CANCEL	  = 51;				// Code Blue Cancel
const   C_NCD_SENSOR1_ON		  = 52;				// Sensor 1 On
const   C_NCD_SENSOR1_OFF		  = 53;				// Sensor 1 Off
const   C_NCD_SENSOR2_ON		  = 54;				// Sensor 2 On
const   C_NCD_SENSOR2_OFF		  = 55;				// Sensor 2 Off
const   C_NCD_ANSWER_CANCEL		  = 80;             // Answer Cancel event
const   C_NCD_CALL_BE_ANSWERED	  = 81;             // Call be answered
const   C_NCD_CALL_BUSY			  = 82;             // Call busy
const   C_FUSION_EVENT			  = 90;				// Fusion Event			Mike[2022/08/23]
const   C_UPDATE_PANEL_TIME		  = 240;			// Update Panel Time	Mike[2022/01/10]
const   C_STOP_ALARM_TONE		  = 241;			// Temp Stop Alarm tone	Mike[2023/07/12]

// ---- NCD LED Number define ----                  // Mike[2020/05/15]
const   C_NLD_LED1_NO			= 8;              // NCD2 relay control 1: Blue
const   C_NLD_LED2_NO			= 6;              // NCD2 relay control 2: Red
const   C_NLD_LED3_NO			= 4;              // NCD2 relay control 3: Green
const   C_NLD_LED4_NO			= 10;             // NCD2 relay control 4: Yellow Led
const   C_CALL_LED1_NO			= 1;              // 01= NCD6 Call LED (呼叫面板指示燈)
const   C_CALL_LED2_NO			= 2;              // 02= NCD6 Relay (呼叫門口指示燈)
const   C_BED_LED1_NO			= 3;              // 03= BW Leave Bed LED (離床面板指示燈)
const   C_BED_LED2_NO			= C_NLD_LED3_NO;  // 04= BW Relay (離床門口指示燈)
const   C_NURSE_LED1_NO			= 5;              // 05= NP LED (巡房面板指示燈)
const   C_NURSE_LED2_NO			= C_NLD_LED1_NO;  // 06= NP Relay (巡房門口指示燈)
const   C_BATH_LED1_NO			= 7;              // 07= BE LED (浴室面板指示燈)
const   C_BATH_LED2_NO			= C_NLD_LED2_NO;  // 08= BE Relay (浴室門口指示燈)
const   C_EMERGENCY_CALL_LED1_NO= 9;
const   C_EMERGENCY_CALL_LED2_NO= C_NLD_LED2_NO;
const	C_STAFF_LED1_NO			= 1;
const	C_DRIP_LED1_NO			= 1;
const	C_CODE_BLUE_LED1_NO		= 9;
const	C_STAFF_LED2_NO			= C_NLD_LED3_NO;
const	C_DRIP_LED2_NO			= 2;
const	C_CODE_BLUE_LED2_NO		= C_NLD_LED2_NO;
const	C_RELAY_LED_NO			= C_NLD_LED4_NO;	// Mike[2021/11/17]

// ---- NCD LED Control define ----                 // Mike[2020/05/15]
const   C_LED_CTRL_OFF            = 1;
const   C_LED_CTRL_ON             = 2;
const   C_LED_CTRL_RING           = 3;              // 100ms on/off
const   C_LED_CTRL_QUICK_FLASH    = 4;              // 250ms on/off
const   C_LED_CTRL_SLOW_FLASH     = 5;              // 300ms on/off

// ---- CTI NCD Event Key code define ----              // Mike[2020/05/15]
const   C_CTI_NCD_CALL_BUTTON         = 1;			// 1= Call from NCD6/PC Call Button
const   C_CTI_NCD_PATIENT_LINE_CALL   = 19;			// 3= Call from NCD6/PC Pull Cord Button
const   C_CTI_NCD_CALL_LINE_PLUG_OUT  = 11;			// 4= NCD6/PC Pull Cord plugged out
const   C_CTI_NCD_EMERGENCY_CALL      = 20;			// 6= Call from NCD6 SOS button/EC Call Button
const   C_CTI_NCD_NP_CALL_BUTTON      = 7;			// 8= NP status on (Nurse Presence Call Button pressed)
const	C_CTI_NCD_DOCTOR_PRESENCE     = 8;			// 8= Doctor Presence Call Button pressed
const   C_CTI_NCD_BATH_BUTTON         = 3;			// 10= Call from BE Call Button
const   C_CTI_NCD_LEAVE_BED_ACTIVE    = 5;			// 12= BW Leave Bed status on
const   C_CTI_NCD_WET_BED_ACTIVE      = 24;			// 14= BW Wet status on
const   C_CTI_NCD_WET_LINE_PLUG_OUT   = 26;			// 15= BW Wet Pull Cord plugged out
const   C_CTI_NCD_BED_LINE_PLUG_OUT   = 28;			// 17= BW Leave Bed Pull Cord plugged out
const   C_CTI_NCD_STAFF_CALL_BUTTON   = 22;			// 21= Call from SC Call Button
const   C_CTI_NCD_CANCEL_CALL_BUTTON  = 34;			// 34= Cancel from NCD6/PC Cancel Button(2->34 for cancel both Patient Call & Emergency Call)
const   C_CTI_NCD_CALL_LINE_PLUG_IN   = 10;			// 5= NCD6/PC Pull Cord plugged in (after Pull Cord plugged out)
const   C_CTI_NCD_CANCEL_EMERGENCY    = 21;			// 7= Cancel from NCD6/EC Cancel Button
const   C_CTI_NCD_NP_CANCEL_CALL      = 9;			// 9= NP status off (Nurse Presence Cancel Button pressed)
const   C_CTI_NCD_CANCEL_BATH_BUTTON  = 4;			// 11= Cancel from BE Cancel Button
const   C_CTI_NCD_CANCEL_BED_BUTTON   = 6;			// 13= Cancel from BW Cancel Button
const   C_CTI_NCD_WET_LINE_PLUG_IN    = 27;			// 16= BW Wet Pull Cord plugged in (after Pull Cord plugged out)
const   C_CTI_NCD_BED_LINE_PLUG_IN    = 29;			// 18= BW Leave Bed Pull Cord plugged in (after Pull Cord plugged out)
const   C_CTI_NCD_WET_BED_DEACTIVE    = 25;			// 19= BW Wet status off (after Wet status on)
const   C_CTI_NCD_LEAVE_BED_DEACTIVE  = 20;			// 20= BW Leave Bed status off (after Leave Bed status on)
const   C_CTI_NCD_CANCEL_STAFF_CALL   = 23;			// 22= Cancel from SC Cancel Button
const   C_CTI_NCD_DRIP_LOW			  = 38;			// Drip Low detect
const   C_CTI_NCD_CODE_BLUE_CALL	  = 40;			// Code Blue detect
const	C_CTI_NCD_CODE_BLUE_CANCEL    = 41;			// Code Blue Cancel
const   C_CTI_NCD_ANSWER_CANCEL		  = 33;			// 50= Answer Cancel event
const   C_CTI_NCD_CALL_BE_ANSWERED	  = 32;			// 51= Answer Cancel event
const   C_CTI_NCD_SET_UP	          = 42;			// 36= Set Up			Mike[2022/06/24]
const	C_CTI_NCD_SET_BED_SIDE        = 43;			// 37= Set bed Side		/

$EventLed1No=array(0, 
		C_CALL_LED1_NO              ,   // 01= Call Button  of NCP-PC: Patient Call (Green button)
		C_CALL_LED1_NO              ,   // 02= Clear Button of NCP-PC: Patient Call (Green button)
		C_CALL_LED1_NO              ,   // 03= Call Button-Line of NCP-PC: Patient Call (Green button)
		C_CALL_LED1_NO              ,   // 04= Button-Line pull out of NCP-PC: Patient Call (Green button)
		C_CALL_LED1_NO				,   // 05= Button-Line pull in of NCP-PC: Patient Call (Green button)
		C_EMERGENCY_CALL_LED1_NO	,   // 06= Call Button  of NCP-EC: Emergency Call (Red button)
		C_EMERGENCY_CALL_LED1_NO	,   // 07= Clear Button of NCP-EC: Emergency Call (Red button)
		C_NURSE_LED1_NO             ,   // 08= Call Button  of NCP-NP: Nurse Presence (Blue button)
		C_NURSE_LED1_NO             ,   // 09= Clear Button of NCP-NP: Nurse Presence (Blue button)
		C_BATH_LED1_NO              ,   // 10= Call Button  of NCP-BE: Bathroom Emergency Call (Red but
		C_BATH_LED1_NO              ,   // 11= Clear Button of NCP-BE: Bathroom Emergency Call (Red but
		C_BED_LED1_NO               ,   // 12= Bed On of NCP-BW: Bed Exit, Bed Wet
		C_BED_LED1_NO               ,   // 13= Clear Button of NCP-BW: Bed Exit, Bed Wet
		C_BED_LED1_NO               ,   // 14= Wet On of NCP-BW: Bed Exit, Bed Wet
		0							,   // 15= Wet pull out of NCP-BW: Bed Exit, Bed Wet
		0							,   // 16= Wet pull in of NCP-BW: Bed Exit, Bed Wet
		0							,   // 17= bed pull out of NCP-BW: Bed Exit, Bed Wet
		0							,   // 18= bed pull in of NCP-BW: Bed Exit, Bed Wet
		0							,   // 19= Wet off of NCP-BW: Bed Exit, Bed Wet
		0							,   // 20= Bed off of NCP-BW: Bed Exit, Bed Wet
		C_STAFF_LED1_NO             ,   // 21= Call Button  of NCP-SC: Staff Call (Yellow button)
		C_STAFF_LED1_NO             ,   // 22= Clear Button of NCP-SC: Staff Call (Yellow button)
		0				            ,   // 23= PC connected
		0							,   // 24= EC connected
		0							,   // 25= NP connected
		0							,   // 26= BE connected
		0							,   // 27= BW connected
		0							,   // 28= SC connected
		0							,   // 29= PC disconnected
		0							,   // 30= EC disconnected
		0							,   // 31= NP disconnected
		0							,   // 32= BE disconnected
		0							,   // 33= BW disconnected
		0							,   // 34= SC disconnected
		C_NURSE_LED1_NO				,   // 35= Docter Presence	Mike[2021/07/19]
		C_BED_LED1_NO				,   // 36= Set Up			Mike[2022/06/24]
		C_BED_LED1_NO				,   // 37= Set bed Side		/
		0							,   // 38=
		0							,   // 39=
		0							,   // 40=
		0							,   // 41=
		0							,   // 42=
		0							,   // 43=
		0							,   // 44=
		0							,   // 45= Clean Room		Mike[2022/01/24]
		0							,   // 46=
		C_DRIP_LED1_NO              ,   // 47= Drip Low detect
		0							,   // 48= NCP-CB connected
		0							,   // 49= NCP-CB disconnected
		C_CODE_BLUE_LED1_NO         ,   // 50= Code Blue
		C_CODE_BLUE_LED1_NO			,   // 51= Code Blue Cancel
		0							,   // 52=
		0							,   // 53=
		0							,   // 54=
		0							,   // 55=
);
$EventLed2No=array(0, 
		C_CALL_LED2_NO              ,   // 01= Call Button  of NCP-PC: Patient Call (Green button)
		C_CALL_LED2_NO              ,   // 02= Clear Button of NCP-PC: Patient Call (Green button)
		C_CALL_LED2_NO              ,   // 03= Call Button-Line of NCP-PC: Patient Call (Green button)
		C_CALL_LED2_NO				,   // 04= Button-Line pull out of NCP-PC: Patient Call (Green button)
		C_CALL_LED2_NO				,   // 05= Button-Line pull in of NCP-PC: Patient Call (Green button)
		C_EMERGENCY_CALL_LED2_NO	,   // 06= Call Button  of NCP-EC: Emergency Call (Red button)
		C_EMERGENCY_CALL_LED2_NO	,   // 07= Clear Button of NCP-EC: Emergency Call (Red button)
		C_NURSE_LED2_NO             ,   // 08= Call Button  of NCP-NP: Nurse Presence (Blue button)
		C_NURSE_LED2_NO             ,   // 09= Clear Button of NCP-NP: Nurse Presence (Blue button)
		C_BATH_LED2_NO              ,   // 10= Call Button  of NCP-BE: Bathroom Emergency Call (Red but
		C_BATH_LED2_NO              ,   // 11= Clear Button of NCP-BE: Bathroom Emergency Call (Red but
		C_BED_LED2_NO               ,   // 12= Bed On of NCP-BW: Bed Exit, Bed Wet
		C_BED_LED2_NO               ,   // 13= Clear Button of NCP-BW: Bed Exit, Bed Wet
		C_BED_LED2_NO               ,   // 14= Wet On of NCP-BW: Bed Exit, Bed Wet
		0							,   // 15= Wet pull out of NCP-BW: Bed Exit, Bed Wet
		0							,   // 16= Wet pull in of NCP-BW: Bed Exit, Bed Wet
		0							,   // 17= bed pull out of NCP-BW: Bed Exit, Bed Wet
		0							,   // 18= bed pull in of NCP-BW: Bed Exit, Bed Wet
		0							,   // 19= Wet off of NCP-BW: Bed Exit, Bed Wet
		0							,   // 20= Bed off of NCP-BW: Bed Exit, Bed Wet
		C_STAFF_LED2_NO             ,   // 21= Call Button  of NCP-SC: Staff Call (Yellow button)
		C_STAFF_LED2_NO             ,   // 22= Clear Button of NCP-SC: Staff Call (Yellow button)
		0				            ,   // 23= PC connected
		0							,   // 24= EC connected
		0							,   // 25= NP connected
		0							,   // 26= BE connected
		0							,   // 27= BW connected
		0							,   // 28= SC connected
		0							,   // 29= PC disconnected
		0							,   // 30= EC disconnected
		0							,   // 31= NP disconnected
		0							,   // 32= BE disconnected
		0							,   // 33= BW disconnected
		0							,   // 34= SC disconnected
		C_NURSE_LED2_NO				,   // 35= Docter Presence	Mike[2021/07/19]
		C_BED_LED2_NO				,   // 36= Set Up			Mike[2022/06/24]
		C_BED_LED2_NO				,   // 37= Set bed Side		/
		0							,   // 38=
		0							,   // 39=
		0							,   // 40=
		0							,   // 41=
		0							,   // 42=
		0							,   // 43=
		0							,   // 44=
		0							,   // 45= Clean Room		Mike[2022/01/24]
		0							,   // 46=
		C_DRIP_LED2_NO              ,   // 47= Drip Low detect
		0							,   // 48= NCP-CB connected
		0							,   // 49= NCP-CB disconnected
		C_CODE_BLUE_LED2_NO         ,   // 50= Code Blue
		C_CODE_BLUE_LED2_NO			,   // 51= Code Blue Cancel
		0							,   // 52=
		0							,   // 53=
		0							,   // 54=
		0							,   // 55=
);
/*
 * Send Led panel show message request to panel_ctrl.c for do LED Panel control
 */
function LedPanelReq( $TxCommand )
{
/* ---- Using Semaphone ---- */
	$SEMAPHORE_KEY = 515;   			//Semaphore unique key (MAKE DIFFERENT TO RPi App KEY)
	//Create the semaphore
	$semaphore_id = sem_get($SEMAPHORE_KEY, 1);		// Creates, or gets if already present, a semaphore
	if ($semaphore_id === false)
		syslog(LOG_ERR, "** LedPanelReq ** Failed to create semaphore.  Reason: $php_errormsg<br />");
	else	// Acquire the semaphore for let panel_ctrl can do LED panel check process
	if (!sem_acquire($semaphore_id))				// If not available this will stall until the semaphore is released by the other process
		syslog(LOG_ERR, "** LedPanelReq ** Failed to acquire semaphore $semaphore_id<br />");
	
/* ---- Using Unix Sockey ----
	do {
		$file = sys_get_temp_dir() . '/' . uniqid('client', true) . '.sock';
	} while (file_exists($file));

	$socket = socket_create(AF_UNIX, SOCK_DGRAM, 0);

	if (socket_bind($socket, $file) === false)
		syslog(LOG_ERR, "** LedPanelReq ** bind failed");

	if ( socket_sendto($socket, $TxCommand, strlen($TxCommand), 0, "/tmp/myserver.sock", 0) != false ) {
		syslog(LOG_ERR, "** LedPanelReq ** socket_sendto sucessful");
		if (socket_recvfrom($socket, $buf, 64 * 1024, 0, $source) == false) 
			syslog(LOG_ERR, "** LedPanelReq ** recv_from failed");
		else
			syslog(LOG_INFO, "** LedPanelReq ** received: [$buf] from: [$source]");

	}
	else
		syslog(LOG_ERR, "** LedPanelReq ** socket_sendto failed");


	socket_close($socket);
	unlink($file);
*/
}

/*
 * Send GET URL
 * Mike[2022/06/14] Move from NcdKeyProc.php
 */
function curl_file_get_contents($durl){
//	syslog(LOG_INFO, "** curl_file_get_contents ** call curl_init" );			// Mike[2022/04/02] for test
	$ch = curl_init();
	if ($ch ==FALSE)
		syslog(LOG_ERR, "** curl_file_get_contents ** curl_init error" );
//	else																		// Mike[2022/04/02] for test
//		syslog(LOG_INFO, "** curl_file_get_contents ** curl_init sucess" );		// /
	$timeout=1;												// Mike[2021/09/16] 5 -> 1
	curl_setopt($ch, CURLOPT_URL, $durl);
//	curl_setopt($ch, CURLOPT_USERAGENT, _USERAGENT_);
//	curl_setopt($ch, CURLOPT_REFERER,_REFERER_);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
//	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	$result = curl_exec($ch);
	if (curl_errno ( $ch ))
		syslog(LOG_ERR, "** curl_file_get_contents ** ".curl_error($ch) );
	curl_close($ch);
	return $result;
}

/*
 * Set Auto recall for NCD when receive disconnect but without cancel event for Bath, Emergency and Core Plug out
 * Iput:NcdNo = Ncd Number 
 */
function SetAutoRecall($NcdNo, $KeyCode)
{
	$tempCallFileName = "/tmp/$NcdNo-recall";
	$autoRecallFileName = "/etc/cron.d/$NcdNo-recall";
	$recallTime = date('i H', strtotime('+2 minutes'));
	$recallCmd="SHELL=/bin/bash\n$recallTime * *  * root (php /var/www/html/NcdKeyCode.php $NcdNo $KeyCode)\n";
    file_put_contents($tempCallFileName, $recallCmd);
	system("sudo /bin/cp $tempCallFileName $autoRecallFileName" );	//  Mike[2022/03/11] add /bin/
	system("sudo /bin/rm $tempCallFileName" );
	syslog(LOG_INFO, "** SetAutoRecall** $recallTime * *  * root (php /var/www/html/NcdKeyCode.php $NcdNo $KeyCode");		// for test
	exec("sudo /usr/sbin/service cron reload");		// Mike[2021/11/11] Mike[2022/03/11] add /usr/sbin/
}

/*
 * Remove Auto recall for NCD 
 * Iput:NcdNo = Ncd Number 
 */
function RemoveAutoRecall($NcdNo)
{
	$autoRecallFileName = "/etc/cron.d/$NcdNo-recall";
	if (file_exists($autoRecallFileName)) {
		$User=get_current_user();
		syslog(LOG_INFO, "** RemoveAutoRecall ** ($User): sudo /bin/rm $autoRecallFileName");		// for test
		exec("sudo /bin/rm $autoRecallFileName",$output, $return_var);		// Mike[2021/11/11] add /bin/
		exec("sudo /usr/sbin/service cron reload");							// / 		Mike[2022/03/11] add /usr/sbin/
	}
}

?>
