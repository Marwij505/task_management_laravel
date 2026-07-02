// =====================================================
// ABOUT OVERLAY
// Mengatur popup About pada halaman register
// =====================================================
const aboutLink = document.getElementById('about-link');
const aboutOverlay = document.getElementById('aboutOverlay');
const aboutClose = document.getElementById('aboutClose');

if (aboutLink && aboutOverlay && aboutClose) {
    aboutLink.addEventListener('click', function (e) {
        e.preventDefault();
        aboutOverlay.classList.add('active');
    });

    aboutClose.addEventListener('click', function () {
        aboutOverlay.classList.remove('active');
    });

    aboutOverlay.addEventListener('click', function (e) {
        if (e.target === aboutOverlay) {
            aboutOverlay.classList.remove('active');
        }
    });
}


// =====================================================
// CONTACT OVERLAY
// Mengatur popup Contact pada halaman register
// =====================================================
const contactLink = document.getElementById('contact-link');
const contactOverlay = document.getElementById('contactOverlay');
const contactClose = document.getElementById('contactClose');

if (contactLink && contactOverlay && contactClose) {
    contactLink.addEventListener('click', function (e) {
        e.preventDefault();
        contactOverlay.classList.add('active');
    });

    contactClose.addEventListener('click', function () {
        contactOverlay.classList.remove('active');
    });

    contactOverlay.addEventListener('click', function (e) {
        if (e.target === contactOverlay) {
            contactOverlay.classList.remove('active');
        }
    });
}


// =====================================================
// TERMS & CONDITIONS OVERLAY
// Mengatur popup Terms & Conditions pada halaman register
// =====================================================
const termsLink = document.getElementById('terms-link');
const termsOverlay = document.getElementById('termsOverlay');
const termsClose = document.getElementById('termsClose');

if (termsLink && termsOverlay && termsClose) {
    termsLink.addEventListener('click', function (e) {
        e.preventDefault();
        termsOverlay.classList.add('active');
    });

    termsClose.addEventListener('click', function () {
        termsOverlay.classList.remove('active');
    });

    termsOverlay.addEventListener('click', function (e) {
        if (e.target === termsOverlay) {
            termsOverlay.classList.remove('active');
        }
    });
}


// =====================================================
// LOGIN BUTTON NAVIGATION
// Mengatur tombol/link untuk pindah dari register ke login
// =====================================================
const topLoginButton = document.getElementById('top-login-btn');
const bottomLoginLink = document.getElementById('bottom-login-link');

function goToLoginPage(event) {
    event.preventDefault();

    document.body.classList.add('fade-out');

    setTimeout(function () {
        window.location.href =
            window.FlowlistRoutes.login;
    }, 400);
}

if (topLoginButton) {
    topLoginButton.addEventListener('click', goToLoginPage);
}

if (bottomLoginLink) {
    bottomLoginLink.addEventListener('click', goToLoginPage);
}


// =====================================================
// CLOSE ICON NAVIGATION
// Mengatur tombol X agar kembali ke halaman login
// =====================================================
const iconClose = document.getElementById('iconClose');

if (iconClose) {
    iconClose.addEventListener('click', function () {
        document.body.classList.add('fade-out');

        setTimeout(function () {
            window.location.href =
                window.FlowlistRoutes.login;
        }, 400);
    });
}


// =====================================================
// REGISTER FORM ELEMENTS
// Mengambil elemen form, input, checkbox, dan custom alert
// =====================================================
const registerForm = document.getElementById('registerForm');

const registerUsername = document.getElementById('registerUsername');
const registerEmail = document.getElementById('registerEmail');
const registerPassword = document.getElementById('registerPassword');
const registerConfirmPassword = document.getElementById('registerConfirmPassword');
const termsCheckbox = document.getElementById('terms-checkbox');

const registerAlertOverlay = document.getElementById('registerAlertOverlay');
const registerAlertTitle = document.getElementById('registerAlertTitle');
const registerAlertMessage = document.getElementById('registerAlertMessage');
const registerAlertBtn = document.getElementById('registerAlertBtn');
const registerAlertIcon = document.getElementById('registerAlertIcon');


