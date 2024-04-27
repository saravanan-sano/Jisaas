<template>
    <a-modal
        :visible="visible"
        :centered="true"
        :maskClosable="false"
        title="Receipt"
        width="400px"
        @cancel="onClose"
    >
        <div class="footer-button">
            <a-button
                type="primary"
                id="printButton"
                v-print="'#receipt_invoice'"
                style="margin-right: 5px"
                @click="onClose"
            >
                <template #icon>
                    <PrinterOutlined />
                </template>
                {{ $t("common.press_p") }}
            </a-button>

            <a-button
                type="primary"
                id="shareButton"
                @click="shareInvoice"
                style="
                    margin-right: 5px;
                    background-color: #25d366;
                    border-color: #25d366;
                "
                v-if="order.user.name != 'Walk In Customer'"
            >
                <template #icon>
                    <WhatsAppOutlined />
                </template>
                {{ $t("common.whatsapp") }}
            </a-button>

            <!-- <a-button
                    type="primary"
                    v-print="'#a4_invoice'"
                >
                    <template #icon>
                        <PrinterOutlined />
                    </template>
                    A4 {{ $t("common.print_invoice") }}
                </a-button> -->
        </div>
        <div id="receipt_invoice" class="receipt-wrap receipt-four">
            <div class="receipt-top">
                <div
                    class="receipt-seperator"
                    v-if="invoiceTemplate.header.logo"
                ></div>
                <div class="company-logo" v-if="invoiceTemplate.header.logo">
                    <img
                        class="invoice-logo"
                        :src="selectedWarehouse.logo_url"
                        :alt="selectedWarehouse.name"
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
                    {{ selectedWarehouse.name }}
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
                    {{ selectedWarehouse.address }}
                </div>
                <div
                    class="company-email"
                    v-if="invoiceTemplate.header.company_email"
                >
                    {{ $t("common.email") }}: {{ selectedWarehouse.email }}
                </div>
                <div
                    class="company-no"
                    v-if="invoiceTemplate.header.company_no"
                >
                    {{ $t("common.phone") }}: {{ selectedWarehouse.phone }}
                </div>
                <div
                    class="company-no"
                    v-if="
                        invoiceTemplate.header.tax_no &&
                        selectedWarehouse.gst_in_no
                    "
                >
                    {{ $t("tax.tax_no") }}: {{ selectedWarehouse.gst_in_no }}
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
                    <div class="desc">{{ order.user && order.user.address ? order.user.address : '-' }}</div>
                </li>
                <li v-if="invoiceTemplate.customer_details.staff_name">
                    <div class="title">{{ $t("stock.sold_by") }}:</div>
                    <div class="desc">{{ order.staff_member.name }}</div>
                </li>
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
                    <div class="desc">{{ order.user && order.user.tax_number ? order.user.tax_number : '-' }}</div>
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
                            {{ formatAmountCurrency1(item.unit_price) }}
                        </td>
                        <td style="text-align: right">
                            {{ formatAmountCurrency1(item.subtotal) }}
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
                        <!-- {{
                            formatAmountCurrency1(
                                order.subtotal
                            )
                        }} -->

                        {{ formatAmountCurrency1(order.subtotal) }}
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
                        {{ formatAmountCurrency1(order.discount) }}
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
                        {{ formatAmountCurrency1(order.shipping) }}
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
                        {{ formatAmountCurrency1(order.tax_amount) }}
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
                        {{ formatAmountCurrency1(order.due_amount) }}
                    </div>
                </div>
                <div
                    class="bill_list_in"
                    v-if="
                        invoiceTemplate.total_details.due &&
                        order.due_amount < 0
                    "
                >
                    <div class="bill_title bill_focus">
                        {{ $t("stock.change_return") }}:
                    </div>
                    <div class="bill_value bill_focus">
                        {{
                            formatAmountCurrency1(order.due_amount)
                                .toString()
                                .replace("-", "")
                        }}
                    </div>
                </div>
                <div class="bill_list_in total-payable">
                    <div class="bill_title bill_focus">
                        {{ $t("common.total") }}:
                    </div>
                    <div class="bill_value bill_focus">
                        {{ formatAmountCurrency1(order.total) }}
                    </div>
                </div>
            </div>
            <div class="receipt-seperator"></div>
            <div
                class="receipt-seperator"
                v-if="
                    selectedWarehouse.gst_in_no != null &&
                    invoiceTemplate.tax_wise_calculations.enabled
                "
            ></div>
            <div
                class="receipt_invoice_footer_calculation"
                v-if="
                    selectedWarehouse.gst_in_no != null &&
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
                                        formatAmountCurrency(tax.totalAmountSum)
                                    }}
                                </td>
                                <td>{{ key / 2 }}%</td>
                                <td>
                                    {{
                                        formatAmountCurrency(
                                            tax.taxAmountSum / 2
                                        )
                                    }}
                                </td>
                                <td>{{ key / 2 }}%</td>
                                <td>
                                    {{
                                        formatAmountCurrency(
                                            tax.taxAmountSum / 2
                                        )
                                    }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <b>{{
                                        formatAmountCurrency(totalAmountSum)
                                    }}</b>
                                </td>
                                <td></td>

                                <td>
                                    <b>{{
                                        formatAmountCurrency(totalTax / 2)
                                    }}</b>
                                </td>
                                <td></td>
                                <td>
                                    <b>{{
                                        formatAmountCurrency(totalTax / 2)
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
                                        formatAmountCurrency(tax.totalAmountSum)
                                    }}
                                </td>
                                <td>{{ key }}%</td>
                                <td>
                                    {{ formatAmountCurrency(tax.taxAmountSum) }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <b>{{
                                        formatAmountCurrency(totalAmountSum)
                                    }}</b>
                                </td>
                                <td></td>
                                <td>
                                    <b>{{
                                        formatAmountCurrency(totalTax / 2)
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
                v-if="
                    invoiceTemplate.footer.qr_code && selectedWarehouse.upi_id
                "
            >
                <div>
                    <QrcodeVue
                        :value="
                            QrCodeValue(selectedWarehouse.upi_id, order.total)
                        "
                        level="H"
                    />
                    <p style="font-size: 10px; margin: 0">
                        <QrcodeOutlined style="color: #000" />
                        {{ selectedWarehouse.upi_id }}
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

        <template #footer>
            <!-- <div class="footer-button">
                <a-button
                    type="primary"
                    id="printButton"
                    v-print="'#receipt_invoice'"
                >
                    <template #icon>
                        <PrinterOutlined />
                    </template>
                    {{ $t("common.print_invoice") }} (p)
                </a-button>
                <a-button
                    type="primary"
                    v-print="'#a4_invoice'"
                >
                    <template #icon>
                        <PrinterOutlined />
                    </template>
                    A4 {{ $t("common.print_invoice") }}
                </a-button>
            </div> -->
        </template>
    </a-modal>
    <div id="a4_invoice"></div>
</template>

<script>
import {
    ref,
    defineComponent,
    computed,
    onBeforeUnmount,
    onMounted,
} from "vue";
import {
    PrinterOutlined,
    WhatsAppOutlined,
    QrcodeOutlined,
} from "@ant-design/icons-vue";
import common from "../../../../common/composable/common";
const posInvoiceCssUrl = window.config.pos_invoice_css;
import QrcodeVue from "qrcode.vue";
export default defineComponent({
    props: ["visible", "order", "orderinfo"],
    emits: ["closed", "success"],
    components: {
        PrinterOutlined,
        WhatsAppOutlined,
        QrcodeVue,
        QrcodeOutlined,
    },
    setup(props, { emit }) {
        const {
            appSetting,
            formatAmountCurrency,
            formatAmountCurrency1,
            formatDateTime,
            selectedWarehouse,
            QrCodeValue,
        } = common();

        const onClose = () => {
            emit("closed");
        };
        const company = appSetting.value;
        const invoiceTemplate = ref(JSON.parse(company.invoice_template));

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

        const shareInvoice = () => {
            var txt1encoded = encodeURIComponent(
                `Dear ${props.order.user.name}`
            );
            var txt2encoded = encodeURIComponent(
                `Welcome to ${appSetting.value.name}! We are delighted to have you visit our store, and we sincerely appreciate the opportunity to serve your needs.`
            );
            var txt3encoded = encodeURIComponent(`*Your Order Details:*`);
            var txt4encoded = encodeURIComponent(
                `- Order Number: ${props.order.invoice_number}`
            );
            var txt5encoded = encodeURIComponent(
                `- Order Date: ${formatDateTime(props.order.order_date)}`
            );
            var txt6encoded = encodeURIComponent(
                `- Total Amount: ${props.order.total}`
            );
            var proddet = [];
            for (let index = 0; index < props.order.items.length; index++) {
                proddet[index] = encodeURIComponent(
                    `${props.order.items[index].product.name} ${props.order.items[index].unit_price} X ${props.order.items[index].quantity} = ${props.order.items[index].subtotal}`
                );
            }
            var txt7aencoded = encodeURIComponent(
                `Total Items : ${props.order.total_items}`
            );
            var txt7bencoded = encodeURIComponent(
                `Payment Mode : ${props.order.order_payments[0].payment.payment_mode.name}`
            );
            var txt7encoded = encodeURIComponent(
                `${window.location.origin}/common/receipt/${props.order.xid}`
            );
            var txt8encoded = encodeURIComponent(`Sincerely,`);
            var txt9encoded = encodeURIComponent(`${appSetting.value.name}`);

            var whatsappUrl = `https://api.whatsapp.com/send?phone=${props.order.user.phone}&text=${txt1encoded}%0a %0a${txt2encoded}%0a %0a${txt3encoded}%0a${txt4encoded}%0a${txt5encoded}%0a${txt6encoded}%0a`;
            for (let i = 0; i < proddet.length; i++) {
                whatsappUrl += `%0a${proddet[i]}`;
            }

            whatsappUrl += ` %0a${txt7aencoded}%0a %0a${txt7bencoded}%0a %0a${txt7encoded}%0a %0a${txt8encoded}%0a${txt9encoded}`;
            window.open(whatsappUrl, "_blank");
        };

        const checkStartingDigits = computed(() => {
            if (
                props.order &&
                props.order.user &&
                props.order.user.tax_number  &&
                selectedWarehouse.value.gst_in_no
            ) {
                const warehouseGSTNumber = selectedWarehouse.value.gst_in_no
                    ? selectedWarehouse.value.gst_in_no
                    : 0;
                const userTaxNumber = props.order.user.tax_number;

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
            const groupedProducts = _.groupBy(props.order.items, "tax_rate");

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

        return {
            appSetting,
            onClose,
            formatDateTime,
            selectedWarehouse,
            formatAmountCurrency,
            formatAmountCurrency1,
            invoiceTemplate,
            shareInvoice,
            checkStartingDigits,
            totalTax,
            totalAmountSum,
            taxDifProducts,
            calculationBasedOnGST,
            QrCodeValue,
        };
    },
});
</script>
<style>
#a4_invoice {
    display: none;
    width: 100%;
    height: 1px;
    margin: 0 auto;
    border: 1px solid #ccc;
    border-radius: 5px;
    background: #fff;
}

@media print {
    #a4_invoice {
        display: block;
    }
}
</style>
