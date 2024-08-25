<?php
// Az 'db_config.php' fájl importálása, amelyben a $pdo PDO objektum van inicializálva.
require_once 'db_config.php';

// Check if the 'category_id' GET parameter is present in the request.
if (isset($_GET['category_id'])) {
    // Store the value of the 'category_id' GET parameter.
    $categoryFilter = $_GET['category_id'];

     // Construct an SQL query to select breeds that belong to the specified category.
    $breedQuery = "SELECT * FROM breeds WHERE category_id = :category_id";

    // Prepare the query using the PDO object.
    $breedStmt = $pdo->prepare($breedQuery);

    // Execute the query with the appropriate parameters (category_id).
    $breedStmt->execute(['category_id' => $categoryFilter]);

    // Fetch the result set and store it as an associative array.
    $breeds = $breedStmt->fetchAll(PDO::FETCH_ASSOC);

    // Generate HTML code for a dropdown list to select a breed.
    echo '<label for="breed">Fajta:</label>';
    echo '<select name="breed" id="breed">';
    echo '<option value="">Válasszon fajtát</option>';

    // Iterate over each breed and generate an option element for each one.
    foreach ($breeds as $breed) {
        // Check if the breed is previously selected.
        // If yes, add the 'selected' attribute to the option.
        $selected = ($breed['breed_id'] == $breedFilter) ? 'selected' : '';
        
        // Create an option element containing the breed name and value.
        echo "<option value=\"" . $breed['breed_id'] . "\" $selected>" . $breed['name'] . "</option>";
    }

    // A select elem lezárása.
    echo '</select>';
}
?>
