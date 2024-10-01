<?php
/*
 * Modify Note
 * --- Ver: 1.06 ---
 * Mike[2022/01/18]/[2022/01/22]/[2022/01/24] Add Send Event indicate API to event server
 * Mike[2022/01/20]	Debug the Door led without  be cleared when assign to another NCD6
 * --- Ver: 1.03 ---
 * Mike[2021/11/30] Add Nurse & Doctor Present panel display
 * Mike[2021/11/29] Modify Only Led_Assgin == NCD No need send Control Panel Msssage foe save time
 * --- Ver: 1.02 ---
 * Mike[2021/11/18] 
 * --- Ver: 1.01 ---
 * Mike[2021/11/11] Debug the auto Recall can't be canceled
 * Mike[2021/11/16] Debug sometime the display in 取消處理盤 can't work
 * Mike[2021/11/17] Add send relay control when has event for 盈慶 Led bear
 */
include("connection.php");
include("ncd_const.php");
include("APIcmd.php");

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
const	C_CTI_NCD_CODE_BLUE_CANCEL		= 41;			// Code Blue Cancel
const   C_CTI_NCD_ANSWER_CANCEL		  = 33;			// 50= Answer Cancel event
const   C_CTI_NCD_CALL_BE_ANSWERED	  = 32;			// 51= Answer Cancel event

// ---- NCD3 LED panel Event Type define ----
const   C_NCD3_NO_EVENT				= 0;
const   C_NCD3_NORMAL_CALL			= 1;
const   C_NCD3_LINE_DROP  			= 2;
const   C_NCD3_EMERGENCY  			= 3;
const   C_NCD3_STAFF_CALL 			= 4;
const   C_NCD3_BATHROOM_CALL		= 5;

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
		0							,   // 36=
		0							,   // 37=
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
		0							,   // 36=
		0							,   // 37=
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
$EventLedPatten=array(0, 
		C_LED_CTRL_ON				,   // 01= Call Button  of NCP-PC: Patient Call (Green button)
		C_LED_CTRL_OFF				,   // 02= Clear Button of NCP-PC: Patient Call (Green button)
		C_LED_CTRL_ON				,   // 03= Call Button-Line of NCP-PC: Patient Call (Green button)
		C_LED_CTRL_ON				,   // 04= Button-Line pull out of NCP-PC: Patient Call (Green button)
		C_LED_CTRL_OFF				,   // 05= Button-Line pull in of NCP-PC: Patient Call (Green button)
		C_LED_CTRL_QUICK_FLASH		,   // 06= Call Button  of NCP-EC: Emergency Call (Red button)
		C_LED_CTRL_OFF				,   // 07= Clear Button of NCP-EC: Emergency Call (Red button)
		C_LED_CTRL_ON				,   // 08= Call Button  of NCP-NP: Nurse Presence (Blue button)
		C_LED_CTRL_OFF				,   // 09= Clear Button of NCP-NP: Nurse Presence (Blue button)
		C_LED_CTRL_QUICK_FLASH		,   // 10= Call Button  of NCP-BE: Bathroom Emergency Call (Red but
		C_LED_CTRL_OFF				,   // 11= Clear Button of NCP-BE: Bathroom Emergency Call (Red but
		C_LED_CTRL_ON				,   // 12= Bed On of NCP-BW: Bed Exit, Bed Wet
		C_LED_CTRL_OFF				,   // 13= Clear Button of NCP-BW: Bed Exit, Bed Wet
		C_LED_CTRL_ON				,   // 14= Wet On of NCP-BW: Bed Exit, Bed Wet
		0							,   // 15= Wet pull out of NCP-BW: Bed Exit, Bed Wet
		0							,   // 16= Wet pull in of NCP-BW: Bed Exit, Bed Wet
		0							,   // 17= bed pull out of NCP-BW: Bed Exit, Bed Wet
		0							,   // 18= bed pull in of NCP-BW: Bed Exit, Bed Wet
		0							,   // 19= Wet off of NCP-BW: Bed Exit, Bed Wet
		0							,   // 20= Bed off of NCP-BW: Bed Exit, Bed Wet
		C_LED_CTRL_QUICK_FLASH		,   // 21= Call Button  of NCP-SC: Staff Call (Yellow button)
		C_LED_CTRL_OFF				,   // 22= Clear Button of NCP-SC: Staff Call (Yellow button)
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
		C_LED_CTRL_ON				,   // 35= Docter Presence	Mike[2021/07/19]
		0							,   // 36=
		0							,   // 37=
		0							,   // 38=
		0							,   // 39=
		0							,   // 40=
		0							,   // 41=
		0							,   // 42=
		0							,   // 43=
		0							,   // 44=
		C_LED_CTRL_ON				,   // 45= Clean Room		Mike[2022/01/24]
		0							,   // 46=
		C_LED_CTRL_ON				,   // 47= Drip Low detect
		0							,   // 48= NCP-CB connected
		0							,   // 49= NCP-CB disconnected
		C_LED_CTRL_QUICK_FLASH		,   // 50= Code Blue
		C_LED_CTRL_OFF				,   // 51= Code Blue Cancel
		0							,   // 52=
		0							,   // 53=
		0							,   // 54=
		0							,   // 55=
);
$cti_event=array(0, C_CTI_NCD_CALL_BUTTON		,	// 01= Call Button  of NCP-PC: Patient Call (Green button)
                    C_CTI_NCD_CANCEL_CALL_BUTTON,	// 02= Clear Button of NCP-PC: Patient Call (Green button)
                    C_CTI_NCD_PATIENT_LINE_CALL	,	// 03= Call Button-Line of NCP-PC: Patient Call (Green button)
                    C_CTI_NCD_CALL_LINE_PLUG_OUT,	// 04= Button-Line pull out of NCP-PC: Patient Call (Green button)
                    C_CTI_NCD_CALL_LINE_PLUG_IN	,	// 05= Button-Line pull in of NCP-PC: Patient Call (Green button)
                    C_CTI_NCD_EMERGENCY_CALL	,	// 06= Call Button  of NCP-EC: Emergency Call (Red button)
                    C_CTI_NCD_CANCEL_EMERGENCY	,	// 07= Clear Button of NCP-EC: Emergency Call (Red button)
                    C_CTI_NCD_NP_CALL_BUTTON	,	// 08= Call Button  of NCP-NP: Nurse Presence (Blue button)
                    C_CTI_NCD_NP_CANCEL_CALL	,	// 09= Clear Button of NCP-NP: Nurse Presence (Blue button)
                    C_CTI_NCD_BATH_BUTTON		,	// 10= Call Button  of NCP-BE: Bathroom Emergency Call
                    C_CTI_NCD_CANCEL_BATH_BUTTON,	// 11= Clear Button of NCP-BE: Bathroom Emergency Call
                    C_CTI_NCD_LEAVE_BED_ACTIVE	,	// 12= Bed On of NCP-BW: Bed Exit, Bed Wet
                    C_CTI_NCD_CANCEL_BED_BUTTON	,	// 13= Clear Button of NCP-BW: Bed Exit, Bed Wet
                    C_CTI_NCD_WET_BED_ACTIVE	,	// 14= Wet On of NCP-BW: Bed Exit, Bed Wet
                    C_CTI_NCD_WET_LINE_PLUG_OUT	,	// 15= Wet pull out of NCP-BW: Bed Exit, Bed Wet
                    C_CTI_NCD_WET_LINE_PLUG_IN	,	// 16= Wet pull in of NCP-BW: Bed Exit, Bed Wet
                    C_CTI_NCD_BED_LINE_PLUG_OUT	,	// 17= bed pull out of NCP-BW: Bed Exit, Bed Wet
                    C_CTI_NCD_BED_LINE_PLUG_IN	,	// 18= bed pull in of NCP-BW: Bed Exit, Bed Wet
                    C_CTI_NCD_WET_BED_DEACTIVE	,	// 19= Wet off of NCP-BW: Bed Exit, Bed Wet
                    C_CTI_NCD_LEAVE_BED_DEACTIVE,	// 20= Bed off of NCP-BW: Bed Exit, Bed Wet
                    C_CTI_NCD_STAFF_CALL_BUTTON	,	// 21= Call Button  of NCP-SC: Staff Call (Yellow button)
                    C_CTI_NCD_CANCEL_STAFF_CALL	,	// 22= Clear Button of NCP-SC: Staff Call (Yellow button)
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
					C_CTI_NCD_DOCTOR_PRESENCE	,   // 35= Docter Presence	Mike[2021/07/19]
					0							,   // 36=
					0							,   // 37=
					0							,   // 38=
					0							,   // 39=
					0							,   // 40=
					0							,   // 41=
					0							,   // 42=
					0							,   // 43=
					0							,   // 44=
					0							,   // 45= Clean Room		Mike[2022/01/24]
					0							,   // 46=
					C_CTI_NCD_DRIP_LOW			,   // 47= Drip Low detect
					0							,   // 48= NCP-CB connected
					0							,   // 49= NCP-CB disconnected
					C_CTI_NCD_CODE_BLUE_CALL	,   // 50= Code Blue
					C_CTI_NCD_CODE_BLUE_CANCEL	,   // 51= Code Blue Cancel
					0							,   // 52=
					0							,   // 53=
					0							,   // 54=
					0							,   // 55=
  );


