<script setup>
import { Head } from '@inertiajs/vue3';

defineProps({
    medicine: Object,
    transactions: Array,
    openingBalance: Number,
    currentStock: Number,
});

function printStockCard() {
    window.print();
}

function goBack() {
    window.history.back();
}

function formatDate(date) {

    if (!date) {
        return '';
    }

    const parsed =
        new Date(
            date + 'T00:00:00'
        );

    return parsed.toLocaleDateString(
        'en-US',
        {
            month: '2-digit',
            day: '2-digit',
            year: '2-digit',
        }
    );
}

function formatNumber(value) {

    if (
        value === ''
        ||
        value === null
        ||
        value === undefined
    ) {
        return '';
    }

    return Number(
        value
    ).toLocaleString();
}
</script>

<template>

    <Head
        :title="
            `Stock Card - ${medicine.name}`
        "
    />

    <div
        class="stock-card-page"
    >

        <div
            class="screen-toolbar"
        >

            <button
                type="button"
                class="btn-back"
                @click="goBack"
            >
                Back
            </button>

            <button
                type="button"
                class="btn-print"
                @click="printStockCard"
            >
                Print Stock Card
            </button>

        </div>

        <div
            class="print-page"
        >

            <table
                class="stock-card-table"
            >

                <colgroup>

                    <col class="col-date">
                    <col class="col-supplier">
                    <col class="col-in">
                    <col class="col-requester">
                    <col class="col-out">
                    <col class="col-balance">

                </colgroup>

                <thead>

                    <!-- School -->

                    <tr>

                        <td
                            colspan="6"
                            class="school-header"
                        >

                            <div
                                class="school-header-inner"
                            >

                                <div
                                    class="logo-wrapper"
                                >

                                    <img
                                        src="/images/mmaci-logo.png"
                                        alt="MMACI Logo"
                                        class="school-logo"
                                    >

                                </div>

                                <div
                                    class="school-details"
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
                                        class="school-email"
                                    >
                                        E-mail Address: <a href="mailto:mmaci2018.bxu@gmail.com" class="school-email-link">mmaci2018.bxu@gmail.com</a>
                                    </div>

                                    <div
                                        class="office-name"
                                    >
                                        PROPERTY CUSTODIAN OFFICE
                                    </div>

                                </div>

                            </div>

                        </td>

                    </tr>

                    <!-- Title -->

                    <tr>

                        <td
                            colspan="6"
                            class="stock-card-title"
                        >
                            STOCK CARD FORM
                        </td>

                    </tr>

                    <!-- Medicine Information -->

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
                            {{
                                medicine.name
                                    ?.toUpperCase()
                            }}
                        </td>

                        <td
                            colspan="3"
                            class="medicine-info"
                        >

                            <div
                                class="medicine-info-row"
                            >

                                <span
                                    class="medicine-info-label"
                                >
                                    Maximum Quantity:
                                </span>

                                <span>
                                    __________
                                </span>

                            </div>

                            <div
                                class="medicine-info-row"
                            >

                                <span
                                    class="medicine-info-label"
                                >
                                    Reorder Level:
                                </span>

                                <span>
                                    {{
                                        medicine.reorder_level
                                        ??
                                        '__________'
                                    }}
                                </span>

                            </div>

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

                    <template
                        v-for="(transaction, index) in transactions"
                        :key="index"
                    >

                        <!-- Opening Balance -->

                        <tr
                            v-if="
                                transaction.is_opening
                            "
                            class="transaction-row"
                        >

                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>

                            <td
                                class="text-center"
                            >
                                {{
                                    formatNumber(
                                        transaction.balance
                                    )
                                }}
                            </td>

                        </tr>

                        <!-- Transaction -->

                        <tr
                            v-else
                            class="transaction-row"
                        >

                            <td
                                class="text-center"
                            >
                                {{
                                    formatDate(
                                        transaction.date
                                    )
                                }}
                            </td>

                            <td
                                class="text-center"
                            >
                                {{
                                    transaction.supplier
                                        ?.toUpperCase()
                                }}
                            </td>

                            <td
                                class="text-center"
                            >
                                {{
                                    formatNumber(
                                        transaction.in
                                    )
                                }}
                            </td>

                            <td
                                class="text-center requester"
                            >
                                {{
                                    transaction.requester
                                        ?.toUpperCase()
                                }}
                            </td>

                            <td
                                class="text-center"
                            >
                                {{
                                    formatNumber(
                                        transaction.out
                                    )
                                }}
                            </td>

                            <td
                                class="text-center"
                            >
                                {{
                                    formatNumber(
                                        transaction.balance
                                    )
                                }}
                            </td>

                        </tr>

                    </template>

                    <!-- Blank Rows -->

                    <tr
                        v-for="i in Math.max(
                            0,
                            26 -
                            Math.max(
                                0,
                                transactions.length
                            )
                        )"
                        :key="
                            `blank-${i}`
                        "
                        class="transaction-row blank-row"
                    >

                        <td>&nbsp;</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>

                    </tr>

                </tbody>

            </table>

            <!-- Footer -->

            <div
                class="document-control-box"
            >

                <div
                    class="document-row"
                >
                    <span
                        class="document-label"
                    >
                        Form No.:
                    </span>

                    <span
                        class="document-value"
                    >
                        QF-SOP-PMGS-ROGS-02
                    </span>
                </div>

                <div
                    class="document-row"
                >
                    <span
                        class="document-label"
                    >
                        Issued Status:
                    </span>

                    <span
                        class="document-value"
                    >
                        02
                    </span>
                </div>

                <div
                    class="document-row"
                >
                    <span
                        class="document-label"
                    >
                        Revision No.:
                    </span>

                    <span
                        class="document-value"
                    >
                        01
                    </span>
                </div>

                <div
                    class="document-row"
                >
                    <span
                        class="document-label"
                    >
                        Date Approved:
                    </span>

                    <span
                        class="document-value"
                    >
                        27 October 2023
                    </span>
                </div>

                <div
                    class="document-row"
                >
                    <span
                        class="document-label"
                    >
                        Date Effective:
                    </span>

                    <span
                        class="document-value"
                    >
                        1 November 2023
                    </span>
                </div>

            </div>

        </div>

    </div>

