$(document).ready(function() {
    // Kategória hozzáadása AJAX
    $('#add-category-form').submit(function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        $.post({
            url: 'manage_categories.php',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    showToast('success', response.message);
                    fetchCategories(); // Frissítjük a kategóriák listáját
                } else {
                    showToast('error', response.message);
                }
            },
            error: function() {
                showToast('error', 'Failed to add category.');
            }
        });
    });

    // Kategória törlése AJAX
    $('.delete-category-form').submit(function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        $.post({
            url: 'manage_categories.php',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    showToast('success', response.message);
                    fetchCategories(); // Frissítjük a kategóriák listáját
                } else {
                    showToast('error', response.message);
                }
            },
            error: function() {
                showToast('error', 'Failed to delete category.');
            }
        });
    });

    // Toast üzenet megjelenítése
    function showToast(type, message) {
        var toastContainer = $('#toast-container');
        var toast = $('<div class="toast ' + type + '">' + message + '</div>');
        toastContainer.append(toast);

        // Automatikus eltüntetés időzítése
        setTimeout(function() {
            toast.remove();
        }, 1000); // 1 másodperc múlva eltűnik
    }

    // Kategóriák frissítése AJAX
    function fetchCategories() {
        $.get({
            url: 'manage_categories.php',
            dataType: 'html',
            success: function(data) {
                $('.manage-categories').replaceWith($(data).find('.manage-categories'));
            },
            error: function() {
                showToast('error', 'Failed to fetch categories.');
            }
        });
    }
});