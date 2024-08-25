function deleteUser(user_id) {
    if (confirm('Biztosan törölni szeretné ezt a felhasználót és az összes hozzá kapcsolódó állatot?')) {
        $.ajax({
            type: 'POST',
            url: 'manage_users.php',
            data: {
                action: 'delete_user',
                user_id: user_id
            },
            success: function (response) {
                if (response == 'success') {
                    alert('Felhasználó és az összes hozzá kapcsolódó állat sikeresen törölve.');
                    location.reload(); // Oldal újratöltése frissítéshez
                } else {
                    alert('Hiba történt a felhasználó törlése során.');
                }
            },
            error: function(xhr, status, error) {
                console.error('Hiba a felhasználó törlése során:', error);
                alert('Hiba történt a felhasználó törlése során.');
            }
        });
    }
}