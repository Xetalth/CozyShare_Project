<?php
session_start();
include('config/db_connect.php');
$user_id = isset($_SESSION['u_id']) ? intval($_SESSION['u_id']) : 0;


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

$most_liked_sql = "
SELECT posts.*, users.username, category.c_name,
       COALESCE(SUM(v.vote), 0) AS vote_total
FROM posts
JOIN users ON posts.u_id = users.u_id
JOIN category ON posts.c_id = category.c_id
LEFT JOIN votes v ON posts.id = v.post_id
GROUP BY posts.id
ORDER BY vote_total DESC
LIMIT 3
";
$most_liked_result = mysqli_query($conn, $most_liked_sql);
$most_liked_posts = mysqli_fetch_all($most_liked_result, MYSQLI_ASSOC);


$result = mysqli_query($conn, $sql);
if (!$result) {
    die("Query error: " . mysqli_error($conn));
}

$posts = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>

<?php include('templates/header.php') ?>




<div class="card-container">
    <div class="area-filter">
        <form class="z-depth-1 filter-card " method="GET" action="index.php">
            <h4 class="center brand-text" style="font-size: 40px; margin-bottom: 24px;">Filters</h4>
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
 

    <div class="area-posts">              
    <!-- POST ALANI -->
    <div style="flex: 1; max-width: 650px;"></div>
    <h4 class="center brand-text" style="font-size: 40px; margin-bottom: 24px;">Posts</h4>

    <?php if (count($posts) === 0): ?>
        <p class="card text center" >
            Nothing posted in this category.
        </p>
    <?php endif; ?>
    
    <?php foreach($posts as $post): ?>
        <div class="card hover-effect z-depth-1" id="post-<?= $post['id']; ?>">
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

                    <form class="comment-form" data-post-id="<?= $post['id']; ?>" style="display: flex; align-items: center; gap: 8px; margin-top: 10px;">

    <input class="text comment-input" autocomplete="off" type="text" name="comment" required placeholder="Add a comment...">
    <button class="btn-small hover-effect brand btn" type="submit">
        <i class="fa-solid fa-arrow-right"></i>
    </button>
</form>
                <div id="comments-<?= $post['id']; ?>">
                    <?php
                        // Yorumları çekme
                        $comment_stmt = $conn->prepare("SELECT comments.*, users.username
                                                        FROM comments 
                                                        JOIN users ON comments.user_id = users.u_id 
                                                        WHERE comments.post_id = ? 
                                                        ORDER BY comments.created_at DESC
                                                        LIMIT 3");
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
                                <?php if ((isset($_SESSION['u_role']) && $_SESSION['u_role'] === 'admin') || (isset($_SESSION['u_id']) && $_SESSION['u_id'] == $c['user_id'])): ?>
                                <button class="delete-comment-btn " data-comment-id="<?= $c['id'] ?>">
                                    <i class="fa fa-times"></i>
                                </button>
                                <?php endif; ?>     
                            </div> 
                        </div>
                    <?php endwhile; $comment_stmt->close(); } ?>               
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
                <?php if ((isset($_SESSION['u_role']) && $_SESSION['u_role'] === 'admin') || (isset($_SESSION['u_id']) && $_SESSION['u_id'] == $post['u_id'])): ?>
                <button class="delete-post-btn btn-small hover-effect btn brand" type="button" data-post-id="<?= $post['id'] ?>">
                    <i class="fa fa-trash"></i> Delete
                </button>
                <?php endif; ?>
    </div>
</div>


    <?php endforeach; ?>
    </div>  
    <div class="area-liked">
        <h4 class="center brand-text" style="font-size: 40px; margin-bottom: 24px;">Top 3 Posts</h4>
        <?php if (!empty($most_liked_posts)): ?>
    <?php 
        $emoji_map = [
            '1' => '🍕',
            '2' => '📚',
            '3' => '🎬',
            '4' => '✈️',
            '5' => '📍',
        ];
    ?>
    <?php foreach ($most_liked_posts as $top_post): ?>
        <div class="card hover-effect z-depth-1" style="margin-top: 12px; padding: 12px;">
            <h6 class="text">
                <?= ($emoji_map[$top_post['c_id']] ?? '') . ' ' . htmlspecialchars($top_post['title']); ?>
            </h6>
            <p class="text">
                <?= nl2br(htmlspecialchars(stripcslashes(mb_substr(strip_tags($top_post['p_description']), 0, 100)))) . '...'; ?>
            </p>
            <?php if (!empty($top_post['p_image'])): ?>
                <div style="margin-top:8px;">
                    <img src="uploads/<?= htmlspecialchars($top_post['p_image']); ?>" alt="<?= htmlspecialchars($top_post['title']); ?>" style="max-width:100%; border-radius: 10px;">
                </div>
            <?php endif; ?>
            <div style="margin-top: 8px; display: flex; justify-content: space-between; align-items: center;">
                <a class="btn-small btn hover-effect brand z-depth-1" href="details.php?id=<?= $top_post['id']; ?>">Details</a>
                <?php
    $vote_count = (int)$top_post['vote_total'];
    if ($vote_count > 0) {
        $vote_icon = '<i class="fa-solid fa-arrow-up" style="color: green;"></i>';
    } elseif ($vote_count < 0) {
        $vote_icon = '<i class="fa-solid fa-arrow-down" style="color: red;"></i>';
    } else {
        $vote_icon = '<i class="fa-solid fa-minus" style="color: gray;"></i>'; // İsteğe bağlı
    }
?>
<span style="font-weight: bold;">
    <?= $vote_icon ?> <?= $vote_count ?>
</span>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p class="text">No posts found.</p>
<?php endif; ?>
    </div>
</div>
<div style="position: fixed; bottom:0px;">
<?php include('templates/footer.php') ?>    
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    initApp();
});

</script>
</html>


