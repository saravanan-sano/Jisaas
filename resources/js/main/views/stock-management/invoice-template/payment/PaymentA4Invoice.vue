<template>
    <a-modal
        :visible="visible"
        :centered="true"
        :maskClosable="false"
        title="Payment Invoice"
        width="22cm"
        @cancel="onClose"
        bodyStyle="height: 79vh;overflow: scroll;"
    >
        <div id="payment_a4_invoice" class="payment_invoice">
            <header class="payment_a4_header">
                <div class="payment_a4_h_sec_1">
                    <p
                        class="payment_a4_invoice_name"
                        :style="`font-size: ${DFontSize + 4}px`"
                    >
                        {{
                            paymentData.payment_type === "in"
                                ? "PAYMENT VOUCHER"
                                : "PAYMENT"
                        }}
                    </p>
                    <p
                        class="payment_a4_invoice_no"
                        :style="`font-size: ${DFontSize}px`"
                    >
                        {{ paymentData.payment_number }}
                    </p>
                </div>
                <div class="payment_a4_h_sec_2">
                    <div class="payment_a4_h_content">
                        <p
                            class="payment_a4_h_c_title"
                            :style="`font-size: ${DFontSize}px`"
                        >
                            Posting Data:
                        </p>
                        <p
                            class="payment_a4_h_c_note"
                            :style="`font-size: ${DFontSize}px`"
                        >
                            {{ formatDate(paymentData.date) }}
                        </p>
                    </div>
                    <div class="payment_a4_h_content">
                        <p
                            class="payment_a4_h_c_title"
                            :style="`font-size: ${DFontSize}px`"
                        >
                            Mode of Payment:
                        </p>
                        <p
                            class="payment_a4_h_c_note"
                            :style="`font-size: ${DFontSize}px`"
                        >
                            {{
                                paymentData.payment_mode &&
                                paymentData.payment_mode.name
                                    ? paymentData.payment_mode.name
                                    : ""
                            }}
                        </p>
                    </div>
                    <div class="payment_a4_h_content">
                        <p
                            class="payment_a4_h_c_title"
                            :style="`font-size: ${DFontSize}px`"
                        >
                            Party Name:
                        </p>
                        <p
                            class="payment_a4_h_c_note"
                            :style="`font-size: ${DFontSize}px`"
                        >
                            {{
                                paymentData.user && paymentData.user.name
                                    ? paymentData.user.name
                                    : ""
                            }}
                        </p>
                    </div>
                    <div class="payment_a4_h_content">
                        <p
                            class="payment_a4_h_c_title"
                            :style="`font-size: ${DFontSize}px`"
                        >
                            Paid Amount:
                        </p>
                        <p
                            class="payment_a4_h_c_note"
                            :style="`font-size: ${DFontSize}px`"
                        >
                            {{ formatAmountCurrency(paymentData.amount) }}
                        </p>
                    </div>
                </div>
            </header>
            <table class="payment_a4_content">
                <thead>
                    <tr class="payment_a4_tr">
                        <th
                            class="payment_a4_th_1"
                            :style="`font-size: ${DFontSize}px`"
                        >
                            S.no
                        </th>
                        <th
                            class="payment_a4_th_2"
                            :style="`font-size: ${DFontSize}px`"
                        >
                            Type
                        </th>
                        <th
                            class="payment_a4_th_3"
                            :style="`font-size: ${DFontSize}px`"
                        >
                            Name
                        </th>
                        <th
                            class="payment_a4_th_4"
                            :style="`font-size: ${DFontSize}px`"
                        >
                            Due Date
                        </th>
                        <th
                            class="payment_a4_th_5"
                            :style="`font-size: ${DFontSize}px`"
                        >
                            Allocated
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        class="payment_a4_tr"
                        v-for="(item, i) in paymentData.orders"
                        :key="item.order.xid"
                    >
                        <td
                            class="payment_a4_td_1"
                            :style="`font-size: ${DFontSize}px`"
                        >
                            {{ i + 1 }}
                        </td>
                        <td
                            class="payment_a4_td_2"
                            :style="`font-size: ${DFontSize}px`"
                        >
                            {{ `${item.order.invoice_type} Invoice` }}
                        </td>
                        <td
                            class="payment_a4_td_3"
                            :style="`font-size: ${DFontSize}px`"
                        >
                            {{ item.order.invoice_number }}
                        </td>
                        <td
                            class="payment_a4_td_4"
                            :style="`font-size: ${DFontSize}px`"
                        >
                            {{ formatDate(paymentData.date) }}
                        </td>
                        <td
                            class="payment_a4_td_5"
                            :style="`font-size: ${DFontSize}px`"
                        >
                            {{ formatAmountCurrency(item.amount) }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <footer class="payment_a4_footer">
                <div class="payment_a4_footer_content">
                    <p
                        class="payment_a4_footer_title"
                        :style="`font-size: ${DFontSize}px`"
                    >
                        Remarks:
                    </p>
                    <p
                        class="payment_a4_footer_note"
                        :style="`font-size: ${DFontSize}px`"
                    >
                        {{ paymentData.notes ? paymentData.notes : "" }}
                    </p>
                </div>
                <div class="payment_a4_footer_content">
                    <p
                        class="payment_a4_footer_title"
                        :style="`font-size: ${DFontSize}px`"
                    >
                        Receiver Signature:
                    </p>
                    <p
                        class="payment_a4_footer_note"
                        :style="`font-size: ${DFontSize}px`"
                    ></p>
                </div>
            </footer>
        </div>
        <template #footer>
            <div class="footer-invoice-button">
                <a-button type="primary" v-print="'#payment_a4_invoice'">
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
        {{ FetchOrderPayments }}
    </a-modal>
</template>
<script>
import { computed, ref } from "vue";
import { PrinterOutlined, FilePdfOutlined } from "@ant-design/icons-vue";
import common from "../../../../../common/composable/common";
import html2pdf from "html2pdf.js";

export default {
    props: ["visible", "data"],
    emits: ["closed"],
    components: {
        PrinterOutlined,
        FilePdfOutlined,
    },
    setup(props, { emit }) {
        const { appSetting, formatAmountCurrency, formatDate } = common();
        const company = appSetting.value;
        const invoiceTemplate = ref(JSON.parse(company.invoice_template));
        const DFontSize =
            invoiceTemplate.value && invoiceTemplate.value.font_size
                ? invoiceTemplate.value.font_size
                : 10;
        const paymentData = ref({});

        const FetchOrderPayments = computed(() => {
            if (props.visible) {
                axiosAdmin
                    .post("order/payment-id", { payment_id: props.data.xid })
                    .then((res) => {
                        paymentData.value = {
                            ...props.data,
                            orders: res.data.order.filter(
                                (order) => order.amount > 0
                            ),
                        };
                    })
                    .catch((err) => {
                        console.log(err);
                    });
            }
        });

        const onClose = () => {
            emit("closed");
        };

        const exportToPDF = () => {
            var element = document.getElementById("sales_a4_invoice");
            var opt = {
                margin: 3,
                filename: `${paymentData.value.payment_number}.pdf`,
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
            onClose,
            DFontSize,
            FetchOrderPayments,
            paymentData,
            formatAmountCurrency,
            formatDate,
            exportToPDF,
        };
    },
};
</script>
<style>
#payment_a4_invoice p {
    margin: 0;
}

.payment_a4_h_sec_1 {
    text-align: center;
    margin-bottom: 1rem;
    border-bottom: 2px solid #aeaeae;
}

.payment_a4_h_sec_2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px 1rem;
}