/*
 * Send Web API Led control to IP-NCD6
 * Iput:$NcdNo = NCD number
 *		$KeyCode = input key code
 *		$BedNo = Bed Number
 *		$CalledNo = be called party number
 *		$AnswerTm = call be answered time
 *		$AnswerNo = answer call party number
 *		$NcdName = NCD Name
 */
function doNcdKeyProc($NcdNo, $Key, $BedNo, $CalledNo, $AnswerTm, $AnswerNo, $NcdName)
{
	global $database;
	global $EventLed1No,$EventLed2No,$EventLedPatten,$cti_event;
	global $NcdIPAddr,$NcdLedIPAddr;
	global $CancelLedNo,$panelType,$Led_assign,$CancelEvent;		// Mike[2022/01/20] add Led_assign & CancelEvent
	
	syslog(LOG_INFO, "** doNcdKeyProc ** NCD=$NcdNo,key=$Key,CalledNo=$CalledNo,AnswerTm=$AnswerTm,AnswerNo=$AnswerNo,NcdName=$NcdName");		// for test
	$KeyCode = intval($Key);
	$AreaId  = 0;
	$LedPanelFlag=0;
	$NcdLedIPAddr="";
	$Led_assign=0;
	// 1. Check NcdNo is correct or not
	if ( strstr($NcdNo,"Public-") ) {
		$strArea = explode("-",$NcdNo);
		$AreaId = $strArea[1]+10;
		$command = "select * from paging_prompt where id =$AreaId";
		$queryId = FUN_SQL_QUERY($command, $database);
		$query_num = FUN_SQL_NUM_ROWS($queryId);
		if ($query_num) {
			$row = FUN_SQL_FETCH_ARRAY( $queryId);
			if ($row['description']) {
				$NcdName = $row['title'];
				$CalledNo = $row['description'];
				$NcdIPAddr = '';
			}
			else {
				return [400, "Wrong Public Area!"];
//				http_response_code(400);	// Bed request
//				exit();
			}
		}
	}
	else {
		$command = "select ipaddr, callerid from sip_buddies WHERE name='".$NcdNo."'";
		$queryId = FUN_SQL_QUERY($command, $database);
		$query_num= FUN_SQL_NUM_ROWS($queryId);
		if ( $query_num == 1) {
			$row = FUN_SQL_FETCH_ARRAY( $queryId);
			$NcdName = $row['callerid'];
			$NcdIPAddr = $row['ipaddr'];
			if ($KeyCode==C_NCD_SENSOR1_ON)			// 52= Sensor 1 On
				$SensorField="sensor1_on,";
			else if ($KeyCode==C_NCD_SENSOR1_OFF)	// 53= Sensor 1 Off
				$SensorField="sensor1_off,";
			else if ($KeyCode==C_NCD_SENSOR2_ON)	// 54= Sensor 2 On
				$SensorField="sensor2_on,";
			else if ($KeyCode==C_NCD_SENSOR2_OFF)	// 55= Sensor 2 Off
				$SensorField="sensor2_off,";
			else 
				$SensorField="";
			if ($SensorField)
				$KeyCode=0;
			$command = "select $SensorField Led_assign from phone WHERE phoneno='$NcdNo'";
			$queryId = FUN_SQL_QUERY($command, $database);
			$query_num= FUN_SQL_NUM_ROWS($queryId);
			if ( $query_num == 1) {
				$row = FUN_SQL_FETCH_ARRAY( $queryId);
				if ($SensorField)
					$KeyCode=$row["$SensorField"];
				$Led_assign=$row['Led_assign'];
//				syslog(LOG_INFO, "** doNcdKeyProc ** Led_assign=$Led_assign");	// for test
				if ($Led_assign and $Led_assign != $NcdNo) {	// Mike[2021/11/29] add check $Led_assign != $NcdNo
					$command = "select ipaddr from sip_buddies WHERE name='".$Led_assign."'";
					$queryId = FUN_SQL_QUERY($command, $database);
					$query_num= FUN_SQL_NUM_ROWS($queryId);
					if ( $query_num == 1) {
						$row = FUN_SQL_FETCH_ARRAY( $queryId);
						$NcdLedIPAddr = $row['ipaddr'];
					}
				}	
			}
		}
		else
			return [400, "Wrong NcdNo!"];
//			exit ();
	}
	// 2. Check Has active event in process or not
	$ActiveEvent = 0;
	$CancelEvent = "";
	$CtiEvent = 0;
	$Led1No=0;
	$Led3No=0;
		
	// 3. Check Has active event in process or not
	switch ( $KeyCode ) {
		case C_NCD_CALL_LINE_PLUG_OUT:  // 4= NCD6/PC Pull Cord plugged out
			$Led3No=C_EMERGENCY_CALL_LED2_NO;		// Mike[2021/11/05] For 丞瑋 脫落 Display 2 color led 
		case C_NCD_CALL_BUTTON:         // 1= Call from NCD6/PC Call Button
		case C_NCD_PATIENT_LINE_CALL:   // 3= Call from NCD6/PC Pull Cord Button
		case C_NCD_EMERGENCY_CALL:      // 6= Call from NCD6 SOS button/EC Call Button
		case C_NCD_NP_CALL_BUTTON:      // 8= NP status on (Nurse Presence Call Button pressed)
		case C_NCD_BATH_BUTTON:         // 10= Call from BE Call Button
		case C_NCD_LEAVE_BED_ACTIVE:	// 12= BW Leave Bed status on
		case C_NCD_WET_BED_ACTIVE:		// 14= BW Wet Bed status on
		case C_NCD_STAFF_CALL_BUTTON:	// 21= Call from SC Call Button
		case C_NCD_DOCTOR_PRESENCE:		// 35= Docter Presence	Mike[2021/07/19]
		case C_NCD_DRIP_LOW:			// 47= Drip Low detect
		case C_NCD_CODE_BLUE_CALL:		// 50= Code Blue detect
			$Led1No = $EventLed1No[$KeyCode];
			$Led2No = $EventLed2No[$KeyCode];
			$LedPatten = $EventLedPatten[$KeyCode];
			$ActiveEvent = $KeyCode;
			break;
		case C_NCD_CANCEL_CALL_BUTTON:	// 2 = Cancel from NCD6/PC Cancel Button
			if (strlen($AnswerNo) and strlen($AnswerTm)==0) {	// Mike[2021/12/29]
				$command ="Update `ncd_active_event` set answer_no='".$AnswerNo."' where ncd_no='".$NcdNo."' and proc_fg IS NULL and answer_no IS NULL";
				$qry_phone = FUN_SQL_QUERY($command, $database);
			}
			$CancelEvent = "and (active_event<>".C_NCD_BATH_BUTTON." || active_event<>".C_NCD_NP_CALL_BUTTON.")";
	//		doNcdLedControl(C_EMERGENCY_CALL_LED1_NO,C_EMERGENCY_CALL_LED2_NO,C_LED_CTRL_OFF);
			break;
		case C_NCD_CALL_LINE_PLUG_IN:	// 5 = NCD6/PC Pull Cord plugged in (after Pull Cord plugged out)
			$Led3No=C_EMERGENCY_CALL_LED2_NO;		// Mike[2021/11/05] For 丞瑋 脫落 Display 2 color led 
			$CancelEvent = "and active_event=".C_NCD_CALL_LINE_PLUG_OUT;
			break;
		case C_NCD_CANCEL_EMERGENCY:	// 7 = Cancel from NCD6/EC Cancel Button
			$CancelEvent = "and active_event=".C_NCD_EMERGENCY_CALL;
			$LedPatten = C_LED_CTRL_OFF;
			break;
		case C_NCD_NP_CANCEL_CALL:		// 9 = NP status off (Nurse Presence Cancel Button pressed)
			$CancelEvent = "and (active_event=".C_NCD_NP_CALL_BUTTON." or active_event=".C_NCD_DOCTOR_PRESENCE.")";
			break;
		case C_NCD_CANCEL_BATH_BUTTON:	// 11= Cancel from BE Cancel Button
			$CancelEvent = "and active_event=".C_NCD_BATH_BUTTON;
			break;
		case C_NCD_CANCEL_BED_BUTTON:	// 13= Cancel from BW Cancel Button
			$CancelEvent = "and (active_event=".C_NCD_LEAVE_BED_ACTIVE." || active_event=".C_NCD_WET_BED_ACTIVE.")";
			break;
		case C_NCD_CANCEL_STAFF_CALL:	// 22= Cancel from SC Cancel Button
			$CancelEvent = "and active_event=".C_NCD_STAFF_CALL_BUTTON;
			break;
		case C_NCD_CODE_BLUE_CANCEL:	// 51= Code Blue Cancel
			$CancelEvent = "and active_event=".C_NCD_CODE_BLUE_CALL;
			break;
		case C_NCD_ANSWER_CANCEL:		// 80= Answer Cancel event
			$CancelEvent = "and (active_event=".C_NCD_CALL_BUTTON." || active_event=".C_NCD_PATIENT_LINE_CALL." || active_event=".C_NCD_CALL_LINE_PLUG_OUT.")";
			$CtiEvent = C_CTI_NCD_ANSWER_CANCEL;
			break;
		case C_NCD_CALL_BE_ANSWERED:	// 81= Call be answered
			if (strlen($AnswerNo)) {
				$command ="Update `ncd_active_event` set answer_tm='".$AnswerTm."', answer_no='".$AnswerNo."' where ncd_no='".$NcdNo."' and proc_fg IS NULL and answer_no IS NULL";
//				syslog(LOG_INFO, "** doNcdKeyProc ** ".$command);		// for test
				$qry_phone = FUN_SQL_QUERY($command, $database);
			}
			$CtiEvent = C_CTI_NCD_CALL_BE_ANSWERED;
			break;
		case C_NCD_CALL_BUSY:			// 82= Call busy
			// ---- { Mike[2021/11/10] Modify
			$CheckEvent="(active_event=".C_NCD_BATH_BUTTON." OR active_event=".C_NCD_EMERGENCY_CALL." OR active_event=".C_NCD_CALL_LINE_PLUG_OUT.")";
			$command ="Select active_event from `ncd_active_event` where ncd_no='".$NcdNo."' and proc_fg IS NULL and $CheckEvent";
			$qry_phone = FUN_SQL_QUERY($command, $database);
			$query_num = FUN_SQL_NUM_ROWS($qry_phone);
			if ($query_num) {
				$row = FUN_SQL_FETCH_ARRAY($qry_phone);
				SetAutoRecall($NcdNo, $row['active_event']);
			}
//			$command ="Update `ncd_active_event` set proc_fg=2 where ncd_no='".$NcdNo."' and proc_fg IS NULL and answer_no IS NULL";
//			$qry_phone = FUN_SQL_QUERY($command, $database);
			// ---- }
			return[200, ""];
		case C_NCD_WET_LINE_PLUG_IN:	// 16= BW Wet Pull Cord plugged in (after Pull Cord plugged out)
		case C_NCD_BED_LINE_PLUG_IN:	// 18= BW Leave Bed Pull Cord plugged in (after Pull Cord plugged out)
		case C_NCD_WET_BED_DEACTIVE:	// 19= BW Wet status off (after Wet status on)
		case C_NCD_LEAVE_BED_DEACTIVE:	// 20= BW Leave Bed status off (after Leave Bed status on)
			break;
		default:
			return[400, "Wrong Key Code!"];

	}

//	syslog(LOG_INFO, "** doNcdKeyProc ** ActiveEvent=$ActiveEvent,AreaId=$AreaId,CalledNo=$CalledNo(".strlen($CalledNo)."),CancelEvent=$CancelEvent");		// for test
	// 4. Check store active event
	if ($ActiveEvent){ // Yes, Set active event of theis NCD
		// == Check the Event is send from PC or not
		$LedPanelFlag=1;
		if ($AreaId)		// Public area Event active
		{
			PublicCallProc($AreaId,$NcdName,$CalledNo,$ActiveEvent);
			echo "{ \"AreaName\":".$NcdName." }";
		}
		else if (strlen($CalledNo)==0 && $KeyCode!=C_NCD_NP_CALL_BUTTON && $KeyCode!=C_NCD_DOCTOR_PRESENCE) 
		{	// Check is receive event from external PC or not
			$url = "http://".$NcdIPAddr."/content.cgi?_method_=ncd6.htm?device=96?status=".$KeyCode;
			syslog(LOG_INFO, "** doNcdKeyProc ** curl_file_get_contents($url)");		// for test
			curl_file_get_contents($url);	// Send request to NCD for make call	// Mike[2021/09/16] file_get_contents -> curl_file_get_contents
			return[200, ""];
//			header("HTTP/1.1 200 OK");
//			exit();
		}	
		//== Check has set HIS server or not ==
		$pms_cfg="/var/www/conf/PMS.conf";
		$pms_ary=parse_ini_file($pms_cfg);
		if (strlen($pms_ary["HIS_URL"]))
			CheckGetCallNofromHis($NcdNo,$pms_ary["HIS_URL"],$pms_ary["StationID"]);
		//== Check send Event Indicate Mike[2022/01/18] ==
		if (strlen($pms_ary["EVENT_URL"]))
			CheckSendEventInd($NcdName,$pms_ary["EVENT_URL"],$pms_ary["StationID"],$KeyCode);
		// == Insert NCD event into record
		$objDateTime = new DateTime('NOW');
		$command = "INSERT INTO ncd_active_event (active_event,ncd_no,called_no,start_tm,ncd_name) VALUES (".$ActiveEvent.",'".$NcdNo."','".$CalledNo."','".$objDateTime->format('Y-m-d H:i:s')."','".$NcdName."')";
//		syslog(LOG_INFO, "** doNcdKeyProc ** ".$command);		// for test
		$qry_phone = FUN_SQL_QUERY($command, $database);
		// == Send Led control to the IP-NCD6 
		syslog(LOG_INFO, "** doNcdKeyProc ** Led1No=$Led1No, NcdIPAddr=$NcdIPAddr");		// for test
		if ($Led1No && $NcdIPAddr) {
			doNcdLedControl($Led1No,$Led2No,$Led3No,$LedPatten);
		}
	}
	// 5. Check Event cancel
	if ($CancelEvent) {
//		syslog(LOG_INFO, "** doNcdKeyProc ** CancelEvent=$CancelEvent");		// for test
		CheckCancelNCDEvent($NcdNo,$KeyCode);		// Mike[2021/11/18] Move into function process
		$LedPanelFlag=1;
		//== Check send Event Indicate Mike[2022/01/18] ==
		$pms_cfg="/var/www/conf/PMS.conf";
		$pms_ary=parse_ini_file($pms_cfg);
		if (strlen($pms_ary["EVENT_URL"]))
			CheckSendEventInd($NcdName,$pms_ary["EVENT_URL"],$pms_ary["StationID"],$KeyCode);
	}
		
	syslog(LOG_INFO, "** doNcdKeyProc ** CtiEvent/KeyCode=$CtiEvent/$KeyCode(".$cti_event[$KeyCode].")");	// for test
	if ($CtiEvent==0)
		$CtiEvent=$cti_event[$KeyCode];

	$conffile="/var/www/conf/PMS.conf";
	$data=fopen("$conffile","r+");
	$CtiFlag=0;
	while (!feof($data)){
		$jason=fgets($data);
		if(preg_match("/^PAR_CTI_ENABLE_FLAG=(.*)/",$jason,$none)){
			$CtiFlag=$none[1];
			break;
		}
	}
//	syslog(LOG_INFO, "** doNcdKeyProc ** (".$CtiFlag .") exec: /usr/share/asterisk/agi-bin/CTI_Tx ".$NcdNo." ".$CtiEvent);	// for test
	if ($CtiFlag) {
//		$big5NcdName=mb_convert_encoding($NcdName, "BIG5", "UTF-8");											// Mike[2021/08/26]
//		exec("/usr/share/asterisk/agi-bin/CTI_Tx ".$NcdNo." ".$CtiEvent." ".$big5NcdName,$output,$return_var);	// Don't do convert to BIG5
		exec("/usr/share/asterisk/agi-bin/CTI_Tx ".$NcdNo." ".$CtiEvent." ".$NcdName,$output,$return_var);		// / add ." ".$NcdName
	}

	if ($LedPanelFlag) {
		// --- { Mike[2021/10/08] add store Led Panel show message
		$panelType=0;
		$command = "select * from panelConf";
		$query = FUN_SQL_QUERY($command, $database);
		$max_record = FUN_SQL_NUM_ROWS($query);
		if ($max_record) {
			$row = FUN_SQL_FETCH_ARRAY($query);
			$activePropaganda	= $row['activePropaganda'];
			$panelType			= $row['panelType'];
			$panelSerialNo		= $row['panelSerialNo'];
			$panelTextNum		= $row['panelTextNum'];
			if ($panelType) {
				$command = "DELETE FROM panel_show_event";
				$qry_phone = FUN_SQL_QUERY($command, $database);
			}
		}
/*		$command = "select * from event_lang";
		$query = FUN_SQL_QUERY($command, $database);
		$qry_num = FUN_SQL_NUM_ROWS($query);
		for($i=0;$i<$qry_num;$i++){
			$row = FUN_SQL_FETCH_ARRAY($qry_phone);
			$EventText.$row['event_no']=$row['panel'];
		} */
		// --- }
		$command ="Select * from `ncd_active_event` where proc_fg IS NULL ";
		$qry_phone = FUN_SQL_QUERY($command, $database);
		$qry_num = FUN_SQL_NUM_ROWS($qry_phone);
		syslog(LOG_INFO, "** doNcdKeyProc ** proc_fg IS NULL enent number=$qry_num");	// for test
		if ($qry_num) {
			$cntP0=$cntP1=$cntP2=$cntP3=$cntP4=0;
			for($i=0;$i<$qry_num;$i++){
				$row = FUN_SQL_FETCH_ARRAY($qry_phone);
				$EventNo= intval($row['active_event'], 10);
				switch($EventNo) {
					case C_NCD_CALL_BUTTON:         // 1= Call from NCD6/PC Call Button
					case C_NCD_PATIENT_LINE_CALL:   // 3= Call from NCD6/PC Pull Cord Button
					case C_NCD_CALL_LINE_PLUG_OUT:  // 4= NCD6/PC Pull Cord plugged out
					case C_NCD_LEAVE_BED_ACTIVE:	// 12= BW Leave Bed status on
					case C_NCD_WET_BED_ACTIVE:		// 14= BW Wet Bed status on
					case C_NCD_DRIP_LOW:			// 47= Drip Low detect
						$PanelMsg_3[$cntP3]['EventNo']=$row['active_event'];
						$PanelMsg_3[$cntP3]['NcdNo']=$row['ncd_no'];
						$PanelMsg_3[$cntP3]['NcdName']=$row['ncd_name'];
//						syslog(LOG_INFO, "** doNcdKeyProc ** PanelMsg_3[$cntP3]=".$PanelMsg_3[$cntP3]['EventNo'].",".$PanelMsg_3[$cntP3]['NcdNo'].",".$PanelMsg_3[$cntP3]['NcdName']);	// for test
						$cntP3=$cntP3+1;
						break;
					case C_NCD_EMERGENCY_CALL:      // 6= Call from NCD6 SOS button/EC Call Button
						$PanelMsg_1[$cntP1]['EventNo']=$row['active_event'];
						$PanelMsg_1[$cntP1]['NcdNo']=$row['ncd_no'];
						$PanelMsg_1[$cntP1]['NcdName']=$row['ncd_name'];
						$cntP1=$cntP1+1;
						break;
					case C_NCD_STAFF_CALL_BUTTON:	// 21= Call from SC Call Button
						$PanelMsg_2[$cntP2]['EventNo']=$row['active_event'];
						$PanelMsg_2[$cntP2]['NcdNo']=$row['ncd_no'];
						$PanelMsg_2[$cntP2]['NcdName']=$row['ncd_name'];
						$cntP2=$cntP2+1;
						break;
					case C_NCD_BATH_BUTTON:         // 10= Call from BE Call Button
					case C_NCD_CODE_BLUE_CALL:		// 50= Code Blue detect
						$PanelMsg_0[$cntP0]['EventNo']=$row['active_event'];
						$PanelMsg_0[$cntP0]['NcdNo']=$row['ncd_no'];
						$PanelMsg_0[$cntP0]['NcdName']=$row['ncd_name'];
						$cntP0=$cntP0+1;
						break;
					case C_NCD_NP_CALL_BUTTON:      // 8= Nurse Presence  Mike[2021/07/19] delete	Mike[2021/11/30] recover & Modify
					case C_NCD_DOCTOR_PRESENCE:		// 35= Doctor Presence
						$PanelMsg_4[$cntP4]['EventNo']=$row['active_event'];
						$PanelMsg_4[$cntP4]['NcdNo']=$row['ncd_no'];
						$PanelMsg_4[$cntP4]['NcdName']=$row['ncd_name'];
						$cntP4=$cntP4+1;
					break;
				}
			}
			syslog(LOG_INFO, "** doNcdKeyProc ** panelType=$panelType, cntP= $cntP0/$cntP1/$cntP2/$cntP3, active_event=$EventNo/".$row['active_event']);	// for test
			if ($cntP0<>0) {			// Priority 1: PanelMsg_0 - BathRoom & Call Blue
				$PanelMsgs=$PanelMsg_0;
				$Ncd3Msg=DoSendLedPanel($PanelMsg_0,C_NCD3_BATHROOM_CALL);
			}
			else if ($cntP1<>0) {		// Priority 2: PanelMsg_1 - Emergency Call
				$PanelMsgs=$PanelMsg_1;
				$Ncd3Msg=DoSendLedPanel($PanelMsg_1,C_NCD3_EMERGENCY);
			}
			else if ($cntP2<>0) {		// Priority 3: PanelMsg_2 - Staff Call
				$PanelMsgs=$PanelMsg_2;
				$Ncd3Msg=DoSendLedPanel($PanelMsg_2,C_NCD3_STAFF_CALL);
			}
			else if ($cntP3<>0) {	 	// Priority 4: PanelMsg_3 - the others
				$PanelMsgs=$PanelMsg_3;
				$Ncd3Msg=DoSendLedPanel($PanelMsg_3,C_NCD3_NORMAL_CALL);
			}
			else if ($cntP4<>0) {	 	// Priority 5: PanelMsg_4 - Nurse/Doctor Presence	Mike[2021/11/30]
				$PanelMsgs=$PanelMsg_4;
				$Ncd3Msg=DoSendLedPanel($PanelMsg_4,C_NCD3_NO_EVENT);
			}
			else
				$Ncd3Msg="BBBB";
		}
		else
			$Ncd3Msg="BBBB";
		
		if ($panelType)							// Mike[2021/11/29] Move from below, let Led panel can quickly then NCD3 display
			LedPanelReq("TxPanelReq");

//		syslog(LOG_INFO, "** doNcdKeyProc ** Ncd3Msg=$Ncd3Msg");	// for test
		$command = "select p.Led_assign,p.IP,s.ipaddr,s.name from phone AS p LEFT JOIN sip_buddies AS s";			// Mike[2021/11/29]
		$command = $command." ON p.phoneno = s.name WHERE ipaddr is not null and ipaddr <> ''";	//
//		$command = "select ipaddr, name from sip_buddies WHERE ipaddr is not null and ipaddr <> ''";			// / Mike[2021/11/16] Debug sometime the display in 取消處理盤 can't work
//		$command = "select IP, phone_type from phone WHERE IP is not null and IP <> ''";		// / Mike[2021/09/13] add phone_type ( and phone_type ==".C_IP_NCD)
		$queryId = FUN_SQL_QUERY($command, $database);
		$query_num= FUN_SQL_NUM_ROWS($queryId);
//		syslog(LOG_INFO, "** doNcdKeyProc ** $command($query_num):$Ncd3Msg");	// for test
		for($i=0;$i<$query_num;$i++){
			$row = FUN_SQL_FETCH_ARRAY( $queryId);
			$NcdIPAddr = $row['ipaddr'];		// Mike[2021/11/16]
//			$PhoneType = $row['phone_type'];	//
//			$NcdIPAddr = $row['IP'];			// /
			$phoneno   = $row['name'];					// ---- [ Mike[2021/11/29]
			$LedAssign = $row['Led_assign'];
//			syslog(LOG_INFO, "** doNcdKeyProc-1 ** Led_assign=$Led_assign");	// for test
			if ($LedAssign == $phoneno && $Ncd3Msg) {	// ---- ] if want check all, need add (IS_NULL($LedAssign) or $LedAssign=="" or) 
				$url = "http://".$NcdIPAddr."/content.cgi?_method_=ncd6.htm?device=128?status=".$Ncd3Msg;
				syslog(LOG_INFO, "** doNcdKeyProc ** file_get_contents:$url");	// for test
				curl_file_get_contents($url);	// Mike[2021/09/16] file_get_contents -> curl_file_get_contents
			}
			// ---- { Mike[2021/11/18]
			if ($CancelEvent) {
				$index=0;
				for($ind=1;$ind<10;$ind++) {
					$LedCtrl=$CancelLedNo[$phoneno][$ind]??0;
//					syslog(LOG_INFO, "** doNcdKeyProc ** CancelLedNo[$phoneno][$ind]=$LedCtrl");	// for test
					if ($LedCtrl) {
						$Led[$index]=$ind;
						$index++;
					}
				}
				for($ind=0;$ind<$index;$ind++) {
					$Led1 = $Led[$ind]??0;
					$ind++;
					$Led2 = $Led[$ind]??0;
					$ind++;
					$Led3 = $Led[$ind]??0;
					doNcdLedControl($Led1,$Led2,$Led3,C_LED_CTRL_OFF);
				}
			}
			// ---- }
		}
	}
//	syslog(LOG_INFO, "** doNcdKeyProc ** Finish exec CTI_Tx ");	// for test
	return[200, ""];
//	header("HTTP/1.1 200 OK");
}

