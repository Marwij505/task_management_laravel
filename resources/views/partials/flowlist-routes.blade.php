<script>
    window.FlowlistRoutes = {
        login: @json(route('login')),
        dashboard: @json(route('dashboard')),
        taskList: @json(route('tasks.index')),
        taskCreate: @json(route('tasks.create')),
        taskDetail: @json(route('tasks.detail')),
        calendar: @json(route('calendar')),
        statistics: @json(route('statistics')),
        profile: @json(route('profile')),

        dashboardApi: @json(route('flowlist.api.dashboard')),
        taskListApi: @json(route('flowlist.api.tasks.index')),
        taskDetailApi: @json(route('flowlist.api.tasks.show')),
        taskStoreApi: @json(route('flowlist.api.tasks.store')),
        taskUpdateApi: @json(route('flowlist.api.tasks.update')),
        taskCompleteApi: @json(route('flowlist.api.tasks.complete')),
        taskDeleteApi: @json(route('flowlist.api.tasks.destroy')),
        calendarApi: @json(route('flowlist.api.calendar')),
        statisticsApi: @json(route('flowlist.api.statistics')),
        sessionExpiresAt: @json(session('login_expires_at', 0)),
        profileApi: @json(route('flowlist.api.profile.show'))
    };
</script>
<script src="{{ asset('assets/js/laravel-fetch.js') }}"></script>
