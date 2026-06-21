@extends('layouts.app')

@section('title', 'Libération de caisse')

@section('styles')
<style>
    .page-header {
        padding: 15px 20px;
        background: white;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .page-title {
        font-size: 18px;
        font-weight: 700;
        color: var(--text);
        margin: 0;
    }

    .table-container {
        padding: 20px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    th {
        background: #f8fafc;
        padding: 12px 15px;
        text-align: left;
        font-weight: 600;
        color: var(--text-secondary);
        border-bottom: 1px solid var(--border);
        font-size: 13px;
    }

    td {
        padding: 12px 15px;
        border-bottom: 1px solid var(--border);
        color: var(--text);
        font-size: 14px;
    }

    tr:last-child td {
        border-bottom: none;
    }

    tr:hover {
        background: #f1f5f9;
    }

    .status-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .status-connected {
        background: #fef08a; /* yellow-200 */
        color: #854d0e; /* yellow-800 */
    }

    .status-free {
        background: #bbf7d0; /* green-200 */
        color: #166534; /* green-800 */
    }

    .btn-liberer {
        background: #ef4444; /* red-500 */
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 500;
        transition: background 0.2s;
    }

    .btn-liberer:hover {
        background: #dc2626; /* red-600 */
    }

    .btn-liberer:disabled {
        background: #fca5a5;
        cursor: not-allowed;
    }

    .alert-success {
        background: #dcfce7;
        color: #166534;
        padding: 10px 15px;
        border-radius: 4px;
        margin: 20px 20px 0 20px;
        border: 1px solid #bbf7d0;
    }
</style>
@endsection

@section('content')
<div class="main-content-inner full-width" style="padding: 0;">
    
    <div class="page-header">
        <h1 class="page-title">Libération de caisse</h1>
    </div>

    @if(session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Caisse</th>
                    <th>Site</th>
                    <th>État</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($caisses as $caisse)
                <tr>
                    <td>{{ $caisse->caisseid }}</td>
                    <td style="font-weight: 500;">{{ $caisse->libelle }}</td>
                    <td>{{ $caisse->site_libelle }}</td>
                    <td>
                        @if($caisse->machineid)
                            <span class="status-badge status-connected">Connectée (Machine: {{ $caisse->machineid }})</span>
                        @else
                            <span class="status-badge status-free">Libre</span>
                        @endif
                    </td>
                    <td>
                        <form action="{{ route('parametre.caisse.liberer', $caisse->caisseid) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir libérer cette caisse ?');">
                            @csrf
                            <button type="submit" class="btn-liberer" {{ !$caisse->machineid ? 'disabled' : '' }}>
                                Libérer
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
@endsection
