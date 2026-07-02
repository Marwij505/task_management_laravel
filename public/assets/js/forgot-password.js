// ABOUT OVERLAY
const aboutLink = document.getElementById('about-link'); // Take link from forgot-password.html
const aboutOverlay = document.getElementById('aboutOverlay'); // Take overlay from forgot-password.html
const aboutClose = document.getElementById('aboutClose'); // Take close button from forgot-password.html

if (aboutLink && aboutOverlay && aboutClose) {
    aboutLink.addEventListener('click', function (e) { // When the about link is clicked
        e.preventDefault(); // Prevent default link behavior
        aboutOverlay.classList.add('active'); // Show the about overlay by adding the 'active' class
    });

    aboutClose.addEventListener('click', function () { // When the close button is clicked
        aboutOverlay.classList.remove('active'); // Hide the about overlay by removing the 'active' class
    });

    aboutOverlay.addEventListener('click', function (e) { // When the overlay itself is clicked
        if (e.target === aboutOverlay) { // Check if the click is on the overlay (not on the content)
            aboutOverlay.classList.remove('active'); //Closing overlay when clicking outside of the content area
        }
    });
}

// CONTACT OVERLAY
// Ambil elemen link Contact dari navbar
const contactLink = document.getElementById('contact-link');
// Ambil elemen overlay Contact
const contactOverlay = document.getElementById('contactOverlay');
// Ambil tombol close pada popup Contact
const contactClose = document.getElementById('contactClose');

// Pastikan semua elemen Contact ada sebelum menjalankan event
if (contactLink && contactOverlay && contactClose) {
    // Saat link Contact diklik
    contactLink.addEventListener('click', function (e) {
        // Cegah link berpindah ke atas halaman
        e.preventDefault();
        // Tampilkan popup Contact
        contactOverlay.classList.add('active');
    });

    // Saat tombol close diklik
    contactClose.addEventListener('click', function () {
        // Sembunyikan popup Contact
        contactOverlay.classList.remove('active');
    });

    // Saat klik area gelap di luar box Contact
    contactOverlay.addEventListener('click', function (e) {
        // Jika yang diklik adalah overlay, tutup popup
        if (e.target === contactOverlay) {
            contactOverlay.classList.remove('active');
        }
    });
}

// TERMS & CONDITIONS POPUP
// Ambil elemen link Terms & Conditions dari navbar
const termsLink = document.getElementById('terms-link');
// Ambil elemen overlay Terms & Conditions
const termsOverlay = document.getElementById('termsOverlay');
// Ambil tombol close pada popup Terms & Conditions
const termsClose = document.getElementById('termsClose');

// Pastikan semua elemen Terms ada sebelum menjalankan event
if (termsLink && termsOverlay && termsClose) {
    // Saat link Terms diklik
    termsLink.addEventListener('click', function (e) {
        // Cegah link berpindah ke atas halaman
        e.preventDefault();
        // Tampilkan popup Terms & Conditions
        termsOverlay.classList.add('active');
    });

    // Saat tombol close diklik
    termsClose.addEventListener('click', function () {
        // Sembunyikan popup Terms & Conditions
        termsOverlay.classList.remove('active');
    });

    // Saat klik area gelap di luar box Terms
    termsOverlay.addEventListener('click', function (e) {
        // Jika yang diklik adalah overlay, tutup popup
        if (e.target === termsOverlay) {
            termsOverlay.classList.remove('active');
        }
    });
}

// LOGIN POPUP
// Ambil tombol Login yang ada di navbar bagian atas
const topLoginButton = document.getElementById('top-login-btn');
// Ambil link Login yang ada di bawah form login
const bottomLoginLink = document.getElementById('bottom-login-link');

// Buat fungsi untuk pindah ke halaman login dengan efek transisi
function goToLoginPage(event) {
    // Cegah perilaku default tombol atau link
    event.preventDefault();

    // Tambahkan class fade-out ke body agar halaman memudar
    document.body.classList.add('fade-out');

    // Tunggu animasi selesai, lalu pindah ke halaman login
    setTimeout(function () {
        window.location.href =
            window.FlowlistRoutes.login;
    }, 400);
}

// Jika tombol Login atas ada, aktifkan event click
if (topLoginButton) {
    // Saat tombol Login atas diklik, jalankan transisi lalu pindah halaman
    topLoginButton.addEventListener('click', goToLoginPage);
}

