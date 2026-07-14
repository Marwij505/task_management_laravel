@php
    /*
    |--------------------------------------------------------------------------
    | Flowlist Footer Data
    |--------------------------------------------------------------------------
    | This section prepares:
    | 1. The currently authenticated user.
    | 2. The user's account role.
    | 3. The correct dashboard route.
    | 4. The current application version.
    |--------------------------------------------------------------------------
    */

    $footerUser = auth()->user();

    $footerIsAdmin = $footerUser?->isAdmin() ?? false;

    /*
     * Administrators are directed to the Admin Dashboard.
     * Regular users are directed to the User Dashboard.
     */
    $footerHomeRoute = $footerIsAdmin
        ? route('admin.dashboard')
        : route('dashboard');

    /*
     * The application version is retrieved from config/app.php
     * or the APP_VERSION value in the .env file.
     */
    $footerAppVersion = config('app.version', '1.0.0');
@endphp

<footer
    class="app-footer"
    aria-label="Flowlist application footer"
>
    <div class="app-footer__card">

        {{-- Footer header --}}
        <div class="app-footer__header">

            {{-- Flowlist brand and dashboard link --}}
            <a
                href="{{ $footerHomeRoute }}"
                class="app-footer__brand"
                aria-label="Return to the Flowlist dashboard"
            >
                <span
                    class="app-footer__logo"
                    aria-hidden="true"
                >
                    <i class="fa-solid fa-list-check"></i>
                </span>

                <span class="app-footer__brand-text">
                    <strong>Flowlist</strong>

                    <small>
                        Personal Task Management System
                    </small>
                </span>
            </a>

            {{-- Temporary domain status --}}
            <div class="app-footer__domain">
                <span class="app-footer__domain-label">
                    <i
                        class="fa-solid fa-globe"
                        aria-hidden="true"
                    ></i>

                    Domain
                </span>

                <span class="app-footer__domain-status">
                    Coming Soon
                </span>
            </div>
        </div>

        {{-- Main footer information --}}
        <div class="app-footer__content">

            {{-- Academic information --}}
            <section
                class="app-footer__section"
                aria-labelledby="footer-academic-title"
            >
                <h3 id="footer-academic-title">
                    <i
                        class="fa-solid fa-graduation-cap"
                        aria-hidden="true"
                    ></i>

                    Academic Information
                </h3>

                <dl class="app-footer__info-list">

                    {{-- Course name --}}
                    <div class="app-footer__info-row">
                        <dt>Course</dt>

                        <dd>Web Programming</dd>
                    </div>

                    {{-- Course lecturer --}}
                    <div class="app-footer__info-row">
                        <dt>Course Lecturer</dt>

                        <dd>
                            DEWI SETIOWATI, A.Md., S.Pd., M.Tr.Kom.
                        </dd>
                    </div>

                    {{-- Class --}}
                    <div class="app-footer__info-row">
                        <dt>Class</dt>

                        <dd>KH002</dd>
                    </div>

                    {{-- Academic year --}}
                    <div class="app-footer__info-row">
                        <dt>Academic Year</dt>

                        <dd>2025/2026</dd>
                    </div>

                    {{-- Domain name --}}
                    <div class="app-footer__info-row">
                        <dt>Domain Name</dt>

                        <dd>
                            <span class="app-footer__coming-soon">
                                Coming Soon
                            </span>
                        </dd>
                    </div>

                    {{-- Website link --}}
                    <div class="app-footer__info-row">
                        <dt>Website Link</dt>

                        <dd>
                            <span class="app-footer__coming-soon">
                                Coming Soon
                            </span>
                        </dd>
                    </div>
                </dl>
            </section>

            {{-- Group members --}}
            <section
                class="app-footer__section"
                aria-labelledby="footer-team-title"
            >
                <h3 id="footer-team-title">
                    <i
                        class="fa-solid fa-users"
                        aria-hidden="true"
                    ></i>

                    Group Members
                </h3>

                <ol class="app-footer__members">

                    {{-- First member --}}
                    <li class="app-footer__member">
                        <span class="app-footer__member-number">
                            01
                        </span>

                        <span class="app-footer__member-profile">
                            <strong>MARCELL</strong>

                            <small>
                                Student ID: 20240801050
                            </small>
                        </span>
                    </li>

                    {{-- Second member --}}
                    <li class="app-footer__member">
                        <span class="app-footer__member-number">
                            02
                        </span>

                        <span class="app-footer__member-profile">
                            <strong>REIFAN</strong>

                            <small>
                                Student ID: 20240801002
                            </small>
                        </span>
                    </li>

                    {{-- Third member --}}
                    <li class="app-footer__member">
                        <span class="app-footer__member-number">
                            03
                        </span>

                        <span class="app-footer__member-profile">
                            <strong>DAVID</strong>

                            <small>
                                Student ID: 20240801026
                            </small>
                        </span>
                    </li>
                </ol>
            </section>
        </div>

        {{-- Footer bottom section --}}
        <div class="app-footer__bottom">

            {{-- Automatic copyright year --}}
            <p class="app-footer__copyright">
                &copy; {{ now()->year }} Flowlist.
                All rights reserved.
            </p>

            {{-- Application metadata --}}
            <div class="app-footer__meta-list">

                {{-- Session status --}}
                <span
                    class="app-footer__meta app-footer__session"
                    title="Your login session is currently active"
                >
                    <span
                        class="app-footer__status-dot"
                        aria-hidden="true"
                    ></span>

                    Session Active
                </span>

                {{-- Current account role --}}
                <span
                    class="app-footer__meta"
                    title="Current account role"
                >
                    <i
                        class="fa-solid {{ $footerIsAdmin ? 'fa-shield-halved' : 'fa-user' }}"
                        aria-hidden="true"
                    ></i>

                    {{ $footerIsAdmin ? 'Admin Account' : 'User Account' }}
                </span>

                {{-- Application version --}}
                <span
                    class="app-footer__meta"
                    title="Current Flowlist application version"
                >
                    <i
                        class="fa-solid fa-code-branch"
                        aria-hidden="true"
                    ></i>

                    Version {{ $footerAppVersion }}
                </span>
            </div>
        </div>
    </div>
</footer>