// =====================================================
// SHOW REGISTER ALERT
// Fungsi untuk menampilkan popup alert register
// =====================================================
function showRegisterAlert(title, message, iconName) {
    registerAlertTitle.textContent = title;
    registerAlertMessage.textContent = message;
    registerAlertIcon.setAttribute('name', iconName);
    registerAlertOverlay.classList.add('active');
}


// =====================================================
// CLOSE REGISTER ALERT
// Fungsi untuk menutup popup alert register
// =====================================================
function closeRegisterAlert() {
    registerAlertOverlay.classList.remove('active');
}

if (registerAlertBtn) {
    registerAlertBtn.addEventListener('click', function () {
        closeRegisterAlert();
    });
}

if (registerAlertOverlay) {
    registerAlertOverlay.addEventListener('click', function (event) {
        if (event.target === registerAlertOverlay) {
            closeRegisterAlert();
        }
    });
}


// =====================================================
// PASSWORD TOGGLE ELEMENTS
// Mengambil tombol dan icon show/hide password
// =====================================================
const registerPasswordToggleBtn = document.getElementById('registerPasswordToggleBtn');
const registerPasswordToggleIcon = document.getElementById('registerPasswordToggleIcon');

const registerConfirmPasswordToggleBtn = document.getElementById('registerConfirmPasswordToggleBtn');
const registerConfirmPasswordToggleIcon = document.getElementById('registerConfirmPasswordToggleIcon');


// =====================================================
// SHOW / HIDE PASSWORD
// Mengatur password utama agar bisa ditampilkan/disembunyikan
// =====================================================
if (registerPasswordToggleBtn && registerPassword && registerPasswordToggleIcon) {
    registerPasswordToggleBtn.addEventListener('click', function () {
        if (registerPassword.type === 'password') {
            registerPassword.type = 'text';
            registerPasswordToggleIcon.setAttribute('name', 'eye-outline');
        } else {
            registerPassword.type = 'password';
            registerPasswordToggleIcon.setAttribute('name', 'eye-off-outline');
        }
    });
}


// =====================================================
// SHOW / HIDE CONFIRM PASSWORD
// Mengatur confirm password agar bisa ditampilkan/disembunyikan
// =====================================================
if (registerConfirmPasswordToggleBtn && registerConfirmPassword && registerConfirmPasswordToggleIcon) {
    registerConfirmPasswordToggleBtn.addEventListener('click', function () {
        if (registerConfirmPassword.type === 'password') {
            registerConfirmPassword.type = 'text';
            registerConfirmPasswordToggleIcon.setAttribute('name', 'eye-outline');
        } else {
            registerConfirmPassword.type = 'password';
            registerConfirmPasswordToggleIcon.setAttribute('name', 'eye-off-outline');
        }
    });
}


