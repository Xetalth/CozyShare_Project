<?php
session_start();
include('config/db_connect.php');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['u_id'] ?? 0;
    $post_id = intval($_POST['post_id'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');

    if ($user_id > 0 && $post_id > 0 && $comment !== '') {
        $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $post_id, $user_id, $comment);
        if ($stmt->execute()) {
            $comment_id = $conn->insert_id;
            $stmt->close();

            $stmt2 = $conn->prepare("SELECT username FROM users WHERE u_id = ?");
            $stmt2->bind_param("i", $user_id);
            $stmt2->execute();
            $result = $stmt2->get_result();
            $user = $result->fetch_assoc();
            $stmt2->close();

            echo json_encode([
                'success' => true,
                'username' => htmlspecialchars($user['username']),
                'comment' => htmlspecialchars($comment),
                'comment_id' => $comment_id  // Burayı ekledim, id gönderilmeli ki JS yorum silme için kullansın
            ]);
            exit;
        }
    }
}

echo json_encode(['success' => false]);
exit;
