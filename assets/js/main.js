// SmartCart - general front-end scripts

document.addEventListener('DOMContentLoaded', function () {
    // Auto-dismiss alert messages after 4 seconds
    document.querySelectorAll('.alert.auto-dismiss').forEach(function (alertEl) {
        setTimeout(function () {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alertEl);
            bsAlert.close();
        }, 4000);
    });

    // Confirm before delete actions
    document.querySelectorAll('.confirm-delete').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (!confirm('Are you sure you want to delete this item?')) {
                e.preventDefault();
            }
        });
    });

    // Simple client-side validation helper for required fields
    document.querySelectorAll('form.needs-validation').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Star rating input widget
    document.querySelectorAll('.star-rating-input').forEach(function (container) {
        const stars = container.querySelectorAll('i');
        const input = container.parentElement.querySelector('input[name="rating"]');
        stars.forEach(function (star) {
            star.addEventListener('click', function () {
                const value = star.getAttribute('data-value');
                input.value = value;
                stars.forEach(function (s) {
                    s.classList.toggle('fa-solid', s.getAttribute('data-value') <= value);
                    s.classList.toggle('fa-regular', s.getAttribute('data-value') > value);
                });
            });
        });
    });
});
