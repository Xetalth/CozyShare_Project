<?php
session_start();
include('config/db_connect.php');
header('Content-Type: application/json');

$response = ['success' => false];

// Giriş yapılmamışsa iptal
if (!isset($_SESSION['u_id'])) {
    echo json_encode($response);
    exit;
}

$type = $_POST['type'] ?? '';
$id = intval($_POST['id'] ?? 0);
$current_user_id = $_SESSION['u_id'];
$current_user_role = $_SESSION['u_role'] ?? 'user';

if ($type === 'post') {
    // Post silme işlemi
    $stmt = $conn->prepare("SELECT u_id, p_image FROM posts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($owner_id, $image_name);
    if (!$stmt->fetch()) {
        echo json_encode($response);
        exit;
    }
    $stmt->close();

    if ($current_user_id == $owner_id || $current_user_role === 'admin') {
        // Görsel varsa sil
        if (!empty($image_name)) {
            $image_path = 'uploads/' . $image_name;
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }

        $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $response['success'] = true;
        }
    }
} elseif ($type === 'comment') {
    // Yorum silme işlemi
    $stmt = $conn->prepare("SELECT user_id FROM comments WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($comment_owner_id);
    if (!$stmt->fetch()) {
        echo json_encode($response);
        exit;
    }
    $stmt->close();

    if ($current_user_id == $comment_owner_id || $current_user_role === 'admin') {
        $stmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $response['success'] = true;
        }
    }
}

echo json_encode($response);