/*
 * Send Web API Led control to IP-NCD6
 * Iput:Led1No & Led2No = control Led number 
 *		LedPatten = Led control pattern
 */
function doNcdLedControl($Led1No, $Led2No, $Led3No, $LedPatten)
{
	global $NcdIPAddr,$NcdLedIPAddr;
	
	$ch = curl_init();
	$timeout = 1; // 100; // set to zero for no timeout	Mike[2021/09/16] 0 -> 1
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);	//Set CURLOPT_RETURNTRANSFER so that the content is returned as a variable.
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);	//Set CURLOPT_FOLLOWLOCATION to true to follow redirects.
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);		// Mike[2021/09/16]
//	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);	// /
	if ($Led1No) {
		$getURL="http://".$NcdIPAddr."/content.cgi?_method_=ncd6.htm?device=".$Led1No."?status=".$LedPatten;
		syslog(LOG_INFO, "** doNcdLedControl ** ".$getURL);		// for test
		curl_setopt($ch, CURLOPT_URL, $getURL);			//Set the URL that you want to GET by using the CURLOPT_URL option.
		$data = curl_exec($ch);							//Execute the request.
		if (curl_errno ( $ch )) {
			syslog(LOG_ERR, "** doNcdLedControl ** ".curl_error($ch) );
//			curl_close ( $ch );								// Mike[2021/09/16] Del, sometime can't get response
//			exit ();										// /
		}
	}

	if ($Led2No) {
		if ($NcdLedIPAddr)
			$getURL="http://".$NcdLedIPAddr."/content.cgi?_method_=ncd6.htm?device=".$Led2No."?status=".$LedPatten;
		else
			$getURL="http://".$NcdIPAddr."/content.cgi?_method_=ncd6.htm?device=".$Led2No."?status=".$LedPatten;
		syslog(LOG_INFO, "** doNcdLedControl ** ".$getURL);		// for test
		curl_setopt($ch, CURLOPT_URL, $getURL);			//Set the URL that you want to GET by using the CURLOPT_URL option.
		$data = curl_exec($ch);							//Execute the request.
	}
	if ($Led3No) {
		if ($NcdLedIPAddr)
			$getURL="http://".$NcdLedIPAddr."/content.cgi?_method_=ncd6.htm?device=".$Led3No."?status=".$LedPatten;
		else
			$getURL="http://".$NcdIPAddr."/content.cgi?_method_=ncd6.htm?device=".$Led3No."?status=".$LedPatten;
		syslog(LOG_INFO, "** doNcdLedControl ** ".$getURL);		// for test
		curl_setopt($ch, CURLOPT_URL, $getURL);			//Set the URL that you want to GET by using the CURLOPT_URL option.
		$data = curl_exec($ch);							//Execute the request.
	}
	if ( $LedPatten != C_LED_CTRL_OFF )	{				// Mike[2021/11/17]
		if ($NcdLedIPAddr)
			$getURL="http://".$NcdLedIPAddr."/content.cgi?_method_=ncd6.htm?device=".C_RELAY_LED_NO."?status=".C_LED_CTRL_ON;
		else
			$getURL="http://".$NcdIPAddr."/content.cgi?_method_=ncd6.htm?device=".C_RELAY_LED_NO."?status=".C_LED_CTRL_ON;
		syslog(LOG_INFO, "** doNcdKeyProc ** ".$getURL);		// for test
		curl_setopt($ch, CURLOPT_URL, $getURL);			//Set the URL that you want to GET by using the CURLOPT_URL option.
		$data = curl_exec($ch);							//Execute the request.
	}
	curl_close($ch);
}

