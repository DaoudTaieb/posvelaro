<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Caisse {{ $siteName }}</title>
    <link href="{{ asset('vendor/fonts/fonts.css') }}" rel="stylesheet">
    <style>
        :root {
            --primary: #0ea5e9;
            --primary-light: #e0f2fe;
            --border: #e2e8f0;
            --text-secondary: #64748b;
        }
        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            background-color: #f8f9fa; /* slightly grey bg */
            overflow: hidden; /* no scrolling on main POS */
        }

        .pos-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
            padding: 5px;
        }

        /* HEADER */
        .pos-header {
            display: flex;
            align-items: flex-start;
            padding: 5px;
            background: white;
            margin-bottom: 5px;
            font-size: 11px;
        }

        .header-title {
            font-family: 'Press Start 2P', monospace;
            font-size: 16px;
            margin: 0;
            padding: 10px;
            width: 350px;
        }

        .header-fields {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            flex: 1;
        }

        .field-group {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .field-group label {
            font-weight: 600;
            color: var(--text-secondary);
        }

        .field-group input {
            border: 1px solid #d1d5db;
            padding: 4px;
            border-radius: 3px;
            outline: none;
        }

        .btn-lookup {
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            padding: 2px 8px;
            cursor: pointer;
            border-radius: 3px;
        }

        .header-stats {
            display: flex;
            gap: 15px;
            font-size: 11px;
            font-weight: 600;
            margin-left: auto;
        }

        /* MAIN AREA */
        .pos-main {
            display: flex;
            flex: 1;
            overflow: hidden;
        }

        /* TICKET TABLE AREA */
        .ticket-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: white;
            margin: 5px;
            border: 1px solid var(--border);
            border-radius: 4px;
        }

        .ticket-table-container {
            flex: 1;
            overflow: auto;
        }

        .ticket-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        .ticket-table th {
            text-align: left;
            padding: 8px;
            border-bottom: 1px solid var(--border);
            font-weight: 600;
            background: #f9fafb;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .ticket-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #f3f4f6;
        }

        .ref-input {
            width: 100%;
            border: 1px solid #d1d5db;
            padding: 4px;
            outline: none;
        }
        
        .ticket-footer {
            padding: 10px;
            background: #f9fafb;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            font-weight: 600;
        }

        /* RIGHT PANEL */
        .pos-right {
            width: 150px;
            background: white;
            border-left: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .grand-total {
            background: white;
            border: 1px solid black;
            text-align: right;
            padding: 15px 10px;
            font-family: 'Share Tech Mono', monospace;
            font-size: 28px;
            font-weight: bold;
            letter-spacing: 2px;
        }

        .btn-validate {
            background: white;
            border: 1px solid black;
            border-radius: 4px;
            cursor: pointer;
            height: 80px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .btn-validate:hover {
            background: #f3f4f6;
        }

        .btn-validate svg {
            width: 48px;
            height: 48px;
        }

        .right-small-btns {
            display: flex;
            gap: 5px;
        }

        .btn-square {
            flex: 1;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            height: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
        }

        .btn-square:hover {
            background: #f3f4f6;
        }

        /* BOTTOM ACTIONS GRID */
        .pos-footer-grid {
            margin-top: 5px;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2px;
            background: white;
        }

        .grid-header {
            background: black;
            color: white;
            text-align: center;
            padding: 6px;
            font-size: 12px;
            font-weight: bold;
        }

        .grid-col {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .grid-btn {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 12px;
            text-align: center;
            font-size: 11px;
            cursor: pointer;
            flex: 1;
        }

        .grid-btn:hover {
            background: #f3f4f6;
        }

        .split-col {
            display: flex;
            gap: 2px;
            flex: 1;
        }

        .split-col .grid-btn {
            flex: 1;
        }
        
        .display-value {
            font-family: 'Share Tech Mono', monospace;
            font-size: 32px;
            color: black;
            text-transform: uppercase;
        }
        
        .align-right { text-align: right !important; }

        /* Editable cells */
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { 
            -webkit-appearance: none; 
            margin: 0; 
        }
        input[type=number] {
            -moz-appearance: textfield;
        }
        .editable-cell {
            border: 1px solid transparent;
            background: transparent;
            font-family: inherit;
            font-size: inherit;
            color: inherit;
            text-align: right;
            width: 60px;
            outline: none;
            cursor: text;
        }
        .editable-cell:focus {
            background: #fff;
            border: 1px solid #3b82f6;
            border-radius: 3px;
        }

        /* MODAL STYLES */
        .form-group {
            display: flex;
            gap: 5px;
            align-items: center;
        }
        .form-group label {
            white-space: nowrap;
            font-size: 11px;
            font-weight: 600;
        }
        .form-control {
            border: 1px solid #d1d5db;
            padding: 4px;
            border-radius: 4px;
            font-size: 11px;
            outline: none;
        }
        .btn-save {
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-save:hover { background: #f3f4f6; }
        
        .cart-row:hover { background-color: #f1f5f9; }
        
        /* Pagination Styles for Modal */
        .pagination-wrapper {
            padding: 10px;
            background: #fff;
            border-top: 1px solid #e5e7eb;
        }
        .pagination-wrapper svg {
            width: 1.25rem;
            height: 1.25rem;
        }
        .pagination-wrapper nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
        }
        .pagination-wrapper nav > div:first-child {
            display: none;
        }
        .pagination-wrapper nav > div:last-child {
            display: flex;
            justify-content: space-between;
            width: 100%;
            align-items: center;
        }
        .pagination-wrapper p {
            margin: 0;
            color: #64748b;
        }
        .pagination-wrapper span.relative,
        .pagination-wrapper a.relative {
            display: inline-flex;
            align-items: center;
            padding: 6px 10px;
            margin-left: -1px;
            background: #fff;
            border: 1px solid #e2e8f0;
            color: #64748b;
            text-decoration: none;
            transition: background 0.2s;
        }
        .pagination-wrapper a.relative:hover {
            background: #f1f5f9;
            color: #0ea5e9;
        }
        .pagination-wrapper span[aria-current="page"] > span {
            background: #e0f2fe;
            color: #0ea5e9;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            padding: 6px 10px;
            border: 1px solid #e2e8f0;
            margin-left: -1px;
        }
        
        .modal-content .table-container table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        .modal-content .table-container th {
            text-align: left;
            padding: 8px;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #4b5563;
            background: white;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .modal-content .table-container td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
            color: #111827;
        }
        .modal-content .table-container tr:hover td {
            background: #f8fafc;
        }
    </style>
</head>
