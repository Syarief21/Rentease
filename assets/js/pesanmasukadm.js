// ======================= BADGE NOTIFICATION =========================

// Update badge pesan di sidebar
function updateMessageBadge() {
  const badge = document.getElementById("badgePesan");
  const storedMessages = JSON.parse(localStorage.getItem("adminMessages")) || [];

  if (badge) {
    if (storedMessages.length > 0) {
      badge.style.display = "inline-flex";
      badge.textContent = storedMessages.length;
    } else {
      badge.style.display = "none";
    }
  }
}

document.addEventListener("DOMContentLoaded", updateMessageBadge);

setInterval(updateMessageBadge, 1000);

// ======================= RENDER PESAN MASUK =========================

function renderPesanMasuk() {
  fetch("../../views/admin/pesan_masuk.php")
    .then(res => res.text())
    .then(html => {
      const main = document.getElementById("mainContent");
      main.innerHTML = html;

      // Eksekusi script bawaan halaman
      const scripts = main.querySelectorAll("script");
      scripts.forEach(oldScript => {
        const newScript = document.createElement("script");
        if (oldScript.src) newScript.src = oldScript.src;
        else newScript.textContent = oldScript.textContent;
        main.appendChild(newScript);
      });

      setTimeout(() => {
        if (typeof renderMessages === "function") renderMessages();
        updateMessageBadge(); // 
      }, 50);
    })
    .catch(err => {
      document.getElementById("mainContent").innerHTML =
        `<p style="color:red; text-align:center;">Gagal memuat Pesan Masuk: ${err}</p>`;
    });
}
