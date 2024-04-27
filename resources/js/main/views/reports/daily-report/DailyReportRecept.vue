<template>
    <a-modal
        :visible="visible"
        :centered="true"
        :maskClosable="false"
        title="Daily Report"
        width="400px"
        @cancel="onClose"
        :footer="false"
    >
        <div class="footer-button">
            <a-button
                type="primary"
                id="printButton"
                v-print="'#daily_report'"
                style="margin-right: 5px"
            >
                <template #icon>
                    <PrinterOutlined />
                </template>
                {{ $t("common.press_p") }}
            </a-button>
        </div>
        <div id="daily_report" class="receipt-wrap receipt-four">
            <div class="receipt-top">
                <div
                    class="receipt-seperator"
                    v-if="invoiceTemplate.header.logo"
                ></div>
                <div class="company-logo" v-if="invoiceTemplate.header.logo">
                    <img
                        class="invoice-logo"
                        :src="appSetting.warehouse.dark_logo_url"
                        :alt="appSetting.warehouse.name"
                    />
                </div>
                <div class="report-receipt-title">DAY END</div>
                <div class="report-receipt-title">SALES AND TAXES SUMMARY</div>
                <table class="overall-report-section">
                    <tbody>
                        <tr>
                            <td>ORDER AVG: {{ report.avg_order }}</td>
                            <td>CUSTOMER AVG: {{ report.avg_customer }}</td>
                        </tr>
                        <tr>
                            <td>
                                TOTAL SALES:
                                {{ formatAmountCurrency(report.total_sales) }}
                            </td>
                            <td>NO. OF ITEMS: {{ report.total_items }}</td>
                        </tr>
                        <!-- <tr>
                            <td>TOTAL DISCOUNT: {{ formatAmountCurrency(report.total_discount) }}</td>
                            <td>OTHER CHARGES: {{ formatAmountCurrency(report.other_charges) }}</td>
                        </tr> -->
                        <tr>
                            <td>
                                ORDER AVG:
                                {{
                                    formatAmountCurrency(report.avg_order_value)
                                }}
                            </td>
                            <td></td>
                        </tr>
                        <tr v-if="report.staff_member.length > 0">
                            <td>
                                <b>STAFF:</b> {{ report.staff_member[0].name }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <b>Date:</b> {{ `${report.start_date} - ${report.end_date}` }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="receipt-seperator"></div>
            <div class="report-receipt-title">HOURLY SALES</div>
            <div class="receipt-seperator"></div>
            <table class="report-receipt-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Hour</th>
                        <th>Avg</th>
                        <th>Avg Value</th>
                        <th>Total Sales</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(item, index) in report.hourly" :key="index">
                        <td>{{ index + 1 }}</td>
                        <td>
                            {{ item.hour }}
                        </td>
                        <td>
                            {{ item.order_count }}
                        </td>
                        <td style="text-align: right">
                            {{ formatAmountCurrency(item.average_order) }}
                        </td>
                        <td style="text-align: right">
                            {{ formatAmountCurrency(item.total_sales) }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="receipt-seperator"></div>
            <div class="report-receipt-title">CATEGORY SALES</div>
            <div class="receipt-seperator"></div>
            <table class="report-receipt-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Category</th>
                        <th>Qty</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(item, index) in report.category" :key="index">
                        <td>{{ index + 1 }}</td>
                        <td>
                            {{ item.category_name }}
                        </td>
                        <td>
                            {{ item.total_quantity }}
                        </td>
                        <td style="text-align: right">
                            {{ formatAmountCurrency(item.total_value) }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="receipt-seperator"></div>
            <div class="report-receipt-title">PRODUCT SALES</div>
            <div class="receipt-seperator"></div>
            <table class="report-receipt-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Qty</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(item, index) in report.product" :key="index">
                        <td>{{ index + 1 }}</td>
                        <td style="width: 42%;">
                            {{ item.product_name }}
                        </td>
                        <td>
                            {{ item.total_quantity }}
                        </td>
                        <td style="text-align: right">
                            {{ formatAmountCurrency(item.total_value) }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="receipt-seperator"></div>
            <div class="report-receipt-title">PAYMENT DETAILS</div>
            <div class="receipt-seperator"></div>
            <table class="report-receipt-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Payment</th>
                        <th>Orders</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(item, index) in report.payments" :key="index">
                        <td>{{ index + 1 }}</td>
                        <td>
                            {{ item.payment_mode }}
                        </td>
                        <td>
                            {{ item.total_orders }}
                        </td>
                        <td style="text-align: right">
                            {{ formatAmountCurrency(item.total_amount) }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="receipt-seperator"></div>
        </div>
    </a-modal>
</template>

<script>
import { ref, defineComponent, onBeforeUnmount, onMounted } from "vue";
import { PrinterOutlined, WhatsAppOutlined } from "@ant-design/icons-vue";
import common from "../../../../common/composable/common";
const posInvoiceCssUrl = window.config.pos_invoice_css;

export default defineComponent({
    props: ["visible", "report"],
    emits: ["closed", "success"],
    components: {
        PrinterOutlined,
        WhatsAppOutlined,
    },
    setup(props, { emit }) {
        const {
            appSetting,
            formatAmountCurrency,
            formatDate,
            selectedWarehouse,
        } = common();
        const company = appSetting.value;
        const invoiceTemplate = ref(JSON.parse(company.invoice_template));

        const onClose = () => {
            emit("closed");
        };

        const handleKeyPress = (event) => {
            if (
                event.key === "p" &&
                document.activeElement.nodeName === "DIV"
            ) {
                const printButton = document.getElementById("printButton");
                if (printButton) {
                    printButton.click();
                }
            }
        };
        // Listen for the key press event on component mount
        onMounted(() => {
            window.addEventListener("keydown", handleKeyPress);
        });

        onBeforeUnmount(() => {
            window.removeEventListener("keydown", handleKeyPress);
        });

        return {
            appSetting,
            onClose,
            formatDate,
            selectedWarehouse,
            formatAmountCurrency,
            invoiceTemplate,
        };
    },
});
</script>
<style>
.report-receipt-title {
    text-align: center;
    font-weight: bold;
}
.report-receipt-table,
.overall-report-section {
    width: 100%;
    color: #000;
}
.report-receipt-table > thead > tr > th:nth-child(4),
.report-receipt-table > thead > tr > th:nth-child(5) {
    text-align: right;
}
.report-receipt-table > thead > tr > th:nth-child(3),
.report-receipt-table > tbody > tr > td:nth-child(3) {
    text-align: center;
}

.overall-report-section > tbody > tr > td:nth-child(1) {
    text-align: left;
}
.overall-report-section > tbody > tr > td:nth-child(2) {
    text-align: right;
}
</style>
