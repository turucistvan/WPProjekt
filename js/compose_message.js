// Toast messages controlled by bootstrap
var toastElList = [].slice.call(document.querySelectorAll('.toast'));
var toastList = toastElList.map(function (toastEl) {
    return new bootstrap.Toast(toastEl);
});

// Automatic toast
if (toastList.length > 0) {
    toastList.forEach(function (toast) {
        toast.show();
    });
}