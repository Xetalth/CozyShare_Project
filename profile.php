<?php 
session_start();
include('config/db_connect.php');

if (!isset($_SESSION['u_id'])) {
    header('Location: log_in.php');
    exit;
}

$user_id = isset($_GET['u_id']) ? intval($_GET['u_id']) : $_SESSION['u_id'];

// Kullanıcı bilgileri
$sql_user = "SELECT username, email, gender, u_role, profile_image, about FROM users WHERE u_id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param('i', $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user = $result_user->fetch_assoc();
$stmt_user->close();

$user_posts = [];

// Silme işlemi için POST kontrolü
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

    header("Location: " . $_SERVER['PHP_SELF'] . '?u_id=' . $user_id);
    exit;
}

// Kullanıcının tüm paylaşımlarını çekiyoruz (filtre yok)
$sql_posts = "
    SELECT posts.id AS post_id, posts.u_id, category.c_name AS category, posts.title, posts.p_description, posts.p_image AS image, posts.created_at
    FROM posts
    JOIN category ON posts.c_id = category.c_id
    WHERE posts.u_id = ?
    ORDER BY posts.created_at DESC, posts.id DESC
";

$stmt = $conn->prepare($sql_posts);
if (!$stmt) {
    die("Prepare hatası: " . $conn->error);
}
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $user_posts[] = $row;
}
$stmt->close();

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
<html>

<?php include('templates/header.php') ?>

<h4 class="center brand-text" style="font-size: 40px; margin-bottom: 24px;"> Your Cozy Shares</h4>
<div class="card-container">
    <div class="g_left">
        <div class="profile-card">
    <div class="right-align link" style="margin-bottom: 10px;">
        <?php if (isset($_SESSION['u_id']) && $_SESSION['u_id'] == $user_id): ?>
            <a href="edit.php" class="text btn-small btn brand hover-effect z-depth-1" style="font-weight:600;">
                Edit Profile <i class="fa-solid fa-pen"></i>
            </a>
        <?php endif; ?>
    </div>
    <h2 class="text" style="margin-bottom: 12px;">Profile</h2>
    <div class="profile-card-img-container">
        <img src="uploads/<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile Image" class="profile-card-img">
    </div>
    <p class="text" style="font-weight:600;"><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
    <p class="text" style="font-weight:600;"><strong>Role:</strong> <?php echo formatRole($user['u_role']); ?></p>
    <p class="text" style="font-weight:600;"><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
    <p class="text" style="font-weight:600;"><strong>Gender:</strong> <?php echo formatGender($user['gender']); ?></p>
    <p class="text" style="font-weight:600;"><strong>About Me</strong></p>
    <p class="text left-align">
        <?php echo nl2br(htmlspecialchars($user['about'])); ?>
    </p>
</div>
    </div>



<div class="g_center">
    <div style="flex: 1; max-width: 650px;">
        <?php if (count($user_posts) > 0): ?>
            <?php foreach ($user_posts as $post): ?>
                <div class="card hover-effect z-depth-1" onclick="openModal(<?= (int)$post['post_id'] ?>)" style="margin-top: 42px;">
                    <div class="center" style="margin: 10px 8px;">
                        <h5 class="text" style="margin:10px 0 8px 0; font-size:1.2rem; letter-spacing:0.5px;">
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
                        <p class="text">
                            <?php echo nl2br(htmlspecialchars(stripcslashes($post['p_description']))); ?>
                        </p>
                        <?php if (!empty($post['image'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($post['image']); ?>" alt="" style="max-width:90%; margin-top:12px; border-radius: 10px;">
                        <?php endif; ?>
                        </div>
                    </div>
                        <div class="card-action right-align" >
                            <small class="text" style="bottom:20px; font-size: 14px;">
                                <?php echo date('d M Y', strtotime($post['created_at'])); ?>
                            </small>
                            <?php if ($_SESSION['u_id'] == $post['u_id']): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                                    <button type="submit" name="delete"  class="btn-small hover-effect btn brand" onclick="return confirm('Are you sure you want to delete?');">
                                        <i class="fa fa-trash"></i> Delete
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
            <?php endforeach; ?>

            <div id="myModal" class="modal">
            <div class="card">
                
            </div>
        </div>
        <?php else: ?>
            <p style="color:var(--accent); font-weight:600;">Nothing posted yet.</p>
        <?php endif; ?>
    </div>
</div>
</div>


<?php include('templates/footer.php') ?>

</html>