</template>

<style scoped>
* {
    box-sizing: border-box;
}

.stock-card-page {
    min-height: 100vh;
    padding: 20px;
    background: #ededed;
    color: #000;
    font-family: Arial, Helvetica, sans-serif;
}

.screen-toolbar {
    width: 100%;
    max-width: 210mm;
    margin: 0 auto 12px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.btn-back,
.btn-print {
    border: 0;
    border-radius: 5px;
    padding: 9px 18px;
    font-size: 14px;
    cursor: pointer;
}

.btn-back {
    background: #5f6368;
    color: #fff;
}

.btn-print {
    background: #155e75;
    color: #fff;
}

.print-page {
    position: relative;
    box-sizing: border-box;
    width: 210mm;
    min-height: 297mm;
    margin: 0 auto;
    padding: 8mm;
    background: #fff;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.12);
}

.stock-card-table {
    print-color-adjust: exact;
    -webkit-print-color-adjust: exact;
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    font-size: 11px;
}

.stock-card-table th,
.stock-card-table td {
    border: 1px solid #000;
    padding: 4px 5px;
    vertical-align: middle;
}

.col-date {
    width: 12%;
}

.col-supplier {
    width: 25%;
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

.school-header {
    padding: 5px 8px !important;
    text-align: center;
}

.school-header-inner {
    min-height: 78px;
    display: inline-flex;
    max-width: 100%;
    gap: 14px;
    align-items: center;
    justify-content: center;
}

.logo-wrapper {
    flex: 0 0 76px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.school-logo {
    width: 76px;
    height: 78px;
    object-fit: contain;
}

.school-details {
    flex: 0 1 auto;
    text-align: center;
    line-height: 1.15;
}

.school-name {
    margin-bottom: 5px;
    font-size: 14px;
    font-weight: 700;
    text-decoration: underline;
}

.school-address {
    font-size: 11px;
}

.school-contact,
.school-email {
    margin-top: 2px;
    font-size: 9px;
}

.school-email-link {
    color: #0563c1;
    text-decoration: underline;
    print-color-adjust: exact;
    -webkit-print-color-adjust: exact;
}

.office-name {
    margin-top: 4px;
    font-size: 13px;
    font-weight: 700;
}

.stock-card-title {
    padding: 4px !important;
    background: #a7cddd;
    background-clip: padding-box;
    text-align: center;
    font-size: 16px;
    font-weight: 700;
}

.item-label {
    font-size: 13px;
    font-weight: 600;
}

.item-name {
    font-size: 14px;
    font-weight: 700;
}

.medicine-info {
    font-size: 12px;
    line-height: 1.5;
}

.medicine-info-row {
    display: flex;
}

.medicine-info-label {
    width: 145px;
}

.column-header th {
    height: 37px;
    text-align: center;
    font-size: 11px;
    line-height: 1.15;
}

.transaction-row td {
    height: 23px;
    font-size: 11px;
}

.text-center {
    text-align: center;
}

.requester {
    font-size: 10px;
}

.blank-row td {
    height: 23px;
}

.document-control-box {
    position: static;
    margin-top: 6mm;
    width: 100mm;
    padding: 4mm 5mm;
    border: 1px solid #000;
    background: #fff;
    font-size: 10px;
}

.document-row {
    display: grid;
    grid-template-columns: 34mm minmax(0, 1fr);
    align-items: baseline;
    min-height: 18px;
}

.document-label {
    white-space: nowrap;
    font-weight: 600;
}

.document-value {
    white-space: nowrap;
    font-weight: 600;
}

@media print {

    @page {
        size: A4 portrait;
        margin: 8mm;
    }

    .stock-card-page {
        min-height: 0;
        padding: 0;
        background: #fff;
    }

    .screen-toolbar {
        display: none !important;
    }

    .print-page {
        width: auto;
        min-height: 0;
        margin: 0;
        padding: 0;
        box-shadow: none;
    }

    .document-control-box {
        break-inside: avoid;
        page-break-inside: avoid;
    }

    thead {
        display: table-header-group;
    }

    tr {
        break-inside: avoid;
        page-break-inside: avoid;
    }
}
</style>
