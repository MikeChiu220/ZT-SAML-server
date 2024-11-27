<?php

class StatusController {
    private $db;

    public function __construct() {
        // Create a database connection
        $this->db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        // Check for connection errors
        if ($this->db->connect_error) {
            die("Connection failed: " . $this->db->connect_error);
        }
    }

    public function getStatus() {
        // Query to get UTs status
        $statusQuery = "SELECT * FROM UT_Info";
        $statusResult = $this->db->query($statusQuery);
        $utsStatus = [];
        if ($statusResult->num_rows > 0) {
            while ($row = $statusResult->fetch_assoc()) {
                if ($row['status'] == "online" )
                    $online = true;
                else
                    $online = false;
				$utStatus = array(
					"online" => $online,
					"imei" => $row['imei'],
					"ip" => $row['ip'],
					"model" => $row['model'],
					"location" => $row['location']                 
				); // Assuming the row structure matches your schema
				$utsStatus[] = $utStatus;
            }
        }

        // Query to get POPs status
        $statusQuery = "SELECT * FROM POP_Info";
        $statusResult = $this->db->query($statusQuery);
        $popsStatus = [];
        if ($statusResult->num_rows > 0) {
            while ($row = $statusResult->fetch_assoc()) {
                if ($row['status'] == "online" )
                    $online = true;
                else
                    $online = false;
				$popStatus = array(
					"online" => $online,
					"name" => $row['name']                 
				); // Assuming the row structure matches your schema
				$popsStatus[] = $popStatus;
            }
        }

        // Query to get IoT gateway status
        $iotStatusQuery = "SELECT * FROM iotDevice_Info"; // Adjust table name and fields as necessary
        $iotStatusResult = $this->db->query($iotStatusQuery);
        $iotStatus = [];
        if ($iotStatusResult->num_rows > 0) {
            while ($row = $iotStatusResult->fetch_assoc()) {
                $iotStatus[] = $row; // Assuming the row structure matches your schema
            }
        }

        // Prepare the response
        $response = [
            'sat' => [
                'uts' => $utsStatus,
                'pops' => $popsStatus
            ],
            'iot' => [
                'gateways' => $iotStatus
            ]
        ];

        // Set the response header and output the JSON
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function __destruct() {
        // Close the database connection
        $this->db->close();
    }
}