document.addEventListener("DOMContentLoaded", () => {
  const params = new URLSearchParams(window.location.search);
  const propertyId = params.get("id");

  const infoKosEl = document.getElementById("infoKos");
  const namaUserEl = document.getElementById("namaUser");
  const emailUserEl = document.getElementById("emailUser");
  const bookingForm = document.getElementById("bookingForm");
  const timeDisplay = document.getElementById("timeNow");

  // ================= VALIDASI AWAL =================
  if (!propertyId) {
    document.querySelector("main").innerHTML =
      "<p style='text-align:center;color:red;'>ID properti tidak valid.</p>";
    return;
  }

  if (!bookingForm) {
    console.error("bookingForm tidak ditemukan");
    return;
  }

  let propertyData = null;

  // ================= LOAD DATA =================
  async function loadPageData() {
    try {
      const [propertyRes, authRes] = await Promise.all([
        fetch(`../../backend/api/properties.php?id=${propertyId}`),
        fetch(`../../backend/api/auth.php?action=status`)
      ]);

      const propertyResult = await propertyRes.json();
      const authResult = await authRes.json();

      // ==== LOGIN CEK ====
      if (!authResult.loggedIn) {
        alert("Silakan login terlebih dahulu untuk booking.");
        window.location.href = "../auth/login.php";
        return;
      }

      // ==== DATA PROPERTI ====
      if (propertyResult.status !== "success") {
        throw new Error(propertyResult.message || "Gagal memuat data kos.");
      }

      propertyData = propertyResult.data;

      if (infoKosEl) {
        infoKosEl.textContent = `Booking untuk: ${propertyData.name} (Rp ${Number(
          propertyData.price
        ).toLocaleString("id-ID")})`;
      }

      document.title = `Booking ${propertyData.name} - Rentease`;

      // ==== DATA USER ====
      if (authResult.data) {
        namaUserEl.value = authResult.data.userName || "";
        emailUserEl.value = authResult.data.email || "";
      }

    } catch (err) {
      console.error(err);
      document.querySelector("main").innerHTML =
        `<p style="text-align:center;color:red;">${err.message}</p>`;
    }
  }

  // ================= SUBMIT BOOKING =================
  bookingForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    const bookingDate = document.getElementById("bookingDate").value;
    const paymentMethod = document.getElementById("paymentMethod").value;

    if (!bookingDate || !paymentMethod) {
      alert("Semua field wajib diisi.");
      return;
    }

    const today = new Date().toISOString().split("T")[0];
    if (bookingDate < today) {
      alert("Tanggal booking tidak boleh di masa lalu.");
      return;
    }

    try {
      const response = await fetch(
        "../../backend/api/bookings.php?action=create",
        {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            property_id: propertyId,
            booking_date: bookingDate,
            payment_method: paymentMethod
          })
        }
      );

      const result = await response.json();

      if (result.status === "success" && result.booking_id) {
        window.location.href = `payment.php?id=${result.booking_id}`;
      } else {
        throw new Error(result.message || "Booking gagal.");
      }

    } catch (err) {
      console.error(err);
      alert(err.message);
    }
  });

  // ================= JAM REALTIME =================
  if (timeDisplay) {
    const updateTime = () => {
      const now = new Date();
      timeDisplay.textContent =
        now.toLocaleString("id-ID", {
          dateStyle: "full",
          timeStyle: "medium"
        });
    };
    updateTime();
    setInterval(updateTime, 1000);
  }

  // ================= START =================
  loadPageData();
});
