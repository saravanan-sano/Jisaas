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
    props: ["data", "dates", "customer"],
    components: {
        FileExcelOutlined,
    },
    setup(props, { emit }) {
        const { formatAmountCurrency } = common();
        const loading = ref(false);

        const handleDownload = () => {
            loading.value = true;
            let FileName = "Customers Products report";
            if (props.dates.length > 0 && props.customer != undefined) {
                FileName = `${SplitDate(props.dates)} ${
                    props.customer
                }Products report`;
            } else if (props.customer != undefined) {
                FileName = `products in ${props.customer}Products report`;
            } else if (props.dates.length > 0) {
                FileName = `${SplitDate(
                    props.dates
                )} Customers Products report`;
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
                    Customers: item.customer,
                    "Item Name": item.name,
                    Brand:
                        item.brand_name && item.brand_name.name
                            ? item.brand_name.name
                            : "",
                    Quantity: item.quantity,
                    Subtotal: formatAmountCurrency(item.subtotal),
                    Discount: formatAmountCurrency(item.discount),
                    "Tax Amount": formatAmountCurrency(item.tax_amount),
                    Total: formatAmountCurrency(item.total),
                    "HSN/SAC Code": item.hsn_sac_code,
                    "Customer Tax No": item.tax_number,
                });
            });

            newExportData.push({
                Customers: "",
                "Item Name": total_products,
                Quantity: total_quantity,
                Subtotal: formatAmountCurrency(total_subtotal),
                Discount: formatAmountCurrency(total_discount),
                "Tax Amount": formatAmountCurrency(total_tax_amount),
                Total: formatAmountCurrency(grand_total),
                "HSN/SAC Code": "",
                "Customer Tax No": "",
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
