// ====================== Jam & Tanggal Real-Time ======================
function updateDateTime() {
  const now = new Date();
  const days = [
    "Minggu",
    "Senin",
    "Selasa",
    "Rabu",
    "Kamis",
    "Jumat",
    "Sabtu",
  ];
  const day = days[now.getDay()];
  const date = now.getDate().toString().padStart(2, "0");
  const month = (now.getMonth() + 1).toString().padStart(2, "0");
  const year = now.getFullYear();
  const hours = now.getHours().toString().padStart(2, "0");
  const minutes = now.getMinutes().toString().padStart(2, "0");
  const seconds = now.getSeconds().toString().padStart(2, "0");

  const datetimeStr = `${day}, ${date}/${month}/${year} - ${hours}:${minutes}:${seconds} WIB`;
  document.getElementById("datetime").textContent = datetimeStr;
}

setInterval(updateDateTime, 1000);
updateDateTime();

// ====================== Logout ======================
function logout() {
  showLogoutModal();
}

function showLogoutModal() {
  const modal = document.getElementById("modalConfirm");
  const modalText = document.getElementById("modalText");
  const btnYes = document.getElementById("modalYes");
  const btnNo = document.getElementById("modalNo");

  modalText.textContent = "Apakah Anda yakin ingin logout?";
  modal.style.display = "flex";

  btnYes.onclick = () => {
    modal.style.display = "none";
    showSuccessMessage();
  };
  
  btnNo.onclick = () => {
    modal.style.display = "none";
  };
  
  window.onclick = (e) => {
    if (e.target === modal) {
      modal.style.display = "none";
    }
  };
}

function showSuccessMessage() {
  // Buat modal success
  const successModal = document.createElement("div");
  successModal.className = "success-modal";
  successModal.innerHTML = `
    <div class="success-modal-content">
      <div class="success-icon">
        <i class="fa-solid fa-check"></i>
      </div>
      <h3>Logout Berhasil!</h3>
      <p>Anda akan dialihkan ke halaman login...</p>
    </div>
  `;
  
  document.body.appendChild(successModal);
  
  // Animasi 
  setTimeout(() => {
    successModal.classList.add("show");
  }, 10);
  
  // Redirect
  setTimeout(() => {
    window.location.href = "../auth/login.php";
  }, 2000);
}

// ====================== Modal ======================
function showModal(message, onYes) {
  const modal = document.getElementById("modalConfirm");
  const modalText = document.getElementById("modalText");
  const btnYes = document.getElementById("modalYes");
  const btnNo = document.getElementById("modalNo");

  modalText.textContent = message;
  modal.style.display = "flex";

  btnYes.onclick = () => {
    modal.style.display = "none";
    onYes();
  };
  btnNo.onclick = () => {
    modal.style.display = "none";
  };
  window.onclick = (e) => {
    if (e.target === modal) modal.style.display = "none";
  };
}