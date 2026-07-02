// ===============================
// FLOWLIST PROFILE PAGE JS
// Versi database PHP
// ===============================

// Tabs
const tabButtons = document.querySelectorAll('.tab-btn');
const profilePanels = document.querySelectorAll('.profile-panel');

// Message
const profileMessage = document.getElementById('profileMessage');

// Profile elements
const avatarInput = document.getElementById('avatarInput');
const avatarCircle = document.getElementById('avatarCircle');
const fullNameInput = document.getElementById('fullName');
const emailAddressInput = document.getElementById('emailAddress');
const profileForm = document.getElementById('profileForm');

// Notification elements
const notificationsForm = document.getElementById('notificationsForm');
const emailNotificationsInput = document.getElementById('emailNotifications');
const taskRemindersInput = document.getElementById('taskReminders');
const weeklyReportInput = document.getElementById('weeklyReport');

// Preferences elements
const preferencesForm = document.getElementById('preferencesForm');
const themeSelect = document.getElementById('themeSelect');
const languageSelect = document.getElementById('languageSelect');
const dateFormatSelect = document.getElementById('dateFormatSelect');

// Security elements
const securityForm = document.getElementById('securityForm');
const currentPasswordInput = document.getElementById('currentPassword');
const newPasswordInput = document.getElementById('newPassword');
const confirmPasswordInput = document.getElementById('confirmPassword');

// Current selected avatar file
let selectedAvatarFile = null;

// ===============================
// TABS
// ===============================
function activateTab(targetId) {
    tabButtons.forEach(function (button) {
        button.classList.remove('active');
    });

    profilePanels.forEach(function (panel) {
        panel.classList.remove('active');
    });

    const activeButton = document.querySelector(`[data-tab="${targetId}"]`);
    if (activeButton) {
        activeButton.classList.add('active');
    }

    const activePanel = document.getElementById(targetId);
    if (activePanel) {
        activePanel.classList.add('active');
    }
}

tabButtons.forEach(function (button) {
    button.addEventListener('click', function () {
        const targetId = button.getAttribute('data-tab');
        activateTab(targetId);
    });
});

// ===============================
// MESSAGE
// ===============================
function showProfileMessage(message, type = '') {
    if (!profileMessage) return;

    profileMessage.textContent = message;
    profileMessage.className = `profile-message show ${type}`.trim();
}

function hideProfileMessage() {
    if (!profileMessage) return;

    profileMessage.textContent = '';
    profileMessage.className = 'profile-message';
}

// ===============================
// HELPERS
// ===============================
function getInitials(name) {
    const words = String(name || '').trim().split(' ').filter(Boolean);

    if (words.length === 0) return 'U';
    if (words.length === 1) return words[0].charAt(0).toUpperCase();

    return (
        words[0].charAt(0).toUpperCase() +
        words[1].charAt(0).toUpperCase()
    );
}

function setAvatar(user) {
    if (!avatarCircle) return;

    if (user.avatar_path) {
        avatarCircle.innerHTML = `<img src="${user.avatar_path}" alt="User Avatar">`;
        return;
    }

    avatarCircle.textContent = getInitials(user.full_name || user.username || user.email);
}

function setButtonLoading(button, isLoading, loadingText, defaultText) {
    if (!button) return;

    button.disabled = isLoading;

    const span = button.querySelector('span');
    if (span) {
        span.textContent = isLoading ? loadingText : defaultText;
    } else {
        button.textContent = isLoading ? loadingText : defaultText;
    }
}

function setSelectValue(select, value) {
    if (!select) return;

    Array.from(select.options).forEach(function (option) {
        option.selected = option.textContent === value || option.value === value;
    });
}

function clearInputErrors(inputs) {
    inputs.forEach(function (input) {
        if (input) input.classList.remove('input-error');
    });
}

