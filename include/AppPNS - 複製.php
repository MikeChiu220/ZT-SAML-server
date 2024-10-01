<?php
// Input:	$env = 'PRODUCTION' or 'DEVELOPMENT', indicate the APP is in production or development state, only for iOS used, 
//			$device_type = 'iOS' or 'Android', indicate send the notify to iOS or Android APP
//			$token = the device token of the be notified mobile
//			$notify_type = 'Alt', 'Call' or 'Ind'
//			$notify_msg = the send notify message
function send_mobile_notification_request($env, $device_type, $token, $notify_type, $notify_msg)
{
    //Default result
    $result = -1;

//	syslog (LOG_NOTICE, "$device_type-$token sending $notify_type: $notify_msg");		// for test
//	echo "device_type=$device_type<br>\n";	// for test
//	echo "token=$token<br>\n";				// for test
//	echo "notify_msg=$notify_msg<br>\n";	// for test
    if ($device_type == 'iOS') {

        $keyfile = 'Hybrex_AuthKey_XK6P4FDSDL.p8';        # <- Your AuthKey file
        $keyid = 'XK6P4FDSDL';                            # <- Your Key ID
        $teamid = '96RU85A92F';                           # <- Your Team ID (see Developer Portal)        $apns_url = NULL;

        $apns_cert = NULL;
        //Apple server listening port
        $apns_port = 2195;

        if ($env == 'PRODUCTION')
            $apns_url = 'https://api.push.apple.com';              # <- production url
        else
            $apns_url = 'https://api.development.push.apple.com';  # <- development url

        $body = array();
        $body['aps'] = array();
        $body['aps']['content-available'] = 1 ;
        $body['aps']['sound'] = "";				  # Mike[2020/11/18] for iOS14 APNS can't work test "default"
        $bundleid = 'com.hybrex.athome.voip';             # <- Your Bundle ID for VOIP call kit
        if ($notify_type == 'Alt' || $notify_type == 'Call') {		// Mike[2020/10/27] Move $notify_type == 'Alt' ||  from below for test security can't wakeup
            $body['IncomingCall']['incoming'] = 1 ;
			$pushType='voip';					// Mike[2021/01/07]
			$priority='10';						// /
        }
        else if ($notify_type == 'Ind')  {
//            $body['aps']['alert'] = array();				// Mike[2020/11/24] Del for test
            $body['SecurityMessage'] = array();
            $body['SecurityMessage']['type'] = $notify_type;
            if ($notify_msg)
                $body['SecurityMessage']['data'] = $notify_msg;
            if ($notify_type == 'Ind')
                $bundleid = 'com.hybrex.athome';                  # <- Your Bundle ID
			$pushType='background';				// Mike[2021/01/07] Miek[2021/02/17] alert -> background 
			$priority='5';						// /
        }

        $payload = json_encode($body);

        $key = openssl_pkey_get_private('file:///var/www/html/'.$keyfile);
		if ($key==FALSE) {
			$status = openssl_error_string();
			syslog (LOG_ERR, "openssl_pkey_get_private Error: $status");		// for test
		}

        $header = ['alg'=>'ES256','kid'=>$keyid];
        $claims = ['iss'=>$teamid,'iat'=>time()];

        $header_encoded = base64($header);
        $claims_encoded = base64($claims);

        $signature = '';
        openssl_sign($header_encoded . '.' . $claims_encoded, $signature, $key, 'sha256');
        $jwt = $header_encoded . '.' . $claims_encoded . '.' . base64_encode($signature);

        // only needed for PHP prior to 5.5.24
        if (!defined('CURL_HTTP_VERSION_2_0')) {
            define('CURL_HTTP_VERSION_2_0', 3);
        }

//		print 'Will send to: ' . "$url/3/device/$token with $payload";
		echo 'Will send to: ' . "$url/3/device/$token with $payload";
  
        $http2ch = curl_init();
        curl_setopt_array($http2ch, array(
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
          CURLOPT_URL => "$apns_url/3/device/$token",
          CURLOPT_PORT => 443,
          CURLOPT_HTTPHEADER => array(
            "apns-topic: {$bundleid}",
//            "apns-push-type: $pushType",		// Mike[2021/01/07] Mike[2021/02/17] Del, will cause the notify cn't be received
            "apns-priority: $priority",			// /
            "authorization: bearer $jwt"
          ),
          CURLOPT_POST => TRUE,
          CURLOPT_POSTFIELDS => $payload,
          CURLOPT_RETURNTRANSFER => TRUE,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HEADER => 1
        ));

        $result = curl_exec($http2ch);
        if ($result === FALSE) {
			$status = curl_error($http2ch);						// for test
			syslog (LOG_ERR, "Push Notify Error: $status");		// for test
			echo "Push Notify Error: $status";		// for test
			throw new Exception("Curl failed: ".curl_error($http2ch));
        }

		$status = curl_getinfo($http2ch, CURLINFO_HTTP_CODE);	// for test
		syslog (LOG_NOTICE, "Send notify to: $apns_url/3/device/$token with $payload -> result: $status");		// for test
		echo "result: $status<br>\n";							// for test
        curl_close($http2ch);
    }
    else if ($device_type == "Android") {

        // API access key from Google FCM App Console
        if (!defined('API_ACCESS_KEY'))		// Mike[2020/12/17]
			define('API_ACCESS_KEY', 'AAAAq3gXWSg:APA91bEllF0qnDlxZHXyOCwBsx7mtCl0ZIZvaTPnt1zFN5bYYLRRyB9ngk5PBniSi3aHUsbiVqCSVENVgrQZABtwUs9CvDMej4n8hYcp4ORsPMxX5mlytuurWTfyI6IttckV8z9ZdOAR');

		// generated via the cordova phonegap-plugin-push using "senderID" (found in FCM App Console)
		// this was generated from my phone and outputted via a console.log() in the function that calls the plugin
		// my phone, using my FCM senderID, to generate the following registrationId 
//		$singleID = $token; 
//		$registrationIDs = array(
//			 $token, 
//			 $token
//			 $token
//		) ;
		// prep the bundle
		// to see all the options for FCM to/notification payload: 
		// https://firebase.google.com/docs/cloud-messaging/http-server-ref#notification-payload-support 

		// 'vibrate' available in GCM, but not in FCM
//		$fcmMsg = array(
//			'body' => 'here is a message. message',
//			'title' => 'This is title #1',
//			'sound' => "default",
//			'color' => "#203E78" 
//		);
        $fcmMsg = array();
        if ($notify_type == 'Alt' || $notify_type == 'Call') {		// Mike[2020/10/27] Move $notify_type == 'Alt' ||  from below for test security can't wakeup
            $fcmMsg['IncomingCall']['incoming'] = 1 ;
        }
        else if ($notify_type == 'Ind')  {
            $fcmMsg['security_message'] = array();
            $fcmMsg['security_message']['type'] = $notify_type;
            if ($notify_msg)
                $fcmMsg['security_message']['data'] = $notify_msg;
        }
		// I haven't figured 'color' out yet.  
		// On one phone 'color' was the background color behind the actual app icon.  (ie Samsung Galaxy S5)
		// On another phone, it was the color of the app icon. (ie: LG K20 Plush)
		$fcmFields = array(
			'to' => $token,  							// expecting a single ID
//			'registration_ids' => $registrationIDs ;	// expects an array of ids
			'collapse_key : type_a',
			'data' => $fcmMsg
//			'priority' => 'high',	// options are normal and high, if not set, defaults to high.
//			'notification' => $fcmMsg
		);

		$headers = array(
			'Authorization: key=' . API_ACCESS_KEY,
			'Content-Type: application/json'
		);
		 
		$ch = curl_init();
		if ($ch <> FALSE ) {
			curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
			curl_setopt( $ch,CURLOPT_POST, true );
			curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
			curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fcmFields ) );
			$result = curl_exec($ch );
//			echo "===== Send FCM Push notify =====<br>\n";			// for test
//			echo json_encode( $fcmFields );							// for test
//			echo "<br>\n===== Send FCM Push notify =====<br>\n";	// for test
			if ($result == FALSE)
				echo "curl_exec Error !";
			else {
//				echo "curl_exec sucess<br>\n";
				$resultArray = json_decode($result,true,3);
				if ($resultArray['failure']==1) 
				{
					echo "result: ";
					print_r($resultArray['results']);
					echo "<br>\n";
				}
//				else												// for test
//					echo "result: success<br>\n";					// for test
			}
			curl_close( $ch );
		}
		else
			echo "curl_init Error !";
    }
    return $result > 0;
}

//Create json file to send to Apple/Google Servers with notification request and body
function create_payload_json($message) {
    //Badge icon to show at users ios app icon after receiving notification
    $badge = "0";
    $sound = 'default';

    $payload = array();
    $payload['aps'] = array('alert' => $message, 'badge' => intval($badge), 'sound' => $sound);
    return json_encode($payload);
}

function base64($data) {
    return rtrim(strtr(base64_encode(json_encode($data)), '+/', '-_'), '=');
}

?>