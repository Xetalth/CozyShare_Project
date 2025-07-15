<?php
session_start();
include('config/db_connect.php');

// GET ile id alınmazsa ana sayfaya yönlendir
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}
$post_id = intval($_GET['id']);

// Silme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'], $_POST['post_id'])) {
    $post_id_to_delete = intval($_POST['post_id']);

    // 1. Resim dosyasını bul
    $get_image_sql = "SELECT p_image FROM posts WHERE id = ?";
    $stmt = $conn->prepare($get_image_sql);
    $stmt->bind_param("i", $post_id_to_delete);
    $stmt->execute();
    $stmt->bind_result($image_name);
    $stmt->fetch();
    $stmt->close();

    // 2. Eğer varsa dosyayı sil
    if (!empty($image_name)) {
        $image_path = 'uploads/' . $image_name;
        if (file_exists($image_path)) {
            unlink($image_path); // Dosyayı sil
        }
    }

    // 3. Postu veritabanından sil
    $sql_del = "DELETE FROM posts WHERE id = ?";
    $stmt = $conn->prepare($sql_del);
    $stmt->bind_param("i", $post_id_to_delete);
    $stmt->execute();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Postu ve kullanıcıyı çek
$sql = "SELECT posts.*,users.*, category.c_name,
       (SELECT COALESCE(SUM(vote), 0) FROM votes WHERE post_id = posts.id) AS vote_total
FROM posts
JOIN users ON posts.u_id = users.u_id
JOIN category ON posts.c_id = category.c_id
WHERE posts.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $post_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die('Post bulunamadı.');
}
$post = $result->fetch_assoc();
$stmt->close();
// Oy sayısını çekiyoruz (oylar 1 veya -1)
$sql_vote = "SELECT COALESCE(SUM(vote), 0) as vote_sum FROM votes WHERE post_id = ?";
$stmt_vote = $conn->prepare($sql_vote);
$stmt_vote->bind_param('i', $post['id']);
$stmt_vote->execute();
$res_vote = $stmt_vote->get_result()->fetch_assoc();
$vote_sum = $res_vote['vote_sum'] ?? 0;
$stmt_vote->close();
// Kullanıcının oyu var mı?
$user_vote = 0;
if (isset($_SESSION['u_id'])) {
$sql_user_vote = "SELECT vote FROM votes WHERE post_id = ? AND user_id = ?";
    $stmt_user_vote = $conn->prepare($sql_user_vote);
    $stmt_user_vote->bind_param('ii', $post['id'], $_SESSION['u_id']);
    $stmt_user_vote->execute();
    $res_user_vote = $stmt_user_vote->get_result()->fetch_assoc();
    $user_vote = $res_user_vote['vote'] ?? 0;
    $stmt_user_vote->close();
}
function formatGender($gender) {
    switch($gender) {
        case 'male': return 'Male';
        case 'female': return 'Female';
        case 'other': return 'Other';
        case 'prefer_not_say': return 'Prefer not to say';
        default: return ucfirst($gender);
    }
}

function formatRole($u_role) {
    switch($u_role) {
        case 'admin': return 'Admin';
        case 'user': return 'User';
        default: return ucfirst($u_role);
    }
}

