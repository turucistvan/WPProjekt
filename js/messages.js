$(document).ready(function() {
    // Function of the delete button
    $('.delete-message').click(function() {
        var messageId = $(this).data('message-id');

        // Ajax request to delete messages
        $.ajax({
            type: 'POST',
            url: 'messages.php',
            data: { action: 'delete_message', message_id: messageId },
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    // Toast message on success
                    showToast('success', response.message);
                    // Toast message on fail
                    $('[data-message-id="' + messageId + '"]').closest('.list-group-item').remove();
                } else {
                    showToast('error', 'Hiba történt az üzenet törlése közben: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                showToast('error', 'Hiba történt az üzenet törlése közben: ' + error);
            }
        });
    });

    
    function showToast(type, message) {
        var toastClass = type === 'success' ? 'bg-success' : 'bg-danger';
        var toastHtml = '<div class="toast ' + toastClass + ' text-white" role="alert" aria-live="assertive" aria-atomic="true">' +
                            '<div class="toast-body">' + message + '</div>' +
                        '</div>';

        $('#toast-container').append(toastHtml); 

        
        setTimeout(function() {
            $('.toast').fadeOut(function() {
                $(this).remove();
            });
        }, 3000); 
    }
});