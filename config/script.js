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

function bindVoteButtons() {
    document.querySelectorAll(".vote-container").forEach(container => {
        const postId = container.getAttribute("data-post-id");
        const upBtn = container.querySelector(".upvote-button");
        const downBtn = container.querySelector(".downvote-button");
        const countEl = container.querySelector(".vote-count");

        if (!upBtn || !downBtn || !countEl) return;

        // Event çakışmasını önlemek için önce klonla
        const newUpBtn = upBtn.cloneNode(true);
        const newDownBtn = downBtn.cloneNode(true);
        upBtn.replaceWith(newUpBtn);
        downBtn.replaceWith(newDownBtn);

        function sendVote(voteValue) {
            fetch("vote.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: `post_id=${postId}&vote=${voteValue}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    countEl.textContent = data.vote_sum;

                    if (voteValue === 1) {
                        newUpBtn.classList.add("active");
                        newDownBtn.classList.remove("active");
                    } else {
                        newDownBtn.classList.add("active");
                        newUpBtn.classList.remove("active");
                    }
                }
            });
        }

        newUpBtn.addEventListener("click", () => sendVote(1));
        newDownBtn.addEventListener("click", () => sendVote(-1));
    });
}

function bindCommentFormSubmit() {
    document.addEventListener('submit', e => {
        if (e.target.classList.contains('comment-form')) {
            e.preventDefault();

            const form = e.target;
            const input = form.querySelector('[name="comment"]');
            const comment = input.value.trim();
            const postId = form.dataset.postId;

            if (!comment) return;

            fetch('comment.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `post_id=${encodeURIComponent(postId)}&comment=${encodeURIComponent(comment)}`
            })
            .then(res => {
                if (!res.ok) throw new Error("Network response was not ok");
                return res.json();
            })
            .then(data => {
                console.log("Yorum ekleme yanıtı:", data);
                if (data.success) {
                    const commentBox = document.getElementById(`comments-${postId}`);
                    if (!commentBox) return;
                    const newComment = document.createElement('div');
                    newComment.className = 'comment-list text';
                    newComment.dataset.createdAt = new Date().toISOString();

                    newComment.innerHTML = `
                        <div>
                            <strong>${data.username}</strong>
                            <small class="time-ago" style="color:#999; font-size: 12px;">now</small>
                        </div>
                        <div>
                            ${data.comment}
                            <button class="delete-comment-btn " data-comment-id="${data.comment_id}">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                    `;

                    commentBox.prepend(newComment);

                    e.target.querySelector('[name="comment"]').value = '';
                } else {
                    alert('Yorum eklenemedi.');
                }
            })
            .catch(err => {
                console.error("Yorum ekleme hatası:", err);
                alert('Sunucuya bağlanırken hata oluştu.');
            });
        }
    });
}

function setupDeletePostButtons() {
    document.querySelectorAll(".delete-post-btn").forEach(button => {
        button.addEventListener("click", function () {
            const postId = this.dataset.postId;
            if (!confirm("Are you sure you want to delete this post?")) return;

            fetch("delete.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "type=post&id=" + encodeURIComponent(postId)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const postElement = document.getElementById("post-" + postId);
                    if (postElement) {
                        const actionElement = postElement.nextElementSibling;
                        postElement.remove();
                        if (actionElement && actionElement.classList.contains("card-action")) {
                            actionElement.remove();
                        }
                    }
                } else {
                    alert("Silinemedi: " + data.message);
                }
            });
        });
    });
}

function bindDeleteCommentButtons() {
    document.addEventListener('click', function(e) {
        const deleteBtn = e.target.closest('.delete-comment-btn');
        if (!deleteBtn) return;

        const commentId = deleteBtn.dataset.commentId;
        if (!commentId) return;

        if (!confirm("Yorumu silmek istediğine emin misin?")) return;

        fetch("delete.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "type=comment&id=" + encodeURIComponent(commentId)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const commentDiv = deleteBtn.closest(".comment-list");
                if (commentDiv) commentDiv.remove();
            } else {
                alert("Yorum silinemedi: " + data.message);
            }
        })
        .catch(() => alert("Sunucuya bağlanırken hata oluştu."));
    });
}

// Artık otomatik çalışan kodları tek bir fonksiyon içine alabiliriz
function initApp(options = {}) {
    if (options.bindVoteButtons !== false) bindVoteButtons();
    if (options.bindCommentFormSubmit !== false) bindCommentFormSubmit();
    if (options.setupDeletePostButtons !== false) setupDeletePostButtons();
    if (options.bindDeleteCommentButtons !== false) bindDeleteCommentButtons();
}

// Sayfa yüklendiğinde initApp çağrılırsa tüm olaylar aktif olur
