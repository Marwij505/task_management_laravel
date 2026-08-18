/*
|--------------------------------------------------------------------------
| FLOWLIST MK2 - QUICK ADD TASK
|--------------------------------------------------------------------------
|
| Fungsi:
|
| 1. Membuka modal Quick Add.
| 2. Menutup modal.
| 3. Floating Add Button.
| 4. Shortcut Alt + Q.
| 5. Client-side validation.
| 6. Mengirim data ke Laravel.
| 7. Loading state.
| 8. Error handling.
| 9. Success notification.
| 10. Memberi tahu halaman aktif agar refresh data.
|
|--------------------------------------------------------------------------
*/

(function () {
    'use strict';


    // =========================================================
    // ELEMENTS
    // =========================================================

    const modal =
        document.getElementById(
            'quickAddModal'
        );

    const form =
        document.getElementById(
            'quickAddTaskForm'
        );

    const titleInput =
        document.getElementById(
            'quickAddTitle'
        );

    const deadlineInput =
        document.getElementById(
            'quickAddDeadline'
        );

    const priorityInput =
        document.getElementById(
            'quickAddPriority'
        );

    const categoryInput =
        document.getElementById(
            'quickAddCategory'
        );

    const sourceInput =
        document.getElementById(
            'quickAddSource'
        );

    const errorBox =
        document.getElementById(
            'quickAddError'
        );

    const submitButton =
        document.getElementById(
            'quickAddSubmitBtn'
        );

    const toast =
        document.getElementById(
            'quickAddToast'
        );


    /*
     * Quick Add hanya dimuat pada halaman tertentu.
     *
     * Kalau modal tidak tersedia, hentikan script.
     */
    if (!modal || !form) {
        return;
    }


    // =========================================================
    // LOCAL TODAY
    // =========================================================

    /**
     * Mendapatkan tanggal lokal browser dalam format:
     *
     * YYYY-MM-DD
     *
     * Kita tidak memakai toISOString() karena UTC dapat
     * menghasilkan tanggal berbeda pada timezone tertentu.
     */
    function localToday() {
        const now = new Date();

        const year =
            now.getFullYear();

        const month =
            String(
                now.getMonth() + 1
            ).padStart(2, '0');

        const day =
            String(
                now.getDate()
            ).padStart(2, '0');

        return `${year}-${month}-${day}`;
    }


    // =========================================================
    // ERROR MESSAGE
    // =========================================================

    function showError(message) {
        if (!errorBox) {
            return;
        }

        errorBox.textContent =
            message;

        errorBox.hidden =
            false;
    }


    function clearError() {
        if (!errorBox) {
            return;
        }

        errorBox.textContent = '';

        errorBox.hidden =
            true;
    }


    // =========================================================
    // SUCCESS TOAST
    // =========================================================

    function showToast(message) {
        if (!toast) {
            return;
        }

        toast.textContent =
            message;

        toast.classList.add(
            'is-visible'
        );

        window.setTimeout(
            function () {
                toast.classList.remove(
                    'is-visible'
                );
            },
            2600
        );
    }


    // =========================================================
    // OPEN MODAL
    // =========================================================

    function openModal() {
        clearError();

        /*
         * Deadline otomatis diisi hari ini.
         *
         * User tetap bebas menggantinya.
         */
        if (
            deadlineInput &&
            !deadlineInput.value
        ) {
            deadlineInput.value =
                localToday();
        }

        modal.classList.add(
            'is-open'
        );

        modal.setAttribute(
            'aria-hidden',
            'false'
        );

        document.body.classList.add(
            'quick-add-modal-open'
        );

        /*
         * Setelah animasi modal,
         * fokus langsung ke title.
         */
        window.setTimeout(
            function () {
                titleInput?.focus();
            },
            60
        );
    }


    // =========================================================
    // CLOSE MODAL
    // =========================================================

    function closeModal(
        force = false
    ) {
        /*
         * Jangan tutup modal ketika request sedang dikirim.
         *
         * "force" dipakai ketika request berhasil.
         */
        if (
            !force &&
            form.classList.contains(
                'ui-busy'
            )
        ) {
            return;
        }

        modal.classList.remove(
            'is-open'
        );

        modal.setAttribute(
            'aria-hidden',
            'true'
        );

        document.body.classList.remove(
            'quick-add-modal-open'
        );

        clearError();
    }


    // =========================================================
    // BUTTON OPEN EVENTS
    // =========================================================

    document
        .querySelectorAll(
            '[data-quick-add-open]'
        )
        .forEach(function (button) {

            button.addEventListener(
                'click',
                openModal
            );
        });


    // =========================================================
    // BUTTON CLOSE EVENTS
    // =========================================================

    document
        .querySelectorAll(
            '[data-quick-add-close]'
        )
        .forEach(function (button) {

            button.addEventListener(
                'click',
                function () {
                    closeModal();
                }
            );
        });


    // =========================================================
    // CLICK BACKDROP
    // =========================================================

    modal.addEventListener(
        'click',
        function (event) {

            /*
             * Modal hanya ditutup jika user
             * benar-benar mengklik backdrop.
             *
             * Klik dialog tidak menutup modal.
             */
            if (
                event.target === modal
            ) {
                closeModal();
            }
        }
    );


    // =========================================================
    // KEYBOARD SHORTCUT
    // =========================================================

    document.addEventListener(
        'keydown',
        function (event) {

            /*
             * Escape menutup modal.
             */
            if (
                event.key === 'Escape' &&
                modal.classList.contains(
                    'is-open'
                )
            ) {
                event.preventDefault();

                closeModal();

                return;
            }


            /*
             * FLOWLIST QUICK ADD SHORTCUT
             *
             * Alt + Q
             *
             * Ctrl + K sengaja tidak dipakai
             * karena roadmap Flowlist MK4 akan
             * menggunakannya untuk Command Palette.
             */
            if (
                event.altKey &&
                !event.ctrlKey &&
                !event.metaKey &&
                String(
                    event.key
                ).toLowerCase() === 'q'
            ) {
                event.preventDefault();

                openModal();
            }
        }
    );


    // =========================================================
    // SUBMIT QUICK ADD
    // =========================================================

    form.addEventListener(
        'submit',
        async function (event) {

            event.preventDefault();


            /*
             * Cegah double submit.
             */
            if (
                form.classList.contains(
                    'ui-busy'
                )
            ) {
                return;
            }


            clearError();


            // =================================================
            // BUILD PAYLOAD
            // =================================================

            const payload = {

                title:
                    titleInput?.value.trim()
                    || '',

                deadline:
                    deadlineInput?.value
                    || '',

                priority:
                    priorityInput?.value
                    || 'medium',

                /*
                * Category dan source bersifat nullable.
                *
                * Jika kosong, kirim NULL dan bukan string kosong.
                * Ini membuat payload konsisten dengan validasi Laravel.
                */
                category:
                    categoryInput?.value.trim()
                    || null,

                source:
                    sourceInput?.value
                    || null,

            };


            // =================================================
            // CLIENT VALIDATION
            // =================================================

            if (!payload.title) {

                showError(
                    'Task title is required.'
                );

                titleInput?.focus();

                return;
            }


            if (!payload.deadline) {

                showError(
                    'Deadline is required.'
                );

                deadlineInput?.focus();

                return;
            }


            // =================================================
            // LOADING STATE
            // =================================================

            form.classList.add(
                'ui-busy'
            );


            if (submitButton) {

                submitButton.disabled =
                    true;

                /*
                 * Simpan HTML tombol sebelum
                 * diganti loading state.
                 */
                submitButton.dataset.originalText =
                    submitButton.innerHTML;

                submitButton.innerHTML =
                    '<span>Adding...</span>';
            }


            // =================================================
            // REQUEST
            // =================================================

            try {

                const response =
                    await fetch(
                        window
                            .FlowlistRoutes
                            .taskQuickAddApi,
                        {
                            method: 'POST',

                            /*
                            * =================================================
                            * REQUEST HEADERS
                            * =================================================
                            *
                            * Accept:
                            * Memastikan Laravel selalu mengetahui bahwa
                            * client mengharapkan response JSON.
                            *
                            * Content-Type:
                            * Payload dikirim dalam format JSON.
                            *
                            * X-CSRF-TOKEN:
                            * Melindungi request POST menggunakan token
                            * CSRF milik session Flowlist.
                            */
                            headers: {
                                'Accept':
                                    'application/json',

                                'Content-Type':
                                    'application/json',

                                'X-CSRF-TOKEN':
                                    document
                                        .querySelector(
                                            'meta[name="csrf-token"]'
                                        )
                                        ?.getAttribute(
                                            'content'
                                        )
                                    || '',
                            },

                            /*
                            * Karena endpoint Quick Add menggunakan
                            * autentikasi session Laravel, browser harus
                            * mengirim cookie session pada request.
                            */
                            credentials:
                                'same-origin',

                            /*
                            * Ubah object payload menjadi JSON.
                            */
                            body:
                                JSON.stringify(
                                    payload
                                ),
                        }
                    );


                /*
                 * Coba baca JSON.
                 *
                 * Kalau server mengembalikan response
                 * yang bukan JSON, gunakan object kosong.
                 */
                const data =
                    await response
                        .json()
                        .catch(
                            function () {
                                return {};
                            }
                        );


                // =============================================
                // SERVER ERROR / VALIDATION ERROR
                // =============================================

                if (
                    !response.ok ||
                    !data.success
                ) {

                    /*
                     * Laravel Form Request mengembalikan
                     * validation errors dalam object:
                     *
                     * errors: {
                     *     title: [...]
                     * }
                     */
                    const validationMessage =
                        data.errors
                            ? Object
                                .values(
                                    data.errors
                                )
                                .flat()[0]
                            : null;


                    throw new Error(
                        validationMessage ||
                        data.message ||
                        'Failed to add task.'
                    );
                }


                // =============================================
                // SUCCESS
                // =============================================

                /*
                 * Reset input.
                 */
                form.reset();


                /*
                 * Kembalikan priority default
                 * ke Medium.
                 */
                if (priorityInput) {

                    priorityInput.value =
                        'medium';
                }


                /*
                 * Kembalikan deadline ke
                 * hari ini.
                 */
                if (deadlineInput) {

                    deadlineInput.value =
                        localToday();
                }


                /*
                 * Tutup modal secara paksa karena
                 * request berhasil.
                 */
                closeModal(true);


                /*
                 * Success notification.
                 */
                showToast(
                    'Task added successfully.'
                );


                /*
                 * =================================================
                 * CUSTOM EVENT
                 * =================================================
                 *
                 * Dashboard, Task List, dan Calendar
                 * akan mendengar event ini.
                 *
                 * Setelah Quick Add berhasil,
                 * halaman memperbarui data tanpa
                 * harus refresh browser manual.
                 */
                window.dispatchEvent(
                    new CustomEvent(
                        'flowlist:quick-task-created',
                        {
                            detail: {

                                task:
                                    data.task
                                    || null,

                                taskId:
                                    data.task_id
                                    || null,

                                source:
                                    payload.source,
                            },
                        }
                    )
                );

            } catch (error) {

                // =============================================
                // ERROR
                // =============================================

                showError(
                    error.message ||
                    'Failed to add task.'
                );

            } finally {

                // =============================================
                // STOP LOADING
                // =============================================

                form.classList.remove(
                    'ui-busy'
                );


                if (submitButton) {

                    submitButton.disabled =
                        false;

                    submitButton.innerHTML =
                        submitButton
                            .dataset
                            .originalText
                        ||
                        '<span>Add Task</span>';
                }
            }
        }
    );


    // =========================================================
    // GLOBAL API
    // =========================================================

    /*
     * Dipersiapkan apabila fitur MK2 lainnya
     * ingin membuka Quick Add melalui JavaScript.
     */
    window.FlowlistQuickAdd = {
        open: openModal,
        close: closeModal,
    };

})();