<?php
include('config/db_connect.php');
header('Content-Type: application/json');

$post_id = intval($_GET['post_id'] ?? 0);
$comments = [];

if ($post_id > 0) {
    $stmt = $conn->prepare("
        SELECT comments.comment, u.username
        FROM comments 
        JOIN users u ON comments.user_id = u.u_id
        WHERE comments.post_id = ?
        ORDER BY comments.created_at DESC
    ");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $comments[] = [
            'username' => htmlspecialchars($row['username']),
            'comment' => htmlspecialchars($row['comment']),
        ];
    }

    $stmt->close();
}

echo json_encode($comments);
