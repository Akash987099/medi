$(document).ready(function () {
    $('#loginForm').on('submit', function (e) {
        e.preventDefault();
        var formdata = $(this).serialize();
        $.ajax({
            url: adminLoginUrl,
            type: "POST",
            data: formdata,
            success: function (response) {
                if (response.status === "success") {
                    if (response.redirect) {
                        window.location.href = response.redirect;
                        return;
                    }

                    window.location.href = adminIndexUrl;
                }

                if (response.status === "error") {
                    showAlert('danger', 'Error!', response.message);
                }
            },
            error: function (xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.message) {
                    showAlert('danger', 'Error!', xhr.responseJSON.message);
                    return;
                }

                console.error(xhr.responseText);
                showAlert('danger', 'Error!', 'There was a problem with your login.');
            }
        });
    });
    function showAlert(type, title, message) {
        const colors = {
            success: {
                bg: 'bg-green-50',
                text: 'text-green-800',
                darkBg: 'dark:bg-gray-800',
                darkText: 'dark:text-green-400'
            },
            danger: {
                bg: 'bg-red-50',
                text: 'text-red-800',
                darkBg: 'dark:bg-gray-800',
                darkText: 'dark:text-red-400'
            },
            warning: {
                bg: 'bg-yellow-50',
                text: 'text-yellow-800',
                darkBg: 'dark:bg-gray-800',
                darkText: 'dark:text-yellow-300'
            },
            info: {
                bg: 'bg-blue-50',
                text: 'text-blue-800',
                darkBg: 'dark:bg-gray-800',
                darkText: 'dark:text-blue-400'
            },
            dark: {
                bg: 'bg-gray-50',
                text: 'text-gray-800',
                darkBg: 'dark:bg-gray-800',
                darkText: 'dark:text-gray-300'
            }
        };
        const alert = colors[type] || colors.info;
        const alertBox = `
            <div class="alert p-4 mb-2 text-sm rounded-lg ${alert.bg} ${alert.text} ${alert.darkBg} ${alert.darkText} shadow-md animate-fade-in-down" role="alert">
                <span class="font-medium">${title}</span> ${message}
            </div>
        `;
        $('#alert-container').append(alertBox);
        setTimeout(() => {
            $('#alert-container .alert').first().fadeOut(500, function () {
                $(this).remove();
            });
        }, 4000);
    }
});