// =====================================================
// REGISTER SUBMIT
// Mengatur proses register:
// validasi input, cek terms, kirim data ke register.php,
// lalu menampilkan hasil dari backend
// =====================================================
if (registerForm) {
    registerForm.addEventListener('submit', function (event) {
        event.preventDefault();

        const usernameValue = registerUsername.value.trim();
        const emailValue = registerEmail.value.trim();
        const passwordValue = registerPassword.value;
        const confirmPasswordValue = registerConfirmPassword.value;

        registerUsername.classList.remove('input-error');
        registerEmail.classList.remove('input-error');
        registerPassword.classList.remove('input-error');
        registerConfirmPassword.classList.remove('input-error');

        // Validasi username kosong
        if (usernameValue === '') {
            registerUsername.classList.add('input-error');
            showRegisterAlert(
                'Registration Failed',
                'Please enter your username first.',
                'alert-circle-outline'
            );
            return;
        }

        // Validasi email kosong
        if (emailValue === '') {
            registerEmail.classList.add('input-error');
            showRegisterAlert(
                'Registration Failed',
                'Please enter your email first.',
                'alert-circle-outline'
            );
            return;
        }

        // Validasi password kosong
        if (passwordValue === '') {
            registerPassword.classList.add('input-error');
            showRegisterAlert(
                'Registration Failed',
                'Please enter your password first.',
                'alert-circle-outline'
            );
            return;
        }

        // Validasi confirm password kosong
        if (confirmPasswordValue === '') {
            registerConfirmPassword.classList.add('input-error');
            showRegisterAlert(
                'Registration Failed',
                'Please confirm your password first.',
                'alert-circle-outline'
            );
            return;
        }

        // Validasi minimal password 4 karakter
        if (passwordValue.length < 4) {
            registerPassword.classList.add('input-error');

            showRegisterAlert(
                'Weak Password',
                'Password must be at least 4 characters.',
                'alert-circle-outline'
            );
            return;
        }

        // Validasi password dan confirm password harus sama
        if (passwordValue !== confirmPasswordValue) {
            registerPassword.classList.add('input-error');
            registerConfirmPassword.classList.add('input-error');

            showRegisterAlert(
                'Password Mismatch',
                'Your password and confirm password do not match.',
                'close-circle-outline'
            );
            return;
        }

        // Validasi terms & conditions harus dicentang
        if (!termsCheckbox.checked) {
            showRegisterAlert(
                'Terms Required',
                'You need to agree to the terms and conditions first.',
                'document-text-outline'
            );
            return;
        }

        // =====================================================
        // SEND DATA TO PHP
        // Mengirim data register ke backend/register.php
        // menggunakan FormData agar sesuai dengan $_POST di PHP
        // =====================================================
        const formData = new FormData();
        formData.append(
            'username',
            usernameValue
        );

        formData.append(
            'email',
            emailValue
        );

        formData.append(
            'password',
            passwordValue
        );

        formData.append(
            'confirm_password',
            confirmPasswordValue
        );

        formData.append(
            'terms',
            termsCheckbox.checked ? '1' : '0'
        );

        formData.append(
            '_token',
            document.querySelector('meta[name="csrf-token"]').content
        );

        fetch(
            window.FlowlistRoutes.registerProcess,
            {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            }
        )
        .then(async function (response) {
            let data;

            try {
                data = await response.json();
            } catch (error) {
                throw new Error(
                    'The server response is not valid JSON.'
                );
            }

            return {
                ok: response.ok,
                status: response.status,
                data: data
            };
        })
        .then(function (result) {
            /*
            |--------------------------------------------------------------------------
            | Register berhasil
            |--------------------------------------------------------------------------
            */

            if (
                result.ok &&
                result.data.success
            ) {
                showRegisterAlert(
                    'Registration Successful',
                    result.data.message +
                        ' You will be redirected to the login page.',
                    'checkmark-circle-outline'
                );

                registerForm.reset();

                setTimeout(function () {
                    window.location.href =
                        result.data.redirect ||
                        window.FlowlistRoutes.login;
                }, 1500);

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Register gagal
            |--------------------------------------------------------------------------
            */

            showRegisterAlert(
                'Registration Failed',
                result.data.message ||
                    'Registration failed. Please try again.',
                'close-circle-outline'
            );
        })
        .catch(function (error) {
            console.error(
                'MVC register error:',
                error
            );

            showRegisterAlert(
                'Server Error',
                'Unable to connect to the Laravel server. Make sure php artisan serve and MySQL are running.',
                'cloud-offline-outline'
            );
        });
    });
}


// =====================================================
// REMOVE INPUT ERROR
// Menghapus tanda error ketika user mulai mengetik ulang
// =====================================================
if (registerUsername) {
    registerUsername.addEventListener('input', function () {
        registerUsername.classList.remove('input-error');
    });
}

if (registerEmail) {
    registerEmail.addEventListener('input', function () {
        registerEmail.classList.remove('input-error');
    });
}

if (registerPassword) {
    registerPassword.addEventListener('input', function () {
        registerPassword.classList.remove('input-error');
    });
}

if (registerConfirmPassword) {
    registerConfirmPassword.addEventListener('input', function () {
        registerConfirmPassword.classList.remove('input-error');
    });
}