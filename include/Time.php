<?php
const C_SERVER_METHOD = 1;

// ---- NTP update time Select ---- Mike[2021/08/31]
$ntpTime = array(
    "Disable",$strServerMethod,$str10Min,$str15Min,$str30Min,$str1Hour,$str4Hour,$str8Hour,$str1Day
);

# Example of job definition:
# .---------------- minute (0 - 59)
# |  .------------- hour (0 - 23)
# |  |  .---------- day of month (1 - 31)
# |  |  |  .------- month (1 - 12) OR jan,feb,mar,apr ...
# |  |  |  |  .---- day of week (0 - 6) (Sunday=0 or 7) OR sun,mon,tue,wed,thu,fri,sat
# |  |  |  |  |
# *  *  *  *  * user-name command to be executed
#		*  *  * root (/usr/sbin/ntpdate
$ntpUpdateString = array(
 "",
 "",
 "*/10 *",
 "*/15 *",
 "*/30 *",
 "0 *",
 "0 */4",
 "0 */8",
 "0 0"
);

#ntp server polling time: minpoll/maxpoll = 4 * 2^x
#												  7 = 4 *  128 =  512 seconds = 8.5 minutes
#												  8 = 4 *  256 = 1024 seconds = 17 minutes
#												  9 = 4 *  512 = 2048 seconds = 34.1 minutes
#												 10 = 4 * 1024 = 4096 seconds = 68.2 minutes
#												 12 = 4 * 4096 =16384 seconds = 4.55 hours
#												 13 = 4 * 8192 =32768 seconds = 9.1 hours
#												 14 = 4 *16384 =65536 seconds = 18.2 hours
$ntpUpdateTime = array(
 "",
 "",
 "7",
 "8",
 "9",
 "10",
 "12",
 "13",
 "14"
);
?>
