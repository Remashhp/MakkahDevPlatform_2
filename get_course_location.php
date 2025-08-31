<?php
require 'connect.php';

$course_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$response = [
    'status' => 'error',
    'message' => 'لم يتم العثور على بيانات'
];

if ($course_id > 0) {
    $stmt = $conn->prepare("SELECT attendance_method, location_name, location_address, teams_link FROM course_info WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        $response = [
            'status' => 'success',
            'data' => [
                'attendance_method' => $row['attendance_method'],
                'location_name'     => $row['location_name'],
                'location_address'  => $row['location_address'],
                'teams_link'        => $row['teams_link']
            ]
        ];
    }
    $stmt->close();
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($response);
?>