// ====================== Render Tambah Kos ======================
function renderTambahKos() {
  fetch("../../views/admin/tambah.php")
    .then((res) => res.text())
    .then((html) => {
      const main = document.getElementById("mainContent");
      main.innerHTML = html; // HTML ditampilkan

      // Ambil elemen yang dibutuhkan
      const form = main.querySelector("#addPropertyForm");
      const modal = main.querySelector("#successModal");
      const closeModalBtn = main.querySelector("#closeModalBtn");
      const previewImage = main.querySelector("#previewImage");
      const fotoInput = main.querySelector("#foto");

      // Modal: tombol tutup dan klik overlay
      closeModalBtn.addEventListener(
        "click",
        () => {
            modal.style.display = "none";
            if (typeof window.showDashboard === "function") {
                window.showDashboard();
            }
        }
      );
      modal.addEventListener("click", (e) => {
        if (e.target === modal) {
            modal.style.display = "none";
            if (typeof window.showDashboard === "function") {
                window.showDashboard();
            }
        }
      });

      // Preview foto
      fotoInput.addEventListener("change", function () {
        const file = this.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = (e) => {
            previewImage.src = e.target.result;
            previewImage.style.display = "block";
          };
          reader.readAsDataURL(file);
        } else {
          previewImage.src = "";
          previewImage.style.display = "none";
        }
      });

      // Submit form
      form.addEventListener("submit", async function (e) {
        e.preventDefault();

        const formData = new FormData(form);

        try {
            const response = await fetch('../../backend/api/properties.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.status === 'success') {
                // Tampilkan modal sukses
                modal.style.display = "flex";
                
                // Reset form & preview
                form.reset();
                previewImage.src = "";
                previewImage.style.display = "none";
            } else {
                alert('Gagal menambahkan properti: ' + result.message);
            }
        } catch (error) {
            console.error('Error adding property:', error);
            alert('Terjadi kesalahan koneksi.');
        }
      });
    })
    .catch((err) => {
      document.getElementById(
        "mainContent"
      ).innerHTML = `<p>Gagal memuat halaman Tambah Kos: ${err}</p>`;
    });
}
