@php
    /*
    |--------------------------------------------------------------------------
    | Flowlist Footer Data
    |--------------------------------------------------------------------------
    | This section prepares:
    | 1. The currently authenticated user.
    | 2. The user's account role.
    | 3. The correct dashboard route.
    | 4. The application version.
    | 5. The production URL and domain name.
    |--------------------------------------------------------------------------
    */

    $footerUser = auth()->user();

    $footerIsAdmin = $footerUser?->isAdmin() ?? false;

    /*
     * Administrators return to the Admin Dashboard.
     * Regular users return to the User Dashboard.
     */
    $footerHomeRoute = $footerIsAdmin
        ? route('admin.dashboard')
        : route('dashboard');

    /*
     * Application version from config/app.php.
     */
    $footerAppVersion = config('app.version', '1.0.0');

    /*
     * Website URL from APP_URL.
     */
    $footerWebsiteUrl = rtrim(
        config('app.url', 'http://127.0.0.1:8000'),
        '/'
    );

    /*
     * Display only the domain name without https://.
     */
    $footerDomainName = parse_url(
        $footerWebsiteUrl,
        PHP_URL_HOST
    ) ?: $footerWebsiteUrl;
@endphp

<footer
    class="app-footer"
    aria-label="Flowlist application footer"
>
    <div class="app-footer__card">

        {{-- Footer header --}}
        <div class="app-footer__header">

            {{-- Flowlist brand --}}
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

            {{-- Website status --}}
            <div class="app-footer__domain">
                <span class="app-footer__domain-label">
                    <i
                        class="fa-solid fa-globe"
                        aria-hidden="true"
                    ></i>

                    Live Website
                </span>

                <a
                    href="{{ $footerWebsiteUrl }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="app-footer__domain-status app-footer__domain-link"
                    title="Open the Flowlist production website"
                >
                    Online

                    <i
                        class="fa-solid fa-arrow-up-right-from-square"
                        aria-hidden="true"
                    ></i>
                </a>
            </div>
        </div>

        {{-- Main footer content --}}
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

                    {{-- Course --}}
                    <div class="app-footer__info-row">
                        <dt>Course</dt>

                        <dd>
                            Web Programming
                        </dd>
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

                        <dd>
                            KH002
                        </dd>
                    </div>

                    {{-- Academic year --}}
                    <div class="app-footer__info-row">
                        <dt>Academic Year</dt>

                        <dd>
                            2025/2026
                        </dd>
                    </div>

                    {{-- Domain name --}}
                    <div class="app-footer__info-row">
                        <dt>Domain Name</dt>

                        <dd>
                            <a
                                href="{{ $footerWebsiteUrl }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="app-footer__website-link"
                                title="Open {{ $footerDomainName }}"
                            >
                                <span>
                                    {{ $footerDomainName }}
                                </span>

                                <i
                                    class="fa-solid fa-arrow-up-right-from-square"
                                    aria-hidden="true"
                                ></i>
                            </a>
                        </dd>
                    </div>

                    {{-- Website link --}}
                    <div class="app-footer__info-row">
                        <dt>Website Link</dt>

                        <dd>
                            <a
                                href="{{ $footerWebsiteUrl }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="app-footer__website-link"
                                title="Visit the Flowlist production website"
                            >
                                <span>
                                    Visit Flowlist Website
                                </span>

                                <i
                                    class="fa-solid fa-arrow-up-right-from-square"
                                    aria-hidden="true"
                                ></i>
                            </a>
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
                            <strong>
                                MARCELL
                            </strong>

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
                            <strong>
                                REIFAN
                            </strong>

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
                            <strong>
                                DAVID
                            </strong>

                            <small>
                                Student ID: 20240801026
                            </small>
                        </span>
                    </li>
                </ol>
            </section>
        </div>

        {{-- Footer bottom --}}
        <div class="app-footer__bottom">

            {{-- Copyright --}}
            <p class="app-footer__copyright">
                &copy; {{ now()->year }} Flowlist.
                All rights reserved.
            </p>

            {{-- Application information --}}
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

                {{-- Account role --}}
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