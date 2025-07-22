<?php 
session_start();
include('config/db_connect.php');

if (!isset($_SESSION['u_id'])) {
    header('Location: log_in.php');
    exit;
}

$user_id = isset($_GET['u_id']) ? intval($_GET['u_id']) : $_SESSION['u_id'];

$sql_user = "SELECT username, email, gender, u_role, profile_image, about FROM users WHERE u_id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param('i', $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user = $result_user->fetch_assoc();
$stmt_user->close();

$user_posts = [];

$sql_posts = "
    SELECT posts.id AS post_id, posts.u_id, category.c_name AS category, posts.title, posts.p_description, posts.p_image AS image, posts.created_at,
        (SELECT COALESCE(SUM(vote), 0) FROM votes WHERE post_id = posts.id) AS vote_total
    FROM posts
    JOIN category ON posts.c_id = category.c_id
    WHERE posts.u_id = ?
    ORDER BY posts.created_at DESC, posts.id DESC
";

$stmt = $conn->prepare($sql_posts);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $user_posts[] = $row;
}
$stmt->close();

$sql_comments = "
    SELECT comments.*, users.username 
    FROM comments 
    JOIN users ON comments.user_id = users.u_id 
    WHERE comments.user_id = ?
    ORDER BY comments.created_at DESC
";

$user_comments = [];
$stmt_comments = $conn->prepare($sql_comments);
$stmt_comments->bind_param('i', $user_id);
$stmt_comments->execute();
$result_comments = $stmt_comments->get_result();
while ($row = $result_comments->fetch_assoc()) {
    $user_comments[] = $row;
}
$stmt_comments->close();
$conn->close();

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
<html lang="en">

<?php include('templates/header.php'); ?>

<div class="card-container">
    <div class="area-your-profile">
        <div class="profile-card">
            <?php if ($_SESSION['u_id'] == $user_id): ?>
                <div class="right-align link" style="margin-bottom: 10px;">
                    <a href="edit.php" class="text btn-small btn brand hover-effect z-depth-1" style="font-weight:600;">
                        Edit Profile <i class="fa-solid fa-pen"></i>
                    </a>
                </div>
            <?php endif; ?>

            <h2 class="text" style="margin-bottom: 12px;">Profile</h2>

            <div class="profile-card-img-container">
                <img src="uploads/<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile Image" class="profile-card-img">
            </div>

            <p class="text"><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
            <p class="text"><strong>Role:</strong> <?= formatRole($user['u_role']) ?></p>
            <p class="text"><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
            <p class="text"><strong>Gender:</strong> <?= formatGender($user['gender']) ?></p>
            <p class="text"><strong>About Me:</strong></p>
            <p class="text left-align"><?= nl2br(htmlspecialchars($user['about'])) ?></p>
        </div>
    </div>

    <div class="area-posts">
        <h4 class="center brand-text" style="font-size: 40px; margin-bottom: 24px;">Your Cozy Shares</h4>

        <?php if (count($user_posts) > 0): ?>
            <?php foreach ($user_posts as $post): ?>
                <div class="card hover-effect z-depth-1" id="post-<?= $post['post_id'] ?>" style="margin-top: 42px;">
                    <div class="center" style="margin: 10px 8px;">
                        <h5 class="text" style="margin:10px 0 8px 0;">
                            <?php
                            $emoji_map = [
                                'Food Recipes' => "🍕",
                                'Book' => "📚",
                                'Movie' => "🎬",
                                'My Spot' => "📍",
                                'Travel' => "✈️",
                            ];
                            echo ($emoji_map[$post['category']] ?? '') . ' ' . htmlspecialchars($post['title']);
                            ?>
                        </h5>
                        <p class="text"><?= nl2br(htmlspecialchars(stripslashes($post['p_description']))) ?></p>

                        <?php if (!empty($post['image'])): ?>
                            <img src="uploads/<?= htmlspecialchars($post['image']) ?>" alt="" style="max-width:90%; margin-top:12px; border-radius:10px;">
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-action" style="margin-top:12px; display: flex; justify-content: space-between; align-items: center;">
                        <a class="btn-small btn hover-effect brand z-depth-1" href="details.php?id=<?= $post['post_id'] ?>">Details</a>
                            <?php
                                $vote_count = (int)$post['vote_total'];
                                if ($vote_count > 0) {
                                    $vote_icon= '<i class="fa-solid fa-arrow-up" style="color:green;"></i>';
                                } elseif ($vote_count < 0) {
                                    $vote_icon= '<i class="fa-solid fa-arrow-down" style="color:red;"></i>';
                                } else {
                                    $vote_icon= '<i class="fa-solid fa-minus" style="color:gray;"></i>';
                                }
                            ?>
                            <span style="font-weight: bold;">
                                <?= $vote_icon ?> <?= $vote_count ?>
                            </span>
                        <small class="text">
                            <?= date('d M Y', strtotime($post['created_at'])) ?>
                        </small>

                        <?php if (isset($_SESSION['u_role']) && $_SESSION['u_role'] === 'admin' || (isset($_SESSION['u_id']) && $_SESSION['u_id'] == $post['u_id'])): ?>
                <button class="delete-post-btn btn-small hover-effect btn brand" type="button" data-post-id="<?= $post['post_id'] ?>">
                    <i class="fa fa-trash"></i> Delete
                </button>
                <?php endif; ?>
                    </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text" style="color:var(--accent); font-weight:600;">Nothing posted yet.</p>
        <?php endif; ?>
    </div>

    <div class="area-your-comments">
        <h4 class="center brand-text" style="font-size: 40px; margin-bottom: 24px;">Your Comments</h4>
        <div class="card">
            <?php if (count($user_comments) > 0): ?>
                <?php foreach ($user_comments as $comment): ?>
                    <div class="comment-list" style="margin-bottom: 16px; border-bottom: 1px solid var(--accent); border-radius: 10px;">
                        <?php if ((isset($_SESSION['u_role']) && $_SESSION['u_role'] === 'admin') || (isset($_SESSION['u_id']) && $_SESSION['u_id'] == $comment['user_id'])): ?>
                                <button class="delete-comment-btn " data-comment-id="<?= $comment['id'] ?>">
                                    <i class="fa fa-times"></i>
                                </button>
                                <?php endif; ?>
                        <div class="card-content">
                            <p class="text"><?= htmlspecialchars($comment['comment']) ?></p>
                            <small class="text">Commented on <?= date('d M Y - H:i', strtotime($comment['created_at'])) ?></small>
                        </div>
                        
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text" style="color:var(--accent); font-weight:600;">No comments yet.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php include('templates/footer.php'); ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    
    initApp();
});

</script>
</html>
