// Function to reject a request by its ID
function rejectRequest(request_id) {
    var formData = new FormData();
    formData.append('action', 'reject_request'); // Specify the action as 'reject_request'
    formData.append('request_id', request_id); // Append the request ID to the form data

    // Send a POST request with the form data
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json()) // Parse the JSON response
    .then(data => {
        if (data.success) {
            // If the request was successful, remove the corresponding request card from the DOM
            document.getElementById('request-card-' + request_id).remove();
            showToast('success', data.message); // Show a success toast notification
        } else {
            showToast('error', data.message);  // Otherwise an error
        }
    })
    .catch(error => {
        console.error('Hiba történt:', error); // Log any errors to the console
        showToast('error', 'Hiba történt az elutasítás során.'); // Show an error toast notification
    });
  }
 // Function to accept a request by its ID
  function acceptRequest(request_id) {
    var formData = new FormData();
    formData.append('action', 'accept_request');
    formData.append('request_id', request_id);
    // Send a POST request with the form data
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('request-card-' + request_id).remove();
            showToast('success', data.message);
        } else {
            showToast('error', data.message);
        }
    })
    .catch(error => {
        console.error('Hiba történt:', error);
        showToast('error', 'Hiba történt az elfogadás során.');
    });
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
    // Show the toast and remove it after a delay
    setTimeout(function() {
      var bsToast = new bootstrap.Toast(toast);
      bsToast.show();
      setTimeout(function() {
        toast.remove();
      }, 3000);
    }, 100);
  }