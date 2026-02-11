
document.addEventListener("DOMContentLoaded", () => {
    // Hydrate data from the server-rendered global variable
    const allBookings = typeof serverRenderedBookings !== 'undefined' ? serverRenderedBookings : [];

    const pendapatanMenu = document.querySelector(".pendapatan-menu");
    const pendapatanDropdown = document.querySelector(".pendapatan-dropdown");
    const downloadBtn = document.getElementById("downloadPdfBtn");
    
    let transactionsChart;
    let currentRange = 'all';

    // Render statistics based on a date range
    function renderStatistik(range = "all") {
        currentRange = range;
        let filteredBookings = allBookings;
        const now = new Date();

        if (range === "hari") {
            filteredBookings = allBookings.filter(p => new Date(p.created_at).toDateString() === now.toDateString());
        } else if (range === "minggu") {
            const weekStart = new Date(now);
            weekStart.setDate(now.getDate() - now.getDay());
            weekStart.setHours(0, 0, 0, 0);
            filteredBookings = allBookings.filter(p => new Date(p.created_at) >= weekStart);
        } else if (range === "bulan") {
            const currentMonth = now.getMonth();
            const currentYear = now.getFullYear();
            filteredBookings = allBookings.filter(p => {
                const bookingDate = new Date(p.created_at);
                return bookingDate.getMonth() === currentMonth && bookingDate.getFullYear() === currentYear;
            });
        }

        const confirmed = filteredBookings.filter(p => p.status === "confirmed").length;
        const rejected = filteredBookings.filter(p => p.status === "rejected").length;
        const pending = filteredBookings.filter(p => p.status === "pending").length;

        const totalPendapatan = filteredBookings
            .filter(p => p.status === "confirmed")
            .reduce((sum, p) => sum + Number(p.total_amount || 0), 0);

        document.getElementById("totalTransaksi").textContent = filteredBookings.length;
        document.getElementById("totalDiterima").textContent = confirmed;
        document.getElementById("totalDitolak").textContent = rejected;
        document.getElementById("totalMenunggu").textContent = pending;
        document.getElementById("totalPendapatan").textContent = "Rp " + totalPendapatan.toLocaleString("id-ID");

        updateChart(confirmed, rejected, pending);
    }
    
    function updateChart(confirmed, rejected, pending) {
        const ctx = document.getElementById("transaksiChart");
        if (!ctx) return;

        if (transactionsChart) {
            transactionsChart.destroy();
        }

        transactionsChart = new Chart(ctx.getContext('2d'), {
            type: "doughnut",
            data: {
                labels: ["Diterima", "Ditolak", "Menunggu"],
                datasets: [{
                    data: [confirmed, rejected, pending],
                    backgroundColor: ["#27ae60", "#e74c3c", "#f1c40f"],
                    borderColor: "#fff",
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: { legend: { position: 'bottom' } },
                animation: { duration: 1500, easing: 'easeOutBounce' }
            }
        });
    }

    // Attach event listeners
    if(pendapatanMenu) {
        pendapatanMenu.addEventListener("click", (e) => {
            e.stopPropagation();
            pendapatanDropdown.classList.toggle("show");
        });
    }

    document.addEventListener("click", () => {
        if(pendapatanDropdown && pendapatanDropdown.classList.contains('show')) {
            pendapatanDropdown.classList.remove("show");
        }
    });

    window.updatePendapatan = (range) => renderStatistik(range);

    if (downloadBtn) {
        downloadBtn.addEventListener("click", () => {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            doc.setFontSize(18);
            doc.text(`Laporan Transaksi (${currentRange})`, 20, 20);
            doc.setFontSize(12);
            doc.text(`Tanggal Cetak: ${new Date().toLocaleDateString('id-ID')}`, 20, 30);
            doc.text(`Total Transaksi: ${document.getElementById("totalTransaksi").textContent}`, 20, 50);
            doc.text(`Diterima: ${document.getElementById("totalDiterima").textContent}`, 20, 60);
            doc.text(`Ditolak: ${document.getElementById("totalDitolak").textContent}`, 20, 70);
            doc.text(`Menunggu: ${document.getElementById("totalMenunggu").textContent}`, 20, 80);
            doc.text(`Total Pendapatan (Diterima): ${document.getElementById("totalPendapatan").textContent}`, 20, 90);
            doc.save(`Laporan_Transaksi_${currentRange}_${Date.now()}.pdf`);
        });
    }

    // Initial calculation and chart render
    renderStatistik('all');
});