function curl_file_get_contents($durl){
	$ch = curl_init();
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
 * Send Web API to HIS server for get shift assignment
 * Iput:NcdNo = NCD number 
 *		HisUrl = HIS Web API URL
 */
function CheckGetCallNofromHis($NcdNo,$HisUrl,$StationID)
{
	$AccessUrl = $HisUrl.'GetBidNrs/webservice.asmx/GetBidNrs?chBedNo='.$NcdNo;
	// GET Url Process
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $AccessUrl);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_SSLv2 );	// Mike[2021/09/14] CURL_SSLVERSION_SSL, 2
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_TIMEOUT, 1);	// Mike[2022/01/22] set to zero for no timeout
	$data = curl_exec($ch);
	
	// POST Url Process
	$postDataArr = array(
		'StationNo'=>$StationID,
		'BedNo'=>$NcdNo,
	);	
	$postJosnData = json_encode($postDataArr);
	$ch = curl_init($HisUrl);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postJosnData);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_TIMEOUT, 1);	// Mike[2022/01/22] set to zero for no timeout
	$data = curl_exec($ch);
	
	$response = json_decode($data);
	return $response->access_token;
}

/*
 * Send Web API to HIS server for get shift assignment
 * Iput:NcdName = NCD NAme 
 *		HisUrl = HIS Web API URL
 *		StationID = Ward Station ID
 *		KeyCode = Event Key Code
 * Date: Mike[2022/01/18]
 */
