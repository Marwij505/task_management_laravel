/*
|--------------------------------------------------------------------------
| FLOWLIST MK2 - SUBTASK & CHECKLIST
|--------------------------------------------------------------------------
|
| Fitur:
|
| - Load checklist
| - Add
| - Edit inline
| - Delete
| - Complete
| - Reopen
| - Move Up
| - Move Down
| - Progress otomatis
| - Statistik otomatis
| - Loading
| - Empty state
| - Error handling
|
|--------------------------------------------------------------------------
*/

(function () {
    'use strict';


    // =========================================================
    // ELEMENTS
    // =========================================================

    const module =
        document.getElementById(
            'subtaskModule'
        );

    /*
     * Script hanya berjalan di halaman Task Detail.
     */
    if (!module) {
        return;
    }


    const addForm =
        document.getElementById(
            'subtaskAddForm'
        );

    const titleInput =
        document.getElementById(
            'subtaskTitleInput'
        );

    const addButton =
        document.getElementById(
            'subtaskAddButton'
        );

    const list =
        document.getElementById(
            'subtaskList'
        );

    const errorBox =
        document.getElementById(
            'subtaskError'
        );

    const totalEl =
        document.getElementById(
            'subtaskTotal'
        );

    const completedEl =
        document.getElementById(
            'subtaskCompleted'
        );

    const pendingEl =
        document.getElementById(
            'subtaskPending'
        );

    const progressFill =
        document.getElementById(
            'subtaskProgressFill'
        );

    const progressText =
        document.getElementById(
            'subtaskProgressText'
        );

    const percentageBadge =
        document.getElementById(
            'subtaskPercentageBadge'
        );

    const toast =
        document.getElementById(
            'subtaskToast'
        );


    // =========================================================
    // STATE
    // =========================================================

    let subtasks = [];

    /*
     * Ambil task ID dari:
     *
     * /task-detail?id=123
     */
    const query =
        new URLSearchParams(
            window.location.search
        );

    const taskId =
        Number(
            query.get('id')
        );


    /*
     * Jika URL tidak mempunyai ID task valid,
     * jangan menjalankan request.
     */
    if (
        !Number.isInteger(taskId) ||
        taskId <= 0
    ) {
        renderFatalError(
            'Invalid task ID.'
        );

        return;
    }


    // =========================================================
    // HELPERS
    // =========================================================

    function csrfToken() {
        return document
            .querySelector(
                'meta[name="csrf-token"]'
            )
            ?.getAttribute(
                'content'
            )
            || '';
    }


    function escapeHTML(value) {
        return String(
            value ?? ''
        )
            .replace(
                /&/g,
                '&amp;'
            )
            .replace(
                /</g,
                '&lt;'
            )
            .replace(
                />/g,
                '&gt;'
            )
            .replace(
                /"/g,
                '&quot;'
            )
            .replace(
                /'/g,
                '&#039;'
            );
    }


    function clearError() {
        if (!errorBox) {
            return;
        }

        errorBox.textContent = '';
        errorBox.hidden = true;
    }


    function showError(message) {
        if (!errorBox) {
            return;
        }

        errorBox.textContent =
            message;

        errorBox.hidden =
            false;
    }


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
            2400
        );
    }


    // =========================================================
    // GENERIC FETCH
    // =========================================================

    async function requestJSON(
        url,
        options = {}
    ) {
        const settings = {
            method:
                options.method
                || 'GET',

            credentials:
                'same-origin',

            headers: {
                'Accept':
                    'application/json',

                ...(
                    options.body
                        ? {
                            'Content-Type':
                                'application/json',

                            'X-CSRF-TOKEN':
                                csrfToken(),
                        }
                        : {}
                ),
            },
        };


        if (options.body) {
            settings.body =
                JSON.stringify(
                    options.body
                );
        }


        const response =
            await fetch(
                url,
                settings
            );


        /*
         * Session expired.
         */
        if (
            response.status === 401
        ) {
            window.location.href =
                window
                    .FlowlistRoutes
                    .login;

            return null;
        }


        const data =
            await response
                .json()
                .catch(
                    function () {
                        return {};
                    }
                );


        if (
            !response.ok ||
            !data.success
        ) {
            const validationMessage =
                data.errors
                    ? Object
                        .values(
                            data.errors
                        )
                        .flat()[0]
                    : null;


            throw new Error(
                validationMessage
                || data.message
                || 'Request failed.'
            );
        }


        return data;
    }


    // =========================================================
    // LOADING STATE
    // =========================================================

    function renderLoading() {
        if (!list) {
            return;
        }

        list.innerHTML = `
            <div class="subtask-loading">
                <i class="fa-solid fa-spinner fa-spin"></i>
                <span>Loading checklist...</span>
            </div>
        `;
    }


    // =========================================================
    // FATAL ERROR
    // =========================================================

    function renderFatalError(
        message
    ) {
        if (!list) {
            return;
        }

        list.innerHTML = `
            <div class="subtask-empty">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span>${escapeHTML(message)}</span>
            </div>
        `;
    }


    // =========================================================
    // EMPTY STATE
    // =========================================================

    function renderEmpty() {
        list.innerHTML = `
            <div class="subtask-empty">

                <i class="fa-regular fa-square-check"></i>

                <strong>
                    No checklist items yet
                </strong>

                <span>
                    Add the first step for this task.
                </span>

            </div>
        `;
    }


    // =========================================================
    // STATISTICS
    // =========================================================

    function renderStatistics(stats) {
        const safeStats = {
            total:
                Number(
                    stats?.total
                )
                || 0,

            completed:
                Number(
                    stats?.completed
                )
                || 0,

            pending:
                Number(
                    stats?.pending
                )
                || 0,

            percentage:
                Math.max(
                    0,
                    Math.min(
                        100,
                        Number(
                            stats?.percentage
                        )
                        || 0
                    )
                ),
        };


        if (totalEl) {
            totalEl.textContent =
                safeStats.total;
        }

        if (completedEl) {
            completedEl.textContent =
                safeStats.completed;
        }

        if (pendingEl) {
            pendingEl.textContent =
                safeStats.pending;
        }


        if (progressFill) {
            progressFill.style.width =
                `${safeStats.percentage}%`;
        }


        if (percentageBadge) {
            percentageBadge.textContent =
                `${safeStats.percentage}%`;
        }


        if (progressText) {
            progressText.textContent =
                `${safeStats.completed} of `
                +`${safeStats.total} completed`;
        }
    }


    // =========================================================
    // CREATE ITEM HTML
    // =========================================================

    function createItemHTML(
        subtask,
        index
    ) {
        const completed =
            Boolean(
                subtask.is_completed
            );


        return `
            <div
                class="subtask-item
                    ${completed
                        ? 'is-completed'
                        : ''
                    }"
                data-subtask-id="${Number(subtask.id)}"
            >

                <button
                    type="button"
                    class="subtask-check"
                    data-action="toggle"
                    aria-label="
                        ${completed
                            ? 'Mark as incomplete'
                            : 'Mark as complete'
                        }
                    "
                    title="
                        ${completed
                            ? 'Mark as incomplete'
                            : 'Mark as complete'
                        }
                    "
                >
                    ${completed
                        ? '<i class="fa-solid fa-check"></i>'
                        : ''
                    }
                </button>


                <div
                    class="subtask-title"
                    data-role="title"
                >
                    ${escapeHTML(
                        subtask.title
                    )}
                </div>


                <div class="subtask-actions">

                    <button
                        type="button"
                        class="subtask-icon-btn"
                        data-action="up"
                        title="Move Up"
                        ${index === 0
                            ? 'disabled'
                            : ''
                        }
                    >
                        <i class="fa-solid fa-arrow-up"></i>
                    </button>


                    <button
                        type="button"
                        class="subtask-icon-btn"
                        data-action="down"
                        title="Move Down"
                        ${index === subtasks.length - 1
                            ? 'disabled'
                            : ''
                        }
                    >
                        <i class="fa-solid fa-arrow-down"></i>
                    </button>


                    <button
                        type="button"
                        class="subtask-icon-btn"
                        data-action="edit"
                        title="Edit"
                    >
                        <i class="fa-solid fa-pen"></i>
                    </button>


                    <button
                        type="button"
                        class="subtask-icon-btn delete"
                        data-action="delete"
                        title="Delete"
                    >
                        <i class="fa-regular fa-trash-can"></i>
                    </button>

                </div>

            </div>
        `;
    }


    // =========================================================
    // RENDER LIST
    // =========================================================

    function renderList() {
        if (!list) {
            return;
        }

        if (
            !Array.isArray(subtasks) ||
            subtasks.length === 0
        ) {
            renderEmpty();

            return;
        }


        list.innerHTML =
            subtasks
                .map(
                    createItemHTML
                )
                .join('');
    }


    // =========================================================
    // APPLY RESPONSE
    // =========================================================

    function applyResponse(data) {
        subtasks =
            Array.isArray(
                data?.subtasks
            )
                ? data.subtasks
                : [];

        renderStatistics(
            data?.stats
        );

        renderList();
    }


    // =========================================================
    // LOAD
    // =========================================================

    async function loadSubtasks() {
        clearError();

        renderLoading();


        try {

            const url =
                new URL(
                    window
                        .FlowlistRoutes
                        .subtaskIndexApi,
                    window.location.origin
                );


            url.searchParams.set(
                'task_id',
                String(taskId)
            );


            const data =
                await requestJSON(
                    url.toString()
                );


            if (!data) {
                return;
            }


            applyResponse(data);

        } catch (error) {

            console.error(
                'Subtask load error:',
                error
            );


            renderFatalError(
                error.message
                || 'Failed to load checklist.'
            );
        }
    }


    // =========================================================
    // ADD
    // =========================================================

    addForm?.addEventListener(
        'submit',
        async function (event) {

            event.preventDefault();

            clearError();


            const title =
                titleInput
                    ?.value
                    .trim()
                || '';


            if (!title) {

                showError(
                    'Subtask title is required.'
                );

                titleInput?.focus();

                return;
            }


            if (addButton) {

                addButton.disabled =
                    true;

                addButton.innerHTML = `
                    <i class="fa-solid fa-spinner fa-spin"></i>
                    <span>Adding</span>
                `;
            }


            try {

                const data =
                    await requestJSON(
                        window
                            .FlowlistRoutes
                            .subtaskStoreApi,
                        {
                            method: 'POST',

                            body: {
                                task_id:
                                    taskId,

                                title:
                                    title,
                            },
                        }
                    );


                if (!data) {
                    return;
                }


                if (titleInput) {
                    titleInput.value = '';
                    titleInput.focus();
                }


                applyResponse(data);


                showToast(
                    'Subtask added.'
                );


            } catch (error) {

                showError(
                    error.message
                    || 'Failed to add subtask.'
                );


            } finally {

                if (addButton) {

                    addButton.disabled =
                        false;

                    addButton.innerHTML = `
                        <i class="fa-solid fa-plus"></i>
                        <span>Add</span>
                    `;
                }
            }
        }
    );


    // =========================================================
    // LIST CLICK EVENT DELEGATION
    // =========================================================

    list?.addEventListener(
        'click',
        async function (event) {

            const button =
                event.target.closest(
                    '[data-action]'
                );


            if (!button) {
                return;
            }


            const item =
                button.closest(
                    '.subtask-item'
                );


            if (!item) {
                return;
            }


            const subtaskId =
                Number(
                    item.dataset.subtaskId
                );


            const index =
                subtasks.findIndex(
                    function (subtask) {
                        return Number(
                            subtask.id
                        ) === subtaskId;
                    }
                );


            if (index < 0) {
                return;
            }


            const action =
                button.dataset.action;


            // =================================================
            // TOGGLE COMPLETE
            // =================================================

            if (
                action === 'toggle'
            ) {
                await toggleSubtask(
                    subtasks[index]
                );

                return;
            }


            // =================================================
            // DELETE
            // =================================================

            if (
                action === 'delete'
            ) {
                await deleteSubtask(
                    subtasks[index]
                );

                return;
            }


            // =================================================
            // EDIT
            // =================================================

            if (
                action === 'edit'
            ) {
                startInlineEdit(
                    item,
                    subtasks[index]
                );

                return;
            }


            // =================================================
            // MOVE UP
            // =================================================

            if (
                action === 'up' &&
                index > 0
            ) {
                await moveItem(
                    index,
                    index - 1
                );

                return;
            }


            // =================================================
            // MOVE DOWN
            // =================================================

            if (
                action === 'down' &&
                index <
                subtasks.length - 1
            ) {
                await moveItem(
                    index,
                    index + 1
                );
            }
        }
    );


    // =========================================================
    // TOGGLE
    // =========================================================

    async function toggleSubtask(
        subtask
    ) {
        clearError();


        try {

            const completed =
                !Boolean(
                    subtask.is_completed
                );


            const data =
                await requestJSON(
                    window
                        .FlowlistRoutes
                        .subtaskToggleApi,
                    {
                        method: 'POST',

                        body: {
                            task_id:
                                taskId,

                            subtask_id:
                                Number(
                                    subtask.id
                                ),

                            is_completed:
                                completed,
                        },
                    }
                );


            if (!data) {
                return;
            }


            applyResponse(data);


            showToast(
                completed
                    ? 'Subtask completed.'
                    : 'Subtask reopened.'
            );


        } catch (error) {

            showError(
                error.message
                || 'Failed to update subtask.'
            );
        }
    }


    // =========================================================
    // DELETE
    // =========================================================

    async function deleteSubtask(
        subtask
    ) {
        const confirmed =
            window.confirm(
                `Delete "${subtask.title}"?`
            );


        if (!confirmed) {
            return;
        }


        clearError();


        try {

            const data =
                await requestJSON(
                    window
                        .FlowlistRoutes
                        .subtaskDeleteApi,
                    {
                        method: 'POST',

                        body: {
                            task_id:
                                taskId,

                            subtask_id:
                                Number(
                                    subtask.id
                                ),
                        },
                    }
                );


            if (!data) {
                return;
            }


            applyResponse(data);


            showToast(
                'Subtask deleted.'
            );


        } catch (error) {

            showError(
                error.message
                || 'Failed to delete subtask.'
            );
        }
    }


    // =========================================================
    // INLINE EDIT
    // =========================================================

    function startInlineEdit(
        item,
        subtask
    ) {
        const titleContainer =
            item.querySelector(
                '[data-role="title"]'
            );


        if (
            !titleContainer ||
            titleContainer
                .querySelector('input')
        ) {
            return;
        }


        const oldTitle =
            subtask.title;


        titleContainer.innerHTML = `
            <input
                type="text"
                class="subtask-edit-input"
                maxlength="180"
                value="${escapeHTML(oldTitle)}"
            >
        `;


        const input =
            titleContainer
                .querySelector(
                    'input'
                );


        if (!input) {
            return;
        }


        input.focus();

        input.select();


        /*
         * Simpan saat Enter.
         *
         * Batalkan saat Escape.
         */
        input.addEventListener(
            'keydown',
            async function (event) {

                if (
                    event.key === 'Enter'
                ) {
                    event.preventDefault();

                    await saveInlineEdit(
                        subtask,
                        input.value
                    );

                    return;
                }


                if (
                    event.key === 'Escape'
                ) {
                    event.preventDefault();

                    renderList();
                }
            }
        );


        /*
         * Simpan saat input kehilangan fokus.
         */
        input.addEventListener(
            'blur',
            async function () {

                /*
                 * Sedikit delay agar keydown Enter
                 * selesai lebih dahulu.
                 */
                window.setTimeout(
                    async function () {

                        if (
                            document.body.contains(
                                input
                            )
                        ) {
                            await saveInlineEdit(
                                subtask,
                                input.value
                            );
                        }

                    },
                    50
                );
            },
            {
                once: true,
            }
        );
    }


    // =========================================================
    // SAVE INLINE EDIT
    // =========================================================

    async function saveInlineEdit(
        subtask,
        newTitle
    ) {
        const title =
            String(
                newTitle
                || ''
            ).trim();


        if (!title) {
            showError(
                'Subtask title cannot be empty.'
            );

            renderList();

            return;
        }


        /*
         * Tidak perlu request apabila
         * judul tidak berubah.
         */
        if (
            title === subtask.title
        ) {
            renderList();

            return;
        }


        clearError();


        try {

            const data =
                await requestJSON(
                    window
                        .FlowlistRoutes
                        .subtaskUpdateApi,
                    {
                        method: 'POST',

                        body: {
                            task_id:
                                taskId,

                            subtask_id:
                                Number(
                                    subtask.id
                                ),

                            title:
                                title,
                        },
                    }
                );


            if (!data) {
                return;
            }


            applyResponse(data);


            showToast(
                'Subtask updated.'
            );


        } catch (error) {

            showError(
                error.message
                || 'Failed to update subtask.'
            );

            renderList();
        }
    }


    // =========================================================
    // MOVE ITEM
    // =========================================================

    async function moveItem(
        fromIndex,
        toIndex
    ) {
        if (
            toIndex < 0 ||
            toIndex >= subtasks.length
        ) {
            return;
        }


        /*
         * Simpan urutan lama untuk rollback
         * jika server gagal.
         */
        const before =
            [...subtasks];


        /*
         * Pindahkan item di memory.
         */
        const moved =
            subtasks.splice(
                fromIndex,
                1
            )[0];


        subtasks.splice(
            toIndex,
            0,
            moved
        );


        /*
         * Render langsung supaya responsif.
         */
        renderList();


        try {

            const data =
                await requestJSON(
                    window
                        .FlowlistRoutes
                        .subtaskReorderApi,
                    {
                        method: 'POST',

                        body: {
                            task_id:
                                taskId,

                            ordered_ids:
                                subtasks.map(
                                    function (
                                        subtask
                                    ) {
                                        return Number(
                                            subtask.id
                                        );
                                    }
                                ),
                        },
                    }
                );


            if (!data) {
                return;
            }


            applyResponse(data);


        } catch (error) {

            /*
             * Rollback tampilan jika server gagal.
             */
            subtasks =
                before;

            renderList();


            showError(
                error.message
                || 'Failed to reorder subtasks.'
            );
        }
    }


    // =========================================================
    // INITIAL LOAD
    // =========================================================

    document.addEventListener(
        'DOMContentLoaded',
        function () {
            loadSubtasks();
        }
    );

})();