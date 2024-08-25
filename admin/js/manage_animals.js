// AJAX kérés az állat szerkesztéséhez
function editAnimal(animal_id) {
    var name = $('#name_' + animal_id).val(); // Állat nevének lekérése
    var description = $('#description_' + animal_id).val(); // Állat leírásának lekérése
    var age = $('#age_' + animal_id).val(); // Állat korának lekérése
    var gender = $('#gender_' + animal_id).val(); // Állat nemének lekérése

    $.ajax({
        type: 'POST', // POST kérés
        url: 'manage_animals.php', // manage_animals.php szkript elérése
        data: {
            action: 'edit_animal', // 'edit_animal' művelet meghatározása
            animal_id: animal_id, // Állat azonosító
            name: name, // Módosított név
            description: description, // Módosított leírás
            age: age, // Módosított kor
            gender: gender // Módosított nem
        },
        success: function (response) {
            var result = JSON.parse(response); // Válasz JSON parse-olása
            if (result.status === 'success') {
                showToast(result.message, 'success'); // Sikeres üzenet megjelenítése
            } else {
                showToast(result.message, 'error'); // Hibaüzenet megjelenítése
            }
        },
        error: function(xhr, status, error) {
            console.error('Hiba az állat szerkesztése során:', error); // Hibakezelés
            showToast('Hiba történt az állat szerkesztése során.', 'error'); // Hibajelzés megjelenítése
        }
    });
}

// AJAX kérés az állat törléséhez
function deleteAnimal(animal_id) {
    if (confirm('Biztosan törölni szeretné ezt az állatot?')) {
        $.ajax({
            type: 'POST', // POST kérés
            url: 'manage_animals.php', // manage_animals.php szkript elérése
            data: {
                action: 'delete_animal', // 'delete_animal' művelet meghatározása
                animal_id: animal_id // Törlendő állat azonosítója
            },
            success: function (response) {
                var result = JSON.parse(response); // Válasz JSON parse-olása
                if (result.status === 'success') {
                    showToast(result.message, 'success'); // Sikeres üzenet megjelenítése
                    $('#row_' + animal_id).remove(); // Sor eltávolítása a táblázatból
                } else {
                    showToast(result.message, 'error'); // Hibaüzenet megjelenítése
                }
            },
            error: function(xhr, status, error) {
                console.error('Hiba az állat törlése során:', error); // Hibakezelés
                showToast('Hiba történt az állat törlése során.', 'error'); // Hibajelzés megjelenítése
            }
        });
    }
}

// AJAX kérés az állatok szűréséhez
function filterAnimals() {
    var category_id = $('#category_filter').val(); // Kategória szűrő értékének lekérése
    var breed_id = $('#breed_filter').val(); // Fajta szűrő értékének lekérése

    $.ajax({
        type: 'POST', // POST kérés
        url: 'manage_animals.php', // manage_animals.php szkript elérése
        data: {
            action: 'filter_animals', // 'filter_animals' művelet meghatározása
            category_id: category_id, // Szűrő kategória azonosító
            breed_id: breed_id // Szűrő fajta azonosító
        },
        success: function (response) {
            var result = JSON.parse(response); // Válasz JSON parse-olása
            if (result.status !== 'error') {
                updateTable(result); // Táblázat frissítése az új adatokkal
            } else {
                showToast('Hiba történt a szűrés során.', 'error'); // Hibaüzenet megjelenítése
            }
        },
        error: function(xhr, status, error) {
            console.error('Hiba a szűrés során:', error); // Hibakezelés
            showToast('Hiba történt a szűrés során.', 'error'); // Hibajelzés megjelenítése
        }
    });
}

// Táblázat frissítése új adatokkal
function updateTable(animals) {
    var tableBody = ''; // Táblázat tartalom inicializálása
    animals.forEach(function(animal) {
        tableBody += `<tr id='row_${animal.animal_id}'>
                        <td>${animal.animal_id}</td>
                        <td><input type='text' id='name_${animal.animal_id}' value='${animal.name}' required></td>
                        <td><textarea id='description_${animal.animal_id}' required>${animal.description}</textarea></td>
                        <td><input type='number' id='age_${animal.animal_id}' value='${animal.age}' required></td>
                        <td>
                            <select id='gender_${animal.animal_id}' required>
                                <option value='Male' ${animal.gender == 'Male' ? 'selected' : ''}>Hím</option>
                                <option value='Female' ${animal.gender == 'Female' ? 'selected' : ''}>Nőstény</option>
                                <option value='Unknown' ${animal.gender == 'Unknown' ? 'selected' : ''}>Ismeretlen</option>
                            </select>
                        </td>
                        <td><img src='../images/${animal.image}' alt='Állat képe' class='animal-image'></td>
                        <td>${animal.advertiser}</td>
                        <td>
                            <button onclick='editAnimal(${animal.animal_id})'>Szerkesztés</button>
                            <button onclick='deleteAnimal(${animal.animal_id})'>Törlés</button>
                        </td>
                      </tr>`; // Táblázat sorának HTML létrehozása
    });
    $('table tbody').html(tableBody); // Táblázat tartalom frissítése
}

// Toast üzenet megjelenítése
function showToast(message, type) {
    var toast = $('<div class="toast ' + type + '">' + message + '</div>'); // Toast elem létrehozása
    $('body').append(toast); // Toast elem hozzáadása a body-hoz
    setTimeout(function() {
        toast.addClass('show'); // Toast megjelenítése animációval
    }, 100);
    setTimeout(function() {
        toast.remove(); // Toast eltávolítása
    }, 3000); // 3 másodperc után
}
