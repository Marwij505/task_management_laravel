{{--
|--------------------------------------------------------------------------
| FLOWLIST MK2 - QUICK ADD TASK
|--------------------------------------------------------------------------
|
| Komponen ini dipakai bersama oleh:
|
| 1. Dashboard
| 2. Task List
| 3. Calendar
|
| Modal yang sama juga dibuka melalui:
|
| - Tombol Quick Add pada halaman
| - Floating Add Button
| - Shortcut Alt + Q
|
|--------------------------------------------------------------------------
--}}

{{-- =========================================================
     FLOATING QUICK ADD BUTTON
     ========================================================= --}}
<button
    type="button"
    class="quick-add-fab"
    data-quick-add-open
    aria-label="Quick Add Task"
    title="Quick Add Task (Alt + Q)"
>
    <i class="fa-solid fa-plus"></i>

    <span class="quick-add-fab-tooltip">
        Quick Add
    </span>
</button>


{{-- =========================================================
     QUICK ADD MODAL
     ========================================================= --}}
<div
    id="quickAddModal"
    class="quick-add-modal"
    aria-hidden="true"
>
    <div
        class="quick-add-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="quickAddHeading"
    >

        {{-- HEADER --}}
        <div class="quick-add-header">

            <div class="quick-add-heading">
                <div class="quick-add-icon">
                    <i class="fa-solid fa-bolt"></i>
                </div>

                <div>
                    <h2 id="quickAddHeading">
                        Quick Add Task
                    </h2>

                    <p>
                        Add the important details now.
                        You can edit the task later.
                    </p>
                </div>
            </div>

            <button
                type="button"
                class="quick-add-close"
                data-quick-add-close
                aria-label="Close Quick Add"
            >
                <i class="fa-solid fa-xmark"></i>
            </button>

        </div>


        {{-- =================================================
             FORM
             ================================================= --}}
        <form
            id="quickAddTaskForm"
            class="quick-add-form"
            autocomplete="off"
        >

            {{--
                Digunakan Activity Log untuk mengetahui
                halaman asal Quick Add.
            --}}
            <input
                type="hidden"
                id="quickAddSource"
                value="{{ request()->route()?->getName() }}"
            >


            {{-- ERROR MESSAGE --}}
            <div
                id="quickAddError"
                class="quick-add-error"
                role="alert"
                hidden
            ></div>


            {{-- TASK TITLE --}}
            <div class="quick-add-field quick-add-field-full">

                <label for="quickAddTitle">
                    Task Title
                    <span class="required">*</span>
                </label>

                <div class="quick-add-input-wrap">
                    <i class="fa-solid fa-list-check"></i>

                    <input
                        type="text"
                        id="quickAddTitle"
                        maxlength="150"
                        placeholder="What needs to be done?"
                    >
                </div>

            </div>


            {{-- DEADLINE + PRIORITY --}}
            <div class="quick-add-grid">

                <div class="quick-add-field">

                    <label for="quickAddDeadline">
                        Deadline
                        <span class="required">*</span>
                    </label>

                    <div class="quick-add-input-wrap">
                        <i class="fa-regular fa-calendar"></i>

                        <input
                            type="date"
                            id="quickAddDeadline"
                        >
                    </div>

                </div>


                <div class="quick-add-field">

                    <label for="quickAddPriority">
                        Priority
                        <span class="required">*</span>
                    </label>

                    <div class="quick-add-input-wrap">
                        <i class="fa-solid fa-flag"></i>

                        <select id="quickAddPriority">
                            <option value="low">
                                Low
                            </option>

                            <option
                                value="medium"
                                selected
                            >
                                Medium
                            </option>

                            <option value="high">
                                High
                            </option>
                        </select>
                    </div>

                </div>

            </div>


            {{-- CATEGORY --}}
            <div class="quick-add-field quick-add-field-full">

                <label for="quickAddCategory">
                    Category
                </label>

                <div class="quick-add-input-wrap">
                    <i class="fa-regular fa-folder"></i>

                    <input
                        type="text"
                        id="quickAddCategory"
                        maxlength="100"
                        placeholder="e.g. Work, Personal, Academic"
                    >
                </div>

            </div>


            {{-- HELPER --}}
            <div class="quick-add-helper">

                <div>
                    <i class="fa-regular fa-circle-info"></i>

                    Task will start as
                    <strong>Pending</strong>
                    with
                    <strong>0% progress</strong>.
                </div>

                <kbd>Alt + Q</kbd>

            </div>


            {{-- ACTION BUTTONS --}}
            <div class="quick-add-actions">

                <button
                    type="button"
                    class="quick-add-cancel-btn"
                    data-quick-add-close
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    id="quickAddSubmitBtn"
                    class="quick-add-submit-btn"
                >
                    <i class="fa-solid fa-plus"></i>
                    <span>Add Task</span>
                </button>

            </div>

        </form>

    </div>
</div>


{{-- =========================================================
     SUCCESS TOAST
     ========================================================= --}}
<div
    id="quickAddToast"
    class="quick-add-toast"
    role="status"
    aria-live="polite"
>
    Task added successfully.
</div>