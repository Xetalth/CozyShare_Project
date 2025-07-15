// Oy verme butonlarını bağlayan fonksiyon (sayfa ve modal için)
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

// Yorum formu submit eventlerini bağlayan fonksiyon
function bindCommentForms() {
    document.querySelectorAll('.comment-form').forEach(form => {
        // Önceki event çakışmalarını önlemek için formu klonla
        const newForm = form.cloneNode(true);
        form.parentNode.replaceChild(newForm, form);

        newForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Sayfa yenilemesini engelle

            const postId = newForm.dataset.postId;
            const input = newForm.querySelector('.comment-input');
            const comment = input.value.trim();

            if (!comment) return;

            fetch('comment.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `post_id=${postId}&comment=${encodeURIComponent(comment)}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const commentBox = document.getElementById(`comments-${postId}`);
                    const newComment = document.createElement('div');
                    newComment.classList.add('comment-list', 'text');
                    const nowISOString = new Date().toISOString();
                    newComment.setAttribute('data-created-at', nowISOString);

                    newComment.innerHTML = `
                        <div>
                            <strong>${data.username}</strong>
                            <small class="time-ago text"></small>
                        </div>
                        <div>${data.comment}</div>
                        `;
                    commentBox.prepend(newComment);

                    const timeAgoEl = newComment.querySelector('.time-ago');
                    timeAgoEl.textContent = timeAgo(nowISOString);
                    
                    input.value = '';
                } else {
                    alert('Yorum eklenemedi.');
                }
            })
            .catch(() => alert('Sunucuya bağlanırken hata oluştu.'));
        });
    });
}


