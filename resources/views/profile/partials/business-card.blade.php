<div class="biz-card {{ $design }}">
    <div class="bc-front">
        <div class="bc-logo">
            <div class="bc-mark">
                @if($logo)
                    <img src="{{ $logo }}" alt="Logo" style="width:34px;height:34px;object-fit:contain">
                @else
                    <i class="mdi mdi-medical-bag"></i>
                @endif
            </div>
            <div>
                <div class="bc-brand">{{ $brandName }}</div>
                <div class="bc-small">Health • Care • Trust</div>
            </div>
        </div>
    </div>

    <div class="bc-front">
        <div class="bc-name">{{ $displayName }}</div>
        <div class="bc-role">{{ $roleName }}</div>
    </div>

    <div class="bc-front bc-info">
        <div><i class="mdi mdi-phone-outline"></i> {{ $brandPhone }}</div>
        <div><i class="mdi mdi-email-outline"></i> {{ $brandEmail }}</div>
        <div><i class="mdi mdi-map-marker-outline"></i> {{ $brandAddress }}</div>
    </div>
</div>