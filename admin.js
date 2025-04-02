document.addEventListener('DOMContentLoaded', function() {
    // DataTable initialization
    const dataTables = document.querySelectorAll('.data-table');
    dataTables.forEach(table => {
        new simpleDatatables.DataTable(table, {
            perPage: 10,
            perPageSelect: [10, 25, 50, 100],
            labels: {
                placeholder: "Search...",
                perPage: "{select} entries per page",
                noRows: "No data found",
                info: "Showing {start} to {end} of {rows} entries"
            }
        });
    });

    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    });

    // Book image preview
    const bookImageInput = document.getElementById('book_image');
    if (bookImageInput) {
        bookImageInput.addEventListener('change', function() {
            const preview = document.getElementById('book_image_preview');
            const file = this.files[0];
            
            if (file) {
                if (!file.type.match('image.*')) {
                    showToast('Please select an image file', 'danger');
                    this.value = '';
                    return;
                }
                
                if (file.size > 2 * 1024 * 1024) { // 2MB
                    showToast('Image size should be less than 2MB', 'danger');
                    this.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });
    }

    // Status toggles
    document.querySelectorAll('.status-toggle').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const id = this.dataset.id;
            const status = this.checked ? 'active' : 'inactive';
            const model = this.dataset.model;
            
            fetch(`admin/api/update_status.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: id,
                    status: status,
                    model: model
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    this.checked = !this.checked;
                    showToast('Failed to update status', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.checked = !this.checked;
                showToast('An error occurred', 'danger');
            });
        });
    });
});