<!DOCTYPE html>
<html lang="en">
    
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Flowlist - Profile</title>
    <link rel="stylesheet" href="{{ asset('assets/css/profile.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"rel="stylesheet" />
    <link rel="stylesheet"href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>

<body>
  
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-top">
                <div class="brand">
                    <div class="brand-logo">
                        <i class="fa-solid fa-table-cells-large"></i>
                    </div>
                    <h1>Flowlist</h1>
                </div>

                <nav class="sidebar-menu">
                    <a href="{{ route('dashboard') }}" class="menu-item">
                        <i class="fa-solid fa-table-columns"></i>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('tasks.index') }}" class="menu-item">
                        <i class="fa-solid fa-list-check"></i>
                        <span>Task List</span>
                    </a>

                    <a href="{{ route('tasks.create') }}" class="menu-item">
                        <i class="fa-solid fa-circle-plus"></i>
                        <span>Create Task</span>
                    </a>

                    <a href="{{ route('calendar') }}" class="menu-item">
                        <i class="fa-regular fa-calendar"></i>
                        <span>Calendar</span>
                    </a>

                    <a href="{{ route('statistics') }}" class="menu-item">
                        <i class="fa-solid fa-chart-column"></i>
                        <span>Statistics</span>
                    </a>

                    <a href="{{ route('profile') }}" class="menu-item active">
                        <i class="fa-regular fa-user"></i>
                        <span>Profile</span>
                    </a>
                </nav>
            </div>

            <div class="sidebar-bottom">
                <form id="logoutForm" method="POST" action="{{ route('logout') }}" style="display:none;">
                    @csrf
                </form>
                <a href="{{ route('logout') }}" class="logout-btn" onclick="event.preventDefault(); document.getElementById('logoutForm').submit();">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Header -->
            <header class="topbar">
                <div class="topbar-left">
                    <h2>User Profile &amp; Settings</h2>
                    <p>Manage account, preferences, theme</p>
                </div>
            </header>

            <!-- Tabs -->
            <section class="profile-tabs">
                <button class="tab-btn active" data-tab="profilePanel">Profile</button>
                <button class="tab-btn" data-tab="notificationsPanel">Notifications</button>
                <button class="tab-btn" data-tab="preferencesPanel">Preferences</button>
                <button class="tab-btn" data-tab="securityPanel">Security</button>
            </section>

            <div id="profileMessage" class="profile-message">
                Loading profile...
            </div>

            <!-- Profile Panel -->
            <section class="profile-panel active" id="profilePanel">
                <div class="panel-card">
                    <div class="panel-header">
                        <h3><i class="fa-regular fa-user"></i> Profile Information</h3>
                    </div>

                    <div class="avatar-row">
                        <div class="avatar-circle" id="avatarCircle">SG</div>

                        <div class="avatar-actions">
                            <label for="avatarInput" class="secondary-btn">Change Avatar</label>
                            <input type="file" id="avatarInput" name="avatar" accept=".jpg,.jpeg,.png,.gif" hidden />
                            <p>JPG, PNG or GIF. Max size 2MB</p>
                        </div>
                    </div>

                    <form id="profileForm" class="form-layout">
                        <div class="input-group">
                            <label for="fullName">Full Name</label>
                            <input type="text" id="fullName" placeholder="Enter your full name" />
                        </div>

                        <div class="input-group">
                            <label for="emailAddress">Email Address</label>
                            <input type="email" id="emailAddress" placeholder="Enter your email address" />
                        </div>

                        <button type="submit" class="primary-btn">
                            <i class="fa-regular fa-floppy-disk"></i>
                            <span>Save Changes</span>
                        </button>
                    </form>
                </div>
            </section>

            <!-- Notifications Panel -->
            <section class="profile-panel" id="notificationsPanel">
                <div class="panel-card">
                    <div class="panel-header">
                        <h3><i class="fa-regular fa-bell"></i> Notification Settings</h3>
                    </div>

                    <form id="notificationsForm" class="settings-list">
                        <div class="setting-item">
                            <div class="setting-text">
                                <h4>Email Notifications</h4>
                                <p>Receive email updates about your tasks</p>
                            </div>
                            <label class="switch">
                                <input type="checkbox" id="emailNotifications" checked />
                                <span class="slider"></span>
                            </label>
                        </div>

                        <div class="setting-item">
                            <div class="setting-text">
                                <h4>Task Reminders</h4>
                                <p>Get reminded about upcoming deadlines</p>
                            </div>
                            <label class="switch">
                                <input type="checkbox" id="taskReminders" checked />
                                <span class="slider"></span>
                            </label>
                        </div>

                        <div class="setting-item">
                            <div class="setting-text">
                                <h4>Weekly Report</h4>
                                <p>Receive a summary of your weekly progress</p>
                            </div>
                            <label class="switch">
                                <input type="checkbox" id="weeklyReport" />
                                <span class="slider"></span>
                            </label>
                        </div>

                        <button type="submit" class="primary-btn">
                            <i class="fa-regular fa-floppy-disk"></i>
                            <span>Save Changes</span>
                        </button>
                    </form>
                </div>
            </section>

            <!-- Preferences Panel -->
            <section class="profile-panel" id="preferencesPanel">
                <div class="panel-card">
                    <div class="panel-header">
                        <h3><i class="fa-solid fa-palette"></i> Preferences</h3>
                    </div>

                    <form id="preferencesForm" class="form-layout">
                        <div class="input-group">
                            <label for="themeSelect">Theme</label>
                            <select id="themeSelect">
                                <option selected>Light</option>
                                <option>Dark</option>
                                <option>System</option>
                            </select>
                        </div>

                        <div class="input-group">
                            <label for="languageSelect">Language</label>
                            <select id="languageSelect">
                                <option selected>English</option>
                                <option>Indonesian</option>
                            </select>
                        </div>

                        <div class="input-group">
                            <label for="dateFormatSelect">Date Format</label>
                            <select id="dateFormatSelect">
                                <option selected>MM/DD/YYYY</option>
                                <option>DD/MM/YYYY</option>
                                <option>YYYY-MM-DD</option>
                            </select>
                        </div>

                        <button type="submit" class="primary-btn">
                            <i class="fa-regular fa-floppy-disk"></i>
                            <span>Save Changes</span>
                        </button>
                    </form>
                </div>
            </section>

            <!-- Security Panel -->
            <section class="profile-panel" id="securityPanel">
                <div class="panel-card">
                    <div class="panel-header">
                        <h3><i class="fa-solid fa-lock"></i> Security Settings</h3>
                    </div>

                    <form id="securityForm" class="form-layout">

                        <div class="input-group">
                            <label for="currentPassword">Current Password</label>
                            <div class="password-wrapper">
                                <input type="password" id="currentPassword" placeholder="Enter current password" />
                                <button type="button" class="toggle-password" data-target="currentPassword">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="input-group">
                            <label for="newPassword">New Password</label>
                            <div class="password-wrapper">
                                <input type="password" id="newPassword" placeholder="Enter new password" />
                                <button type="button" class="toggle-password" data-target="newPassword">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="input-group">
                            <label for="confirmPassword">Confirm New Password</label>
                            <div class="password-wrapper">
                                <input type="password" id="confirmPassword" placeholder="Confirm new password" />
                                <button type="button" class="toggle-password" data-target="confirmPassword">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="primary-btn">
                            <i class="fa-regular fa-floppy-disk"></i>
                            <span>Update Password</span>
                        </button>

                    </form>

                </div>

            </section>

        </main>
        
    </div>

    @include('partials.flowlist-routes')
  <script src="{{ asset('assets/js/theme.js') }}"></script>
    <script src="{{ asset('assets/js/preferences.js') }}"></script>
    <script src="{{ asset('assets/js/profile.js') }}"></script>

</body>
</html>