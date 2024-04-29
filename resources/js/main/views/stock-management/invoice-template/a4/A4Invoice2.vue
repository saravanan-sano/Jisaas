<template>
    <a-modal
        :visible="visible"
        :centered="true"
        :maskClosable="false"
        title="Press(p) To Print Invoice"
        width="22cm"
        @cancel="onClose"
        bodyStyle="height: 79vh;overflow: scroll;"
    >
        <div
            id="sales_a4_2_invoice"
            class="A4_2_invoice"
            :style="`font-size: ${DFontSize}px !important`"
        >
            {{ handleChunksItems(order.items) }}
            <div
                v-for="(items, key) in chunkOrderItem"
                :key="key"
                class="a4_2_chunk_wrapper"
            >
                <div
                    class="a4_2_invoice_header_title"
                    :style="`font-size: ${DFontSize + 2}px;`"
                >
                    <u>{{
                        order.order.order_type == "quotations"
                            ? "Quotation"
                            : "Invoice"
                    }}</u>
                </div>
                <header class="a4_2_invoice_header_wraper">
                    <div class="a4_2_invoice_header" v-if="!order.duplicate">
                        <!-- <div
                            class="a4_2_invoice_header_logo"
                            v-if="
                                invoiceTemplate.header.logo &&
                                selectedWarehouse.logo_url
                            "
                        >
                            <img
                                class="invoice-logo"
                                :src="selectedWarehouse.logo_url"
                            />
                        </div> -->
                        <div
                            class="a4_2_invoice_header_title"
                            :style="`font-size: ${DFontSize + 2}px;`"
                        >
                            <p>{{ selectedWarehouse.name }}</p>
                        </div>
                        <div
                            class="a4_2_invoice_gstin_title"
                            v-if="
                                selectedWarehouse && selectedWarehouse.address
                            "
                        >
                            <p>{{ selectedWarehouse.address }}</p>
                        </div>

                        <div class="a4_2_invoice_contact_info">
                            <p>Tel: {{ selectedWarehouse.phone }}</p>
                            <p>Email: {{ selectedWarehouse.email }}</p>
                        </div>
                        <div
                            class="a4_2_invoice_gstin_title"
                            v-if="
                                selectedWarehouse && selectedWarehouse.gst_in_no
                            "
                        >
                            <b>
                                {{ $t("tax.tax_no") }}:
                                {{ selectedWarehouse.gst_in_no }}
                            </b>
                        </div>
                    </div>
                    <div class="a4_2_invoice_order_details_wrapper">
                        <div class="a4_2_invoice_customer_details">
                            <div class="a4_2_invoice_customer_address">
                                <span style="font-weight: 900">Invoice:</span>
                                {{ order.order.invoice_number }} |
                                {{
                                    formatDateTime(order.order.order_date_local)
                                }}
                                |
                                {{
                                    selectedWarehouse.gst_in_no.substring(0, 2)
                                }}
                            </div>
                            <div
                                class="a4_2_invoice_customer_address"
                                v-if="order && order.user && order.user.name"
                            >
                                <div>Party Details</div>
                            </div>
                            <div class="text">
                                {{ order.user.name }}
                                <br />
                                {{
                                    order.order.order_type == "online-orders"
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
                                style="text-align: left"
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
                                style="text-align: left"
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
                                style="text-align: left; font-weight: 900"
                                v-if="
                                    order && order.user && order.user.tax_number
                                "
                            >
                                {{ $t("tax.tax_no") }}/UIN :
                                {{ order.user.tax_number }}
                            </div>
                            <div
                                class="a4_2_invoice_customer"
                                v-if="
                                    order &&
                                    order.order &&
                                    order.order.delivery_to
                                "
                            >
                                <div>Delivered To</div>
                                <div>:</div>
                                <div>
                                    {{ order.order.delivery_to }}
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                {{ calculationBasedOnGST(order) }}
                <div class="invoice_product_wrapper">
                    <table
                        class="a4_2_invoice_body_wrapper"
                        style="border-collapse: collapse; width: 100%"
                        ref="contentRef"
                    >
                        <thead
                            style="background: #eee; border-top: 1px solid #000"
                        >
                            <tr>
                                <th
                                    style="
                                        width: 2%;
                                        border-bottom: 1px solid #000;
                                        border-left: 1px solid #000;
                                    "
                                >
                                    S.No
                                </th>
                                <th
                                    style="
                                        width: 38%;
                                        border: 1px solid #000;
                                        border-top: none;
                                    "
                                >
                                    Description of Goods
                                </th>
                                <th
                                    style="
                                        width: 11%;
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
                                        width: 13%;
                                        border: 1px solid #000;
                                        border-top: none;
                                    "
                                    v-if="
                                        invoiceTemplate.table_setting &&
                                        invoiceTemplate.table_setting.units
                                    "
                                >
                                    Unit
                                </th>
                                <th
                                    style="
                                        width: 10%;
                                        border: 1px solid #000;
                                        border-top: none;
                                    "
                                    v-if="invoiceTemplate.table_setting.mrp"
                                >
                                    MRP
                                </th>
                                <th
                                    style="
                                        width: 10%;
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
                                    v-if="selectedWarehouse.gst_in_no != null"
                                >
                                    {{ $t("tax.tax") }}
                                </th>
                                <th
                                    style="
                                        width: 10%;
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
                                    "
                                >
                                    Amount
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in items" :key="item.xid">
                                <td
                                    :style="`
                                        font-size: ${DFontSize}px;
                                        border: 1px solid #000;`"
                                >
                                    {{ item.sn }}
                                </td>
                                <td
                                    :style="`font-size: ${DFontSize}px;
                                        border: 1px solid #000;
                                        text-align: center;`"
                                >
                                    {{ item.name }} <br />
                                    <div
                                        v-if="item && item.identity_code"
                                        style="text-align: left"
                                    >
                                        <b>Additional Details:</b> <br />
                                        {{ item.identity_code }}
                                    </div>
                                </td>
                                <td
                                    :style="`
                                        font-size: ${DFontSize}px;
                                        border: 1px solid #000;`"
                                >
                                    {{
                                        item && item.hsn_sac_code
                                            ? item.hsn_sac_code
                                            : "-"
                                    }}
                                </td>

                                <td
                                    :style="`font-size: ${DFontSize}px;
                                        border: 1px solid #000;
                                        border-right: none;`"
                                >
                                    {{
                                        item && item.quantity
                                            ? item.quantity
                                            : "-"
                                    }}
                                </td>
                                <td
                                    :style="`
                                        font-size: ${DFontSize}px;
                                        border: 1px solid #000;`"
                                    v-if="
                                        invoiceTemplate.table_setting &&
                                        invoiceTemplate.table_setting.units
                                    "
                                >
                                    {{
                                        item && item.unit_short_name
                                            ? item.unit_short_name
                                            : "-"
                                    }}
                                </td>
                                <td
                                    :style="`font-size: ${DFontSize}px;
                                        border: 1px solid #000;`"
                                    v-if="invoiceTemplate.table_setting.mrp"
                                >
                                    {{
                                        item && item.mrp
                                            ? formatAmountCurrency(item.mrp)
                                            : "-"
                                    }}
                                </td>
                                <td
                                    :style="`
                                        font-size: ${DFontSize}px;
                                        border: 1px solid #000;`"
                                >
                                    {{
                                        item && item.unit_price
                                            ? formatAmountCurrency(
                                                  item.unit_price
                                              )
                                            : "-"
                                    }}
                                </td>
                                <td
                                    :style="`
                                        font-size: ${DFontSize}px;
                                        border: 1px solid #000;`"
                                    v-if="selectedWarehouse.gst_in_no != null"
                                >
                                    {{
                                        item && item.tax_rate
                                            ? item.tax_rate
                                            : "-"
                                    }}
                                </td>
                                <td
                                    :style="`font-size: ${DFontSize}px;
                                        border: 1px solid #000;`"
                                >
                                    {{
                                        item && item.total_discount
                                            ? `${formatAmountCurrency(
                                                  item.total_discount
                                              )} `
                                            : "0"
                                    }}
                                </td>

                                <td
                                    :style="`
                                        font-size: ${DFontSize}px;
                                        border: 1px solid #000;`"
                                >
                                    {{
                                        item && item.subtotal
                                            ? formatAmountCurrency(
                                                  item.subtotal
                                              )
                                            : "-"
                                    }}
                                </td>
                            </tr>
                            <tr
                                v-if="
                                    invoiceTemplate.footer &&
                                    invoiceTemplate.footer.total_item_count &&
                                    chunkOrderItem.length == key + 1
                                "
                            >
                                <td></td>
                                <td></td>
                                <td
                                    :style="`font-size: ${DFontSize}px`"
                                    v-if="
                                        invoiceTemplate.footer &&
                                        invoiceTemplate.footer.total_item_count
                                    "
                                >
                                    Items:
                                </td>
                                <td
                                    :style="`font-size: ${DFontSize}px`"
                                    v-if="
                                        invoiceTemplate.footer &&
                                        invoiceTemplate.footer.total_item_count
                                    "
                                >
                                    {{ order.order.total_quantity }}
                                </td>
                                <td
                                    v-if="
                                        invoiceTemplate.table_setting &&
                                        invoiceTemplate.table_setting.units
                                    "
                                ></td>
                                <td
                                    v-if="invoiceTemplate.table_setting.mrp"
                                ></td>
                                <td></td>
                                <td
                                    v-if="selectedWarehouse.gst_in_no != null"
                                ></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p
                    class="continue_page"
                    :style="`font-size: ${DFontSize}px`"
                    v-if="chunkOrderItem.length != key + 1"
                >
                    * Continue Page {{ key + 1 }} *
                </p>
                <footer
                    class="a4_2_invoice_footer_wrapper"
                    v-if="chunkOrderItem.length == key + 1"
                >
                    <div class="a4_2_invoice_gst_total_new">
                        <div
                            class="a4_2_invoice_footer_calculation"
                            v-if="selectedWarehouse.gst_in_no != null"
                        >
                            <div
                                class="footer_calculation"
                                v-if="!checkStartingDigits"
                                :style="`font-size:${DFontSize - 2}px`"
                            >
                                <div
                                    class="footer_tax"
                                    v-for="(tax, key) in taxDifProducts"
                                    :key="key"
                                >
                                    <p>
                                        Sales@{{ key }}% ={{
                                            formatAmountCurrency(
                                                tax.totalAmountSum
                                            )
                                        }}
                                    </p>
                                    <p>
                                        {{ $t("tax.c_tax") }} ={{
                                            formatAmountCurrency(
                                                tax.taxAmountSum / 2
                                            )
                                        }}
                                    </p>
                                    <p>
                                        {{ $t("tax.s_tax") }} ={{
                                            formatAmountCurrency(
                                                tax.taxAmountSum / 2
                                            )
                                        }}
                                    </p>
                                    <p>
                                        {{ $t("tax.tax") }} ={{
                                            formatAmountCurrency(
                                                tax.taxAmountSum
                                            )
                                        }}
                                    </p>
                                    <p>
                                        Sale ={{
                                            formatAmountCurrency(
                                                tax.totalAmountSum +
                                                    tax.taxAmountSum
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>
                            <div
                                class="footer_calculation"
                                :style="`font-size:${DFontSize - 2}px`"
                                v-else
                            >
                                <div
                                    class="footer_tax"
                                    v-for="(tax, key) in taxDifProducts"
                                    :key="key"
                                >
                                    <p>
                                        Sales@{{ key }}% ={{
                                            formatAmountCurrency(
                                                tax.totalAmountSum
                                            )
                                        }}
                                    </p>
                                    <p>
                                        {{ $t("tax.i_tax") }} ={{
                                            formatAmountCurrency(
                                                tax.taxAmountSum
                                            )
                                        }}
                                    </p>
                                    <p>
                                        Sale ={{
                                            formatAmountCurrency(
                                                tax.totalAmountSum +
                                                    tax.taxAmountSum
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="a4_2_invoice_total_wrapper">
                            <div
                                class="a4_2_invoice_gst_footer_new"
                                v-if="selectedWarehouse.gst_in_no != null"
                            >
                                <div>SUB TOTAL</div>
                                <div>:</div>
                                <div>
                                    {{ formatAmountCurrency(totalAmountSum) }}
                                </div>
                            </div>
                            <div
                                class="a4_2_invoice_gst_footer_new"
                                v-if="
                                    !checkStartingDigits &&
                                    selectedWarehouse.gst_in_no != null &&
                                    totalTax != 0
                                "
                            >
                                <div>ADD : {{ $t("tax.c_tax") }}</div>
                                <div>:</div>
                                <div>
                                    {{ formatAmountCurrency(totalTax / 2) }}
                                </div>
                            </div>
                            <div
                                class="a4_2_invoice_gst_footer_new"
                                v-if="
                                    !checkStartingDigits &&
                                    selectedWarehouse.gst_in_no != null &&
                                    totalTax != 0
                                "
                            >
                                <div>ADD : {{ $t("tax.s_tax") }}</div>
                                <div>:</div>
                                <div>
                                    {{ formatAmountCurrency(totalTax / 2) }}
                                </div>
                            </div>
                            <div
                                class="a4_2_invoice_gst_footer_new"
                                v-if="
                                    checkStartingDigits &&
                                    selectedWarehouse.gst_in_no != null &&
                                    totalTax != 0
                                "
                            >
                                <div>ADD : {{ $t("tax.i_tax") }}</div>
                                <div>:</div>
                                <div>
                                    {{ formatAmountCurrency(totalTax) }}
                                </div>
                            </div>
                            <div
                                class="a4_2_invoice_gst_footer_new"
                                v-if="order.order.shipping != 0"
                            >
                                <div>SHIPPING</div>
                                <div>:</div>
                                <div>
                                    {{
                                        formatAmountCurrency(
                                            order.order.shipping
                                        )
                                    }}
                                </div>
                            </div>
                            <div
                                class="a4_2_invoice_gst_footer_new"
                                v-if="order.order.discount != 0"
                            >
                                <div>DISCOUNT</div>
                                <div>:</div>
                                <div>
                                    {{
                                        formatAmountCurrency(
                                            order.order.discount
                                        )
                                    }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="a4_2_invoice_gst_total_1">
                        <p style="width: 75%">
                            In Words : {{ numberToWords(order.order.total) }}
                        </p>
                        <div class="a4_2_invoice_gst_footer">
                            <div>Round Off</div>
                            <div>:</div>
                            <div>
                                {{
                                    formatAmountCurrency(
                                        roundOff(
                                            totalAmountSum,
                                            totalTax,
                                            order.order.shipping,
                                            order.order.discount
                                        ).difference
                                    )
                                }}
                            </div>
                            <div>Total</div>
                            <div>:</div>
                            <div>
                                {{
                                    formatAmountCurrency(
                                        roundOff(
                                            totalAmountSum,
                                            totalTax,
                                            order.order.shipping,
                                            order.order.discount
                                        ).total
                                    )
                                }}
                            </div>
                        </div>
                    </div>

                    <div
                        class="a4_2_invoice_bank_details"
                        v-if="
                            selectedWarehouse &&
                            selectedWarehouse.bank_details &&
                            !order.duplicate
                        "
                    >
                        <p>
                            Bank Details :
                            {{ selectedWarehouse.bank_details }}
                        </p>
                    </div>
                    <div
                        class="a4_2_invoice_footer_last"
                        v-if="!order.duplicate"
                    >
                        <div class="a4_2_invoice_signature">
                            <div class="a4_2_invoice_terms">
                                <p v-if="order.order.notes">Notes:</p>
                                <p v-if="order.order.notes">
                                    {{ order.order.notes }}<br />
                                </p>
                                <p>Terms & Conditions</p>
                                <p>E & O.E</p>
                                <template v-if="order.order.terms_condition">
                                    <p
                                        v-for="(
                                            item, index
                                        ) in order.order.terms_condition.split(
                                            '\n'
                                        )"
                                        :key="index"
                                    >
                                        {{ item }}
                                    </p>
                                </template>
                            </div>
                            <div class="a4_2_receiver_signature">
                                <p>Receiver's Signature:</p>
                            </div>
                        </div>
                        <div class="a4_2_invoice_store_signature">
                            <div class="a4_2_auth_signature">
                                <p>for {{ selectedWarehouse.name }}</p>
                                <p>Authorized Signature</p>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>

        <template #footer>
            <div class="footer-invoice-button">
                <a-input-number
                    :max="40"
                    :min="20"
                    :value="chunkValue"
                    @change="
                        (e) => {
                            chunkValue = e;
                            handleChunksItems(order.items);
                        }
                    "
                />
                <a-button type="primary" v-print="'#sales_a4_2_invoice'">
                    <template #icon>
                        <PrinterOutlined />
                    </template>
                    {{ $t("common.print_invoice") }}
                </a-button>
                <a-button type="primary" @click="exportToPDF">
                    <template #icon>
                        <FilePdfOutlined />
                    </template>
                    PDF
                </a-button>
            </div>
        </template>
    </a-modal>
</template>

<script>
import {
    ref,
    defineComponent,
    computed,
    onBeforeUnmount,
    onMounted,
} from "vue";
import { PrinterOutlined, FilePdfOutlined } from "@ant-design/icons-vue";
import { useStore } from "vuex";
import { useRouter } from "vue-router";
import html2pdf from "html2pdf.js";
import common from "../../../../../common/composable/common";
import _ from "lodash-es"

export default defineComponent({
    props: ["visible", "order", "routeBack"],
    emits: ["closed", "success"],
    components: {
        PrinterOutlined,
        FilePdfOutlined,
    },
    setup(props, { emit }) {
        const {
            appSetting,
            formatAmountCurrency,
            formatAmountCurrency1,
            formatDateTime,
            selectedWarehouse,
            numberToWords,
        } = common();
        const store = useStore();
        const router = useRouter();
        const company = appSetting.value;
        const invoiceTemplate = ref(JSON.parse(company.invoice_template));
        const DFontSize =
            invoiceTemplate.value && invoiceTemplate.value.font_size
                ? invoiceTemplate.value.font_size
                : 10;

        const chunkValue = ref(30);
        const chunkOrderItem = ref([]);

        const handleChunksItems = (value) => {
            console.log("test", chunkValue.value);
            let chunkItems = _.chunk(value, chunkValue.value);

            if (chunkItems[chunkItems.length - 1].length > 20) {
                let lastItem = chunkItems.pop();
                _.chunk(lastItem, 20).map((item) => chunkItems.push(item));
            }

            chunkOrderItem.value = chunkItems;
        };

        const onClose = () => {
            store.dispatch("auth/updateVisibleSubscriptionModules");
            if (props.routeBack) {
                router.push(props.routeBack);
            } else {
                router.go(-1);
            }
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

        const checkStartingDigits = computed(() => {
            if (
                props.order &&
                props.order.user &&
                props.order.user.tax_number &&
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

        const roundOff = (total, tax, shipping, discount) => {
            let overallSum = total + tax + shipping - discount;
            let roundOverallSum = _.round(overallSum);

            return {
                total: roundOverallSum,
                difference: Math.abs(overallSum - roundOverallSum),
            };
        };

        const exportToPDF = () => {
            var element = document.getElementById("sales_a4_2_invoice");
            var opt = {
                margin: 3,
                filename: `${props.order.order.invoice_number}.pdf`,
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
            appSetting,
            onClose,
            formatDateTime,
            selectedWarehouse,
            formatAmountCurrency,
            formatAmountCurrency1,
            numberToWords,
            checkStartingDigits,
            calculationBasedOnGST,
            totalTax,
            totalAmountSum,
            taxDifProducts,
            invoiceTemplate,
            roundOff,
            handleChunksItems,
            DFontSize,
            exportToPDF,
            chunkValue,
            chunkOrderItem,
        };
    },
});
</script>
<style>
#sales_a4_2_invoice {
    text-align: center;
    color: #000;
    font-family: Arial, Helvetica, sans-serif;
}

#sales_a4_2_invoice p {
    margin: 0;
}

.a4_2_invoice_contact_info {
    display: flex;
    justify-content: flex-start;
    gap: 10px;
}

.a4_2_invoice_header_wraper {
    display: flex;
    justify-content: space-between;
    width: 100%;
    border: 1px solid #000;
    border-bottom: none;
}

.a4_2_invoice_header {
    width: 50%;
    text-align: left;
    padding: 10px;
}
.a4_2_invoice_order_details_wrapper {
    width: 50%;
    border-left: 1px solid #000;
}

.text {
    text-align: left;
}
.a4_2_invoice_customer {
    display: grid;
    grid-template-columns: 38% 2% 60%;
    text-align: left;
}

.a4_2_invoice_customer_address {
    text-align: left;
}

.a4_2_invoice_customer_details {
    padding: 10px;
}

.a4_2_invoice_order_details {
    padding: 10px;
}

.a4_2_invoice_customer_address > div:nth-child(1),
.a4_2_invoice_customer > div:nth-child(1),
.a4_2_invoice_customer_address > div:nth-child(2),
.a4_2_invoice_customer > div:nth-child(2) {
    font-weight: 900;
    text-align: left;
}

.a4_2_invoice_gst_total {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    justify-content: flex-start;
    padding: 10px;
    border-bottom: 1px solid #000;
}
.a4_2_invoice_gst_total_1 {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px;
    border-bottom: 1px solid #000;
    width: 100%;
}

.a4_2_invoice_gst_footer {
    display: grid;
    grid-template-columns: 45% 10% 45%;
    width: 27%;
}
.a4_2_invoice_gst_total_1 > p:nth-child(1) {
    width: 73%;
    text-align: left;
}
.a4_2_invoice_gst_footer > div:nth-child(1),
.a4_2_invoice_gst_footer > div:nth-child(2),
.a4_2_invoice_gst_footer > div:nth-child(4),
.a4_2_invoice_gst_footer > div:nth-child(5) {
    font-weight: 900;
}
.a4_2_invoice_gst_footer > div:nth-child(3),
.a4_2_invoice_gst_footer > div:nth-child(6) {
    text-align: right;
}

.a4_2_invoice_footer_calculation {
    width: 75%;
    display: flex;
    flex-direction: column;
    align-items: baseline;
}
.a4_2_invoice_footer_calculation > p {
    font-weight: 900;
}

.footer_tax {
    display: flex;
    justify-content: flex-start;
    gap: 5px;
}

.a4_2_invoice_bank_details {
    padding: 10px;
    border-bottom: 1px solid #000;
    font-weight: 900;
    text-align: left;
}

.a4_2_invoice_footer_last {
    display: grid;
    grid-template-columns: 1fr 1fr;
    text-align: left;
}

.a4_2_invoice_terms {
    padding: 10px;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
}
.a4_2_invoice_terms > p:nth-child(1) {
    text-decoration: underline;
}
.a4_2_receiver_signature {
    padding: 10px;
    font-weight: 900;
    text-align: left;
    border-top: 1px solid #000;
}

.a4_2_auth_signature {
    padding: 10px;
    font-weight: 900;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}

.a4_2_auth_signature > p:nth-child(1) {
    margin-bottom: 40px !important;
}

.a4_2_invoice_body_wraper {
    width: 100%;
    border-collapse: collapse;
}
.a4_2_invoice_body_wraper > tbody > td {
    padding: 0 3px;
}

.invoice_product_wrapper {
    min-height: 60mm;
}

.a4_2_invoice_footer_wrapper {
    border: 1px solid #000;
}

.seprator {
    width: 100%;
    padding: 10px;
    border-top: 1px solid #000;
}

.a4_2_invoice_gst_total_new {
    display: flex;
    justify-content: flex-start;
    align-items: flex-start;
    width: 100%;
    border-bottom: 1px solid #000;
    padding: 10px;
}
.a4_2_invoice_total_wrapper {
    width: 25%;
    display: flex;
    flex-direction: column;
    align-items: baseline;
    justify-content: flex-start;
}
.a4_2_invoice_gst_footer_new {
    display: grid;
    grid-template-columns: 45% 10% 45%;
    width: 100%;
    justify-items: flex-start;
}
.a4_2_invoice_gst_footer_new > div:nth-child(1),
.a4_2_invoice_gst_footer_new > div:nth-child(2) {
    font-weight: 900;
}
.a4_2_invoice_gst_footer_new > div:nth-child(3) {
    text-align: right;
    width: 100%;
}

.a4_2_invoice_signature {
    border-right: 1px solid #000;
}

.continue_page {
    margin: 0;
    font-weight: bold;
    page-break-after: always;
}
.footer-invoice-button {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1rem;
}

.a4_2_chunk_wrapper {
    min-height: 281mm;
    position: relative;
}
.a4_2_invoice_footer_wrapper {
    position: absolute;
    width: 100%;
    bottom: 20px;
}
@media print {
    @page {
        margin: 8mm 10mm;
    }
}
@media print {
    .A4_2_invoice {
        width: 100%;
    }
}
</style>
