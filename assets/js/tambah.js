
      document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById("addPropertyForm");
    const modal = document.getElementById("successModal");
    const closeModalBtn = document.getElementById("closeModalBtn");
    const fotoInput = document.getElementById("foto");
    const previewImage = document.getElementById("previewImage");

    // Preview Foto
    fotoInput.addEventListener("change", function () {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                previewImage.src = e.target.result;
                previewImage.style.display = "block";
            };
            reader.readAsDataURL(file);
        } else {
            previewImage.style.display = "none";
            previewImage.src = "";
        }
    });

    // Modal handling
    function showSuccessModal() {
        if(modal) modal.style.display = "flex";
    }

    if(closeModalBtn) {
        closeModalBtn.addEventListener("click", () => {
            modal.style.display = "none";
        });
    }

    if(modal) {
        modal.addEventListener("click", (e) => {
            if (e.target === modal) modal.style.display = "none";
        });
    }

    // Form submission
    form.addEventListener("submit", async function (e) {
        e.preventDefault();

        const formData = new FormData();
        formData.append('name', document.getElementById("namaKos").value.trim());
        formData.append('location', document.getElementById("lokasi").value.trim());
        formData.append('price', document.getElementById("harga").value);
        formData.append('total_rooms', document.getElementById("totalKamar").value);
        formData.append('available_rooms', document.getElementById("tersediaKamar").value);
        formData.append('description', document.getElementById("deskripsi").value.trim());
        
        const imageFile = fotoInput.files[0];
        if (imageFile) {
            formData.append('image', imageFile);
        }

        try {
            const response = await fetch('../../backend/api/properties.php', {
                method: 'POST',
                body: formData // FormData sets the Content-Type header automatically
            });

            const result = await response.json();

            if (result.status === 'success') {
                showSuccessModal();
                form.reset();
                previewImage.style.display = "none";
                previewImage.src = "";
            } else {
                alert(`Gagal menambahkan properti: ${result.message}`);
            }
        } catch (error) {
            console.error('Error submitting form:', error);
            alert('Terjadi kesalahan koneksi. Silakan coba lagi.');
        }
    });
});

