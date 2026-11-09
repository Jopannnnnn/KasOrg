@props(['heading' => 'Dashboard', 'role' => 'anggota', 'userName' => 'Pengguna Preview', 'userInitials' => 'KP', 'bootstrap' => false])
@if ($bootstrap)
    <x-bootstrap.topbar :heading="$heading" :role="$role" :user-name="$userName" :user-initials="$userInitials" />
@else
    <x-legacy.topbar :heading="$heading" :role="$role" :user-name="$userName" :user-initials="$userInitials" />
@endif
