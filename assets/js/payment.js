document.addEventListener("DOMContentLoaded", () => {
    const params = new URLSearchParams(window.location.search);
    const bookingId = params.get("id");

    const paymentInfoEl = document.getElementById("paymentInfo");
    const paymentAmountEl = document.getElementById("paymentAmount");
    const userNameEl = document.getElementById("userName");
    const userEmailEl = document.getElementById("userEmail");
    const bookingDateEl = document.getElementById("bookingDate");
    const selectedDateEl = document.getElementById("selectedDate");
    const paymentForm = document.getElementById("paymentForm");
    const buktiInput = document.getElementById("buktiPembayaran");
    const previewBukti = document.getElementById("previewBukti");
    const loadingOverlay = document.getElementById("loadingOverlay");

    let bookingData = null;

    if (!bookingId) {
        document.querySelector("main").innerHTML = "<p style='text-align:center; color:red;'>ID Booking tidak ditemukan.</p>";
        return;
    }

    // Ambil detail booking dari API
    async function loadBookingDetails() {
        try {
            const response = await fetch(`../../backend/api/bookings.php?id=${bookingId}`);
            const result = await response.json();

            if (result.status === 'success') {
                bookingData = result.data;
                populatePaymentDetails(bookingData);
            } else {
                throw new Error(result.message || 'Gagal memuat detail booking.');
            }
        } catch (error) {
            console.error('Error fetching booking details:', error);
            document.querySelector("main").innerHTML = `<p style='text-align:center; color:red;'>${error.message}</p>`;
        }
    }

    // Isi halaman dengan data booking
    function populatePaymentDetails(data) {
        document.title = `Pembayaran untuk ${data.property_name} - Rentease`;
        paymentInfoEl.textContent = `Silakan transfer untuk pembayaran kos: ${data.property_name}`;
        paymentAmountEl.textContent = `Rp${Number(data.price).toLocaleString()}`;
        userNameEl.textContent = data.user_name;
        userEmailEl.textContent = data.user_email;
        // Tanggal booking dibuat (dari DB) vs tanggal pilihan user
        const createdAt = new Date(data.created_at);
        bookingDateEl.textContent = createdAt.toLocaleDateString('id-ID', { dateStyle: 'full' });
        const chosenDate = new Date(data.booking_date);
        selectedDateEl.textContent = chosenDate.toLocaleDateString('id-ID', { dateStyle: 'full'});
    }

    // Preview gambar bukti pembayaran
    buktiInput.addEventListener("change", () => {
        const file = buktiInput.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                previewBukti.src = e.target.result;
                previewBukti.style.display = "block";
            };
            reader.readAsDataURL(file);
        }
    });

    // Handle submit form pembayaran
    paymentForm.addEventListener("submit", async (e) => {
        e.preventDefault();

        if (buktiInput.files.length === 0) {
            alert("Silakan upload bukti pembayaran terlebih dahulu!");
            return;
        }

        if (!bookingData) {
            alert("Data booking tidak valid. Silakan coba lagi.");
            return;
        }

        loadingOverlay.style.display = "flex";

        const formData = new FormData();
        formData.append('booking_id', bookingId);
        formData.append('total_amount', bookingData.price);
        formData.append('payment_proof', buktiInput.files[0]);

        try {
            const response = await fetch('../../backend/api/bookings.php?action=submit_payment', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.status === 'success') {
                // Redirect ke halaman sukses setelah sedikit delay
                setTimeout(() => {
                    loadingOverlay.style.display = "none";
                    window.location.href = "succes.php";
                }, 1500);
            } else {
                throw new Error(result.message || 'Gagal mengirim pembayaran.');
            }
        } catch (error) {
            loadingOverlay.style.display = "none";
            console.error("Error submitting payment:", error);
            alert(`Terjadi kesalahan: ${error.message}`);
        }
    });

    // Mulai proses
    loadBookingDetails();
});