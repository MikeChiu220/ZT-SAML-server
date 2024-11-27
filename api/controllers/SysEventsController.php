<?php
class SysEventsController {
    private $db;

    public function __construct() {
        // Create a database connection
        $this->db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        // Check for connection errors
        if ($this->db->connect_error) {
            die("Connection failed: " . $this->db->connect_error);
        }
    }

    public function getEvents() {
        // Implement pagination and filtering logic
        $pi = isset($_GET['pi']) ? intval($_GET['pi']) : 0;
        $ps = isset($_GET['ps']) ? intval($_GET['ps']) : 10;
        $sortCol = isset($_GET['sortCol']) ? $_GET['sortCol'] : 'createdAt';
        $sortOrder = isset($_GET['sortOrder']) ? $_GET['sortOrder'] : 'ASC';
        $startTime = isset($_GET['startTime']) ? $_GET['startTime'] : null;
        $endTime = isset($_GET['endTime']) ? $_GET['endTime'] : null;
        // Calculate the offset for pagination
        $offset = $pi * $ps;

        // Fetch events from database based on parameters
        // Query to get IoT gateway status
        $query = "SELECT * FROM EventLog ORDER BY $sortCol $sortOrder LIMIT $offset, $ps "; // Adjust table name and fields as necessary
        $queryResult = $this->db->query($query);
        // Check for query errors
        if (!$queryResult) {
            die("Query failed: " . $this->db->error);
        }
        $events = [];
        $totalEvents = $queryResult->num_rows;
        while ($row = $queryResult->fetch_assoc()) {
            $event['deviceId'] = $row['deviceId'];
            $event['deviceName'] = $row['deviceName'];
            $event['code'] = $row['alarmStatus'];
            $event['level'] = $row['alarmType'];
            $event['time'] = $row['createdAt'];
            $event['description'] = $row['alarmDescription'];
            $events[] = $event;
        }
 
        $result = [
            'total' => $totalEvents,
            'list' => $events
        ];

        header('Content-Type: application/json');
        echo json_encode($result);
    }

    public function getSatInfo() {
        // Implement pagination and filtering logic
        $pi = isset($_GET['pi']) ? intval($_GET['pi']) : 0;			// The page index
        $ps = isset($_GET['ps']) ? intval($_GET['ps']) : 0;			// The page size
        $sortCol = isset($_GET['sortCol']) ? $_GET['sortCol'] : 'satId';
        $sortOrder = isset($_GET['sortOrder']) ? $_GET['sortOrder'] : 'ASC';
        // Calculate the offset for pagination
        if ( $ps ) {
			$limit = "LIMIT $ps";
			$offsetVal = $pi * $ps;
			$offset = "OFFSET $offsetVal";
		}
		else
			$limit = $offset = '';
        // Fetch events from database based on parameters
        // Query to get IoT gateway status
        $query = "SELECT * FROM satellite_Info ORDER BY $sortCol $sortOrder $limit $offset"; // Adjust table name and fields as necessary
        $queryResult = $this->db->query($query);
        // Check for query errors
        if (!$queryResult) {
            die("Query failed: " . $this->db->error);
        }
        $satInfos = [];
        $totalSatellites = $queryResult->num_rows;
        while ($row = $queryResult->fetch_assoc()) {
            $info['satId'] = $row['satId'];
            $info['name'] = $row['name'];
            $info['altId'] = $row['altId'];
            $info['altName'] = $row['altName'];
            $info['tle1'] = $row['tle1'];
            $info['tle2'] = $row['tle2'];
            $info['bus'] = $row['bus'];
            $info['country'] = $row['country'];
            $info['manufacturer'] = $row['manufacturer'];
            $info['mission'] = $row['mission'];
            $info['owner'] = $row['owner'];
            $info['rcs'] = $row['rcs'];
            $info['vmag'] = $row['vmag'];
            $info['type'] = $row['type'];
            $info['intlDes'] = $row['intlDes'];
            $info['inc'] = $row['inc'];
            $info['raan'] = $row['raan'];
            $info['period'] = $row['period'];
            $satInfos[] = $info;
       }
 
        $result = [
            'total' => $totalSatellites,
            'list' => $satInfos
        ];

        header('Content-Type: application/json');
        echo json_encode($result);
    }
}