<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title>
    Stock Card - {{ $medicine->name }}
</title>

<style>

@page {
    size: A4 portrait;
    margin: 8mm;
}

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    padding: 0;
    font-family: Arial, Helvetica, sans-serif;
    color: #000;
    font-size: 10px;
}

.stock-card {
    print-color-adjust: exact;
    -webkit-print-color-adjust: exact;
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}

.stock-card th,
.stock-card td {
    border: 1px solid #000;
    padding: 3px 4px;
    vertical-align: middle;
}

.header-cell {
    padding: 5px !important;
}

.header-table {
    width: 140mm;
    margin: 0 auto;
    border-collapse: collapse;
}

.header-table td {
    border: 0 !important;
}

.logo-cell {
    width: 80px;
    text-align: right;
    padding: 0 10px 0 0 !important;
}

.logo {
    width: 76px;
    height: auto;
}

.school-cell {
    text-align: center;
    padding: 0 !important;
}

.school-name {
    font-size: 14px;
    font-weight: bold;
    text-decoration: underline;
    margin-bottom: 3px;
}

.school-address {
    font-size: 11px;
}

.school-contact {
    margin-top: 2px;
    font-size: 9px;
}

.school-email-link {
    color: #0563c1;
    text-decoration: underline;
}

.school-office {
    margin-top: 3px;
    font-size: 13px;
    font-weight: bold;
}

.title {
    background: #a7cddd;
    background-clip: padding-box;
    text-align: center;
    font-size: 13px;
    font-weight: bold;
    padding: 3px !important;
}

.item-label {
    width: 12%;
    font-weight: bold;
}

.item-name {
    font-size: 12px;
    font-weight: bold;
}

.info-cell {
    font-size: 10px;
    line-height: 1.5;
}

.column-header th {
    text-align: center;
    font-size: 9px;
    height: 32px;
}

.transaction-row td {
    height: 21px;
    font-size: 9px;
}

.center {
    text-align: center;
}

.requester {
    font-size: 8px !important;
}

.col-date {
    width: 13%;
}

.col-supplier {
    width: 24%;
}

.col-in {
    width: 8%;
}

.col-requester {
    width: 25%;
}

.col-out {
    width: 8%;
}

.col-balance {
    width: 22%;
}

.document-control {
    margin-top: 6mm;
    page-break-inside: avoid;
    width: 100mm;
    border: 1px solid #000;
    padding: 4mm;
    font-size: 9px;
}

.document-control table {
    width: 100%;
    table-layout: fixed;
    border-collapse: collapse;
}

.document-control td {
    border: 0;
    padding: 1px 2px;
}

.document-label {
    width: 34mm;
    white-space: nowrap;
    font-weight: bold;
}

.document-value {
    white-space: nowrap;
    font-weight: bold;
}

thead {
    display: table-header-group;
}

tr {
    page-break-inside: avoid;
}

</style>

</head>

<body>