function CheckSendEventInd($NcdName,$EventUrl,$StationID,$KeyCode)
{
	$AccessUrl = $EventUrl.'PushNCallNotice';
	switch($KeyCode) {
		case C_NCD_CALL_LINE_PLUG_OUT:
			$EventType="cordout";
			break;
		case C_NCD_CALL_BUTTON:
		case C_NCD_PATIENT_LINE_CALL:
			$EventType="ring";
			break;
		case C_NCD_EMERGENCY_CALL:
			$EventType="urgent";
			break;
		case C_NCD_CANCEL_BATH_BUTTON:
			$AccessUrl = $EventUrl.'ClearNCallNotice';
		case C_NCD_BATH_BUTTON:
			$EventType="toilet";
			break;
		case C_NCD_CANCEL_CALL_BUTTON:
			$AccessUrl = $EventUrl.'ClearNCallNotice';
			$EventType="all";
			break;
		case C_NCD_ANSWER_CANCEL:
			$AccessUrl = $EventUrl.'ClearNCallNotice';
			$EventType="ring";
			break;
		case C_NCD_CALL_LINE_PLUG_IN:
			$AccessUrl = $EventUrl.'ClearNCallNotice';
			$EventType="cordout";
			break;
		default: 
			return;
	}
	// POST Url Process
	$postDataArr = array(
//		'StationNo'=>$StationID,
		'BedName'=>$NcdName,
		'Code'=>$EventType,
	);
    $postFormData = http_build_query($postDataArr);			// Mike[2022/01/24]
//	$postJosnData = json_encode($postDataArr);				//
	if ( ($ch=curl_init()) ) {								// / $AccessUrl
	// ---- { Mike[2022/01/24] Modify for using formdata
		curl_setopt($ch, CURLOPT_URL, $AccessUrl."?");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postFormData);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		// ---- } { using JSON
/*		curl_setopt($ch, CURLOPT_HEADER, FALSE);				
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postFormData);							// Mike[2022/01/24] postJosnData -> postFormData
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
*/
		curl_setopt($ch, CURLOPT_TIMEOUT, 1);	// Mike[2022/01/22] set to zero for no timeout
		syslog(LOG_INFO, "** CheckSendEventInd ** $AccessUrl");		// for test
		$data = curl_exec($ch);
		if (curl_errno ( $ch ))
			syslog(LOG_ERR, "** CheckSendEventInd: curl_exec Error ** ".curl_error($ch) );
		else {
			$response = json_decode($data);
			$rspState = $response->State??"";
			if ( $rspState != "100" && $rspState != "106" && strlen($rspState) != 0 )
				syslog(LOG_ERR, "** CheckSendEventInd: 'BedNo'=>$NcdName,'Code'=>$EventType rsp Error: ($rspState)".$response->Message );
		}
	}
	else
		syslog(LOG_ERR, "** CheckSendEventInd: curl_init($AccessUrl) Error ** ".curl_error($ch) );

}

