<template>
    <a-button type="primary" @click="handleDownload" :loading="loading">
        <FileExcelOutlined /> {{ $t("common.excel") }}
    </a-button>
</template>
<script>
import { defineComponent, ref } from "vue";
import { FileExcelOutlined } from "@ant-design/icons-vue";
import moment from "moment";
import * as XLSX from "xlsx";
import common from "../../../../common/composable/common";

export default defineComponent({
    props: ["data", "dates", "categories"],
    components: {
        FileExcelOutlined,
    },
    setup(props, { emit }) {
        const { formatAmountCurrency } = common();
        const loading = ref(false);

        const handleDownload = () => {
            loading.value = true;
            let FileName = "products in all category report";
            if (props.dates.length > 0 && props.categories != undefined) {
                FileName = `${SplitDate(props.dates)} products in ${
                    props.categories
                }_category report`;
            } else if (props.categories != undefined) {
                FileName = `products in ${props.categories}_category report`;
            } else if (props.dates.length > 0) {
                FileName = `${SplitDate(
                    props.dates
                )} products in category report`;
            }
            let newExportData = [];
            let total_products = 0;
            let total_quantity = 0;
            let total_subtotal = 0;
            let total_discount = 0;
            let total_tax_amount = 0;
            let grand_total = 0;
            props.data.forEach((item, index) => {
                total_products = index + 1;
                total_quantity += item.quantity;
                total_subtotal += item.subtotal;
                total_discount += item.discount;
                total_tax_amount += item.tax_amount;
                grand_total += item.total;
                newExportData.push({
                    Category: item.category,
                    "Item Name": item.name,
                    Quantity: item.quantity,
                    Subtotal: formatAmountCurrency(item.subtotal),
                    Discount: formatAmountCurrency(item.discount),
                    "Tax Amount": formatAmountCurrency(item.tax_amount),
                    Total: formatAmountCurrency(item.total),
                    "HSN/SAC Code": item.hsn_sac_code,
                });
            });

            newExportData.push({
                Category: "",
                "Item Name": total_products,
                Quantity: total_quantity,
                Subtotal: formatAmountCurrency(total_subtotal),
                Discount: formatAmountCurrency(total_discount),
                "Tax Amount": formatAmountCurrency(total_tax_amount),
                Total: formatAmountCurrency(grand_total),
                "HSN/SAC Code": "",
            });


            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.json_to_sheet(newExportData);
            XLSX.utils.book_append_sheet(wb, ws, "Sheet1");
            XLSX.writeFile(wb, `${FileName}.xlsx`);
            loading.value = false;
        };

        const SplitDate = (dates) => {
            return `${moment(dates[0]).format("DD_MM_YYYY")} - ${moment(
                dates[1]
            ).format("DD_MM_YYYY")}`;
        };

        return {
            loading,
            handleDownload,
        };
    },
});
</script>