?>
<!DOCTYPE html>
<html>
<?php include('templates/header.php'); ?>
    <h4 class="center text">
        <?php 
            $emoji_map = [
                '1' => '🍕',
                '2' => '📚',
                '3' => '🎬',
                '4' => '✈️',
                '5' => '📍',
            ];
            echo ($emoji_map[$post['c_id']] ?? '') . ' ' . htmlspecialchars($post['title']); 
        ?>
    </h6>
    <div class="flex">
        <div class="details-card z-depth-1">
            <?php if (!empty($post['p_image'])): ?>
                <div class="area-img center" style="margin-top:8px;">
                    <img src="uploads/<?= htmlspecialchars($post['p_image']); ?>" alt="<?= htmlspecialchars($post['title']); ?>">
                </div>
            <?php endif; ?>
            <div class="area-profile center">
                <h2 class="text">Profile</h2>
                <div class="profile-card-img-container">
                    <img src="uploads/<?php echo htmlspecialchars($post['profile_image']); ?>" alt="Profile Image" class="profile-card-img">
                </div>
                <p class="text" style="font-weight:600;"><strong>Username:</strong> <?php echo htmlspecialchars($post['username']); ?></p>
                <p class="text" style="font-weight:600;"><strong>Role:</strong> <?php echo formatRole($post['u_role']); ?></p>
                <p class="text" style="font-weight:600;"><strong>Email:</strong> <?php echo htmlspecialchars($post['email']); ?></p>
                <p class="text" style="font-weight:600;"><strong>Gender:</strong> <?php echo formatGender($post['gender']); ?></p>
                <p class="text" style="font-weight:600;"><strong>About Me</strong></p>
                <p class="text left-align">
                    <?php echo nl2br(htmlspecialchars($post['about'])); ?>
                </p>
            </div>   
            <div class="card-grid">
                <p class="area-description text">
                    <?= nl2br(htmlspecialchars(stripcslashes($post['p_description']))); ?>
                </p>
                <small class="area-date text" style="font-size: 14px;">
                    <?= date('d M Y', strtotime($post['created_at'] ?? '')); ?>
                </small>    
                <div class="area-action">
                    <div class="vote-container" data-post-id="<?= $post['id']; ?>">
                        <button class="btn-small brand hover-effect upvote-button <?= $user_vote == 1 ? 'active' : ''; ?>" style="border-radius: 36px !important;">
                            <i class="fa-solid fa-arrow-up"></i>
                        </button>
                        <span>|</span>
                        <button class="btn-small brand hover-effect downvote-button <?= $user_vote == -1 ? 'active' : ''; ?>" style="border-radius: 36px !important;" >
                            <i class="fa-solid fa-arrow-down"></i>
                        </button>
                        <span class="vote-count"><?= $post['vote_total'] ?? 0; ?></span>
                    </div>
                    <?php if (isset($_SESSION['u_role']) && $_SESSION['u_role'] === 'admin'): ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="post_id" value="<?= $post['id']; ?>">
                        <button type="submit" name="delete" class="btn-small hover-effect btn brand" onclick="return confirm('Are you sure you want to delete?');">
                            <i class="fa fa-trash"></i> 
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
            <div class="area-comments">
                <form class="comment-form" data-post-id="<?= $post['id']; ?>" method="post" style="display: flex; align-items: center; gap: 8px; margin-top: 10px;">
                    <input type="hidden" name="post_id" value="<?= $post['id']; ?>">
                    <input class="text comment-input" autocomplete="off" type="text" name="comment" >
                    <button type="submit" class="btn-small hover-effect brand btn">
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>
            </div>  
            <div class="area-comment-list">
                <div id="comments-<?= $post['id']; ?>">
                    <?php
                        // Yorumları çekme
                        $comment_stmt = $conn->prepare("SELECT comments.*, users.username
                                                        FROM comments 
                                                        JOIN users ON comments.user_id = users.u_id 
                                                        WHERE comments.post_id = ? 
                                                        ORDER BY comments.created_at DESC");
                        if ($comment_stmt) {
                            $comment_stmt->bind_param("i", $post['id']);
                            $comment_stmt->execute();
                            $comments_result = $comment_stmt->get_result();
                            while ($c = $comments_result->fetch_assoc()):
                    ?>
                        <div class="comment-list text" data-created-at="<?= htmlspecialchars($c['created_at']) ?>">
                            <div>
                                <strong><?= htmlspecialchars($c['username']) ?></strong>
                                <small class="time-ago text" style="color:#999; font-size: 12px;"></small>
                            </div>
                            <div>
                                <?= nl2br(htmlspecialchars($c['comment'])) ?>
                            </div> 
                        </div>
                    <?php endwhile; $comment_stmt->close(); } ?>               
                </div>
            </div>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    bindVoteButtons();
    bindCommentForms();
});

function timeAgo(dateString) {
    const now = new Date();
    const past = new Date(dateString);
    const diffInSeconds = Math.floor((now - past) / 1000);

    if (diffInSeconds < 5) {
        return 'now';
    }

    const intervals = [
        { label: 'year', seconds: 31536000 },
        { label: 'month', seconds: 2592000 },
        { label: 'week', seconds: 604800 },
        { label: 'day', seconds: 86400 },
        { label: 'hour', seconds: 3600 },
        { label: 'minute', seconds: 60 },
        { label: 'second', seconds: 1 }
    ];

    for (const interval of intervals) {
        const count = Math.floor(diffInSeconds / interval.seconds);
        if (count >= 1) {
            return `${count} ${interval.label} ago`;
        }
    }
}

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".comment-list").forEach(comment => {
        const dateStr = comment.dataset.createdAt;
        const timeAgoEl = comment.querySelector(".time-ago");

        if (dateStr && timeAgoEl) {
            timeAgoEl.textContent = timeAgo(dateStr);
        }
    });
});

</script>

<?php include('templates/footer.php'); ?>
</html>