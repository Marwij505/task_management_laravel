{{--
|--------------------------------------------------------------------------
| FLOWLIST MK2 - SUBTASK & CHECKLIST
|--------------------------------------------------------------------------
|
| Komponen checklist untuk halaman Task Detail.
|
|--------------------------------------------------------------------------
--}}

<section
    class="subtask-card"
    id="subtaskModule"
>

    {{-- ======================================================
         HEADER
         ====================================================== --}}
    <div class="subtask-header">

        <div class="subtask-header-title">

            <div class="subtask-header-icon">
                <i class="fa-solid fa-list-check"></i>
            </div>

            <div>
                <h3>Subtasks & Checklist</h3>

                <p>
                    Break this task into smaller,
                    manageable steps.
                </p>
            </div>

        </div>


        <div
            class="subtask-percentage-badge"
            id="subtaskPercentageBadge"
        >
            0%
        </div>

    </div>


    {{-- ======================================================
         STATISTICS
         ====================================================== --}}
    <div class="subtask-statistics">

        <div class="subtask-stat">
            <strong id="subtaskTotal">
                0
            </strong>

            <span>
                Total
            </span>
        </div>


        <div class="subtask-stat completed">
            <strong id="subtaskCompleted">
                0
            </strong>

            <span>
                Completed
            </span>
        </div>


        <div class="subtask-stat pending">
            <strong id="subtaskPending">
                0
            </strong>

            <span>
                Remaining
            </span>
        </div>

    </div>


    {{-- ======================================================
         AUTOMATIC CHECKLIST PROGRESS
         ====================================================== --}}
    <div class="subtask-progress-section">

        <div class="subtask-progress-label">

            <span>
                Checklist Progress
            </span>

            <strong id="subtaskProgressText">
                0 of 0 completed
            </strong>

        </div>


        <div class="subtask-progress-track">

            <div
                class="subtask-progress-fill"
                id="subtaskProgressFill"
            ></div>

        </div>

    </div>


    {{-- ======================================================
         ADD SUBTASK
         ====================================================== --}}
    <form
        id="subtaskAddForm"
        class="subtask-add-form"
        autocomplete="off"
    >

        <div class="subtask-input-wrapper">

            <i class="fa-solid fa-plus"></i>

            <input
                type="text"
                id="subtaskTitleInput"
                maxlength="180"
                placeholder="Add a new checklist item..."
                aria-label="Subtask title"
            >

        </div>


        <button
            type="submit"
            id="subtaskAddButton"
            class="subtask-add-button"
        >
            <i class="fa-solid fa-plus"></i>

            <span>
                Add
            </span>
        </button>

    </form>


    {{-- ERROR --}}
    <div
        id="subtaskError"
        class="subtask-message error"
        hidden
    ></div>


    {{-- ======================================================
         LIST CONTAINER
         ====================================================== --}}
    <div
        id="subtaskList"
        class="subtask-list"
    >

        <div class="subtask-loading">
            <i class="fa-solid fa-spinner fa-spin"></i>
            <span>Loading checklist...</span>
        </div>

    </div>


    {{-- ======================================================
         INFORMATION
         ====================================================== --}}
    <div class="subtask-footer-info">

        <i class="fa-regular fa-circle-info"></i>

        <span>
            Use the arrows to change checklist order.
            Progress updates automatically.
        </span>

    </div>

</section>


{{-- ==========================================================
     SUBTASK TOAST
     ========================================================== --}}
<div
    id="subtaskToast"
    class="subtask-toast"
    role="status"
    aria-live="polite"
></div>