<table class="stock-card">

    <colgroup>

        <col class="col-date">
        <col class="col-supplier">
        <col class="col-in">
        <col class="col-requester">
        <col class="col-out">
        <col class="col-balance">

    </colgroup>

    <thead>

        <!-- School Header -->

        <tr>

            <td
                colspan="6"
                class="header-cell"
            >

                <table class="header-table">

                    <tr>

                        <td
                            class="logo-cell"
                        >

                            @if(
                                file_exists(
                                    public_path(
                                        'images/mmaci-logo.png'
                                    )
                                )
                            )

                                <img
                                    src="{{ public_path('images/mmaci-logo.png') }}"
                                    class="logo"
                                    alt="MMACI Logo"
                                >

                            @endif

                        </td>

                        <td
                            class="school-cell"
                        >

                            <div
                                class="school-name"
                            >
                                MERCHANT MARINE ACADEMY OF CARAGA, INC.
                            </div>

                            <div
                                class="school-address"
                            >
                                North Montilla Boulevard, Brgy. Ong-Yiu, Butuan City, 8600
                            </div>

                            <div
                                class="school-contact"
                            >
                                Tel. No.: (085) 817 0476 · Mobile No.: (+63) 917 105 9644 (Globe)
                            </div>

                            <div
                                class="school-contact"
                            >
                                E-mail Address: <a href="mailto:mmaci2018.bxu@gmail.com" class="school-email-link">mmaci2018.bxu@gmail.com</a>
                            </div>

                            <div
                                class="school-office"
                            >
                                PROPERTY CUSTODIAN OFFICE
                            </div>

                        </td>

                    </tr>

                </table>

            </td>

        </tr>

        <!-- Title -->

        <tr>

            <td
                colspan="6"
                class="title"
            >
                STOCK CARD FORM
            </td>

        </tr>

        <!-- Medicine -->

        <tr>

            <td
                class="item-label"
            >
                Item Name:
            </td>

            <td
                colspan="2"
                class="item-name"
            >
                {{ strtoupper($medicine->name) }}
            </td>

            <td
                colspan="3"
                class="info-cell"
            >

                Maximum Quantity:
                &nbsp;&nbsp;
                __________

                <br>

                Reorder Level:
                &nbsp;&nbsp;

                {{
                    $medicine->reorder_level
                    ??
                    '__________'
                }}

            </td>

        </tr>

        <!-- Columns -->

        <tr
            class="column-header"
        >

            <th>
                DATE OF
                <br>
                TRANSACTION
            </th>

            <th>
                NAME OF SUPPLIER
            </th>

            <th>
                IN
            </th>

            <th>
                NAME OF REQUESTER
            </th>

            <th>
                OUT
            </th>

            <th>
                OUTSTANDING BALANCE
            </th>

        </tr>

    </thead>

    <tbody>

        @foreach(
            $transactions
            as $transaction
        )

            @if(
                $transaction[
                    'is_opening'
                ]
            )

                <tr
                    class="transaction-row"
                >

                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>

                    <td
                        class="center"
                    >
                        {{
                            number_format(
                                $transaction[
                                    'balance'
                                ]
                            )
                        }}
                    </td>

                </tr>

            @else

                <tr
                    class="transaction-row"
                >

                    <td
                        class="center"
                    >
                        {{
                            \Carbon\Carbon::parse(
                                $transaction[
                                    'date'
                                ]
                            )->format(
                                'm/d/y'
                            )
                        }}
                    </td>

                    <td
                        class="center"
                    >
                        {{
                            strtoupper(
                                $transaction[
                                    'supplier'
                                ]
                                ?? ''
                            )
                        }}
                    </td>

                    <td
                        class="center"
                    >

                        @if(
                            $transaction['in']
                            !== ''
                            &&
                            $transaction['in']
                            !== null
                        )

                            {{
                                number_format(
                                    $transaction[
                                        'in'
                                    ]
                                )
                            }}

                        @endif

                    </td>

                    <td
                        class="center requester"
                    >
                        {{
                            strtoupper(
                                $transaction[
                                    'requester'
                                ]
                                ?? ''
                            )
                        }}
                    </td>

                    <td
                        class="center"
                    >

                        @if(
                            $transaction['out']
                            !== ''
                            &&
                            $transaction['out']
                            !== null
                        )

                            {{
                                number_format(
                                    $transaction[
                                        'out'
                                    ]
                                )
                            }}

                        @endif

                    </td>

                    <td
                        class="center"
                    >
                        {{
                            number_format(
                                $transaction[
                                    'balance'
                                ]
                            )
                        }}
                    </td>

                </tr>

            @endif

        @endforeach

        @php

            $nonOpeningTransactions =
                collect(
                    $transactions
                )
                ->where(
                    'is_opening',
                    false
                )
                ->count();

            $blankRows =
                max(
                    0,
                    26
                    -
                    $nonOpeningTransactions
                );

        @endphp

        @for(
            $i = 0;
            $i < $blankRows;
            $i++
        )

            <tr
                class="transaction-row"
            >

                <td>&nbsp;</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>

            </tr>

        @endfor

    </tbody>

</table>

<!-- Document Control -->

<div
    class="document-control"
>

    <table>

        <tr>

            <td
                class="document-label"
            >
                Form No.:
            </td>

            <td
                class="document-value"
            >
                QF-SOP-PMGS-ROGS-02
            </td>

        </tr>

        <tr>

            <td
                class="document-label"
            >
                Issued Status:
            </td>

            <td
                class="document-value"
            >
                02
            </td>

        </tr>

        <tr>

            <td
                class="document-label"
            >
                Revision No.:
            </td>

            <td
                class="document-value"
            >
                01
            </td>

        </tr>

        <tr>

            <td
                class="document-label"
            >
                Date Approved:
            </td>

            <td
                class="document-value"
            >
                27 October 2023
            </td>

        </tr>

        <tr>

            <td
                class="document-label"
            >
                Date Effective:
            </td>

            <td
                class="document-value"
            >
                1 November 2023
            </td>

        </tr>

    </table>

</div>

</body>

</html>
