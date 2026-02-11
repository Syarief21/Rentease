// search.js - Fungsi-fungsi untuk pencarian dan menampilkan daftar kos

document.addEventListener('DOMContentLoaded', () => {
    const list = document.getElementById('propertyList');
    const btn = document.getElementById('searchBtn');
    const input = document.getElementById('searchInput');

    function loadData(query = '') {
        // Tampilkan pesan loading
        list.innerHTML = '<p class="loading">Memuat data...</p>';
        
        fetch('../../backend/controllers/PropertyController.php?action=getAll&search=' + encodeURIComponent(query))
            .then(response => {
                // Cek apakah response berupa JSON
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return response.json();
                } else {
                    // Jika bukan JSON, mungkin ada error PHP
                    return response.text().then(text => {
                        console.error('Server response:', text);
                        throw new Error('Server mengembalikan error bukan JSON');
                    });
                }
            })
            .then(data => {
                // Cek apakah data berisi error
                if (data.success === false) {
                    console.error('Server error:', data.message);
                    list.innerHTML = '<p class="error">Server error: ' + data.message + '</p>';
                    return;
                }
                
                if (!data || data.length === 0) {
                    list.innerHTML = '<p class="no-results">Tidak ada kos yang ditemukan.</p>';
                    return;
                }

                list.innerHTML = data.map(item => `
                    <div class="card">
                        <img src="../../uploads/property/${item.foto}" alt="${item.nama_kos}" onerror="this.src='../../assets/img/default.jpg'">
                        <div class="card-content">
                            <h3>${item.nama_kos}</h3>
                            <p>${item.lokasi}</p>
                            <p>${formatRupiah(item.harga)}</p>
                            <p>Admin: ${item.admin_nama || 'Tidak diketahui'}</p>
                            <a href="detail.php?id=${item.id_property}">Detail</a>
                        </div>
                    </div>
                `).join('');
            })
            .catch(error => {
                console.error('Error loading data:', error);
                list.innerHTML = '<p class="error">Gagal memuat data kos. Silakan periksa koneksi atau hubungi administrator.</p>';
            });
    }

    // Muat semua data kos saat halaman dimuat
    loadData();

    // Tambahkan event listener untuk tombol pencarian
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        loadData(input.value);
    });

    // Tambahkan event listener untuk tombol Enter
    input.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            loadData(input.value);
        }
    });
});