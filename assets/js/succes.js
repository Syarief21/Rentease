
      document.addEventListener("DOMContentLoaded", () => {
        // Navbar & Footer
        document.getElementById("navbar-placeholder").innerHTML = `
        <nav class="navbar">
          <div class="nav-container">
            <div class="logo">
              <a href="index.php">
                <img src="../../assets/img/rentease.jpg" alt="Rentease Logo">
              </a>
            </div>
          </div>
        </nav>`;
        document.getElementById("footer-placeholder").innerHTML = `
        <footer>&copy; 2025 Rentease. Semua Hak Dilindungi.</footer>`;

        const homeBtn = document.getElementById("homeBtn");
        homeBtn.addEventListener("click", () => {
          const overlay = document.getElementById("loading-overlay");
          overlay.style.display = "flex";
          overlay.style.pointerEvents = "auto"; // hentikan interaksi user

          setTimeout(() => {
            window.location.href = "index.php";
          }, 2000); // loading 1 detik
        });

        const fireworksContainer = document.querySelector(".fireworks");

        function launchFirework() {
          const rocket = document.createElement("div");
          rocket.classList.add("rocket");
          const startX = Math.random() * (window.innerWidth - 50) + 25;
          rocket.style.left = startX + "px";
          fireworksContainer.appendChild(rocket);

          // naik ke atas
          const endY = 100 + Math.random() * 150;
          rocket.animate([{ bottom: "0px" }, { bottom: endY + "px" }], {
            duration: 800,
            easing: "ease-out",
          }).onfinish = () => {
            rocket.remove();
            // explode
            const numParticles = 15 + Math.floor(Math.random() * 20);
            const colors = [
              "#ff3d00",
              "#ffea00",
              "#00e5ff",
              "#76ff03",
              "#ff00c8",
              "#ff6f61",
              "#ffa500",
            ];
            for (let i = 0; i < numParticles; i++) {
              const p = document.createElement("div");
              p.classList.add("firework-particle");
              const angle = Math.random() * 2 * Math.PI;
              const dist = 80 + Math.random() * 80;
              const dx = Math.cos(angle) * dist + "px";
              const dy = Math.sin(angle) * dist + "px";
              p.style.setProperty("--dx", dx);
              p.style.setProperty("--dy", dy);
              p.style.backgroundColor =
                colors[Math.floor(Math.random() * colors.length)];
              p.style.left = startX + "px";
              p.style.bottom = endY + "px";
              fireworksContainer.appendChild(p);
              setTimeout(() => p.remove(), 1200);
            }
          };
        }

        // launch fireworks setiap 0.1 detik
        setInterval(launchFirework, 100);
      });
