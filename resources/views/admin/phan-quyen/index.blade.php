@extends('layouts.admin')

@section('title', 'Phân quyền hệ thống động - CineHome')
@section('page-title', 'Quản lý phân quyền động')
@section('page-subtitle', 'Cấu hình quyền truy cập theo vai trò vận hành trong hệ thống CineHome')

@section('content')
@php
    $editableRoles = $roles->reject(fn ($role) => $role->name === 'Quản trị viên')->values();
    $summary = $summary ?? [
        'roles' => $editableRoles->count(),
        'permissions' => $permissions->count(),
        'assigned' => $editableRoles->sum(fn ($role) => $role->permissions->count()),
        'selected' => $selectedRole?->permissions->count() ?? 0,
    ];

    $systemPermissionKeys = [
        'phan_quyen_he_thong',
        'quan_ly_cau_hinh_he_thong',
        'xem_nhat_ky_hoat_dong',
        'quan_ly_thong_bao_day',
    ];

    $permissionGroups = collect([
        [
            'key' => 'system',
            'tone' => 'is-system',
            'icon' => 'fa-shield-halved',
            'title' => 'Hệ thống & bảo mật',
            'description' => 'Các quyền tác động sâu đến cấu hình, nhật ký và ma trận phân quyền.',
            'items' => $permissions->filter(fn ($permission) => in_array($permission->name, $systemPermissionKeys, true)),
        ],
        [
            'key' => 'operation',
            'tone' => 'is-operation',
            'icon' => 'fa-clapperboard',
            'title' => 'Vận hành rạp',
            'description' => 'Quản lý phim, phòng chiếu, suất chiếu, bán vé, đồ ăn và báo cáo doanh thu.',
            'items' => $permissions->reject(fn ($permission) => in_array($permission->name, $systemPermissionKeys, true) || str_contains($permission->name, 'khach_hang_')),
        ],
        [
            'key' => 'customer',
            'tone' => 'is-customer',
            'icon' => 'fa-users',
            'title' => 'Dịch vụ khách hàng',
            'description' => 'Quyền thao tác của khách hàng trên website và lịch sử vé cá nhân.',
            'items' => $permissions->filter(fn ($permission) => str_contains($permission->name, 'khach_hang_')),
        ],
    ])->filter(fn ($group) => $group['items']->isNotEmpty());

    $selectedPermissionCount = $selectedRole?->permissions->count() ?? 0;
    $permissionTotal = max($permissions->count(), 1);
    $selectedPercent = round(($selectedPermissionCount / $permissionTotal) * 100);
@endphp

