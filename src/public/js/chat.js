const messageInput = document.getElementById("messageInput");
const chatForm = document.getElementById("chatForm");
document.getElementById("messageInput").addEventListener("input", function () {
    sessionStorage.setItem("chat_draft", this.value);
});

window.addEventListener("load", function () {
    const draft = sessionStorage.getItem("chat_draft");
    if (draft && !document.getElementById("messageInput").value) {
        document.getElementById("messageInput").value = draft;
    }
});

document.getElementById("chatForm").addEventListener("submit", function () {
    sessionStorage.removeItem("chat_draft");
});

document.addEventListener("click", async function (e) {
    const csrfToken =
        document.querySelector('meta[name="csrf-token"]')?.content || "";

    if (e.target.classList.contains("message-delete")) {
        const ownMessageDiv = e.target.closest(".own-message");
        const messageDiv = ownMessageDiv.querySelector(".message-actions");

        console.log("ownMessageDiv:", ownMessageDiv);
        console.log("messageDiv:", messageDiv);
        console.log("dataset.id:", messageDiv?.dataset.id);

        if (!messageDiv?.dataset.id) {
            console.error("IDが取得できません");
            return;
        }

        const id = messageDiv.dataset.id;

        if (!confirm("このメッセージを削除しますか?")) return;

        try {
            const res = await fetch(`/messages/${id}`, {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                },
            });

            if (res.ok) {
                ownMessageDiv.remove();
            } else {
                alert("削除に失敗しました");
            }
        } catch (error) {
            alert("エラーが発生しました");
        }
    }

    if (e.target.classList.contains("message-edit")) {
        const ownMessageDiv = e.target.closest(".own-message");
        const messageSpace = ownMessageDiv?.querySelector(".message-space.own");

        if (!messageSpace || ownMessageDiv.querySelector(".edit-input")) return;

        const originalHTML = messageSpace.innerHTML;
        const original = messageSpace.textContent.trim();

        messageSpace.innerHTML = `
            <textarea class="edit-input" rows="3">${original}</textarea>
            <div class="edit-buttons">
                <button class="edit-save">保存</button>
                <button class="edit-cancel">キャンセル</button>
            </div>
        `;
        messageSpace.dataset.originalHtml = originalHTML;
    }

    if (e.target.classList.contains("edit-cancel")) {
        const ownMessageDiv = e.target.closest(".own-message");
        const messageSpace = ownMessageDiv.querySelector(".message-space.own");
        const originalHtml = messageSpace.dataset.originalHtml;

        messageSpace.innerHTML = originalHtml;
        delete messageSpace.dataset.originalHtml;
    }

    if (e.target.classList.contains("edit-save")) {
        const ownMessageDiv = e.target.closest(".own-message");
        const messageDiv = ownMessageDiv.querySelector(".message-actions");
        const input = ownMessageDiv.querySelector(".edit-input");
        const messageSpace = ownMessageDiv.querySelector(".message-space.own");

        console.log("edit-save - messageDiv:", messageDiv);
        console.log("edit-save - dataset.id:", messageDiv?.dataset.id);

        if (!messageDiv?.dataset.id || !input || !messageSpace) return;

        const id = messageDiv.dataset.id;
        const newText = input.value.trim();

        if (!newText) {
            alert("メッセージを入力してください");
            return;
        }

        try {
            const res = await fetch(`/messages/${id}`, {
                method: "PATCH",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
                body: JSON.stringify({
                    body: newText,
                }),
            });

            if (res.ok) {
                messageSpace.innerHTML = newText.replace(/\n/g, "<br>");
                delete messageSpace.dataset.originalHtml;
            } else {
                alert("保存に失敗しました");
            }
        } catch (error) {
            alert("エラーが発生しました");
        }
    }
});

function initRatingSystem(config) {
    const ratingModal = document.getElementById("ratingModal");
    const starRating = document.getElementById("starRating");
    const submitRatingBtn = document.getElementById("submitRatingBtn");
    const completeTransactionBtn = document.getElementById(
        "completeTransactionBtn"
    );
    let selectedRating = 0;

    console.log("評価スクリプト読み込み完了");

    if (config.showModalOnLoad) {
        ratingModal.style.display = "flex";
    }

    if (completeTransactionBtn) {
        completeTransactionBtn.addEventListener("click", function (e) {
            e.preventDefault();
            ratingModal.style.display = "flex";
        });
    }

    const stars = starRating.querySelectorAll(".star");
    stars.forEach((star) => {
        star.addEventListener("mouseenter", function () {
            const rating = parseInt(this.dataset.rating);
            highlightStars(rating);
        });

        star.addEventListener("click", function () {
            selectedRating = parseInt(this.dataset.rating);
            console.log("クリック - 選択された評価:", selectedRating);
            highlightStars(selectedRating);
        });
    });

    starRating.addEventListener("mouseleave", function () {
        highlightStars(selectedRating);
    });

    function highlightStars(rating) {
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.add("active");
            } else {
                star.classList.remove("active");
            }
        });
    }

    submitRatingBtn.addEventListener("click", async function () {
        if (selectedRating === 0) {
            alert("評価を選択してください");
            return;
        }

        const csrfToken =
            document.querySelector('meta[name="csrf-token"]')?.content || "";

        try {
            const response = await fetch(
                `/chat/${config.purchaseId}/complete`,
                {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                    },
                    body: JSON.stringify({
                        rating: selectedRating,
                        is_seller: config.isSeller,
                    }),
                }
            );

            if (response.ok) {
                window.location.href = "/";
            } else {
                alert("評価の送信に失敗しました");
            }
        } catch (error) {
            console.error("評価送信エラー:", error);
            alert("評価の送信に失敗しました");
        }
    });
}
