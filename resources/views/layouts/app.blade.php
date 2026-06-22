<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Velaro POS')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #e0e7ff;
            --accent: #0ea5e9;
            --accent-light: #e0f2fe;
            --bg: #f1f5f9;
            --bg-card: rgba(255, 255, 255, 0.85);
            --border: rgba(226, 232, 240, 0.8);
            --border-light: rgba(255, 255, 255, 0.6);
            --text: #0f172a;
            --text-secondary: #475569;
            --text-muted: #94a3b8;
            --success: #10b981;
            --success-bg: #d1fae5;
            --success-border: #a7f3d0;
            --warning: #f59e0b;
            --warning-bg: #fef3c7;
            --danger: #ef4444;
            --danger-bg: #fee2e2;
            --danger-border: #fecaca;
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.02);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.04);
            --shadow-premium: 0 10px 30px -5px rgba(0,0,0,0.08), 0 4px 6px -2px rgba(0,0,0,0.04);
            --glass-blur: blur(12px);

            /* Reusable Posvelaro Theme Variables */
            --primary-hover: #4338ca;
            --surface: #ffffff;
            --background: #f8fafc;
            --text-main: #0f172a;
            --border-hover: #cbd5e1;
            --border-focus: #a5b4fc;
            --purple: #8b5cf6;
            --purple-bg: #ede9fe;
            --default: #64748b;
            --default-bg: #f1f5f9;
            --radius-lg: 12px;
            --radius-md: 8px;
            --radius-sm: 6px;
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }

        /* Top navigation */
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 32px;
            background: var(--bg-card);
            border-bottom: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .dashboard-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
        }

        .dashboard-grid-advanced {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        .col-span-2 { grid-column: span 2; }
        .col-span-3 { grid-column: span 3; }

        @media (max-width: 1024px) {
            .dashboard-grid-advanced { grid-template-columns: 1fr; }
            .col-span-2, .col-span-3 { grid-column: span 1; }
        }

        /* Cards */
        .topbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
        }

        .brand-icon svg {
            width: 18px;
            height: 18px;
            color: white;
        }

        .brand-name {
            font-size: 17px;
            font-weight: 700;
            color: var(--text);
            letter-spacing: -0.3px;
        }

        .topbar-user {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .user-info {
            text-align: right;
        }

        .user-name {
            font-size: 14px;
            font-weight: 600;
            color: var(--text);
        }

        .user-site {
            font-size: 12px;
            color: var(--text-muted);
        }

        .user-avatar {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, var(--primary), #818cf8);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 15px;
            color: white;
        }

        .btn-logout {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            background: var(--danger-bg);
            border: 1px solid var(--danger-border);
            color: var(--danger);
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-logout:hover {
            background: #fee2e2;
        }

        .btn-logout svg {
            width: 16px;
            height: 16px;
        }

        /* App Layout */
        .app-layout {
            display: flex;
            min-height: calc(100vh - 61px); /* minus topbar */
        }

        /* Sidebar Professional Redesign */
        .sidebar {
            width: 280px;
            background: #ffffff;
            border-right: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 4px 0 24px rgba(15, 23, 42, 0.02);
            padding: 32px 20px;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            gap: 32px;
            overflow-y: auto;
        }

        /* Scrollbar for sidebar */
        .sidebar::-webkit-scrollbar {
            width: 4px;
        }
        .sidebar::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 4px;
        }

        .menu-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .menu-title, .menu-header {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-weight: 700;
            color: #94a3b8;
            padding: 0 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            user-select: none;
        }

        .menu-title svg, .menu-header svg:not(.chevron) {
            width: 18px;
            height: 18px;
            color: var(--primary);
            filter: drop-shadow(0 2px 4px rgba(99, 102, 241, 0.2));
        }

        .menu-header {
            cursor: pointer;
            justify-content: space-between;
            padding: 8px 16px;
            margin: 0;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .menu-header:hover {
            background: #f8fafc;
            color: #475569;
        }

        .menu-header .chevron {
            width: 16px;
            height: 16px;
            color: #cbd5e1;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .menu-header.collapsed .chevron {
            transform: rotate(-90deg);
        }

        .menu-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 4px;
            overflow: hidden;
            transition: max-height 0.3s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.3s ease;
        }

        .menu-list.collapsed {
            max-height: 0;
            opacity: 0;
            pointer-events: none;
        }

        .menu-item {
            position: relative;
            display: flex;
            align-items: center;
            padding: 10px 16px 10px 46px;
            color: #64748b;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            border-radius: 10px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .menu-item::before {
            content: '';
            position: absolute;
            left: 22px;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #cbd5e1;
            transition: all 0.3s ease;
        }

        .menu-item:hover {
            color: var(--primary-dark);
            background: #f8fafc;
            transform: translateX(4px);
        }

        .menu-item:hover::before {
            background: var(--primary);
            box-shadow: 0 0 8px rgba(99, 102, 241, 0.4);
            transform: scale(1.2);
        }

        .menu-item.active {
            color: var(--primary-dark);
            background: linear-gradient(90deg, #eef2ff 0%, transparent 100%);
            font-weight: 600;
        }

        .menu-item.active::after {
            content: '';
            position: absolute;
            left: 0;
            top: 15%;
            height: 70%;
            width: 4px;
            background: var(--primary);
            border-radius: 0 4px 4px 0;
        }

        .menu-item.active::before {
            background: var(--primary);
            box-shadow: 0 0 10px rgba(99, 102, 241, 0.6);
            transform: scale(1.2);
        }

        /* Main content */
        .main-content {
            padding: 32px;
            flex: 1;
            min-width: 0;
            max-width: 100%; /* For full width tables */
            margin: 0;
        }
        
        .main-content-inner {
            max-width: 1100px;
            margin: 0 auto;
        }
        
        .main-content-inner.full-width {
            max-width: 100%;
        }        /* Global Posvelaro UI Framework Classes */
        
        /* Layout */
        .pos-container {
            padding: 24px;
            max-width: 1600px;
            margin: 0 auto;
        }

        /* Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 24px;
        }
        .page-title {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-main);
            margin: 0 0 4px 0;
            letter-spacing: -0.025em;
        }
        .page-subtitle {
            font-size: 14px;
            color: var(--text-muted);
            margin: 0;
        }
        .header-actions {
            display: flex;
            gap: 12px;
        }

        /* KPI Cards */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 24px;
            margin-bottom: 24px;
        }
        .kpi-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: var(--shadow-sm);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        .kpi-icon-wrapper {
            width: 48px;
            height: 48px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .bg-indigo-light { background: var(--primary-light); }
        .bg-blue-light { background: var(--info-bg); }
        .bg-green-light { background: var(--success-bg); }
        .bg-red-light { background: var(--danger-bg); }
        
        .kpi-info {
            display: flex;
            flex-direction: column;
        }
        .kpi-label {
            font-size: 13px;
            font-weight: 500;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 4px;
        }
        .kpi-value {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-main);
            line-height: 1.2;
        }
        .text-success { color: var(--success); }
        .text-danger { color: var(--danger); }
        .text-info { color: var(--info); }
        .text-primary { color: var(--primary); }
        .text-warning { color: var(--warning); }
        .text-muted { color: var(--text-muted) !important; }

        /* Content Card */
        .content-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        /* Toolbar */
        .toolbar {
            padding: 16px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--surface);
        }
        .search-wrapper {
            position: relative;
            width: 360px;
        }
        .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }
        .search-input {
            width: 100%;
            padding: 10px 16px 10px 40px;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            font-family: inherit;
        }
        .search-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 16px;
            border-radius: var(--radius-md);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid transparent;
            font-family: inherit;
        }
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        .btn-primary:hover {
            background: var(--primary-hover);
            box-shadow: var(--shadow-sm);
        }
        .btn-outline {
            background: var(--surface);
            border-color: var(--border);
            color: var(--text-main);
        }
        .btn-outline:hover {
            background: var(--background);
            border-color: var(--border-hover);
        }
        .btn-ghost {
            background: transparent;
            color: var(--text-muted);
        }
        .btn-ghost:hover {
            background: var(--background);
        }

        /* Advanced Filters */
        .advanced-filters {
            background: #fdfdfe;
            border-bottom: 1px solid var(--border);
            padding: 24px;
            display: none; /* Controlled by JS */
        }
        .advanced-filters.show {
            display: block;
            animation: slideDown 0.3s ease-out;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            align-items: flex-end;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .form-label {
            font-size: 13px;
            font-weight: 500;
            color: var(--text-main);
        }
        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            font-size: 13px;
            outline: none;
            transition: all 0.2s;
            font-family: inherit;
            background: var(--surface);
        }
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
        }
        .filters-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px dashed var(--border);
        }
        .btn-reset {
            background: transparent;
            border-color: var(--border);
            color: var(--text-muted);
        }
        .btn-reset:hover {
            background: #fef2f2 !important;
            border-color: #fca5a5 !important;
            color: #ef4444 !important;
        }
        .btn-apply {
            background: var(--primary) !important;
            color: white !important;
        }
        .btn-apply:hover {
            background: var(--primary-hover) !important;
            box-shadow: var(--shadow-sm);
        }

        /* Table */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            min-height: 400px;
        }
        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            text-align: left;
            font-size: 13px;
        }
        .data-table th, .data-table td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
            vertical-align: middle;
        }
        .data-table thead th {
            background: var(--background);
            color: var(--text-muted);
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .data-table tbody tr {
            transition: background-color 0.15s ease;
        }
        .data-table tbody tr:hover {
            background-color: var(--background);
        }
        .filter-row th {
            padding: 8px 16px;
            background: var(--surface);
            border-bottom: 2px solid var(--border);
            top: 45px; /* Offset for sticky header */
            z-index: 9;
        }
        .filter-col {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: 12px;
            outline: none;
            transition: border-color 0.2s;
            background: var(--background);
        }
        .filter-col:focus {
            border-color: var(--primary);
            background: var(--surface);
            box-shadow: 0 0 0 2px var(--primary-light);
        }
        
        /* Typography Utilities */
        .font-medium { font-weight: 500; }
        .font-bold { font-weight: 600; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .truncate-text {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .amount-cell {
            font-family: 'Inter', sans-serif;
            font-variant-numeric: tabular-nums;
            text-align: right;
        }

        /* Badges */
        .modern-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 500;
            white-space: nowrap;
            line-height: 1.5;
        }
        .badge-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
        }
        
        .badge-success { background: var(--success-bg); color: #065f46; }
        .badge-success .badge-dot { background: var(--success); }
        
        .badge-danger { background: var(--danger-bg); color: #991b1b; }
        .badge-danger .badge-dot { background: var(--danger); }
        
        .badge-warning { background: var(--warning-bg); color: #92400e; }
        .badge-warning .badge-dot { background: var(--warning); }
        
        .badge-info { background: var(--info-bg); color: #075985; }
        .badge-info .badge-dot { background: var(--info); }
        
        .badge-purple { background: var(--purple-bg); color: #5b21b6; }
        .badge-purple .badge-dot { background: var(--purple); }
        
        .badge-default { background: var(--default-bg); color: #334155; }
        .badge-default .badge-dot { background: var(--default); }

        /* Empty State */
        .empty-state {
            padding: 48px 24px;
            text-align: center;
            color: var(--text-muted);
        }
        .empty-state svg {
            margin: 0 auto 16px;
            color: var(--border-hover);
        }
        .empty-state p {
            font-size: 16px;
            font-weight: 500;
            color: var(--text-main);
            margin: 0 0 8px;
        }

        /* Totals Footer */
        .table-totals {
            background: var(--background);
        }
        .table-totals td {
            border-top: 2px solid var(--border);
            font-weight: 600;
        }
        .totals-label {
            text-align: right;
            color: var(--text-main);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-size: 12px;
        }

        /* Pagination (Bootstrap 5 markup) */
        .pagination-wrapper {
            padding: 16px 24px;
            background: var(--surface);
            border-top: 1px solid var(--border);
        }
        .pagination-wrapper nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }
        .pagination-wrapper .d-sm-none {
            display: flex !important;
            justify-content: space-between;
            width: 100%;
        }
        .pagination-wrapper .d-none.d-sm-flex {
            display: none !important;
        }
        .pagination-wrapper ul.pagination {
            display: flex;
            list-style: none;
            padding: 0;
            margin: 0;
            gap: 6px;
        }
        .pagination-wrapper .page-item {
            margin: 0;
        }
        .pagination-wrapper .page-link,
        .pagination-wrapper .page-item span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
            padding: 0 14px;
            border-radius: var(--radius-md);
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
        }
        .pagination-wrapper .page-item a.page-link:hover {
            background: var(--background);
            border-color: var(--border-hover);
            color: var(--primary);
        }
        .pagination-wrapper .page-item.active .page-link,
        .pagination-wrapper .page-item.active span {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        .pagination-wrapper .page-item.disabled .page-link,
        .pagination-wrapper .page-item.disabled span {
            background: var(--background);
            color: #cbd5e1;
            border-color: var(--border);
            cursor: not-allowed;
        }
        .pagination-wrapper p {
            color: var(--text-muted);
            font-size: 13px;
            margin: 0;
        }

        /* Select2 Customization */
        .select2-container--default .select2-selection--single {
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            height: 40px;
            display: flex;
            align-items: center;
            background: var(--surface);
        }
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px;
            right: 8px;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: var(--text-main);
            font-size: 13px;
            padding-left: 12px;
        }
        .select2-dropdown {
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-lg);
            font-size: 13px;
            overflow: hidden;
        }
        .select2-search__field {
            border-radius: var(--radius-sm) !important;
            border: 1px solid var(--border) !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__clear {
            height: 38px;
            line-height: 38px;
            margin-right: 12px;
        }

        /* Modal */
        .modal-backdrop {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            z-index: 1000;
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .modal-backdrop.show {
            opacity: 1;
        }
        .modal-content {
            background: var(--surface);
            border-radius: var(--radius-lg);
            padding: 24px;
            max-width: 400px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            transform: scale(0.95);
            transition: transform 0.3s;
        }
        .modal-backdrop.show .modal-content {
            transform: scale(1);
        }
        .modal-close {
            position: absolute;
            top: 16px; right: 16px;
            background: transparent;
            border: none;
            font-size: 24px;
            color: var(--text-muted);
            cursor: pointer;
            line-height: 1;
            transition: color 0.2s;
        }
        .modal-close:hover {
            color: var(--danger);
        }
        .modal-action {
            position: absolute;
            top: 16px; left: 16px;
        }
        .modal-body {
            margin-top: 40px;
            display: flex;
            justify-content: center;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .pos-container { padding: 12px; }
            .toolbar { flex-direction: column; gap: 12px; align-items: stretch; }
            .search-wrapper { width: 100%; }
            .page-header { flex-direction: column; gap: 16px; }
            .header-actions { width: 100%; }
            .header-actions .btn { flex: 1; }
        }

    </style>
    @yield('styles')
</head>
<body>
    <!-- Top bar -->
    <nav class="topbar">
        <a href="{{ route('dashboard') }}" class="topbar-brand" style="text-decoration: none; cursor: pointer;">
            <div class="brand-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <path d="M16 10a4 4 0 01-8 0"/>
                </svg>
            </div>
            <span class="brand-name">Velaro POS</span>
        </a>

        <div class="topbar-user">
            <div class="user-info">
                <div class="user-name">{{ Auth::user()->login ?? 'Utilisateur' }}</div>
                <div class="user-site">Site #{{ Auth::user()->siteid ?? '0' }}</div>
            </div>
            <div class="user-avatar">
                {{ strtoupper(substr(Auth::user()->login ?? 'U', 0, 1)) }}
            </div>
            <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                @csrf
                <button type="submit" class="btn-logout">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    Déconnexion
                </button>
            </form>
        </div>
    </nav>

    <!-- App Layout -->
    <div class="app-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="menu-group">
                <div class="menu-title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                    Vente
                </div>
                <ul class="menu-list">
                    <li>
                        <a href="{{ route('vente.caisse.pos') }}" class="menu-item {{ request()->routeIs('vente.caisse.*') ? 'active' : '' }}">Caisse</a>
                    </li>
                    <li>
                        <a href="{{ route('vente.tickets.index') }}" class="menu-item {{ request()->routeIs('vente.tickets.*') ? 'active' : '' }}">Consultation Tickets</a>
                    </li>
                    <li>
                        <a href="{{ route('vente.commissions.index') }}" class="menu-item {{ request()->routeIs('vente.commissions.*') ? 'active' : '' }}">Calcul Commissions</a>
                    </li>
                    <li>
                        <a href="{{ route('vente.clients.index') }}" class="menu-item {{ request()->routeIs('vente.clients.*') ? 'active' : '' }}">Clients</a>
                    </li>
                </ul>
            </div>

            <div class="menu-group">
                <div class="menu-title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    Journée
                </div>
                <ul class="menu-list">
                    <li>
                        <a href="{{ route('vente.journee.ouverture') }}" class="menu-item {{ request()->routeIs('vente.journee.ouverture') ? 'active' : '' }}">Ouverture Journée</a>
                    </li>
                    <li>
                        <a href="{{ route('vente.journee.cloture') }}" class="menu-item {{ request()->routeIs('vente.journee.cloture') ? 'active' : '' }}">Clôture Journée</a>
                    </li>
                    <li>
                        <a href="{{ route('vente.journee.etat') }}" class="menu-item">
                            Etat Journée
                        </a>
                        <a href="{{ route('vente.journee.index') }}" class="menu-item {{ request()->routeIs('vente.journee.index') ? 'active' : '' }}">Consultation Journées</a>
                    </li>
                </ul>
            </div>

            <div class="menu-group">
                <div class="menu-title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                        <line x1="12" y1="22.08" x2="12" y2="12"></line>
                    </svg>
                    Stock
                </div>
                <ul class="menu-list">
                    <li>
                        <a href="{{ route('stock.articles.index') }}" class="menu-item {{ request()->routeIs('stock.articles.*') ? 'active' : '' }}">Articles</a>
                    </li>
                    <li>
                        <a href="{{ route('stock.consultation.index') }}" class="menu-item {{ request()->routeIs('stock.consultation.*') ? 'active' : '' }}">Consultation Stock</a>
                    </li>
                    <li>
                        <a href="{{ route('stock.detaille.index') }}" class="menu-item {{ request()->routeIs('stock.detaille.*') ? 'active' : '' }}">Stock Détaillé</a>
                    </li>
                    <li>
                        <a href="{{ route('stock.mouvements.index') }}" class="menu-item {{ request()->routeIs('stock.mouvements.*') ? 'active' : '' }}">Mouvements Articles</a>
                    </li>
                    <li>
                        <a href="{{ route('stock.etat.index') }}" class="menu-item {{ request()->routeIs('stock.etat.*') ? 'active' : '' }}">Etat de Stock</a>
                    </li>
                </ul>
            </div>
            <!-- Section Transfert -->
            <div class="menu-group">
                <div class="menu-header" onclick="toggleMenu('transfert-menu')">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                            <line x1="8" y1="21" x2="16" y2="21"></line>
                            <line x1="12" y1="17" x2="12" y2="21"></line>
                        </svg>
                        Transfert
                    </div>
                    <svg class="chevron" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </div>
                <ul class="menu-list" id="transfert-menu">
                    <li>
                        <a href="{{ route('transfert.demande_envoye.index') }}" class="menu-item {{ request()->routeIs('transfert.demande_envoye.*') ? 'active' : '' }}">Demande Trans Envoyé</a>
                    </li>
                    <li>
                        <a href="{{ route('transfert.demande_recu.index') }}" class="menu-item {{ request()->routeIs('transfert.demande_recu.*') ? 'active' : '' }}">Demande Trans reçu</a>
                    </li>
                    <li>
                        <a href="{{ route('transfert.envoye.index') }}" class="menu-item {{ request()->routeIs('transfert.envoye.*') ? 'active' : '' }}">Transfert Envoyé</a>
                    </li>
                    <li>
                        <a href="{{ route('transfert.recu.index') }}" class="menu-item {{ request()->routeIs('transfert.recu.*') ? 'active' : '' }}">Transfert reçu</a>
                    </li>
                </ul>
            </div>

            <!-- Section Paramètres -->
            <div class="menu-group">
                <div class="menu-title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                    </svg>
                    Parametres
                </div>
                <ul class="menu-list">
                    <li><a href="{{ route('parametre.configuration.general') }}" class="menu-item {{ request()->routeIs('parametre.configuration.general') ? 'active' : '' }}">General</a></li>
                    <li><a href="{{ route('parametre.caisse.index') }}" class="menu-item {{ request()->routeIs('parametre.caisse.index') ? 'active' : '' }}">Caisse</a></li>
                    <li><a href="{{ route('parametre.utilisateur.index') }}" class="menu-item {{ request()->routeIs('parametre.utilisateur.*') ? 'active' : '' }}">Gestion des utilisateur</a></li>
                    <li><a href="{{ route('parametre.droit.index') }}" class="menu-item {{ request()->routeIs('parametre.droit.*') ? 'active' : '' }}">Gestion des Droits</a></li>
                    <li><a href="{{ route('parametre.vendeur.index') }}" class="menu-item {{ request()->routeIs('parametre.vendeur.*') ? 'active' : '' }}">Gestion des Vendeurs</a></li>
                    <li><a href="{{ route('parametre.caisse.liberation') }}" class="menu-item {{ request()->routeIs('parametre.caisse.liberation') ? 'active' : '' }}">Libération de caisse</a></li>
                </ul>
            </div>
        </aside>

        <!-- Main content -->
        <main class="main-content">
            @yield('content')
        </main>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function toggleMenu(menuId) {
            const menu = document.getElementById(menuId);
            const header = menu.previousElementSibling;
            
            if (menu.classList.contains('collapsed')) {
                menu.classList.remove('collapsed');
                menu.style.maxHeight = menu.scrollHeight + "px";
                header.classList.remove('collapsed');
                setTimeout(() => {
                    menu.style.maxHeight = 'none';
                }, 300);
            } else {
                menu.style.maxHeight = menu.scrollHeight + "px";
                // Force reflow to ensure the browser registers the transition from a fixed height
                void menu.offsetHeight;
                menu.style.maxHeight = "0";
                menu.classList.add('collapsed');
                header.classList.add('collapsed');
            }
        }
    </script>
    @yield('scripts')
</body>
</html>
