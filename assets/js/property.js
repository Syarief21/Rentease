document.addEventListener("DOMContentLoaded", () => {
  const propertyListContainer = document.getElementById("propertyList");

  // Data dari PHP
  const allProperties =
    typeof serverRenderedProperties !== "undefined"
      ? serverRenderedProperties
      : [];

  let isLoggedIn = false;

  // ================= LOGIN STATUS =================
  fetch("../../backend/api/auth.php?action=status")
    .then((res) => res.json())
    .then((data) => {
      isLoggedIn = data.loggedIn;
      renderProperties(allProperties); // render awal
    });

  // ================= RENDER =================
  function renderProperties(items) {
    if (!propertyListContainer) return;

    propertyListContainer.innerHTML = "";

    if (!items || items.length === 0) {
      propertyListContainer.innerHTML =
        `<p class="no-data">Kos tidak ditemukan.</p>`;
      return;
    }

    items.forEach((p) => {
      const isFull = p.available_rooms == 0;

      propertyListContainer.innerHTML += `
        <div class="property-card ${isFull ? "full" : "available"}" data-id="${p.id}">
          <img src="../../${p.image_url || "assets/img/default.jpg"}" class="property-image" alt="${p.name}">
          <h3>${p.name}</h3>
          <p>
            <span class="map-icon">📍</span>
            <a class="location-link"
              href="https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(
                p.location
              )}"
              target="_blank">
              ${p.location}
            </a>
          </p>
          <p>Harga: Rp ${Number(p.price).toLocaleString("id-ID")}</p>
          <p>
            Kamar:
            <span class="${isFull ? "kamar-full" : "kamar-available"}">
              ${p.available_rooms} / ${p.total_rooms}
              ${isFull ? "(Full)" : ""}
            </span>
          </p>
          <button class="booking-btn" ${isFull ? "disabled" : ""}>
            Booking
          </button>
        </div>
      `;
    });

    attachCardEventListeners();
  }

  // ================= EVENT CARD =================
  function attachCardEventListeners() {
    document.querySelectorAll(".property-card").forEach((card) => {
      const propertyId = card.dataset.id;
      const property = allProperties.find((p) => p.id == propertyId);
      if (!property) return;

      const bookingBtn = card.querySelector(".booking-btn");

      if (bookingBtn) {
        bookingBtn.addEventListener("click", (e) => {
          e.stopPropagation();
          if (!isLoggedIn) return showLoginModal();
          if (property.available_rooms == 0)
            return showFullKosModal(property.name);

          window.location.href = `booking.php?id=${propertyId}`;
        });
      }

      card.addEventListener("click", (e) => {
        if (e.target.closest(".booking-btn, .location-link")) return;
        if (!isLoggedIn) return showLoginModal();
        if (property.available_rooms == 0)
          return showFullKosModal(property.name);

        window.location.href = `detail.php?id=${propertyId}`;
      });
    });
  }

  // ================= SEARCH =================
  function filterProperties(keyword) {
    const q = keyword.trim().toLowerCase();

    if (!q) {
      renderProperties(allProperties);
      return;
    }

    const filtered = allProperties.filter(
      (p) =>
        p.name.toLowerCase().includes(q) ||
        p.location.toLowerCase().includes(q)
    );

    renderProperties(filtered);
  }

  /// ================= SEARCH (MENUNGGU NAVBAR) =================
document.addEventListener("navbarReady", () => {
  const searchInput = document.getElementById("searchInput");
  const searchBtn = document.getElementById("searchBtn");

  if (!searchInput || !searchBtn) {
    console.warn("Search input / button tidak ditemukan");
    return;
  }

  searchInput.addEventListener("input", (e) => {
    filterProperties(e.target.value);
  });

  searchBtn.addEventListener("click", () => {
    filterProperties(searchInput.value);
  });
});


  // ================= MODAL =================
  function showLoginModal() {
    const modal = document.getElementById("loginModal");
    if (modal) modal.style.display = "flex";
  }

  function showFullKosModal(namaKos) {
    const modal = document.getElementById("fullKosModal");
    const msg = document.getElementById("fullKosMessage");
    if (modal && msg) {
      msg.innerText = `Kamar kos "${namaKos}" sudah penuh.`;
      modal.style.display = "flex";
    }
  }
});
