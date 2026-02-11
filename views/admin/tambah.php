<!-- views/admin/tambah.php -->
<link rel="stylesheet" href="../../assets/css/tambah.css" />
<style>
  /* Modal popup styles */
  .modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 9999;
  }
  .modal-content {
    background: #fff;
    padding: 20px 30px;
    border-radius: 10px;
    text-align: center;
    max-width: 400px;
    width: 90%;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    animation: fadeIn 0.3s ease-in-out;
  }
  .modal-content h3 {
    margin-bottom: 10px;
    color: #4caf50;
  }
  .modal-content p {
    margin-bottom: 20px;
  }
  .btn-close-modal {
    padding: 8px 16px;
    background: #4caf50;
    color: #fff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
  }
  @keyframes fadeIn {
    from {
      opacity: 0;
      transform: translateY(-20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
</style>

<section class="add-property-card">
  <h2>Tambah Kos Baru</h2>
  <form id="addPropertyForm" class="form" enctype="multipart/form-data">
    <div class="form-group">
      <label for="namaKos">Nama Kos</label>
      <input type="text" id="namaKos" name="name" required />
    </div>
    <div class="form-group">
      <label for="lokasi">Lokasi</label>
      <input type="text" id="lokasi" name="location" required />
    </div>
    <div class="form-group">
      <label for="harga">Harga (Rp)</label>
      <input type="number" id="harga" name="price" required />
    </div>
    <div class="form-group">
      <label for="totalKamar">Total Kamar</label>
      <input type="number" id="totalKamar" name="total_rooms" required />
    </div>
    <div class="form-group">
      <label for="tersediaKamar">Kamar Tersedia</label>
      <input
        type="number"
        id="tersediaKamar"
        name="available_rooms"
        required
      />
    </div>
    <div class="form-group">
      <label for="deskripsi">Deskripsi</label>
      <textarea id="deskripsi" name="description" required></textarea>
    </div>
    <div class="form-group">
      <label for="foto">Foto Kos</label>
      <input type="file" id="foto" name="image" accept="image/*" />
      <img
        id="previewImage"
        src=""
        alt="Preview Foto"
        style="
          display: none;
          margin-top: 10px;
          width: 200px;
          border-radius: 8px;
        "
      />
    </div>
    <button type="submit" class="btn-primary">Konfirmasi</button>
  </form>
</section>

<!-- Modal popup -->
<div id="successModal" class="modal-overlay">
  <div class="modal-content">
    <h3>Berhasil!</h3>
    <p>Kos baru berhasil ditambahkan.</p>
    <button id="closeModalBtn" class="btn-close-modal">Tutup</button>
  </div>
</div>