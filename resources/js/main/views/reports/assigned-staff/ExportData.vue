<template>
    <a-button type="primary" @click="handleDownload" :loading="loading">
        <FileExcelOutlined /> {{ $t("common.excel") }}
    </a-button>
</template>
<script>
import { defineComponent, ref } from "vue";
import * as XLSX from "xlsx";
import { FileExcelOutlined } from "@ant-design/icons-vue";
import moment from "moment";
import common from "../../../../common/composable/common";

export default defineComponent({
    props: ["data", "columns", "filter", "title", "loading"],
    components: {
        FileExcelOutlined,
    },
    setup(props, { emit }) {
        const { formatDateTime } = common();
        const loading = ref(false);
        const handleDownload = () => {
            loading.value = true;
            const newExportData = [];

            // Iterate over each staff member
            props.data.forEach((staffData) => {
                // Add staff member and customer count
                newExportData.push({
                    "Staff Name": staffData.staff_member,
                    "Customer Count": staffData.customer_count,
                });

                // Add customers for the current staff member
                staffData.customers.forEach((customer) => {
                    newExportData.push({
                        "Staff Name": "",
                        "Customer Name": customer.name,
                        "Customer Email": customer.email ? customer.email : '-',
                        "Customer Phone": customer.phone ? customer.phone : '-',
                        "Customer Address": customer.address ? customer.address : '-',
                        "Customer Pincode": customer.pincode ? customer.pincode : '-',
                        // "Customer Status": customer.status,
                        "Onboarding Date & Time": formatDateTime(customer.created_at),
                    });
                });

                // Add a blank row between staff members for clarity
                newExportData.push({});
            });
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.json_to_sheet(newExportData);
            XLSX.utils.book_append_sheet(wb, ws, "Sheet1");
            XLSX.writeFile(
                wb,
                `${props.title}_${
                    moment(props.filter[0]).format("DD_MM_YYYY") +
                    "-" +
                    moment(props.filter[1]).format("DD_MM_YYYY")
                }.xlsx`
            );
            loading.value = false;
        };

        return {
            loading,
            handleDownload,
        };
    },
});
</script>
