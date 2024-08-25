  // Function to edit the animals
  function editPet(animal_id) {
    // Getting all the data about the animals
    var animal_name = document.getElementById('animal-name-' + animal_id).innerText.trim();
    var animal_description = document.getElementById('animal-description-' + animal_id).innerText.trim();
    var animal_age = document.getElementById('animal-age-' + animal_id).innerText.trim();
    var animal_gender = document.getElementById('animal-gender-' + animal_id).innerText.trim();
  
    // Form to edit the animals
    var editFormHTML = `
      <form id="editForm-${animal_id}" onsubmit="submitEditForm(event, ${animal_id})">
        <div class="mb-3">
          <label for="edit-name-${animal_id}" class="form-label">Név</label>
          <input type="text" class="form-control" id="edit-name-${animal_id}" value="${animal_name}" required>
        </div>
        <div class="mb-3">
          <label for="edit-description-${animal_id}" class="form-label">Leírás</label>
          <textarea class="form-control" id="edit-description-${animal_id}" rows="3" required>${animal_description}</textarea>
        </div>
        <div class="mb-3">
          <label for="edit-age-${animal_id}" class="form-label">Életkor</label>
          <input type="number" class="form-control" id="edit-age-${animal_id}" value="${animal_age}" required>
        </div>
        <div class="mb-3">
          <label for="edit-gender-${animal_id}" class="form-label">Nem</label>
          <select class="form-select" id="edit-gender-${animal_id}" required>
            <option value="male" ${animal_gender === 'hím' ? 'selected' : ''}>Hím</option>
            <option value="female" ${animal_gender === 'nőstény' ? 'selected' : ''}>Nőstény</option>
            <option value="unknown" ${animal_gender === 'ismeretlen' ? 'selected' : ''}>Ismeretlen</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary me-2">Mentés</button>
        <button type="button" class="btn btn-secondary" onclick="cancelEdit(${animal_id})">Mégse</button>
      </form>
    `;
  
    // Upadting the info inside the card via the form
    var cardBody = document.getElementById(`animal-card-body-${animal_id}`);
    cardBody.innerHTML = editFormHTML;
  }
  
 // Function to cancel editing an animal
  function cancelEdit(animal_id) {
    // Reload the page to cancel editing (restoration can be optionally implemented)
    window.location.reload();
  }
  
  // Handle form submission for editing an animal
  function submitEditForm(event, animal_id) {
    event.preventDefault(); // Prevent the default form submission behavior
  
    var formData = {
      action: 'edit_pet', // Action to perform
      animal_id: animal_id, // ID of the animal being edited
      animal_data: [
        $('#edit-name-' + animal_id).val().trim(),
        $('#edit-description-' + animal_id).val().trim(),
        $('#edit-age-' + animal_id).val().trim(),
        $('#edit-gender-' + animal_id).val().trim()
      ].join(',')
    };
  
     // Send an AJAX request to update the animal
    $.ajax({
      type: 'POST',
      url: window.location.href,
      data: formData,
      dataType: 'json',
      encode: true,
      success: function (data) {
        if (data.success) {
          // On successful response, update the UI
          showToast('success', data.message);
  
          // Update animal details on the page
          $('#animal-name-' + animal_id).text($('#edit-name-' + animal_id).val().trim());
          $('#animal-description-' + animal_id).text($('#edit-description-' + animal_id).val().trim());
          $('#animal-age-' + animal_id).text($('#edit-age-' + animal_id).val().trim());
          $('#animal-gender-' + animal_id).text($('#edit-gender-' + animal_id).val().trim());
  
             // Restore the card view with updated animal details
          var cardBody = $('#animal-card-body-' + animal_id);
          cardBody.html(`
            <h5 class="card-title" id="animal-name-${animal_id}">${$('#edit-name-' + animal_id).val().trim()}</h5>
            <p class="card-text" id="animal-description-${animal_id}">${$('#edit-description-' + animal_id).val().trim()}</p>
            <p class="card-text">Életkor: <span id="animal-age-${animal_id}">${$('#edit-age-' + animal_id).val().trim()}</span> év</p>
            <p class="card-text">Nem: <span id="animal-gender-${animal_id}">${$('#edit-gender-' + animal_id).val().trim()}</span></p>
            <div class="d-flex justify-content-between align-items-center">
              <button type="button" class="btn btn-edit me-2" onclick="editPet(${animal_id})">
                Szerkesztés
              </button>
              <button type="button" class="btn btn-delete" onclick="deletePet(${animal_id})">
                Törlés
              </button>
            </div>
          `);
        } else {
          // On error response, show an error message
          showToast('error', data.message);
        }
      },
      error: function (xhr, status, error) {
        console.error('Hiba történt:', error);
        showToast('error', 'Hiba történt az állat szerkesztése során.');
      }
    });
  }
  
  // Function to delete an animal by its ID
  function deletePet(animal_id) {
        // Ask for confirmation before deleting
    if (confirm('Biztosan törölni szeretnéd ezt az állatot?')) {
      var formData = new FormData();
      formData.append('action', 'delete_pet');
      formData.append('animal_id', animal_id);
  
       // Use Fetch API to delete the animal
      fetch(window.location.href, {
        method: 'POST', // HTTP method
        body: formData // Form data to be sent
      })
      .then(response => response.json()) // Parse the JSON response
      .then(data => {
        if (data.success) {
          // On successful deletion, show a success message and remove the animal card from the DOM
          showToast('success', data.message);
          var deletedCard = document.getElementById('animal-card-' + animal_id);
          if (deletedCard) {
            deletedCard.remove();
          }
        } else {
          // Show an error toast if failed
          showToast('error', data.message);
        }
      })
      .catch(error => {
        console.error('Hiba történt:', error);
        showToast('error', 'Hiba történt az állat törlése során.');
      });
    }
  }
  
// Function to display toast notifications
  function showToast(type, message) {
    var toastContainer = document.querySelector('.toast-container');
    var toast = document.createElement('div');
    toast.className = 'toast align-items-center text-white bg-' + (type === 'success' ? 'success' : 'danger') + ' show';
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    toast.innerHTML = `
      <div class="d-flex">
        <div class="toast-body">
          ${message}
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    `;
    toastContainer.appendChild(toast);
  
    setTimeout(function() {
      var bsToast = new bootstrap.Toast(toast);
      bsToast.show();
      setTimeout(function() {
        toast.remove();
      }, 3000);
    }, 100);
  }