.payment_a4_h_content {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.payment_a4_h_c_title {
    font-weight: 900;
}

.payment_a4_content {
    width: 100%;
    margin: 1rem 0;
}

.payment_a4_footer {
    width: 50%;
}

.payment_a4_footer_content {
    display: flex;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.payment_a4_footer_title {
    width: 35%;
    font-weight: 900;
}

.payment_a4_footer_note {
    width: 65%;
    white-space: break-spaces;
}

div.payment_a4_h_content:nth-child(1) > p:nth-child(2) {
    font-weight: 900;
}

div.payment_a4_h_content:nth-child(4) > p:nth-child(2) {
    font-weight: 900;
}

.payment_a4_th_1,
.payment_a4_th_2,
.payment_a4_th_3,
.payment_a4_th_4,
.payment_a4_th_5,
.payment_a4_td_1,
.payment_a4_td_2,
.payment_a4_td_3,
.payment_a4_td_4,
.payment_a4_td_5 {
    padding: 2px 5px;
}
.payment_a4_th_1,
.payment_a4_th_2,
.payment_a4_th_3,
.payment_a4_th_4,
.payment_a4_th_5 {
    background: #d5d5d5;
}

.payment_a4_th_1 {
    width: 5%;
    border: 1px solid #000;
}
.payment_a4_th_2,
.payment_a4_th_3,
.payment_a4_th_4,
.payment_a4_th_5 {
    border: 1px solid #000;
    border-left: none;
    text-align: left;
}

/* Table Data */
.payment_a4_td_1 {
    border: 1px solid #000;
    border-top: none;
}
.payment_a4_td_2 {
    text-transform: capitalize;
}
.payment_a4_td_2,
.payment_a4_td_3,
.payment_a4_td_4,
.payment_a4_td_5 {
    border: 1px solid #000;
    border-left: none;
    border-top: none;
}

/* Table Alignment */
.payment_a4_th_5,
.payment_a4_td_5 {
    text-align: right;
}

#payment_a4_invoice {
    height: 281mm;
    position: relative;
}

.payment_a4_footer {
    position: absolute;
    bottom: 0;
}
</style>
