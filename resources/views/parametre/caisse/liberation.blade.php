@extends('layouts.app')

@section('title', 'Libération de caisse - Velaro')

@section('content')
<div class="pos-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Libération de Caisse</h1>
            <p class="page-subtitle">Déconnectez les caisses des machines qui y sont actuellement rattachées.</p>
        </div>
    </div>

    @if(session('success'))
        <div style="background: var(--success); color: white; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; display: flex; align-items: center; gap: 8px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="content-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 10%;">ID</th>
                        <th style="width: 30%;">Caisse</th>
                        <th style="width: 25%;">Site</th>
                        <th style="width: 20%; text-align: center;">État</th>
                        <th style="width: 15%; text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($caisses as $caisse)
                    <tr class="hover-row">
                        <td style="font-weight: 600; color: var(--text-muted);">{{ str_pad($caisse->caisseid, 3, '0', STR_PAD_LEFT) }}</td>
                        <td class="font-medium" style="color: var(--primary);">{{ $caisse->libelle }}</td>
                        <td>{{ $caisse->site_libelle }}</td>
                        <td style="text-align: center;">
                            @if($caisse->machineid)
                                <span class="status-badge status-draft" style="background: #fef3c7; color: #92400e; border-color: #fde68a;">
                                    <span style="display: flex; align-items: center; gap: 4px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                                        ID: {{ $caisse->machineid }}
                                    </span>
                                </span>
                            @else
                                <span class="status-badge status-paid" style="background: #dcfce7; color: #166534; border-color: #bbf7d0;">Libre</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <form action="{{ route('parametre.caisse.liberer', $caisse->caisseid) }}" method="POST" style="margin: 0;" onsubmit="return confirm('Êtes-vous sûr de vouloir libérer cette caisse ?');">
                                @csrf
                                <button type="submit" class="btn btn-outline" style="padding: 4px 12px; height: auto; {{ !$caisse->machineid ? 'opacity: 0.5; cursor: not-allowed;' : 'color: #dc2626; border-color: #fca5a5; background: #fef2f2;' }}" {{ !$caisse->machineid ? 'disabled' : '' }}>
                                    Libérer
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="padding: 40px; text-align: center; color: var(--text-muted);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 16px; opacity: 0.5;">
                                <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line>
                            </svg>
                            <div style="font-weight: 600; font-size: 15px; margin-bottom: 8px;">Aucune caisse trouvée</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