// Jika link Login bawah ada, aktifkan event click
if (bottomLoginLink) {
    // Saat link Login bawah diklik, jalankan transisi lalu pindah halaman
    bottomLoginLink.addEventListener('click', goToLoginPage);
}

// CLOSE ICON (X) TO LOGIN POPUP
// Ambil tombol close (ikon silang)
const iconClose = document.getElementById('iconClose');

// Jika ikon close ada
if (iconClose) {
    // Saat ikon close diklik
    iconClose.addEventListener('click', function () {
        // Tambahkan efek fade-out ke body
        document.body.classList.add('fade-out');

        // Tunggu animasi selesai lalu pindah ke login page
        setTimeout(function () {
            window.location.href =
                window.FlowlistRoutes.login;
        }, 400);
    });
}

// FORGOT PASSWORD FORM CUSTOM ALERT
// Ambil form forgot password
const forgotPasswordForm = document.getElementById('forgotPasswordForm');

// Ambil input utama
const forgotEmailInput = document.getElementById('email');
const forgotPasswordInput = document.getElementById('password');
const forgotConfirmPasswordInput = document.getElementById('confirmPassword');

// Ambil elemen alert custom
const forgotAlertOverlay = document.getElementById('forgotAlertOverlay');
const forgotAlertTitle = document.getElementById('forgotAlertTitle');
const forgotAlertMessage = document.getElementById('forgotAlertMessage');
const forgotAlertBtn = document.getElementById('forgotAlertBtn');
const forgotAlertIcon = document.getElementById('forgotAlertIcon');

// Fungsi tampilkan alert
function showForgotAlert(title, message, iconName) {
    forgotAlertTitle.textContent = title;
    forgotAlertMessage.textContent = message;
    forgotAlertIcon.setAttribute('name', iconName);
    forgotAlertOverlay.classList.add('active');
}

// Fungsi tutup alert
function closeForgotAlert() {
    forgotAlertOverlay.classList.remove('active');
}

// Tombol continue pada alert
if (forgotAlertBtn) {
    forgotAlertBtn.addEventListener('click', function () {
        closeForgotAlert();
    });
}

// Klik area gelap untuk tutup alert
if (forgotAlertOverlay) {
    forgotAlertOverlay.addEventListener('click', function (event) {
        if (event.target === forgotAlertOverlay) {
            closeForgotAlert();
        }
    });
}

// Ambil tombol toggle password forgot password
const forgotPasswordToggleBtn = document.getElementById('forgotPasswordToggleBtn');
const forgotPasswordToggleIcon = document.getElementById('forgotPasswordToggleIcon');

// Ambil tombol toggle confirm password forgot password
const forgotConfirmPasswordToggleBtn = document.getElementById('forgotConfirmPasswordToggleBtn');
const forgotConfirmPasswordToggleIcon = document.getElementById('forgotConfirmPasswordToggleIcon');

// SHOW / HIDE PASSWORD FORGOT PASSWORD
// Jika tombol toggle password utama ada
if (forgotPasswordToggleBtn && forgotPasswordInput && forgotPasswordToggleIcon) {
    forgotPasswordToggleBtn.addEventListener('click', function () {
        // Jika password sedang tersembunyi, tampilkan password
        if (forgotPasswordInput.type === 'password') {
            forgotPasswordInput.type = 'text';
            forgotPasswordToggleIcon.setAttribute('name', 'eye-outline');
        }

        // Jika password sedang terlihat, sembunyikan kembali
        else {
            forgotPasswordInput.type = 'password';
            forgotPasswordToggleIcon.setAttribute('name', 'eye-off-outline');
        }
    });
}

// SHOW / HIDE CONFIRM PASSWORD FORGOT PASSWORD
// Jika tombol toggle confirm password ada
if (forgotConfirmPasswordToggleBtn && forgotConfirmPasswordInput && forgotConfirmPasswordToggleIcon) {
    forgotConfirmPasswordToggleBtn.addEventListener('click', function () {
        // Jika confirm password sedang tersembunyi, tampilkan
        if (forgotConfirmPasswordInput.type === 'password') {
            forgotConfirmPasswordInput.type = 'text';
            forgotConfirmPasswordToggleIcon.setAttribute('name', 'eye-outline');
        }

        // Jika confirm password sedang terlihat, sembunyikan kembali
        else {
            forgotConfirmPasswordInput.type = 'password';
            forgotConfirmPasswordToggleIcon.setAttribute('name', 'eye-off-outline');
        }
    });
}