/*
 * Receive Web API command fro external PC for Public Are event process
 * Iput:AreaId = Public Area ID 1~10 
 *		AreaName = Public Area Name
 *		CalledNo = ring assign party
 *		ActiveEvent = Event type
 */
function PublicCallProc($AreaId,$AreaName,$CalledNo,$ActiveEvent)
{
	$tempCallFileName = "/tmp/Public$AreaId.call";
	$autoCallFloder = "/var/spool/asterisk/outgoing/";
    $contents = "Channel: Local/".$CalledNo."@ael-nurse_public\r\nWaitTime: 0\r\nCallerID: ".$AreaName."\r\nContext: ael-NursePublicAnswer\r\nExtension: $AreaId\r\nSet: EventNo=$ActiveEvent";
//	echo "$contents<br>";		// for test
    file_put_contents($tempCallFileName, $contents);     // Save our content to the file.
	system("mv $tempCallFileName $autoCallFloder" );
}

/*
 * Send show Led panel message control to IP-NCD6
 * Iput:Led1No & Led2No = control Led number 
 *		LedPatten = Led control pattern
 */
function DoSendLedPanel($PanelMsgs,$EventId)
{
	global $database, $panelType;
	$Ncd3Msg="";
	foreach($PanelMsgs as $ShowMsg) {
		// for show 丞瑋 7-segment Led message
		if ( $EventId != C_NCD3_NO_EVENT ) {	// Mike[2021/11/30]
			$ShowEvent=$EventId;
			if ($EventId==C_NCD3_STAFF_CALL || $EventId==C_NCD3_BATHROOM_CALL)
				$NcdNum=substr($ShowMsg['NcdNo'],-3, 2).'b';
			else {
				$NcdNum=substr($ShowMsg['NcdNo'],-3);
				if ($EventId==C_NCD3_NORMAL_CALL && $ShowMsg['EventNo']==C_NCD_CALL_LINE_PLUG_OUT)
					$ShowEvent=C_NCD3_LINE_DROP;
			}
			$Ncd3Msg=$Ncd3Msg.$ShowEvent.$NcdNum;
			syslog(LOG_INFO, "** DoSendLedPanel ** ($EventId,".$ShowMsg['NcdNo'].") -> [$ShowEvent,$NcdNum]; panelType=".$GLOBALS['panelType']);	// for test
		}
		// --- { Mike[2021/10/08] add store Led Panel show message
		syslog(LOG_INFO, "** DoSendLedPanel ** panelType=$panelType");	// for test
		if ($panelType) {
			// --- { Mike[2021/11/04] for test
			$active_event	=$ShowMsg['EventNo'];
			$ncd_no			=$ShowMsg['NcdNo'];
			$ncd_name		=$ShowMsg['NcdName'];
			$nameLength = strlen($ncd_name);
			if ( $nameLength )
				$EventMessage=$ncd_name;
			else {
				$nameLength = strlen($ncd_no);
				$EventMessage=$ncd_no;
			}
			// ---- { Temp for 丞瑋 panel modify
			if ($nameLength < 8)
				$EventMessage=$EventMessage.substr("        ",0,8-$nameLength);
//			if ($nameLength&0x01)
//				$EventMessage=$EventMessage." ";
			// ---- }
			$command  = "select panel from event_lang WHERE event_no=".$ShowMsg['EventNo'];
			$queryId  = FUN_SQL_QUERY($command, $database);
			$query_num= FUN_SQL_NUM_ROWS($queryId);
			if ($query_num) {
				$row = FUN_SQL_FETCH_ARRAY( $queryId);
				$EventMessage=$EventMessage.$row['panel'];
			}
			$big5Message=mb_convert_encoding($EventMessage, "BIG5", "UTF-8");
			syslog(LOG_INFO, "** DoSendLedPanel ** Big5String-$big5Message(".strlen($big5Message).")");		// for test
			$command = "INSERT INTO panel_show_event (active_event,showMsg) VALUES ($active_event,\"$big5Message\")";
//			$command = "INSERT INTO panel_show_event (active_event,ncd_no,ncd_name) VALUES (".$ShowMsg['EventNo'].",'".$ShowMsg['NcdNo']."','".$ShowMsg['NcdName']."')";
			// --- }
			$qry_phone = FUN_SQL_QUERY($command, $database);
		}
		// --- }
	}
	return($Ncd3Msg);
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
	system("sudo cp $tempCallFileName $autoRecallFileName" );
	system("sudo /bin/rm $tempCallFileName" );
	syslog(LOG_INFO, "** SetAutoRecall** $recallTime * *  * root (php /var/www/html/NcdKeyCode.php $NcdNo $KeyCode");		// for test
	exec("sudo service cron reload");		// Mike[2021/11/11]
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
		exec("sudo service cron reload");									// /
	}
}

