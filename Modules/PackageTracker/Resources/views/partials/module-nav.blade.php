<div class="card shadow-sm border-0 mb-3">
    <div class="card-body py-2">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <a href="{{ route('package_tracker.dashboard') }}" class="lsg-action-btn lsg-action-btn--primary lsg-action-btn--compact"><i class="fa-solid fa-gauge-high"></i><span>Dashboard</span></a>
            <a href="{{ route('package_tracker.shipments.index') }}" class="lsg-action-btn lsg-action-btn--primary lsg-action-btn--compact"><i class="fa-solid fa-boxes-packing"></i><span>Shipments</span></a>
            <a href="{{ route('package_tracker.shipments.create') }}" class="lsg-action-btn lsg-action-btn--success lsg-action-btn--compact"><i class="fa-solid fa-plus"></i><span>New shipment</span></a>
            <a href="{{ route('package_tracker.clients.index') }}" class="lsg-action-btn lsg-action-btn--primary lsg-action-btn--compact"><i class="fa-solid fa-users"></i><span>Clients</span></a>
            <a href="{{ route('package_tracker.carriers.index') }}" class="lsg-action-btn lsg-action-btn--primary lsg-action-btn--compact"><i class="fa-solid fa-truck"></i><span>Carriers</span></a>
        </div>
    </div>
</div>
