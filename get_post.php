<?php
session_start();
include('config/db_connect.php');
if (!isset($_SESSION['u_id'])) {
    header('Location: log_in.php');
    exit;
}

if (isset($_GET['id'])) {
    $post_id = intval($_GET['id']);

    $sql = "SELECT posts.*, users.username FROM posts 
            JOIN users ON posts.u_id = users.u_id 
            WHERE posts.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($post = $result->fetch_assoc()):
        
?>
    <script src="config/script.js"></script>
    
        <div class="modal-flex-container">
    
    <!-- Sol taraf: Post içeriği -->
    <div class="modal-post" style="flex: 1 1 60%;">
        <div class="center" style="margin: 10px 8px;">
            <a class="btn-small btn hover-effect brand z-depth-1" href="profile.php?u_id=<?= $post['u_id']; ?>">
                <?= htmlspecialchars($post['username']); ?> <i class="fa-regular fa-user" style="margin-left:3px; font-size:0.95em;"></i>
            </a>
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
            <p class="text"><?= nl2br(htmlspecialchars(stripcslashes($post['p_description']))); ?></p>
            <?php if (!empty($post['p_image'])): ?>
                <div style="margin-top:8px;">
                    <img src="uploads/<?= htmlspecialchars($post['p_image']); ?>" alt="<?= htmlspecialchars($post['title']); ?>" style="max-width:90%; border-radius: 10px;">
                </div>
            <?php endif; ?>
        </div>

        <div class="card-action" style="margin-top:12px; display: flex; justify-content: space-between; align-items: center;">
            <small class="text" style="font-size: 14px;">
                <?= date('d M Y', strtotime($post['created_at'] ?? '')); ?>
            </small>

            <div style="display: flex; align-items: center; gap: 8px;">
                <?php 
                    $sql_vote = "SELECT COALESCE(SUM(vote), 0) as vote_sum FROM votes WHERE post_id = ?";
                    $stmt_vote = $conn->prepare($sql_vote);
                    $stmt_vote->bind_param('i', $post['id']);
                    $stmt_vote->execute();
                    $res_vote = $stmt_vote->get_result()->fetch_assoc();
                    $vote_sum = $res_vote['vote_sum'] ?? 0;
                    $stmt_vote->close();

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
                <div class="vote-container" data-post-id="<?= $post['id']; ?>">
                    <button class="btn-small brand hover-effect upvote-button <?= $user_vote == 1 ? 'active' : ''; ?>" style="border-radius: 36px !important;">
                        <i class="fa-solid fa-arrow-up"></i>
                    </button>
                    <span>|</span>
                    <button class="btn-small brand hover-effect downvote-button <?= $user_vote == -1 ? 'active' : ''; ?>" style="border-radius: 36px !important;">
                        <i class="fa-solid fa-arrow-down"></i>
                    </button>
                    <span class="vote-count"><?= $vote_sum; ?></span>
                </div>

                <?php if (isset($_SESSION['u_role']) && $_SESSION['u_role'] === 'admin'): ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="post_id" value="<?= $post['id']; ?>">
                        <input type="hidden" name="c_id" value="<?= (int)$post['c_id']; ?>">
                        <button type="submit" name="delete" class="btn-small hover-effect btn brand" onclick="return confirm('Are you sure you want to delete?');">
                            <i class="fa fa-trash"></i> Delete
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>



    <!-- Modal yorum kutusu ve formu, modal-comments kapsayıcısı ile -->
    <div class="modal-comments">
        <div id="comments-<?= $post['id'] ?>" class="comment-box"></div>
        
    </div>

    <form class="comment-form" data-post-id="<?= $post['id'] ?>" style="margin-top: 10px;">
            <input type="text" class="comment-input" placeholder="Yorum yaz..." required>
            <button type="submit" class="btn-small btn brand hover-effect" style="margin-top: 6px;">Gönder</button>
        </form>

</div> <!-- modal-flex-container -->

    
    <!-- VOTE BUTONLARI MODALDA DA ÇALIŞSIN DİYE -->
    <script>
        if (typeof bindVoteButtons === "function") {
            bindVoteButtons(); 
        }
    </script>

<?php
    else:
        echo "<p>Post bulunamadı.</p>";
    endif;

    $stmt->close();
}
?>
