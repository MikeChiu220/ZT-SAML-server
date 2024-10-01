<?php
class StatusController {
    public function getStatus() {
        // Implement logic to get system status
        $status = [
            'sat' => [
                'uts' => [
                    // ... populate with actual data
                ]
            ],
            'iot' => [
                'gateways' => [
                    // ... populate with actual data
                ]
            ]
        ];

        header('Content-Type: application/json');
        echo json_encode($status);
    }
}