document.addEventListener("DOMContentLoaded", () => {
  // Fungsi utama untuk menginisialisasi navbar dan semua fungsionalitas terkait
  async function initializeNavbar() {
    try {
      const response = await fetch("../../backend/api/auth.php?action=status");
      if (!response.ok) {
        throw new Error("Gagal mengambil status login.");
      }
      const authData = await response.json();
      renderNavbar(authData);
      attachEventListeners(authData);
    } catch (error) {
      console.error("Error initializing navbar:", error);
      // Render navbar untuk state logged-out jika terjadi error
      renderNavbar({ loggedIn: false });
      attachEventListeners({ loggedIn: false });
    }
  }

  // Merender HTML untuk navbar dan footer
  function renderNavbar(authData) {
    const userData = authData.loggedIn ? authData.data : null;
    const isLoggedIn = authData.loggedIn;
    
    // Tentukan URL profil picture
    const profilePicUrl = userData?.profile_picture 
      ? `../../${userData.profile_picture}` 
      : '../../assets/img/prof pengguna.jpg';

    // Render Navbar
    const navbarHTML = `
      <nav class="navbar">
        <div class="nav-container">
          <a href="index.php" class="logo">
            <img src="../../assets/img/rentease.jpg" alt="Rentease Logo" />
          </a>
          <div class="search-container-navbar">
            <input type="text" id="searchInput" placeholder="Cari kos...">
            <button id="searchBtn">Cari</button>
          </div>
          <ul class="nav-menu" id="navMenu">
            <li><a href="#tentang" id="tentangLink">Tentang</a></li>
            <li><a href="#kontak" id="kontakLink">Kontak</a></li>
            ${
              isLoggedIn
                ? `<li><div id="profileIcon" class="profile-icon">
                     <img src="${profilePicUrl}" alt="Profil" style="width:100%; height:100%; border-radius:50%; object-fit:cover;">
                   </div></li>`
                : `<li><a href="../auth/login.php">Login</a></li>`
            }
          </ul>
        </div>
      </nav>
      ${
        isLoggedIn
          ? `
      <div class="profile-sidebar" id="profileSidebar">
        <div>
          <div class="profile-photo" id="profilePhotoClick" style="cursor:pointer;">
            <img src="${profilePicUrl}">
            <h3>Selamat datang, ${userData?.userName || "User"}!</h3>
            <p class="detail-akun">Detail Akun :</p>
          </div>
          <div class="user-info">
            <p><strong>Username:</strong> ${userData?.userName || ""}</p>
            <p><strong>Email:</strong> ${userData?.email || ""}</p>
          </div>
        </div>
        <button id="logoutBtn" class="logout-js-btn">Logout</button>
      </div>
      <div class="profile-overlay" id="profileOverlay"></div>`
          : ""
      }
    `;
    document.getElementById("navbar-placeholder").innerHTML = navbarHTML;

    document.dispatchEvent(new Event("navbarReady"));
    // Render Footer
    document.getElementById("footer-placeholder").innerHTML = `<footer><p>&copy; 2025 Rentease. Semua Hak Dilindungi.</p></footer>`;
  }

  // Melampirkan semua event listener setelah DOM dirender
  function attachEventListeners(authData) {
    // Smooth scroll
    const tentangLink = document.getElementById("tentangLink");
    const kontakLink = document.getElementById("kontakLink");
    if (tentangLink) {
      tentangLink.addEventListener("click", (e) => {
        e.preventDefault();
        document.querySelector("#tentang")?.scrollIntoView({ behavior: "smooth" });
      });
    }
    if (kontakLink) {
      kontakLink.addEventListener("click", (e) => {
        e.preventDefault();
        document.querySelector("#kontak")?.scrollIntoView({ behavior: "smooth" });
      });
    }

    if (authData.loggedIn) {
      // Event listener untuk sidebar profil
      const profileIcon = document.getElementById("profileIcon");
      const sidebar = document.getElementById("profileSidebar");
      const overlayProfile = document.getElementById("profileOverlay");
      if (profileIcon) {
        profileIcon.addEventListener("click", () => {
          sidebar.classList.toggle("active");
          overlayProfile.classList.toggle("active");
        });
        overlayProfile.addEventListener("click", () => {
          sidebar.classList.remove("active");
          overlayProfile.classList.remove("active");
        });
      }

      // Event listener untuk tombol logout
      const logoutBtn = document.querySelector(".logout-js-btn");
      if (logoutBtn) {
        logoutBtn.addEventListener("click", performLogout);
      }
      
      // Event listener untuk membuka popup edit profil
      const profilePhotoClick = document.getElementById("profilePhotoClick");
      const editOverlay = document.getElementById("editProfileOverlay");
      if (profilePhotoClick) {
        profilePhotoClick.addEventListener("click", () => {
          const userData = authData.data;
          document.getElementById("editUsername").value = userData.userName;
          document.getElementById("editEmail").value = userData.email;
          const preview = document.getElementById("previewFoto");
          if (userData.profile_picture) {
            preview.src = `../../${userData.profile_picture}`;
            preview.style.display = "block";
          }
          editOverlay.style.display = "flex";
        });
      }
    }
    
    // Event listener untuk form edit (di luar kondisi login agar selalu ada)
    const editForm = document.getElementById("editProfileForm");
    if (editForm) {
      editForm.addEventListener("submit", handleProfileUpdate);
    }
    
    // Penutupan modal
    document.addEventListener("click", (e) => {
      const editOverlay = document.getElementById("editProfileOverlay");
      if (e.target && (e.target.id === "closeEditProfile" || e.target.classList.contains('btn-tutup'))) {
        editOverlay.style.display = "none";
      }
      if (e.target && e.target.id === "closeSuccessBtn") {
        document.getElementById("successModal").style.display = "none";
        location.reload();
      }
    });

    // Preview foto
    const editFotoInput = document.getElementById("editFoto");
    if (editFotoInput) {
      editFotoInput.addEventListener("change", function () {
        const file = this.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = (e) => {
            document.getElementById("previewFoto").src = e.target.result;
            document.getElementById("previewFoto").style.display = "block";
          };
          reader.readAsDataURL(file);
        }
      });
    }
  }

  // Fungsi untuk menangani update profil
  async function handleProfileUpdate(e) {
    e.preventDefault();
    const form = e.target;
    
    // Debug: Check form elements
    console.log('Form elements:', {
      editUsername: document.getElementById('editUsername'),
      editEmail: document.getElementById('editEmail'),
      editFoto: document.getElementById('editFoto')
    });
    
    const nameInput = document.getElementById('editUsername');
    const emailInput = document.getElementById('editEmail');
    const fileInput = document.getElementById('editFoto');
    
    if (!nameInput || !emailInput) {
      console.error('Form inputs not found');
      alert('Gagal: Form tidak lengkap');
      return;
    }
    
    const name = nameInput.value.trim();
    const email = emailInput.value.trim();
    
    if (!name || !email) {
      alert('Nama dan email tidak boleh kosong');
      return;
    }
    
    const formData = new FormData();
    formData.append('name', name);
    formData.append('email', email);
    
    const imageFile = fileInput?.files[0];
    if (imageFile) {
      formData.append('profile_picture', imageFile);
      console.log('File attached:', imageFile.name);
    }

    try {
      console.log('Sending update request...');
      const response = await fetch('../../backend/api/users.php?action=update_profile', {
        method: 'POST',
        body: formData
      });
      
      const responseText = await response.text();
      console.log('Response text:', responseText);
      
      let result;
      try {
        result = JSON.parse(responseText);
      } catch (parseError) {
        console.error('JSON parse error:', parseError, 'Response:', responseText);
        alert('Error: Server tidak mengembalikan JSON yang valid');
        return;
      }
      
      if (result.status === 'success') {
        console.log('Profile update success, data:', result.data);
        
        // Update all profile picture elements
        if (result.data.profile_picture_url || result.data.profile_picture) {
          const picUrl = result.data.profile_picture_url 
            ? (result.data.profile_picture_url.startsWith('../../') ? result.data.profile_picture_url : `../../${result.data.profile_picture_url}`)
            : result.data.profile_picture 
            ? `../../${result.data.profile_picture}`
            : '../../assets/img/prof pengguna.jpg';
          
          // Update profile icon in navbar
          const profileImages = document.querySelectorAll('img[alt="Profil"]');
          profileImages.forEach(img => {
            img.src = picUrl;
          });
          
          // Update profile photo in sidebar
          const profilePhoto = document.querySelector('.profile-photo img');
          if (profilePhoto) {
            profilePhoto.src = picUrl;
          }
          
          console.log('Updated profile picture:', picUrl);
        }
        
        // Update profile name in sidebar
        if (result.data.userName) {
          const profileName = document.querySelector('.profile-photo h3');
          if (profileName) {
            profileName.textContent = `Selamat datang, ${result.data.userName}!`;
          }
          console.log('Updated name:', result.data.userName);
        }
        
        // Update email in user-info section
        if (result.data.userEmail) {
          const userInfo = document.querySelector('.user-info');
          if (userInfo) {
            userInfo.innerHTML = `<p><strong>Username:</strong> ${result.data.userName || ""}</p><p><strong>Email:</strong> ${result.data.userEmail}</p>`;
          }
          console.log('Updated email:', result.data.userEmail);
        }
        
        document.getElementById('editProfileOverlay').style.display = 'none';
        alert('Profil berhasil diperbarui!');
        
        // Reload setelah delay kecil agar UI update terlihat dengan sempurna
        setTimeout(() => {
          location.reload();
        }, 1000);
      } else {
        alert('Gagal memperbarui profil: ' + (result.message || 'Unknown error'));
      }
    } catch (error) {
      console.error('Error updating profile:', error);
      alert('Terjadi kesalahan: ' + error.message);
    }
  }
  
  // Fungsi untuk melakukan logout
  function performLogout() {
    fetch("../../backend/api/auth.php?action=logout", { method: "POST" })
      .catch(err => console.error("Logout request failed:", err))
      .finally(() => {
        window.location.href = "../auth/login.php";
      });
  }

  // Mulai proses inisialisasi
  initializeNavbar();
});