// Submit form forgot password
if (forgotPasswordForm) {
    forgotPasswordForm.addEventListener('submit', function (event) {
        // Cegah submit default browser
        event.preventDefault();

        // Ambil nilai input
        const emailValue = forgotEmailInput.value.trim();
        const passwordValue = forgotPasswordInput.value;
        const confirmPasswordValue = forgotConfirmPasswordInput.value;

        // Hapus error lama
        forgotEmailInput.classList.remove('input-error');
        forgotPasswordInput.classList.remove('input-error');
        forgotConfirmPasswordInput.classList.remove('input-error');

        // Cek email kosong
        if (emailValue === '') {
            forgotEmailInput.classList.add('input-error');
            showForgotAlert(
                'Reset Failed',
                'Please enter your email address before continuing.',
                'alert-circle-outline'
            );
            return;
        }

        // Validasi format email sederhana
        if (!emailValue.includes('@')) {
            forgotEmailInput.classList.add('input-error');
            showForgotAlert(
                'Invalid Email',
                'Please enter a valid email address.',
                'alert-circle-outline'
            );
            return;
        }

        // Validasi password kosong
        if (passwordValue === '') {
            forgotPasswordInput.classList.add('input-error');
            showForgotAlert(
                'Reset Failed',
                'Please enter a new password before continuing.',
                'alert-circle-outline'
            );
            return;
        }

        // Cek minimal password 4 karakter
        if (passwordValue.length < 4) {
            forgotPasswordInput.classList.add('input-error');
            showForgotAlert(
                'Weak Password',
                'Password must be at least 4 characters.',
                'alert-circle-outline'
            );
            return;
        }

        // Validasi confirm password kosong
        if (confirmPasswordValue === '') {
            forgotConfirmPasswordInput.classList.add('input-error');
            showForgotAlert(
                'Reset Failed',
                'Please confirm your new password before continuing.',
                'alert-circle-outline'
            );
            return;
        }

        // Cek password tidak sama
        if (passwordValue !== confirmPasswordValue) {
            forgotPasswordInput.classList.add('input-error');
            forgotConfirmPasswordInput.classList.add('input-error');
            showForgotAlert(
                'Password Mismatch',
                'Your new password and confirm password do not match. Please check them carefully.',
                'close-circle-outline'
            );
            return;
        }

        // Jika semua validasi lolos, kirim data ke backend
        // Kirim data Forgot Password ke Controller MVC
        const formData = new FormData();

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
            '_token',
            document.querySelector('meta[name="csrf-token"]').content
        );

        fetch(
            window.FlowlistRoutes.forgotPasswordProcess,
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
            | Reset password berhasil
            |--------------------------------------------------------------------------
            */

            if (
                result.ok &&
                result.data.success
            ) {
                showForgotAlert(
                    'Password Updated',
                    result.data.message +
                        ' You will be redirected to the login page.',
                    'checkmark-circle-outline'
                );

                forgotPasswordForm.reset();

                setTimeout(function () {
                    window.location.href =
                        result.data.redirect ||
                        window.FlowlistRoutes.login;
                }, 1500);

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Reset password gagal
            |--------------------------------------------------------------------------
            */

            showForgotAlert(
                'Reset Failed',
                result.data.message ||
                    'Failed to reset password.',
                'close-circle-outline'
            );
        })
        .catch(function (error) {
            console.error(
                'MVC forgot password error:',
                error
            );

            showForgotAlert(
                'Server Error',
                'Unable to connect to the Laravel server. Make sure php artisan serve and MySQL are running.',
                'cloud-offline-outline'
            );
        });
    });
}

// Hapus error saat user mengetik lagi
if (forgotEmailInput) {
    forgotEmailInput.addEventListener('input', function () {
        forgotEmailInput.classList.remove('input-error');
    });
}

if (forgotPasswordInput) {
    forgotPasswordInput.addEventListener('input', function () {
        forgotPasswordInput.classList.remove('input-error');
    });
}

if (forgotConfirmPasswordInput) {
    forgotConfirmPasswordInput.addEventListener('input', function () {
        forgotConfirmPasswordInput.classList.remove('input-error');
    });
}
