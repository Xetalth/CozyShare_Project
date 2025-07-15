<?php
session_start();
include('config/db_connect.php');

header('Content-Type: application/json');

if (!isset($_SESSION['u_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['u_id'];
$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
$vote = isset($_POST['vote']) ? intval($_POST['vote']) : 0;

if ($post_id <= 0 || !in_array($vote, [-1, 1])) {
    echo json_encode(['success' => false, 'message' => 'Invalid post ID or vote value']);
    exit;
}

// Oy kontrolü: kullanıcı bu post için daha önce oy verdi mi?
$stmt = $conn->prepare("SELECT vote FROM votes WHERE user_id = ? AND post_id = ?");
$stmt->bind_param("ii", $user_id, $post_id);
$stmt->execute();
$stmt->store_result();

$already_voted = $stmt->num_rows > 0;
$stmt->bind_result($existing_vote);
$stmt->fetch();
$stmt->close();

if ($already_voted) {
    if ($existing_vote == $vote) {
        // Aynı oy tekrar verilmiş: oy silinsin (toggle)
        $stmt = $conn->prepare("DELETE FROM votes WHERE user_id = ? AND post_id = ?");
        $stmt->bind_param("ii", $user_id, $post_id);
        $stmt->execute();
        $stmt->close();
    } else {
        // Farklı oy verilmiş: güncelle
        $stmt = $conn->prepare("UPDATE votes SET vote = ? WHERE user_id = ? AND post_id = ?");
        $stmt->bind_param("iii", $vote, $user_id, $post_id);
        $stmt->execute();
        $stmt->close();
    }
} else {
    // Oy yok, yeni oy ekle
    $stmt = $conn->prepare("INSERT INTO votes (post_id, user_id, vote) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $post_id, $user_id, $vote);
    $stmt->execute();
    $stmt->close();
}

// Yeni toplam oyu hesapla
$stmt = $conn->prepare("SELECT COALESCE(SUM(vote),0) FROM votes WHERE post_id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$stmt->bind_result($vote_sum);
$stmt->fetch();
$stmt->close();

echo json_encode(['success' => true, 'vote_sum' => (int)$vote_sum]);
?>