/*
 * Check cancel NCD event
 * Iput:NcdNo = Ncd Number 
 * Date: Mike[2021/11/18] Move & modify
 */
function CheckCancelNCDEvent($NcdNo,$KeyCode) {
	global $database,$AnswerTm,$AnswerNo,$CancelEvent,$NcdIPAddr,$Led_assign,$SensorField,$EventLed1No,$EventLed2No;
	global $CancelLedNo;

//	syslog(LOG_INFO, "** CheckCancelNCDEvent ** CancelEvent=$CancelEvent");		// for test
	if ($KeyCode == C_NCD_CANCEL_CALL_BUTTON)
		$cmdWhere = "WHERE ncd_no in (SELECT phoneno FROM `phone` WHERE Led_assign='$NcdNo' OR phoneno='$NcdNo') AND proc_fg IS NULL ";
	else
		$cmdWhere = "WHERE ncd_no='".$NcdNo."' AND proc_fg IS NULL ";
	if (strlen($AnswerTm) and $AnswerTm<> "0")
		$UpdateCmd ="Update `ncd_active_event` set proc_fg=1, answer_tm='$AnswerTm', answer_no='$AnswerNo' ";
	else
		$UpdateCmd ="Update `ncd_active_event` set proc_fg=1";
	$command ="SELECT ncd_no, start_tm, active_event FROM `ncd_active_event` ".$cmdWhere.$CancelEvent;	// Mike[2021/08/26] $CancelWhere -> $cmdWhere$CancelEvent
	$qry_phone = FUN_SQL_QUERY($command, $database);
	$cancel_num = FUN_SQL_NUM_ROWS($qry_phone);
	if ( $Led_assign )					// Mike[2021/11/29] remove == 0
		$Led2Control = $Led_assign;
	else
		$Led2Control = $NcdNo;
//	syslog(LOG_INFO, "** CheckCancelNCDEvent ** Led_assign=$Led_assign/$Led2Control");	// for test
	for($i=0;$i<$cancel_num;$i++){
		$row = FUN_SQL_FETCH_ARRAY($qry_phone);
		$eventNcdNo  =$row['ncd_no'];
		$active_event=$row['active_event'];
		$CancelTm=strtotime("NOW")-strtotime($row['start_tm']);
		$ExecUpdateCmd=$UpdateCmd.", cancel_tm=$CancelTm $cmdWhere $CancelEvent and start_tm='".$row['start_tm']."' ";	// Mike[2022/01/19] add .$CancelEvent
		syslog(LOG_INFO, "** CheckCancelNCDEvent ** ".$ExecUpdateCmd);	// for test
		$qry_update=FUN_SQL_QUERY($ExecUpdateCmd, $database);
		$CancelLedNo[$eventNcdNo][$EventLed1No[$active_event]]=1;	// Set be cancel event led
//		syslog(LOG_INFO, "** CheckCancelNCDEvent ** CancelLedNo[$eventNcdNo][$EventLed1No[$active_event]]=1");	// for test
		if ($active_event == C_NCD_CALL_LINE_PLUG_OUT ) {	// ---- [ Mike[2021/11/05] For 丞瑋 脫落 Display 2 color led 
			$CancelLedNo[$Led2Control][C_CALL_LED2_NO]=1;
			$CancelLedNo[$Led2Control][C_EMERGENCY_CALL_LED2_NO]=1;
		}
		else {												// ---- ]
			$CancelLedNo[$Led2Control][$EventLed2No[$active_event]]=1;	// Mike[2021/11/03]
//			syslog(LOG_INFO, "** CheckCancelNCDEvent ** CancelLedNo[$Led2Control][$EventLed2No[$active_event]]=1");	// for test
		}
	}
	
	// 5.1. Check send cancel Led control to the IP-NCD6 
	if ($NcdIPAddr) {
		$seletInPhone="SELECT phoneno FROM `phone` WHERE Led_assign='$NcdNo' OR phoneno='$NcdNo'";
		if ( $Led_assign and $Led_assign != $NcdNo)		// Mike[2021/11/29] add check $Led_assign != $NcdNo
			$seletInPhone=$seletInPhone." OR Led_assign='$Led_assign' OR phoneno='$Led_assign'";
		$WhereCheck="WHERE ncd_no in ($seletInPhone)";
		$command ="SELECT active_event,ncd_no from `ncd_active_event` $WhereCheck AND proc_fg IS NULL ";
		$qry_phone = FUN_SQL_QUERY($command, $database);
		$qry_num = FUN_SQL_NUM_ROWS($qry_phone);
		syslog(LOG_INFO, "** CheckCancelNCDEvent ** $command -> $qry_num");	// for test
		if ( $qry_num ) {				// Mike[2021/08/26]
			$autoRecallEvent=0;			// Mike[2022/01/20]
			for($i=0;$i<$qry_num;$i++){
				$row = FUN_SQL_FETCH_ARRAY($qry_phone);
				$eventNcdNo  =$row['ncd_no'];
				$active_event=$row['active_event'];
				syslog(LOG_INFO, "** CheckCancelNCDEvent ** Still has Event-$i:".$row['ncd_no']."/".$active_event);	// for test
				if ($NcdNo==$row['ncd_no']) {
					$CancelLedNo[$eventNcdNo][$EventLed1No[$active_event]]=0;
//					syslog(LOG_INFO, "** CheckCancelNCDEvent ** CancelLedNo[$eventNcdNo][$EventLed1No[$active_event]]=0");	// for test
					// ---- { Mike[2021/11/08] for auto recall
					if ($active_event == C_NCD_BATH_BUTTON || $active_event == C_NCD_EMERGENCY_CALL 
															|| $active_event == C_NCD_CALL_LINE_PLUG_OUT)
						$autoRecallEvent=$active_event;
					// ---- }
				}
				$CancelLedNo[$Led2Control][$EventLed2No[$active_event]]=0;	// Mike[2021/11/03]
//				syslog(LOG_INFO, "** CheckCancelNCDEvent ** CancelLedNo[$Led2Control][$EventLed2No[$active_event]]=0");	// for test
			}
			if ($autoRecallEvent)
				SetAutoRecall($NcdNo, $autoRecallEvent);
		}
		else {		// ---- [ Mike[2021/08/26] Move & Modify
		// All Event be canceled -> Check is receive cancel event from sensor detect || C_NCD_CALL_LINE_PLUG_IN
			if ($SensorField || $KeyCode == C_NCD_CALL_LINE_PLUG_IN || $KeyCode == C_NCD_CANCEL_CALL_BUTTON) {
				$cancelList=array();
				if ($KeyCode == C_NCD_CANCEL_CALL_BUTTON) {
					$qry_phone = FUN_SQL_QUERY($seletInPhone, $database);
					$qry_num = FUN_SQL_NUM_ROWS($qry_phone);
					for($i=0;$i<$qry_num;$i++) {
						$row = FUN_SQL_FETCH_ARRAY($qry_phone);
						array_push($cancelList, $row['phoneno']);
					}
				}
				else
					array_push($cancelList, $NcdNo);

				// -> search call channel then hang up this call
				/* Get Channel Information
					core show channels concise
					SIP/520-00000387!out!~~s~~!124!Up!Dial!SIP/28206@atc-itu,60,tkg!520!!!3!122!af02750f-94df-469a-b751-645c2df1d6a6!1568971797.903
					SIP/515-00000384!out!~~s~~!124!Up!Dial!SIP/515@atc-itu,60,tkg!515!!!3!134!f0303274-c65f-4702-8f24-ebcb8fb0821d!1568971785.900
					SIP/atc-itu-00000388!default!!1!Up!AppDial!(Outgoing Line)!6441!!!3!122!af02750f-94df-469a-b751-645c2df1d6a6!1568971797.904
					SIP/atc-itu-00000386!default!!1!Up!AppDial!(Outgoing Line)!6441!!!3!133!f0303274-c65f-4702-8f24-ebcb8fb0821d!1568971786.902
				 */
				$channel_data= API_command("core show channels concise");
				foreach ($channel_data as $read_line) {	
					if (strstr($read_line, "SIP/")) {
						list($channel, $context, $extension, $prio, $state, $app, $data, $callerID, $tmp1, $tmp2, $tmp3, $dur, $account) =
							explode("!", $read_line, 13);
//						syslog(LOG_INFO, "$read_line");				// Mike for test
						$channel_info= explode(":", $channel);
//						syslog(LOG_INFO, "-> $channel_info[1], $context, $extension, $callerID");				// Mike for test
						if (in_array($callerID,$cancelList))
						{
							syslog(LOG_INFO, "API_command(channel request hangup $channel_info[1])");				// Mike for test
							API_command("channel request hangup ".$channel_info[1]);
						}
					}
				}
			}
			RemoveAutoRecall($NcdNo);							// Mike[2021/11/08] for auto recall
			doNcdLedControl(C_RELAY_LED_NO,0,0,C_LED_CTRL_OFF);	// Mike[2021/11/17]
		}			// ---- ]
/*
		$qry_phone = FUN_SQL_QUERY($seletInPhone, $database);
		$qry_num = FUN_SQL_NUM_ROWS($qry_phone);
		syslog(LOG_INFO, "** doNcdKeyProc ** $command -> $qry_num");	// for test
		if ( $qry_num ) {		// Mike[2021/08/26]
			for($phoneIndex=0;$phoneIndex<$qry_num;$phoneIndex++){
				$row = FUN_SQL_FETCH_ARRAY($qry_phone);
				$phoneno=$row['phoneno'];
				for($i=1;$i<9;$i++) {
					$Led1=$CancelLedNo[$phoneno][$i]??0;
					if ($Led1)
						doNcdLedControl($i,0,0,C_LED_CTRL_OFF);
					if ($CancelLedNo[$i])						// Mike[2021/11/03]
						doNcdLedControl(0,$i,0,C_LED_CTRL_OFF);	// /
				}
			}
		}
*/
	}
}

?>