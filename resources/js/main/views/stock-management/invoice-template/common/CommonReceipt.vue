<template>
    <div class="loader" v-if="loading">
        <a-spin></a-spin>
    </div>
    <div v-else>
        <!-- <div class="receipt-wrap">
            <a-button type="primary" v-print="'#receipt_invoice'">
                <template #icon>
                    <PrinterOutlined />
                </template>
                {{ $t("common.print") }}
            </a-button>
        </div> -->
        <div id="receipt_invoice" class="receipt-wrap receipt-four">
            <div class="receipt-top">
                <div
                    class="receipt-seperator"
                    v-if="invoiceTemplate.header.logo"
                ></div>
                <div class="company-logo" v-if="invoiceTemplate.header.logo">
                    <img
                        class="invoice-logo"
                        :src="order.warehouse.logo_url"
                        :alt="order.warehouse.name"
                    />
                </div>
                <div class="receipt-seperator"></div>
                <div
                    class="receipt-title"
                    v-if="invoiceTemplate.header.recipt_title"
                >
                    Payment Receipt
                </div>
                <div
                    class="receipt-title"
                    v-if="invoiceTemplate.header.company_name"
                >
                    {{ order.warehouse.name }}
                </div>
                <!-- <div
                    class="company-name"
                    v-if="invoiceTemplate.header.company_full_name"
                >
                    {{ invoiceTemplate.company_full_name }}
                </div> -->
                <div
                    class="company-address"
                    v-if="invoiceTemplate.header.company_address"
                >
                    {{ order.warehouse.address }}
                </div>
                <div
                    class="company-email"
                    v-if="invoiceTemplate.header.company_email"
                >
                    {{ $t("common.email") }}: {{ order.warehouse.email }}
                </div>
                <div
                    class="company-no"
                    v-if="invoiceTemplate.header.company_no"
                >
                    {{ $t("common.phone") }}: {{ order.warehouse.phone }}
                </div>
                <div
                    class="company-no"
                    v-if="
                        invoiceTemplate.header.tax_no &&
                        order.warehouse.gst_in_no
                    "
                >
                    {{ $t("tax.tax_no") }}:
                    {{ order.warehouse.gst_in_no }}
                </div>
            </div>
            <div
                class="receipt-seperator"
                v-if="
                    invoiceTemplate.header.recipt_title ||
                    invoiceTemplate.header.company_full_name ||
                    invoiceTemplate.header.company_address ||
                    invoiceTemplate.header.company_email ||
                    invoiceTemplate.header.company_no ||
                    invoiceTemplate.header.company_name
                "
            ></div>
            <ul class="customer-list">
                <li v-if="invoiceTemplate.customer_details.name">
                    <div class="title">{{ $t("stock.customer") }}:</div>
                    <div class="desc">
                        {{
                            order.user.name == "Walk In Customer"
                                ? "WC"
                                : order.user.name
                        }}
                    </div>
                </li>
                <li
                    class="text-right me-0"
                    v-if="invoiceTemplate.customer_details.invoice_no"
                >
                    <div class="title">{{ $t("sales.invoice") }}:</div>
                    <div class="desc">{{ order.invoice_number }}</div>
                </li>
                <li
                    class="text-right me-0"
                    v-if="invoiceTemplate.customer_details.address"
                >
                    <div class="title">{{ $t("user.address") }}:</div>
                    <div class="desc">
                        {{
                            order.user && order.user.address
                                ? order.user.address
                                : "-"
                        }}
                    </div>
                </li>
                <!-- <li v-if="invoiceTemplate.customer_details.staff_name">
                    <div class="title">{{ $t("stock.sold_by") }}:</div>
                    <div class="desc">{{ order.staff_member.name }}</div>
                </li> -->
                <li
                    class="text-right me-0"
                    v-if="invoiceTemplate.customer_details.phone"
                >
                    <div class="title">{{ $t("common.phone") }}:</div>
                    <div class="desc">{{ order.user.phone }}</div>
                </li>
                <li
                    class="text-right me-0"
                    v-if="invoiceTemplate.customer_details.date"
                >
                    <div class="title">{{ $t("common.date") }}:</div>
                    <div class="desc">
                        {{ formatDateTime(order.order_date) }}
                    </div>
                </li>
                <li
                    class="text-right me-0"
                    v-if="invoiceTemplate.customer_details.tax_no"
                >
                    <div class="title">{{ $t("tax.tax_no") }}:</div>
                    <div class="desc">
                        {{
                            order.user && order.user.tax_number
                                ? order.user.tax_number
                                : "-"
                        }}
                    </div>
                </li>
            </ul>

            <div
                class="receipt-seperator"
                v-if="
                    invoiceTemplate.customer_details.date ||
                    invoiceTemplate.customer_details.invoice_no ||
                    invoiceTemplate.customer_details.name ||
                    invoiceTemplate.customer_details.staff_name
                "
            ></div>
            <table class="receipt-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ $t("common.item") }}</th>
                        <th>{{ $t("common.qty") }}</th>
                        <th>{{ $t("common.rate") }}</th>
                        <th>{{ $t("common.total") }}</th>
                    </tr>
                </thead>
                <tbody>
                    {{
                        calculationBasedOnGST(order)
                    }}
                    <tr v-for="(item, index) in order.items" :key="item.xid">
                        <td>{{ index + 1 }}</td>
                        <td>{{ item.product.name }}</td>
                        <td>
                            {{ item.quantity + "" + item.unit.short_name }}
                        </td>
                        <td style="text-align: right">
                            {{
                                `${order.currency.symbol}${formatAmount(
                                    item.unit_price
                                )}`
                            }}
                        </td>
                        <td style="text-align: right">
                            {{
                                `${order.currency.symbol}${formatAmount(
                                    item.subtotal
                                )}`
                            }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="receipt-seperator"></div>
            <div class="bill-list">
                <div
                    class="bill_list_in"
                    v-if="invoiceTemplate.total_details.sub_total"
                >
                    <div class="bill_title">Sub-Total:</div>
                    <div class="bill_value">
                        {{
                            `${order.currency.symbol}${formatAmount(
                                order.subtotal
                            )}`
                        }}
                    </div>
                </div>
                <div
                    class="bill_list_in"
                    v-if="
                        invoiceTemplate.total_details.discount &&
                        order.discount != 0
                    "
                >
                    <div class="bill_title">{{ $t("stock.discount") }}:</div>
                    <div class="bill_value">
                        {{
                            `${order.currency.symbol}${formatAmount(
                                order.discount
                            )}`
                        }}
                    </div>
                </div>
                <div
                    class="receipt-seperator"
                    v-if="
                        invoiceTemplate.total_details.sub_total ||
                        invoiceTemplate.total_details.discount
                    "
                ></div>
                <div
                    class="bill_list_in"
                    v-if="
                        invoiceTemplate.total_details.service_charges &&
                        order.shipping != 0
                    "
                >
                    <div class="bill_title">{{ $t("stock.shipping") }}:</div>
                    <div class="bill_value">
                        {{
                            `${order.currency.symbol}${formatAmount(
                                order.shipping
                            )}`
                        }}
                    </div>
                </div>
                <div
                    class="bill_list_in"
                    v-if="
                        invoiceTemplate.total_details.tax &&
                        order.tax_amount != 0
                    "
                >
                    <div class="bill_title">{{ $t("stock.order_tax") }}:</div>
                    <div class="bill_value">
                        {{
                            `${order.currency.symbol}${formatAmount(
                                order.tax_amount
                            )}`
                        }}
                    </div>
                </div>
                <div
                    class="receipt-seperator"
                    v-if="
                        invoiceTemplate.total_details.service_charges ||
                        invoiceTemplate.total_details.tax
                    "
                ></div>
                <div
                    class="bill_list_in"
                    v-if="
                        invoiceTemplate.total_details.due &&
                        order.due_amount > 0
                    "
                >
                    <div class="bill_title bill_focus">
                        {{ $t("payments.due_amount") }}:
                    </div>
                    <div class="bill_value bill_focus">
                        {{
                            `${order.currency.symbol}${formatAmount(
                                order.due_amount
                            )}`
                        }}
                    </div>
                </div>
                <div
                    class="bill_list_in"
                    v-if="
                        invoiceTemplate.total_details.due &&
                        order.due_amount <= 0
                    "
                >
                    <div class="bill_title bill_focus">
                        {{ $t("stock.change_return") }}:
                    </div>
                    <div class="bill_value bill_focus">
                        {{
                            `${order.currency.symbol}${formatAmount(
                                order.due_amount
                            )
                                .toString()
                                .replace("-", "")}`
                        }}
                    </div>
                </div>
                <div class="bill_list_in total-payable">
                    <div class="bill_title bill_focus">
                        {{ $t("common.total") }}:
                    </div>
                    <div class="bill_value bill_focus">
                        {{
                            `${order.currency.symbol}${formatAmount(
                                order.total
                            )}`
                        }}
                    </div>
                </div>
            </div>
            <div class="receipt-seperator"></div>
            <div
                class="receipt-seperator"
                v-if="
                    order.warehouse.gst_in_no != null &&
                    invoiceTemplate.tax_wise_calculations.enabled
                "
            ></div>
            <div
                class="receipt_invoice_footer_calculation"
                v-if="
                    order.warehouse.gst_in_no != null &&
                    invoiceTemplate.tax_wise_calculations.enabled
                "
            >
                <div
                    class="receipt_footer_calculation"
                    v-if="!checkStartingDigits"
                >
                    <table>
                        <thead>
                            <tr>
                                <th>Taxable Amt</th>
                                <th>{{ $t("tax.c_tax") }} %</th>
                                <th>{{ $t("tax.c_tax") }} Amt</th>
                                <th>{{ $t("tax.s_tax") }} %</th>
                                <th>{{ $t("tax.s_tax") }} Amt</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(tax, key) in taxDifProducts" :key="key">
                                <td>
                                    {{
                                        `${
                                            order.currency.symbol
                                        } ${formatAmount(tax.totalAmountSum)}`
                                    }}
                                </td>
                                <td>{{ key / 2 }}%</td>
                                <td>
                                    {{
                                        `${
                                            order.currency.symbol
                                        } ${formatAmount(tax.taxAmountSum / 2)}`
                                    }}
                                </td>
                                <td>{{ key / 2 }}%</td>
                                <td>
                                    {{
                                        `${
                                            order.currency.symbol
                                        } ${formatAmount(tax.taxAmountSum / 2)}`
                                    }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <b>{{
                                        `${
                                            order.currency.symbol
                                        } ${formatAmount(totalAmountSum)}`
                                    }}</b>
                                </td>
                                <td></td>

                                <td>
                                    <b>{{
                                        `${
                                            order.currency.symbol
                                        } ${formatAmount(totalTax / 2)}`
                                    }}</b>
                                </td>
                                <td></td>
                                <td>
                                    <b>{{
                                        `${
                                            order.currency.symbol
                                        } ${formatAmount(totalTax / 2)}`
                                    }}</b>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="receipt_footer_calculation" v-else>
                    <table>
                        <thead>
                            <tr>
                                <th>Taxable Amt</th>
                                <th>{{ $t("tax.i_tax") }} %</th>
                                <th>{{ $t("tax.i_tax") }} Amt</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(tax, key) in taxDifProducts" :key="key">
                                <td>
                                    {{
                                        `${
                                            order.currency.symbol
                                        } ${formatAmount(tax.totalAmountSum)}`
                                    }}
                                </td>
                                <td>{{ key }}%</td>
                                <td>
                                    {{
                                        `${
                                            order.currency.symbol
                                        } ${formatAmount(tax.taxAmountSum)}`
                                    }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <b>{{
                                        `${
                                            order.currency.symbol
                                        } ${formatAmount(totalAmountSum)}`
                                    }}</b>
                                </td>
                                <td></td>
                                <td>
                                    <b>{{
                                        `${
                                            order.currency.symbol
                                        } ${formatAmount(totalTax / 2)}`
                                    }}</b>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="receipt-seperator"></div>
            <div
                class="receipt-seperator"
                v-if="invoiceTemplate.footer.barcode"
            ></div>
            <div class="sample_text" v-if="invoiceTemplate.footer.barcode">
                <vue-barcode
                    :value="order.invoice_number"
                    :options="{ height: 25, width: 1, fontSize: 15 }"
                    tag="svg"
                ></vue-barcode>
            </div>
            <div
                class="receipt-seperator"
                v-if="invoiceTemplate.footer.qr_code"
            ></div>

            <div
                class="sample_text"
                v-if="invoiceTemplate.footer.qr_code && order.warehouse.upi_id"
            >
                <div style="text-align: center">
                    <QrcodeVue
                        :value="
                            QrCodeValue(order.warehouse.upi_id, order.total)
                        "
                        level="H"
                    />
                    <p style="font-size: 10px; margin: 0">
                        <QrcodeOutlined style="color: #000" />
                        {{ order.warehouse.upi_id }}
                    </p>
                </div>
            </div>
            <div class="receipt-seperator"></div>
            <div class="sample_text" v-if="invoiceTemplate.footer.message">
                {{ invoiceTemplate.thanks_message }}
            </div>
            <div class="watermark_text" v-if="invoiceTemplate.footer.watermark">
                Powered by YESERP.online
            </div>
        </div>
    </div>
</template>
<script>
import axios from "axios";
import { ref, defineComponent, computed, onMounted } from "vue";
import { useRoute } from "vue-router";
import {
    PrinterOutlined,
    WhatsAppOutlined,
    QrcodeOutlined,
} from "@ant-design/icons-vue";
import common from "../../../../../common/composable/common";
import QrcodeVue from "qrcode.vue";
import _ from "lodash-es"
export default defineComponent({
    components: {
        PrinterOutlined,
        WhatsAppOutlined,
        QrcodeOutlined,
        QrcodeVue,
    },
    setup() {
        const { formatDateTime, numberToWords, QrCodeValue } = common();

        const route = useRoute();
        const order = ref({});
        const invoiceTemplate = ref({});
        const loading = ref(true);

        onMounted(() => {
            axios
                .post(`/api/ordersget/${route.params.id}`)
                .then((res) => {
                    invoiceTemplate.value = JSON.parse(
                        res.data.data[0].invoice_template
                    );
                    order.value = res.data.data[0];
                    loading.value = false;
                })
                .catch((err) => {
                    loading.value = false;
                });
        });

        const checkStartingDigits = computed(() => {
            if (
                order.value &&
                order.value.user &&
                order.value.user.tax_number &&
                order.value.warehouse.gst_in_no
            ) {
                const warehouseGSTNumber = order.value.warehouse.gst_in_no
                    ? order.value.warehouse.gst_in_no
                    : 0;
                const userTaxNumber = order.value.user.tax_number;

                // Compare the first two characters of the strings and return the result
                return (
                    warehouseGSTNumber.substring(0, 2) !=
                    userTaxNumber.substring(0, 2)
                );
            } else {
                // Handle the case when props.order.order is undefined
                return false;
            }
        });

        const totalTax = ref("");
        const totalAmountSum = ref("");
        const taxDifProducts = ref({});

        const calculationBasedOnGST = () => {
            // Group products by tax_rate
            const groupedProducts = _.groupBy(order.value.items, "tax_rate");

            // Calculate the sum of tax_amount and total_amount for each tax_rate
            const result = _.mapValues(groupedProducts, (productList) => {
                const taxAmountSum = _.sumBy(productList, "total_tax");
                const totalAmountSum = _.sumBy(
                    productList,
                    (product) => product.subtotal - product.total_tax
                );
                return { taxAmountSum, totalAmountSum };
            });

            // Calculate the sum of taxAmountSum and totalAmountSum from the result object
            let sumOfTaxAmountSum = 0;
            let sumOfTotalAmountSum = 0;

            for (const key in result) {
                if (result.hasOwnProperty(key)) {
                    sumOfTaxAmountSum += parseFloat(result[key].taxAmountSum);
                    sumOfTotalAmountSum += parseFloat(
                        result[key].totalAmountSum
                    );
                }
            }
            totalTax.value = sumOfTaxAmountSum;
            totalAmountSum.value = sumOfTotalAmountSum;
            taxDifProducts.value = result;
        };

        const formatAmount = (amount) => {
            return parseFloat(amount).toFixed(2);
        };

        return {
            formatDateTime,
            formatAmount,
            numberToWords,
            calculationBasedOnGST,
            totalTax,
            totalAmountSum,
            taxDifProducts,
            checkStartingDigits,
            order,
            loading,
            invoiceTemplate,
            QrCodeValue,
        };
    },
});
</script>
