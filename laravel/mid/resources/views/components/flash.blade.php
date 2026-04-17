@if (session('status'))
    <div class="mb-4 rounded-md bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">
        {{ session('status') }}
    </div>
@endif
