@props(['role' => 'anggota', 'bootstrap' => false])
@if ($bootstrap)
    <x-bootstrap.sidebar :role="$role" />
@else
    <x-legacy.sidebar :role="$role" />
@endif
