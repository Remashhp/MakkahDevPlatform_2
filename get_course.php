<?php
$conn = new mysqli("localhost", "root", "12345678", "ministry_education_ksa");
$conn->set_charset("utf8mb4");
$id = intval($_GET['id']);
$res = $conn->query("SELECT * FROM course_info WHERE id=$id LIMIT 1");
$data = $res->fetch_assoc();
echo json_encode($data);
?>