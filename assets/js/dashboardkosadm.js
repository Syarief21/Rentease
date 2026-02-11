// assets/js/dashboardkosadm.js

// Make adminProperties global so it can be updated by navigationadm.js
window.adminProperties = typeof serverRenderedAdminProperties !== 'undefined' ? serverRenderedAdminProperties : [];

document.addEventListener('DOMContentLoaded', function() {
    // Attach listeners for interactive elements that are already rendered by the server
    attachCardActionListeners();
    setupGlobalEventListeners();
});

/**
 * Attaches event listeners to action buttons on each property card (menu, edit, delete).
 */
window.attachCardActionListeners = function() {
    document.querySelectorAll('.menu-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const menuId = `menu-${e.target.dataset.id}`;
            const dropdown = document.getElementById(menuId);
            // Hide all other menus
            document.querySelectorAll('.menu-dropdown').forEach(menu => {
                if (menu.id !== menuId) menu.style.display = 'none';
            });
            // Toggle the clicked menu
            if(dropdown) dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        });
    });

    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const id = e.target.dataset.id;
            showDeletePopup(id);
        });
    });

    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const id = e.target.dataset.id;
            openEditPopup(id);
        });
    });
}

/**
 * Sets up global event listeners for popups and forms.
 */
function setupGlobalEventListeners() {
    document.getElementById('cancelDeleteBtn')?.addEventListener('click', () => {
        document.getElementById('confirmDeletePopup').style.display = 'none';
    });

    document.getElementById('confirmDeleteBtn')?.addEventListener('click', handleConfirmDelete);

    document.getElementById('editCancel')?.addEventListener('click', () => {
        document.getElementById('editPopup').style.display = 'none';
    });

    document.getElementById('editForm')?.addEventListener('submit', handleEditFormSubmit);
    
    // Close menus if clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.matches('.menu-btn')) {
            document.querySelectorAll('.menu-dropdown').forEach(menu => menu.style.display = 'none');
        }
    });
}

let propertyIdToDelete = null;

function showDeletePopup(id) {
    propertyIdToDelete = id;
    document.getElementById('confirmDeletePopup').style.display = 'flex';
}

async function handleConfirmDelete() {
    if (!propertyIdToDelete) return;

    try {
        const response = await fetch(`../../backend/api/properties.php?id=${propertyIdToDelete}`, {
            method: 'DELETE'
        });
        const result = await response.json();

        if (result.status === 'success') {
            alert('Kos berhasil dihapus!');
            // Remove dari array lokal
            window.adminProperties = window.adminProperties.filter(p => p.id != propertyIdToDelete);
            
            // Refresh dashboard
            if (typeof window.showDashboard === 'function') {
                window.showDashboard();
            } else {
                location.reload();
            }
        } else {
            alert('Gagal menghapus properti: ' + result.message);
        }
    } catch (error) {
        console.error('Error deleting property:', error);
        alert('Terjadi kesalahan koneksi saat menghapus.');
    } finally {
        document.getElementById('confirmDeletePopup').style.display = 'none';
        propertyIdToDelete = null;
    }
}

function openEditPopup(id) {
    // Gunakan window.adminProperties yang selalu terbaru
    const property = window.adminProperties.find(p => p.id == id);
    if (!property) {
        // Fallback: coba fetch jika tidak ada di list lokal
        fetch(`../../backend/api/properties.php?id=${id}`)
            .then(res => res.json())
            .then(res => {
                if(res.status === 'success') {
                    populateEditForm(res.data, id);
                    document.getElementById('editPopup').style.display = 'block';
                } else {
                    alert('Data properti tidak ditemukan.');
                }
            });
        return;
    }

    populateEditForm(property, id);
    document.getElementById('editPopup').style.display = 'block';
}

function populateEditForm(property, id) {
    document.getElementById("editNama").value = property.name;
    document.getElementById("editAlamat").value = property.location;
    document.getElementById("editHarga").value = property.price;
    document.getElementById("editTotalKamar").value = property.total_rooms;
    document.getElementById("editTersedia").value = property.available_rooms;
    document.getElementById("editDeskripsi").value = property.description;
    document.getElementById("editFoto").value = ""; // Reset file input

    document.getElementById("editForm").dataset.propertyId = id;
}

async function handleEditFormSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const id = form.dataset.propertyId;
    if (!id) {
        alert('ID properti tidak ditemukan');
        return;
    }

    const formData = new FormData(form);
    formData.append('id', id);

    try {
        const response = await fetch('../../backend/api/properties.php', {
            method: 'POST',
            body: formData,
        });
        const result = await response.json();

        if (result.status === 'success') {
            alert('Kos berhasil diperbarui!');
            document.getElementById('editPopup').style.display = 'none';
            
            // Update array lokal
            const updatedProperty = window.adminProperties.find(p => p.id == id);
            if (updatedProperty) {
                updatedProperty.name = form.name.value;
                updatedProperty.location = form.location.value;
                updatedProperty.price = form.price.value;
                updatedProperty.total_rooms = form.total_rooms.value;
                updatedProperty.available_rooms = form.available_rooms.value;
                updatedProperty.description = form.description.value;
            }
            
            // Refresh dashboard
            if (typeof window.showDashboard === 'function') {
                window.showDashboard();
            } else {
                location.reload();
            }
        } else {
            alert('Gagal memperbarui properti: ' + result.message);
        }
    } catch (error) {
        console.error('Error updating property:', error);
        alert('Terjadi kesalahan koneksi saat update.');
    }
}