<div class="permission-page">
    @include('admin.partials.flash')

    <section class="permission-hero">
        <div>
            <span class="permission-kicker">
                <i class="fa-solid fa-user-shield"></i>
                Ma trận phân quyền
            </span>
            <h2>Quản lý quyền truy cập theo vai trò</h2>
            <p>Chọn một vai trò, bật/tắt các quyền nghiệp vụ cần thiết và lưu lại để hệ thống áp dụng ngay cho nhân sự đang sử dụng CineHome.</p>
        </div>
    </section>

    <section class="permission-stats">
        <article class="permission-stat">
            <span class="is-role"><i class="fa-solid fa-users-gear"></i></span>
            <div>
                <small>Vai trò cấu hình</small>
                <strong>{{ number_format($summary['roles']) }}</strong>
            </div>
        </article>
        <article class="permission-stat">
            <span class="is-permission"><i class="fa-solid fa-key"></i></span>
            <div>
                <small>Tổng quyền</small>
                <strong>{{ number_format($summary['permissions']) }}</strong>
            </div>
        </article>
        <article class="permission-stat">
            <span class="is-assigned"><i class="fa-solid fa-link"></i></span>
            <div>
                <small>Quyền đã gán</small>
                <strong>{{ number_format($summary['assigned']) }}</strong>
            </div>
        </article>
        <article class="permission-stat">
            <span class="is-selected"><i class="fa-solid fa-circle-check"></i></span>
            <div>
                <small>Vai trò hiện tại</small>
                <strong>{{ number_format($summary['selected']) }}</strong>
            </div>
        </article>
    </section>

    <div class="permission-layout">
        <aside class="permission-role-panel">
            <div class="permission-panel-head">
                <div>
                    <span class="permission-kicker">Vai trò</span>
                    <h3>Bộ phận vận hành</h3>
                    <p>Chọn vai trò để xem và cập nhật nhóm quyền tương ứng.</p>
                </div>
            </div>

            <div class="permission-role-list">
                @forelse($editableRoles as $role)
                    @php
                        $isSelected = $selectedRole?->id === $role->id;
                        $roleIcon = match ($role->name) {
                            'Quản lý hệ thống' => 'fa-gears',
                            'Quản lý', 'Quản lý phòng chiếu' => 'fa-user-tie',
                            'Nhân viên' => 'fa-headset',
                            'Khách hàng' => 'fa-user',
                            default => 'fa-user-shield',
                        };
                        $rolePercent = $permissions->count()
                            ? round(($role->permissions->count() / $permissions->count()) * 100)
                            : 0;
                    @endphp

                    <a href="{{ route('admin.phan-quyen.index', ['role_id' => $role->id]) }}" class="permission-role-card {{ $isSelected ? 'is-active' : '' }}">
                        <span class="permission-role-icon">
                            <i class="fa-solid {{ $roleIcon }}"></i>
                        </span>
                        <div>
                            <strong>{{ $role->name }}</strong>
                            <small>{{ $role->permissions->count() }} / {{ $permissions->count() }} quyền</small>
                            <span class="permission-role-progress">
                                <i style="width: {{ $rolePercent }}%"></i>
                            </span>
                        </div>
                        <em>{{ $rolePercent }}%</em>
                    </a>
                @empty
                    <div class="permission-empty is-compact">
                        <i class="fa-solid fa-users-slash"></i>
                        <h3>Chưa có vai trò</h3>
                        <p>Hệ thống chưa có vai trò nào có thể cấu hình.</p>
                    </div>
                @endforelse
            </div>
        </aside>

        <main class="permission-matrix-panel">
            @if($selectedRole)
                <div class="permission-matrix-head">
                    <div>
                        <span class="permission-kicker">Đang cấu hình</span>
                        <h3>{{ $selectedRole->name }}</h3>
                        <p>Bật quyền cần dùng, bỏ chọn quyền không nên truy cập. Quyền sẽ có hiệu lực sau khi lưu.</p>
                    </div>
                    <div class="permission-score">
                        <strong>{{ $selectedPercent }}%</strong>
                        <span>{{ $selectedPermissionCount }} / {{ $permissions->count() }} quyền</span>
                    </div>
                </div>

                <form action="{{ route('admin.phan-quyen.updateMatrix', $selectedRole->id) }}" method="POST" class="permission-form">
                    @csrf
                    @method('PUT')

                    <div class="permission-group-list">
                        @foreach($permissionGroups as $group)
                            @php
                                $enabledInGroup = $group['items']->filter(fn ($permission) => $selectedRole->hasPermissionTo($permission->name))->count();
                            @endphp

                            <section class="permission-group {{ $group['tone'] }}">
                                <div class="permission-group-head">
                                    <span class="permission-group-icon">
                                        <i class="fa-solid {{ $group['icon'] }}"></i>
                                    </span>
                                    <div>
                                        <h4>{{ $group['title'] }}</h4>
                                        <p>{{ $group['description'] }}</p>
                                    </div>
                                    <em>{{ $enabledInGroup }} / {{ $group['items']->count() }}</em>
                                </div>

                                <div class="permission-grid">
                                    @foreach($group['items'] as $permission)
                                        @php
                                            $hasPermission = $selectedRole->hasPermissionTo($permission->name);
                                        @endphp

                                        <label class="permission-check {{ $hasPermission ? 'is-checked' : '' }}">
                                            <input
                                                type="checkbox"
                                                name="permissions[]"
                                                value="{{ $permission->name }}"
                                                @checked($hasPermission)
                                            >
                                            <span class="permission-check-box">
                                                <i class="fa-solid fa-check"></i>
                                            </span>
                                            <span class="permission-check-copy">
                                                <strong>{{ $permission->description }}</strong>
                                                <small>{{ $permission->name }}</small>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </section>
                        @endforeach
                    </div>

                    <div class="permission-submit-bar">
                        <div>
                            <strong>Sẵn sàng cập nhật quyền?</strong>
                            <span>Spatie cache sẽ được làm mới sau khi lưu để quyền mới có hiệu lực.</span>
                        </div>
                        <button type="submit" class="permission-primary-btn">
                            <i class="fa-solid fa-square-check"></i>
                            Lưu ma trận quyền
                        </button>
                    </div>
                </form>
            @else
                <div class="permission-empty">
                    <i class="fa-solid fa-user-check"></i>
                    <h3>Chưa chọn vai trò</h3>
                    <p>Chọn một vai trò ở khung bên trái để bắt đầu cấu hình quyền truy cập.</p>
                </div>
            @endif
        </main>
    </div>
</div>
@endsection
