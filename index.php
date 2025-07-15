<?php
session_start();
include('config/db_connect.php');
$user_id = isset($_SESSION['u_id']) ? intval($_SESSION['u_id']) : 0;



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


$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$where = [];
if ($category_id > 0) {
    $where[] = "posts.c_id = $category_id";
}
if ($search !== '') {
    $search_escaped = mysqli_real_escape_string($conn, $search);
    $where[] = "posts.title LIKE '%$search_escaped%'";
}

$where_sql = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";


$sql = "
SELECT posts.*, users.username, category.c_name,
       (SELECT COALESCE(SUM(vote), 0) FROM votes WHERE post_id = posts.id) AS vote_total
FROM posts
JOIN users ON posts.u_id = users.u_id
JOIN category ON posts.c_id = category.c_id
$where_sql
ORDER BY posts.created_at DESC, posts.id DESC
";


$result = mysqli_query($conn, $sql);
if (!$result) {
    die("Query error: " . mysqli_error($conn));
}

$posts = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>

<?php include('templates/header.php') ?>



<h4 class="center brand-text" style="font-size: 40px; margin-bottom: 24px;">Posts</h4>
<div class="card-container">
    <div class="g_left">
        <form class="z-depth-1 filter-card " method="GET" action="index.php">
            <h5 class="text">Filters</h5>
            <button class="btn-small hover-effect brand btn" type="submit" name="category_id" value="0">All Posts</button>
            <button class="btn-small hover-effect brand btn" type="submit" name="category_id" value="1">🍕 Food</button>
            <button class="btn-small hover-effect brand btn" type="submit" name="category_id" value="2">✈️ Vacation/View</button>
            <button class="btn-small hover-effect brand btn" type="submit" name="category_id" value="3">📚 Book</button>
            <button class="btn-small hover-effect brand btn" type="submit" name="category_id" value="4">🎬 Film & Series</button>
            <button class="btn-small hover-effect brand btn" type="submit" name="category_id" value="5">📍 My Spot</button>
            <input class="search-bar" type="text" name="search" placeholder="Search...">
            <button class="btn-small hover-effect brand btn" type="submit">Ara</button>
            <?php if ($category_id > 0): ?>
            <p style="text-align:center; font-weight:bold; color: var(--accent);">
                Results for: 
                <?php
                    $kategori_etiket = [
                        1 => '🍕 Food',
                        2 => '✈️ Vacation/View',
                        3 => '📚 Book',
                        4 => '🎬 Movie & Series',
                        5 => '📍 My Spot',
                    ];
                    echo $kategori_etiket[$category_id] ?? 'Bilinmeyen';
                ?>
            </p>
        <?php endif; ?>
                </form>
    </div>
 

    <div class="g_center">              
    <!-- POST ALANI -->
    <div style="flex: 1; max-width: 650px;"></div>
    <?php foreach($posts as $post): ?>
        <div class="card hover-effect z-depth-1" >
            <div class="center" style="margin: 10px 8px;" >
                <a class="btn-small btn hover-effect brand z-depth-1" href="profile.php?u_id=<?php echo $post['u_id']; ?>">
                    <?php echo htmlspecialchars($post['username']); ?> <i class="fa-regular fa-user" style="margin-left:3px; font-size:0.95em;"></i>
                </a>
                    <small class="text" style="font-size: 14px; position: absolute; right: 28px; top: 23px;">
                        <?php echo date('d M Y', strtotime($post['created_at'] ?? '')); ?>
                    </small>
                <h6 class="text">
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
                    <p class="text">
                        <?php 
                            echo nl2br(htmlspecialchars(stripcslashes(mb_substr(strip_tags($post['p_description']), 0, 150)))) .'...'; 
                        ?>
                    </p>
                <?php if (!empty($post['p_image'])): ?>
                    <div style="margin-top:8px;">
                        <img src="uploads/<?php echo htmlspecialchars($post['p_image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" style="max-width:90%; border-radius: 10px;">
                    </div>
                <?php endif; ?>

                    <form class="comment-form" data-post-id="<?= $post['id']; ?>" method="post" style="display: flex; align-items: center; gap: 8px; margin-top: 10px;">
                        <input type="hidden" name="post_id" value="<?= $post['id']; ?>">
                        <input class="text comment-input" autocomplete="off" type="text" name="comment" ></input>
                        <button type="submit" class="btn-small hover-effect brand btn">
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </form>
                    <div id="comments-<?= $post['id']; ?>">
                    <?php
                    
                    // Yorumları çekme
                    $comment_stmt = $conn->prepare("SELECT comments.comment, users.username
                                                    FROM comments 
                                                    JOIN users ON comments.user_id = users.u_id 
                                                    WHERE comments.post_id = ? 
                                                    ORDER BY comments.created_at DESC
                                                    LIMIT 3");
                    if (!$comment_stmt) {
                        die("Sorgu hazırlanamadı: " . $conn->error);
                    }                               
                    $comment_stmt->bind_param("i", $post['id']);
                    $comment_stmt->execute();
                    $comments_result = $comment_stmt->get_result();
                    while ($c = $comments_result->fetch_assoc()):
                    ?>
                        <div class="comment-list text" style="margin-left:10px; font-size: 14px; color:#555;">
                            <strong><?= htmlspecialchars($c['username']) ?></strong> <?= nl2br(htmlspecialchars($c['comment'])) ?>
                        </div>
                    <?php endwhile; $comment_stmt->close(); ?>
                    </div>
            </div>
        </div>
        
<div class="card-action" style="margin-top:12px; display: flex; justify-content: space-between; align-items: center;">
    <a class="btn-small btn hover-effect brand z-depth-1" href="details.php?id=<?= $post['id']; ?>">Details</a>

    <div style="display: flex; align-items: center; gap: 8px;">
        <?php 
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
        ?>

<div class="vote-container" data-post-id="<?php echo $post['id']; ?>">
    <button class="btn-small brand hover-effect upvote-button <?php echo $user_vote == 1 ? 'active' : ''; ?>" style="border-radius: 36px !important;">
        <i class="fa-solid fa-arrow-up"></i>
    </button>
    <span>|</span>
    <button class="btn-small brand hover-effect downvote-button <?php echo $user_vote == -1 ? 'active' : ''; ?>" style="border-radius: 36px !important;" >
        <i class="fa-solid fa-arrow-down"></i>
    </button>
    <span class="vote-count"><?php echo $post['vote_total'] ?? 0; ?></span>
    
</div>

        <?php if (isset($_SESSION['u_role']) && $_SESSION['u_role'] === 'admin'): ?>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                <input type="hidden" name="c_id" value="<?php echo (int)$post['c_id']; ?>">
                <button type="submit" name="delete"  class="btn-small hover-effect btn brand" onclick="return confirm('Are you sure you want to delete?');">
                    <i class="fa fa-trash"></i> Delete
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>


    <?php endforeach; ?>
    </div>  
    <div class="g_right">
        <h2>Most liked</h2>
        <div class="card"></div>
    </div>
</div>
<div style="position: fixed; bottom:0px;">
<?php include('templates/footer.php') ?>    
</div>

</html>
<script>
    document.addEventListener('DOMContentLoaded', function() {
    bindVoteButtons();
    bindCommentForms();
});
</script>

