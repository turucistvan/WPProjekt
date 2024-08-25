document.addEventListener('DOMContentLoaded', function() {
    var categorySelect = document.getElementById('category_id');
    var breedSelect = document.getElementById('breed_id');

    // By default, display dog breeds
    var defaultCategoryId = 1; // For example, the ID for the dog category
    loadBreeds(defaultCategoryId);

    // Event listener for changes in category selection
    categorySelect.addEventListener('change', function() {
        var categoryId = this.value;
        if (categoryId) {
            loadBreeds(categoryId); // Load breeds based on selected category
        } else {
            breedSelect.innerHTML = '<option value="">-- Válasszon kategóriát először --</option>';
        }
    });

    // Function to load breeds based on category ID
    function loadBreeds(categoryId) {
                // Send an AJAX request
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'get_breeds.php?category_id=' + categoryId, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                       // Update the breeds dropdown list with the response text
                    breedSelect.innerHTML = xhr.responseText;
                } else {
                    // Log an error if the request fails
                    console.error('AJAX hiba: ' + xhr.status);
                }
            }
        };
        xhr.send();
    }
});