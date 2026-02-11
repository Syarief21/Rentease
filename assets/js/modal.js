// Login modal
const loginModal = document.getElementById("loginModal");
const loginNowBtn = document.getElementById("loginNowBtn");
const cancelLoginBtn = document.getElementById("cancelLoginBtn");

loginNowBtn.addEventListener("click", () => {
  window.location.href = "../auth/login.php";
});
cancelLoginBtn.addEventListener("click", () => {
  loginModal.style.display = "none";
});

// Full kos modal
const fullKosModal = document.getElementById("fullKosModal");
const fullKosCloseBtn = document.getElementById("fullKosCloseBtn");
fullKosCloseBtn.addEventListener("click", () => {
  fullKosModal.style.display = "none";
});