// ===============================
// LOAD PROFILE
// ===============================
function loadProfile() {
    showProfileMessage('Loading profile...');

    fetch(window.FlowlistRoutes.profileApi)
        .then(function (response) {
            if (response.status === 401) {
                window.location.href = window.FlowlistRoutes.login;
                return null;
            }

            if (!response.ok) {
                throw new Error('Profile request failed with status ' + response.status);
            }

            return response.json();
        })
        .then(function (data) {
            if (!data) return;

            if (!data.success) {
                throw new Error(data.message || 'Failed to load profile.');
            }

            renderProfile(data.user);
            hideProfileMessage();
        })
        .catch(function (error) {
            console.error('Profile load error:', error);
            showProfileMessage('Failed to load profile data.', 'error');
        });
}

function renderProfile(user) {
    if (!user) return;

    if (fullNameInput) fullNameInput.value = user.full_name || '';
    if (emailAddressInput) emailAddressInput.value = user.email || '';

    setAvatar(user);

    if (emailNotificationsInput) emailNotificationsInput.checked = Number(user.email_notifications) === 1;
    if (taskRemindersInput) taskRemindersInput.checked = Number(user.task_reminders) === 1;
    if (weeklyReportInput) weeklyReportInput.checked = Number(user.weekly_report) === 1;

    setSelectValue(themeSelect, user.theme || 'Light');
    setSelectValue(languageSelect, user.language || 'English');
    setSelectValue(dateFormatSelect, user.date_format || 'MM/DD/YYYY');

    if (window.FlowlistPreferences) {
        window.FlowlistPreferences.setDateFormat(user.date_format || 'MM/DD/YYYY');
    }

    if (window.FlowlistTheme) {
        window.FlowlistTheme.applyTheme(user.theme || 'Light');
    }
}

// ===============================
// AVATAR PREVIEW
// ===============================
if (fullNameInput && avatarCircle) {
    fullNameInput.addEventListener('input', function () {
        if (!avatarCircle.querySelector('img')) {
            avatarCircle.textContent = getInitials(fullNameInput.value);
        }
    });
}

if (avatarInput && avatarCircle) {
    avatarInput.addEventListener('change', function (event) {
        const file = event.target.files[0];

        if (!file) return;

        if (file.size > 2 * 1024 * 1024) {
            showProfileMessage('Image size must be less than 2MB.', 'error');
            avatarInput.value = '';
            selectedAvatarFile = null;
            return;
        }

        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

        if (!allowedTypes.includes(file.type)) {
            showProfileMessage('Only JPG, PNG, or GIF files are allowed.', 'error');
            avatarInput.value = '';
            selectedAvatarFile = null;
            return;
        }

        selectedAvatarFile = file;

        const reader = new FileReader();

        reader.onload = function (e) {
            avatarCircle.innerHTML = `<img src="${e.target.result}" alt="Avatar Preview">`;
        };

        reader.readAsDataURL(file);
    });
}

