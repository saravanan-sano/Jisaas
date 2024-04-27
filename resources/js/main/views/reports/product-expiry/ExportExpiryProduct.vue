<template>
    <a-space>
        <PdfTable :tableName="tableName" :title="title" />
        <PrintTable :tableName="tableName" />
        <a-button type="primary" @click="exportExcel">
            <FileExcelOutlined /> {{ $t("common.excel") }}
        </a-button>
    </a-space>
</template>

<script>
import { defineComponent } from "vue";
import common from "../../../../common/composable/common";
import { message } from "ant-design-vue";
import * as XLSX from "xlsx";
import { useI18n } from "vue-i18n";
import { FileExcelOutlined } from "@ant-design/icons-vue";
import PdfTable from "../../../components/report-exports/PdfTable.vue";
import PrintTable from "../../../components/report-exports/PrintTable.vue";

export default defineComponent({
    props: ["data", "tableName", "title"],
    components: { PdfTable, PrintTable, FileExcelOutlined },
    setup(props) {
        const { t } = useI18n();
        const { formatAmountCurrency, formatDate } = common();
        const exportExcel = () => {
            try {
                let products = props.data;
                if (products.length === 0) {
                    message.warning("No Report Founded");
                } else {
                    exportToExcel(products);
                }
            } catch (error) {
                console.error(error);
            }
        };
        const exportToExcel = (products) => {
            const productsWithCustomColumns = products.map((item) => {
                return {
                    [t("product.product")]: item.product.name,
                    [t("product.item_code")]: item.product.item_code,
                    [t("product.sales_price")]: formatAmountCurrency(
                        item.sales_price
                    ),
                    [t("product.purchase_price")]: formatAmountCurrency(
                        item.purchase_price
                    ),
                    [t("product.current_stock")]: item.current_stock,
                    [t("product.expiry")]: formatDate(item.expiry),
                };
            });
            const worksheet = XLSX.utils.json_to_sheet(
                productsWithCustomColumns
            );
            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, worksheet, "GSTR1");
            const excelBuffer = XLSX.write(workbook, {
                bookType: "xlsx",
                type: "array",
            });
            saveExcelFile(excelBuffer);
        };
        const saveExcelFile = (buffer) => {
            const data = new Blob([buffer], {
                type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            });
            const fileName = `Expiry Product Report ${moment().format(
                "DD_MM_yy"
            )}.xlsx`;
            if (navigator.msSaveBlob) {
                navigator.msSaveBlob(data, fileName);
            } else {
                const link = document.createElement("a");
                if (link.download !== undefined) {
                    // Modern browsers
                    const url = URL.createObjectURL(data);
                    link.setAttribute("href", url);
                    link.setAttribute("download", fileName);
                    link.style.visibility = "hidden";
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }
            }
        };
        return {
            formatAmountCurrency,
            exportExcel,
        };
    },
});
</script>
