@if ($bootstrap ?? false)
    @include('bootstrap.pagination')
@else
    @include('legacy.pagination')
@endif
