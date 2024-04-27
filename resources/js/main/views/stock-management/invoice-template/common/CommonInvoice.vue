<template>
    <div>
        <div class="loader" v-if="loading">
            <a-spin></a-spin>
        </div>
        <div
            v-else
            style="padding: 20px; width: 100%; max-width: 56%; margin: 0 auto"
        >
            <div class="footer-button" style="margin: 20px">
                <a-button type="primary" @click="exportToPDF">
                    <template #icon>
                        <FilePdfOutlined />
                    </template>
                    PDF
                </a-button>
            </div>
            <div id="sales_a4_invoice" class="A4_invoice">
                <header class="a4_invoie_header_wraper">
                    <div class="a4_invoie_header">
                        <div class="a4_invoie_header_title">
                            <u>{{
                                order.order_type == "quotations"
                                    ? "Quotation"
                                    : "Invoice"
                            }}</u>
                        </div>
                        <div
                            class="a4_invoie_header_logo"
                            v-if="
                                invoiceTemplate.header.logo &&
                                order.warehouse.logo_url
                            "
                        >
                            <img
                                class="invoice-logo"
                                :src="order.warehouse.logo_url"
                            />
                        </div>
                        <div class="a4_invoie_header_title">
                            <p>{{ order.warehouse.name }}</p>
                        </div>
                        <div
                            class="a4_invoie_gstin_title"
                            v-if="order.warehouse && order.warehouse.address"
                        >
                            <p>{{ order.warehouse.address }}</p>
                        </div>
                        <div class="a4_invoie_contact_info">
                            <p>Tel: {{ order.warehouse.phone }}</p>
                            <p>Email: {{ order.warehouse.email }}</p>
                        </div>
                        <div
                            class="a4_invoie_gstin_title"
                            v-if="order.warehouse && order.warehouse.gst_in_no"
                        >
                            <p>
                                {{ $t("tax.tax_no") }}:
                                {{ order.warehouse.gst_in_no }}
                            </p>
                        </div>
                    </div>
                    <div class="a4_invoice_order_details_wrapper">
                        <div class="a4_invoice_customer_details">
                            <div
                                class="a4_invoice_customer_address"
                                v-if="order && order.user && order.user.name"
                            >
                                <div>Party Details</div>
                            </div>
                            <div class="text">
                                <b> {{ order.user.name }}</b>
                                <br />
                                {{
                                    order.order_type == "online-orders"
                                        ? order.orderShippingAddress &&
                                          order.orderShippingAddress
                                              .shipping_address
                                            ? order.orderShippingAddress
                                                  .shipping_address
                                            : "Self Pickup"
                                        : order && order.user
                                        ? order.user.address
                                        : ""
                                }}
                            </div>
                            <div
                                style="font-size: 12px; text-align: left"
                                v-if="
                                    order &&
                                    order.user &&
                                    order.user.email &&
                                    invoiceTemplate.customer_details.email
                                "
                            >
                                {{ order.user.email }}
                            </div>
                            <div
                                style="font-size: 12px; text-align: left"
                                v-if="
                                    order &&
                                    order.user &&
                                    order.user.phone &&
                                    invoiceTemplate.customer_details.phone
                                "
                            >
                                {{ order.user.phone }}
                            </div>
                            <div
                                style="font-size: 12px; text-align: left"
                                v-if="
                                    order && order.user && order.user.tax_number
                                "
                            >
                                {{ $t("tax.tax_no") }}/UIN :
                                {{ order.user.tax_number }}
                            </div>
                            <div
                                class="a4_invoice_customer"
                                v-if="order && order.delivery_to"
                            >
                                <div>Delivered To</div>
                                <div>:</div>
                                <div>
                                    {{ order.delivery_to }}
                                </div>
                            </div>
                        </div>
                        <div class="a4_invoice_order_details">
                            <div class="a4_invoice_customer">
                                <div>Invoice No</div>
                                <div>:</div>
                                <div>{{ order.invoice_number }}</div>
                            </div>
                            <div class="a4_invoice_customer">
                                <div>Dated</div>
                                <div>:</div>
                                <div>
                                    {{ formatDateTime(order.order_date_local) }}
                                </div>
                            </div>
                            <div
                                class="a4_invoice_customer"
                                v-if="order && order.place_of_supply"
                            >
                                <div>Place of Supply</div>
                                <div>:</div>
                                <div>
                                    {{ order.place_of_supply }}
                                </div>
                            </div>
                            <div
                                class="a4_invoice_customer"
                                v-if="order && order.reverse_charge"
                            >
                                <div>Reverse Charge</div>
                                <div>:</div>
                                <div>
                                    {{ order.reverse_charge }}
                                </div>
                            </div>
                            <div
                                class="a4_invoice_customer"
                                v-if="order && order.gr_rr_no"
                            >
                                <div>GR/RR No</div>
                                <div>:</div>
                                <div>
                                    {{ order.gr_rr_no }}
                                </div>
                            </div>
                            <div
                                class="a4_invoice_customer"
                                v-if="order && order.transport"
                            >
                                <div>Transport</div>
                                <div>:</div>
                                <div>
                                    {{ order.transport }}
                                </div>
                            </div>
                            <div
                                class="a4_invoice_customer"
                                v-if="order && order.vechile_no"
                            >
                                <div>Vehicle No</div>
                                <div>:</div>
                                <div>
                                    {{ order.vechile_no.toUpperCase() }}
                                </div>
                            </div>
                            <div
                                class="a4_invoice_customer"
                                v-if="order && order.station"
                            >
                                <div>Station</div>
                                <div>:</div>
                                <div>
                                    {{ order.station }}
                                </div>
                            </div>
                            <div
                                class="a4_invoice_customer"
                                v-if="order && order.buyer_order_no"
                            >
                                <div>BUYER ORDER NO</div>
                                <div>:</div>
                                <div>
                                    {{ order.buyer_order_no }}
                                </div>
                            </div>
                        </div>
                    </div>
                </header>
                <div
                    class="invoice_product_wrapper"
                    style="
                        border: 1px solid;
                        border-top: none;
                        border-bottom: none;
                    "
                >
                    <table
                        class="a4_invoice_body_wrapper"
                        style="border-collapse: collapse; width: 100%"
                    >
                        <thead
                            style="background: #eee; border-top: 1px solid #000"
                        >
                            <tr>
                                <th
                                    style="
                                        width: 2%;
                                        border-bottom: 1px solid #000;
                                    "
                                >
                                    S.No
                                </th>
                                <th
                                    style="
                                        width: 30%;
                                        border: 1px solid #000;
                                        border-top: none;
                                    "
                                >
                                    Description of Goods
                                </th>
                                <th
                                    style="
                                        width: 8%;
                                        border: 1px solid #000;
                                        border-top: none;
                                    "
                                >
                                    HSN/SAC
                                </th>

                                <th
                                    style="
                                        width: 5%;
                                        border: 1px solid #000;
                                        border-top: none;
                                    "
                                >
                                    Qty
                                </th>
                                <th
                                    style="
                                        width: 8%;
                                        border: 1px solid #000;
                                        border-top: none;
                                    "
                                >
                                    Unit
                                </th>
                                <th
                                    style="
                                        width: 8%;
                                        border: 1px solid #000;
                                        border-top: none;
                                    "
                                    v-if="invoiceTemplate.table_setting.mrp"
                                >
                                    MRP
                                </th>
                                <th
                                    style="
                                        width: 8%;
                                        border: 1px solid #000;
                                        border-top: none;
                                    "
                                >
                                    Rate
                                </th>
                                <th
                                    style="
                                        width: 5%;
                                        border: 1px solid #000;
                                        border-top: none;
                                    "
                                    v-if="order.warehouse.gst_in_no != null"
                                >
                                    {{ $t("tax.tax") }}
                                </th>
                                <th
                                    style="
                                        width: 8%;
                                        border: 1px solid #000;
                                        border-top: none;
                                    "
                                >
                                    Discount
                                </th>
                                <th
                                    style="
                                        border: 1px solid #000;
                                        border-top: none;
                                        border-right: none;
                                    "
                                >
                                    Amount
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {{
                                calculationBasedOnGST(order)
                            }}
                            <tr
                                v-for="(item, index) in order.items"
                                :key="item.xid"
                            >
                                <td
                                    style="
                                        border: 1px solid #000;
                                        border-left: none;
                                    "
                                >
                                    {{ index + 1 }}
                                </td>
                                <td
                                    style="
                                        border: 1px solid #000;
                                        text-align: center;
                                    "
                                >
                                    {{ item.product.name }} <br />
                                    <div
                                        v-if="item && item.identity_code"
                                        style="text-align: left"
                                    >
                                        <b>Additional Details:</b> <br />
                                        {{ item.identity_code }}
                                    </div>
                                </td>
                                <td style="border: 1px solid #000">
                                    {{
                                        item && item.product.hsn_sac_code
                                            ? item.product.hsn_sac_code
                                            : "-"
                                    }}
                                </td>

                                <td
                                    style="
                                        border: 1px solid #000;
                                        border-right: none;
                                    "
                                >
                                    {{
                                        item && item.quantity
                                            ? item.quantity
                                            : "-"
                                    }}
                                </td>
                                <td style="border: 1px solid #000">
                                    {{
                                        item && item.unit.short_name
                                            ? item.unit.short_name
                                            : "-"
                                    }}
                                </td>
                                <td
                                    style="border: 1px solid #000"
                                    v-if="invoiceTemplate.table_setting.mrp"
                                >
                                    {{
                                        item && item.mrp
                                            ? `${
                                                  order.currency.symbol
                                              } ${formatAmount(item.mrp)}`
                                            : "-"
                                    }}
                                </td>
                                <td style="border: 1px solid #000">
                                    {{
                                        item && item.unit_price
                                            ? `${
                                                  order.currency.symbol
                                              } ${formatAmount(
                                                  item.unit_price
                                              )}`
                                            : "-"
                                    }}
                                </td>
                                <td
                                    style="border: 1px solid #000"
                                    v-if="order.warehouse.gst_in_no != null"
                                >
                                    {{
                                        item && item.tax_rate
                                            ? item.tax_rate
                                            : "-"
                                    }}
                                </td>
                                <td style="border: 1px solid #000">
                                    {{
                                        item && item.total_discount
                                            ? `${order.currency.symbol}
                                              ${formatAmount(
                                                  item.total_discount
                                              )}`
                                            : "0"
                                    }}
                                </td>
                                <td
                                    style="
                                        border: 1px solid #000;
                                        border-right: none;
                                    "
                                >
                                    {{
                                        item && item.subtotal
                                            ? `${
                                                  order.currency.symbol
                                              } ${formatAmount(item.subtotal)}`
                                            : "-"
                                    }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <footer class="a4_invoie_footer_wraper">
                    <div class="a4_invoie_gst_total_new">
                        <div
                            class="a4_invoie_footer_calculation"
                            v-if="order.warehouse.gst_in_no != null"
                        >
                            <div
                                class="footer_calculation"
                                v-if="!checkStartingDigits"
                            >
                                <div
                                    class="footer_tax"
                                    v-for="(tax, key) in taxDifProducts"
                                    :key="key"
                                >
                                    <p>
                                        Sales@{{ key }}%={{
                                            `${
                                                order.currency.symbol
                                            } ${formatAmount(
                                                tax.totalAmountSum
                                            )}`
                                        }}
                                    </p>
                                    <p>
                                        {{ $t("tax.c_tax") }}={{
                                            `${
                                                order.currency.symbol
                                            } ${formatAmount(
                                                tax.taxAmountSum / 2
                                            )}`
                                        }}
                                    </p>
                                    <p>
                                        {{ $t("tax.s_tax") }}={{
                                            `${
                                                order.currency.symbol
                                            } ${formatAmount(
                                                tax.taxAmountSum / 2
                                            )}`
                                        }}
                                    </p>
                                    <p>
                                        {{ $t("tax.tax") }}={{
                                            `${
                                                order.currency.symbol
                                            } ${formatAmount(tax.taxAmountSum)}`
                                        }}
                                    </p>
                                    <p>
                                        Sale={{
                                            `${order.currency.symbol}
                                        ${formatAmount(
                                            tax.totalAmountSum +
                                                tax.taxAmountSum
                                        )}`
                                        }}
                                    </p>
                                </div>
                            </div>
                            <div class="footer_calculation" v-else>
                                <div
                                    class="footer_tax"
                                    v-for="(tax, key) in taxDifProducts"
                                    :key="key"
                                >
                                    <p>
                                        <span
                                            style="text-transform: capitalize"
                                            >{{ order.order_type }}</span
                                        >
                                        @{{ key }}% =
                                        {{
                                            `${
                                                order.currency.symbol
                                            } ${formatAmount(
                                                tax.totalAmountSum
                                            )}`
                                        }}
                                    </p>
                                    <p>
                                        {{ $t("tax.i_tax") }}={{
                                            `${
                                                order.currency.symbol
                                            } ${formatAmount(tax.taxAmountSum)}`
                                        }}
                                    </p>
                                    <p>
                                        Sale={{
                                            `${order.currency.symbol}
                                        ${formatAmount(
                                            tax.totalAmountSum +
                                                tax.taxAmountSum
                                        )}`
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="a4_invoice_total_wrapper">
                            <div class="a4_invoice_gst_footer_new">
                                <div>SUB TOTAL</div>
                                <div>:</div>
                                <div>
                                    {{
                                        `${
                                            order.currency.symbol
                                        } ${formatAmount(totalAmountSum)}`
                                    }}
                                </div>
                            </div>
                            <div
                                class="a4_invoice_gst_footer_new"
                                v-if="
                                    !checkStartingDigits &&
                                    order.warehouse.gst_in_no != null &&
                                    totalTax != 0
                                "
                            >
                                <div>ADD : {{ $t("tax.c_tax") }}</div>
                                <div>:</div>
                                <div>
                                    {{
                                        `${
                                            order.currency.symbol
                                        } ${formatAmount(totalTax / 2)}`
                                    }}
                                </div>
                            </div>
                            <div
                                class="a4_invoice_gst_footer_new"
                                v-if="
                                    !checkStartingDigits &&
                                    order.warehouse.gst_in_no != null &&
                                    totalTax != 0
                                "
                            >
                                <div>ADD : {{ $t("tax.s_tax") }}</div>
                                <div>:</div>
                                <div>
                                    {{
                                        `${
                                            order.currency.symbol
                                        } ${formatAmount(totalTax / 2)}`
                                    }}
                                </div>
                            </div>
                            <div
                                class="a4_invoice_gst_footer_new"
                                v-if="
                                    checkStartingDigits &&
                                    order.warehouse.gst_in_no != null &&
                                    totalTax != 0
                                "
                            >
                                <div>ADD : {{ $t("tax.i_tax") }}</div>
                                <div>:</div>
                                <div>
                                    {{
                                        `${
                                            order.currency.symbol
                                        } ${formatAmount(totalTax)}`
                                    }}
                                </div>
                            </div>
                            <div
                                class="a4_invoice_gst_footer_new"
                                v-if="order.shipping != 0"
                            >
                                <div>SHIPPING</div>
                                <div>:</div>
                                <div>
                                    {{
                                        `${
                                            order.currency.symbol
                                        } ${formatAmount(order.shipping)}`
                                    }}
                                </div>
                            </div>
                            <div
                                class="a4_invoice_gst_footer_new"
                                v-if="order.discount != 0"
                            >
                                <div>DISCOUNT</div>
                                <div>:</div>
                                <div>
                                    {{
                                        `${
                                            order.currency.symbol
                                        } ${formatAmount(order.discount)}`
                                    }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="a4_invoie_gst_total_1">
                        <p style="font-size: 12px; width: 75%">
                            In Words: {{ numberToWords(order.total) }}
                        </p>
                        <div class="a4_invoice_gst_footer">
                            <div>Round Off</div>
                            <div>:</div>
                            <div>
                                {{
                                    `${order.currency.symbol}
                                ${formatAmount(
                                    roundOff(
                                        totalAmountSum,
                                        totalTax,
                                        order.shipping,
                                        order.discount
                                    ).difference
                                )}`
                                }}
                            </div>
                            <div>Total</div>
                            <div>:</div>
                            <div>
                                {{
                                    `${order.currency.symbol}
                                ${formatAmount(
                                    roundOff(
                                        totalAmountSum,
                                        totalTax,
                                        order.shipping,
                                        order.discount
                                    ).total
                                )}`
                                }}
                            </div>
                        </div>
                    </div>

                    <div
                        class="a4_invoie_bank_details"
                        v-if="order.warehouse && order.warehouse.bank_details"
                    >
                        <p>
                            Bank Details :
                            {{ order.warehouse.bank_details }}
                        </p>
                    </div>
                    <div class="a4_invoie_footer_last">
                        <div class="a4_invoice_signature">
                            <div class="a4_invoice_terms">
                                <p v-if="order.notes">Notes:</p>
                                <p v-if="order.notes">
                                    {{ order.notes }}<br />
                                </p>
                                <p>Terms & Conditions</p>
                                <p>E & O.E</p>
                                <template v-if="order.terms_condition">
                                    <p
                                        v-for="(
                                            item, index
                                        ) in order.terms_condition.split('\n')"
                                        :key="index"
                                    >
                                        {{ item }}
                                    </p>
                                </template>
                            </div>
                            <div class="a4_receiver_signature">
                                <p>Receiver's Signature:</p>
                            </div>
                        </div>

                        <div class="a4_invoice_store_signature">
                            <div class="a4_auth_signature">
                                <p>for {{ order.warehouse.name }}</p>
                                <p>Authorized Signature</p>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
    </div>
</template>
<script>
import axios from "axios";
import { ref, defineComponent, computed, onMounted } from "vue";
import { useRoute } from "vue-router";
import { FilePdfOutlined } from "@ant-design/icons-vue";
import common from "../../../../../common/composable/common";
import html2pdf from "html2pdf.js";
export default defineComponent({
    components: {
        FilePdfOutlined,
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

        const roundOff = (total, tax, shipping, discount) => {
            let overallSum = total + tax + shipping - discount;
            let roundOverallSum = _.round(overallSum);
            return {
                total: roundOverallSum,
                difference: Math.abs(overallSum - roundOverallSum),
            };
        };

        const exportToPDF = () => {
            var element = document.getElementById("sales_a4_invoice");
            var opt = {
                margin: 3,
                filename: `${order.value.invoice_number}.pdf`,
                image: { type: "jpeg", quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: {
                    orientation: "p",
                    unit: "mm",
                    format: "a4",
                    putOnlyUsedFonts: true,
                    floatPrecision: 16,
                },
            };
            html2pdf().from(element).set(opt).save();
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
            roundOff,
            exportToPDF,
        };
    },
});
</script>