// ===============================
// SUBMIT PROFILE
// ===============================
if (profileForm) {
    profileForm.addEventListener('submit', function (event) {
        event.preventDefault();

        clearInputErrors([fullNameInput, emailAddressInput]);

        const fullName = fullNameInput ? fullNameInput.value.trim() : '';
        const email = emailAddressInput ? emailAddressInput.value.trim() : '';

        let isValid = true;

        if (fullName === '') {
            fullNameInput.classList.add('input-error');
            isValid = false;
        }

        if (email === '' || !email.includes('@')) {
            emailAddressInput.classList.add('input-error');
            isValid = false;
        }

        if (!isValid) {
            showProfileMessage('Please check your profile information.', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'update_profile');
        formData.append('full_name', fullName);
        formData.append('email', email);

        if (selectedAvatarFile) {
            formData.append('avatar', selectedAvatarFile);
        }

        const submitButton = profileForm.querySelector('.primary-btn');

        setButtonLoading(submitButton, true, 'Saving...', 'Save Changes');
        showProfileMessage('Saving profile...', '');

        fetch(window.FlowlistRoutes.profileApi, {
            method: 'POST',
            body: formData
        })
            .then(function (response) {
                if (response.status === 401) {
                    window.location.href = window.FlowlistRoutes.login;
                    return null;
                }

                return response.json().then(function (data) {
                    return {
                        ok: response.ok,
                        data
                    };
                });
            })
            .then(function (result) {
                if (!result) return;

                if (!result.ok || !result.data.success) {
                    throw new Error(result.data.message || 'Failed to update profile.');
                }

                selectedAvatarFile = null;

                if (result.data.user) {
                    renderProfile(result.data.user);
                }

                showProfileMessage('Profile updated successfully.', 'success');
            })
            .catch(function (error) {
                console.error('Profile update error:', error);
                showProfileMessage(error.message || 'Failed to update profile.', 'error');
            })
            .finally(function () {
                setButtonLoading(submitButton, false, 'Saving...', 'Save Changes');
            });
    });
}

// ===============================
// SUBMIT NOTIFICATIONS
// ===============================
if (notificationsForm) {
    notificationsForm.addEventListener('submit', function (event) {
        event.preventDefault();

        const formData = new FormData();
        formData.append('action', 'update_notifications');
        formData.append('email_notifications', emailNotificationsInput && emailNotificationsInput.checked ? '1' : '0');
        formData.append('task_reminders', taskRemindersInput && taskRemindersInput.checked ? '1' : '0');
        formData.append('weekly_report', weeklyReportInput && weeklyReportInput.checked ? '1' : '0');

        const submitButton = notificationsForm.querySelector('.primary-btn');

        setButtonLoading(submitButton, true, 'Saving...', 'Save Changes');

        fetch(window.FlowlistRoutes.profileApi, {
            method: 'POST',
            body: formData
        })
            .then(function (response) {
                if (response.status === 401) {
                    window.location.href = window.FlowlistRoutes.login;
                    return null;
                }

                return response.json().then(function (data) {
                    return {
                        ok: response.ok,
                        data
                    };
                });
            })
            .then(function (result) {
                if (!result) return;

                if (!result.ok || !result.data.success) {
                    throw new Error(result.data.message || 'Failed to update notifications.');
                }

                showProfileMessage('Notification settings updated successfully.', 'success');
            })
            .catch(function (error) {
                console.error('Notification update error:', error);
                showProfileMessage(error.message || 'Failed to update notifications.', 'error');
            })
            .finally(function () {
                setButtonLoading(submitButton, false, 'Saving...', 'Save Changes');
            });
    });
}

// ===============================
// SUBMIT PREFERENCES
// ===============================
if (preferencesForm) {
    preferencesForm.addEventListener('submit', function (event) {
        event.preventDefault();

        const formData = new FormData();
        formData.append('action', 'update_preferences');
        formData.append('theme', themeSelect ? themeSelect.value : 'Light');
        formData.append('language', languageSelect ? languageSelect.value : 'English');
        formData.append('date_format', dateFormatSelect ? dateFormatSelect.value : 'MM/DD/YYYY');

        const submitButton = preferencesForm.querySelector('.primary-btn');

        setButtonLoading(submitButton, true, 'Saving...', 'Save Changes');

        fetch(window.FlowlistRoutes.profileApi, {
            method: 'POST',
            body: formData
        })
            .then(function (response) {
                if (response.status === 401) {
                    window.location.href = window.FlowlistRoutes.login;
                    return null;
                }

                return response.json().then(function (data) {
                    return {
                        ok: response.ok,
                        data
                    };
                });
            })
            .then(function (result) {
                if (!result) return;

                if (!result.ok || !result.data.success) {
                    throw new Error(result.data.message || 'Failed to update preferences.');
                }

                showProfileMessage('Preferences updated successfully.', 'success');

                const selectedTheme = themeSelect ? themeSelect.value : 'Light';
                const selectedDateFormat = dateFormatSelect ? dateFormatSelect.value : 'MM/DD/YYYY';

                if (window.FlowlistTheme) {
                    window.FlowlistTheme.applyTheme(selectedTheme);
                }

                if (window.FlowlistPreferences) {
                    window.FlowlistPreferences.setDateFormat(selectedDateFormat);
                }
            })
            .catch(function (error) {
                console.error('Preferences update error:', error);
                showProfileMessage(error.message || 'Failed to update preferences.', 'error');
            })
            .finally(function () {
                setButtonLoading(submitButton, false, 'Saving...', 'Save Changes');
            });
    });
}

// ===============================
// SUBMIT SECURITY
// ===============================
if (securityForm) {
    securityForm.addEventListener('submit', function (event) {
        event.preventDefault();

        clearInputErrors([currentPasswordInput, newPasswordInput, confirmPasswordInput]);

        const currentPassword = currentPasswordInput ? currentPasswordInput.value : '';
        const newPassword = newPasswordInput ? newPasswordInput.value : '';
        const confirmPassword = confirmPasswordInput ? confirmPasswordInput.value : '';

        let isValid = true;

        if (currentPassword === '') {
            currentPasswordInput.classList.add('input-error');
            isValid = false;
        }

        if (newPassword.length < 4) {
            newPasswordInput.classList.add('input-error');
            isValid = false;
        }

        if (confirmPassword !== newPassword) {
            confirmPasswordInput.classList.add('input-error');
            isValid = false;
        }

        if (!isValid) {
            showProfileMessage('Please check your password fields.', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'update_password');
        formData.append('current_password', currentPassword);
        formData.append('new_password', newPassword);
        formData.append('confirm_password', confirmPassword);

        const submitButton = securityForm.querySelector('.primary-btn');

        setButtonLoading(submitButton, true, 'Updating...', 'Update Password');

        fetch(window.FlowlistRoutes.profileApi, {
            method: 'POST',
            body: formData
        })
            .then(function (response) {
                if (response.status === 401) {
                    window.location.href = window.FlowlistRoutes.login;
                    return null;
                }

                return response.json().then(function (data) {
                    return {
                        ok: response.ok,
                        data
                    };
                });
            })
            .then(function (result) {
                if (!result) return;

                if (!result.ok || !result.data.success) {
                    throw new Error(result.data.message || 'Failed to update password.');
                }

                securityForm.reset();
                showProfileMessage('Password updated successfully.', 'success');
            })
            .catch(function (error) {
                console.error('Password update error:', error);
                showProfileMessage(error.message || 'Failed to update password.', 'error');
            })
            .finally(function () {
                setButtonLoading(submitButton, false, 'Updating...', 'Update Password');
        });
    });
}

// ===============================
// TOGGLE SHOW / HIDE PASSWORD
// ===============================
const togglePasswordButtons = document.querySelectorAll('.toggle-password');

togglePasswordButtons.forEach(function (button) {
    button.addEventListener('click', function () {
        const targetId = button.getAttribute('data-target');
        const passwordInput = document.getElementById(targetId);
        const icon = button.querySelector('i');

        if (!passwordInput) return;

        const isPassword = passwordInput.type === 'password';

        passwordInput.type = isPassword ? 'text' : 'password';

        if (icon) {
            icon.classList.toggle('fa-eye', !isPassword);
            icon.classList.toggle('fa-eye-slash', isPassword);
        }
    });
});

// ===============================
// CLEAR ERROR ON INPUT
// ===============================
[
    fullNameInput,
    emailAddressInput,
    currentPasswordInput,
    newPasswordInput,
    confirmPasswordInput
].forEach(function (input) {
    if (!input) return;

    input.addEventListener('input', function () {
        input.classList.remove('input-error');
    });
});

// ===============================
// INIT
// ===============================
document.addEventListener('DOMContentLoaded', function () {
    loadProfile();
});
