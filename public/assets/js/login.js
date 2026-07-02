// ABOUT POPUP
const aboutLink = document.getElementById('about-link'); // Take link from login.html
const aboutOverlay = document.getElementById('aboutOverlay'); // Take overlay from login.html
const aboutClose = document.getElementById('aboutClose'); // Take close button from login.html

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

// CONTACT POPUP
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

// REGISTER BUTTONS
// Ambil tombol Register yang ada di navbar bagian atas
const topRegisterButton = document.getElementById('top-register-btn');
// Ambil link Register yang ada di bawah form login
const bottomRegisterLink = document.getElementById('bottom-register-link');

// Buat fungsi untuk pindah ke halaman register dengan efek transisi
function goToRegisterPage(event) {
    // Cegah perilaku default tombol atau link
    event.preventDefault();
    // Tambahkan class fade-out ke body agar halaman memudar
    document.body.classList.add('fade-out');
    // Tunggu animasi selesai, lalu pindah ke halaman register
    setTimeout(function () {
        window.location.href = window.FlowlistRoutes.register;
    }, 400);
}

// Jika tombol Register atas ada, aktifkan event click
if (topRegisterButton) {
    // Saat tombol Register atas diklik, jalankan transisi lalu pindah halaman
    topRegisterButton.addEventListener('click', goToRegisterPage);
}

// Jika link Register bawah ada, aktifkan event click
if (bottomRegisterLink) {
    // Saat link Register bawah diklik, jalankan transisi lalu pindah halaman
    bottomRegisterLink.addEventListener('click', goToRegisterPage);
}

// FORGOT PASSWORD LINK
// Ambil link Forgot Password dari form login
const forgotPasswordLink = document.getElementById('forgot-password-link');

// Jika link Forgot Password ada, aktifkan event click
if (forgotPasswordLink) {
    // Saat link Forgot Password diklik
    forgotPasswordLink.addEventListener('click', function (event) {
        // Mencegah perpindahan halaman secara langsung
        event.preventDefault();
        // Menambahkan class fade-out ke body agar muncul efek transisi
        document.body.classList.add('fade-out');
        // Menunggu animasi selesai, lalu pindah ke halaman forgot-password
        setTimeout(function () {
            window.location.href = window.FlowlistRoutes.forgotPassword;
        }, 400);
    });
}

// LOGIN FORM CUSTOM ALERT
// Ambil form login
const loginForm = document.getElementById('loginForm');
// Ambil input login
const loginInput = document.getElementById('loginInput');
// Ambil input password
const passwordInput = document.getElementById('passwordInput');
// Ambil checkbox remember me
const rememberMeCheckbox = document.getElementById('rememberMeCheckbox');

// Ambil tombol toggle password dan icon-nya
const passwordToggleBtn = document.getElementById('passwordToggleBtn');
const passwordToggleIcon = document.getElementById('passwordToggleIcon');

// Ambil elemen alert custom
const loginAlertOverlay = document.getElementById('loginAlertOverlay');
const loginAlertTitle = document.getElementById('loginAlertTitle');
const loginAlertMessage = document.getElementById('loginAlertMessage');
const loginAlertBtn = document.getElementById('loginAlertBtn');
const loginAlertIcon = document.querySelector('.login-alert-icon ion-icon');

// Fungsi untuk menampilkan alert custom
function showLoginAlert(title, message, iconName) {
    // Isi judul alert
    loginAlertTitle.textContent = title;

    // Isi pesan alert
    loginAlertMessage.textContent = message;

    // Ganti icon alert
    loginAlertIcon.setAttribute('name', iconName);

    // Tampilkan overlay alert
    loginAlertOverlay.classList.add('active');
}

// Fungsi untuk menutup alert custom
function closeLoginAlert() {
    // Sembunyikan overlay
    loginAlertOverlay.classList.remove('active');
}

// Jika tombol alert ada
if (loginAlertBtn) {
    // Saat tombol diklik, tutup alert
    loginAlertBtn.addEventListener('click', function () {
        closeLoginAlert();
    });
}

// Jika klik area gelap di luar box
if (loginAlertOverlay) {
    loginAlertOverlay.addEventListener('click', function (event) {
        // Tutup hanya jika klik tepat di overlay
        if (event.target === loginAlertOverlay) {
            closeLoginAlert();
        }
    });
}

// SHOW / HIDE PASSWORD
// Jika tombol toggle password ada
if (passwordToggleBtn && passwordInput && passwordToggleIcon) {
    passwordToggleBtn.addEventListener('click', function () {
        // Jika password sedang tersembunyi, ubah jadi terlihat
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            passwordToggleIcon.setAttribute('name', 'eye-outline');
        }

        // Jika password sedang terlihat, sembunyikan lagi
        else {
            passwordInput.type = 'password';
            passwordToggleIcon.setAttribute('name', 'eye-off-outline');
        }
    });
}

// Jika form login ada
if (loginForm) {
    loginForm.addEventListener('submit', function (event) {
        // Cegah submit default browser
        event.preventDefault();

        // Ambil nilai input dan rapikan spasi
        const loginValue = loginInput.value.trim();
        const passwordValue = passwordInput.value;

        // Hapus style error lama
        loginInput.classList.remove('input-error');
        passwordInput.classList.remove('input-error');

        // Jika username/email kosong
        if (loginValue === '') {
            loginInput.classList.add('input-error');
            showLoginAlert(
                'Email or Username Bruh',
                'Enter your beloved email or username before continuing.',
                'alert-circle-outline'
            );
            return;
        }

        // Jika password kosong
        if (passwordValue === '') {
            passwordInput.classList.add('input-error');
            showLoginAlert(
                'What is Your Password Dumbass??!!',
                'Enter your goddamn password before continuing.',
                'alert-circle-outline'
            );
            return;
        }
        // Kirim data login ke Controller MVC
        const formData = new FormData();
        formData.append('login', loginValue);
        formData.append('password', passwordValue);
        formData.append('remember', rememberMeCheckbox && rememberMeCheckbox.checked ? '1' : '0');
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

        fetch(window.FlowlistRoutes.loginProcess, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            // Jika login berhasil
            if (data.success) {
                showLoginAlert(
                    'Login Successful',
                    data.message,
                    'checkmark-circle-outline'
                );

                // Tunggu sebentar lalu pindah ke dashboard
                setTimeout(function () {
                    window.location.href = data.redirect || window.FlowlistRoutes.dashboard;
                }, 1200);
            }

            // Jika login gagal
            else {
                showLoginAlert(
                    'Login Failed',
                    data.message,
                    'close-circle-outline'
                );
            }
        })
        .catch(error => {
            console.error('Login error:', error);

            showLoginAlert(
                'Server Error',
                'Unable to connect to the Laravel server. Make sure php artisan serve and MySQL are running.',
                'cloud-offline-outline'
            );
        });
    });
}

// Saat user mengetik lagi, hapus style error
if (loginInput) {
    loginInput.addEventListener('input', function () {
        loginInput.classList.remove('input-error');
    });
}

if (passwordInput) {
    passwordInput.addEventListener('input', function () {
        passwordInput.classList.remove('input-error');
    });
}