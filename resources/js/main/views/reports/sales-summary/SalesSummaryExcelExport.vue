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
import { useI18n } from "vue-i18n";

export default defineComponent({
    props: ["date", "staffUserId", "staffMembers"],
    components: {
        FileExcelOutlined,
    },
    setup(props, { emit }) {
        const { formatDateTime, formatAmountCurrency } = common();
        const loading = ref(false);
        const { t } = useI18n();

        const handleDownload = async () => {
            loading.value = true;
            let filters = "";
            let FileName = "overall sales-summary";
            if (props.date.length > 0 && props.staffUserId != undefined) {
                filters = `filters=staff_user_id eq "${props.staffUserId}"&dates=${props.date}&hashable=${props.staffUserId}&`;
                FileName = `${FilterStaffMember(props.staffUserId)} ${SplitDate(
                    props.date
                )} sales-summary`;
            } else if (props.staffUserId != undefined) {
                filters = `filters=staff_user_id eq "${props.staffUserId}"&hashable=${props.staffUserId}&`;
                FileName = `${FilterStaffMember(
                    props.staffUserId
                )} sales-summary`;
            } else if (props.date.length > 0) {
                filters = `dates=${props.date}&`;
                FileName = `${SplitDate(props.date)} sales-summary`;
            }
            const query = `sales?fields=id,xid,order_date,invoice_number,total,payment_status,user_id,x_user_id,user{id,xid,name,profile_image,profile_image_url,user_type},staff_user_id,x_staff_user_id,staffMember{id,xid,name,profile_image,profile_image_url,user_type}&${filters}order=order_date desc&offset=0&limit=10000`;

            let newExportData = [];
            let totalCount = 0;
            let totalAmount = 0;
            try {
                const res = await axiosAdmin.get(query);
                newExportData = await Promise.all(
                    res.data.map(async (item) => {
                        totalAmount += item.total;
                        totalCount++;
                        return {
                            [t("stock.order_date")]: formatDateTime(
                                item.order_date
                            ),
                            [t("stock.invoice_number")]: item.invoice_number,
                            [t("common.party")]:
                                item && item.user ? item.user.name : "N/A",
                            [t("payments.amount")]: formatAmountCurrency(
                                item.total
                            ),
                            [t("payments.payment_status")]: item.payment_status,
                            [t("common.created_by")]:
                                item && item.staff_member
                                    ? item.staff_member.name
                                    : "N/A",
                        };
                    })
                );
            } catch (err) {
                console.log(err);
            }

            newExportData.push({
                [t("stock.order_date")]: `Total Count = ${totalCount}`,
                [t("stock.invoice_number")]: "",
                [t("common.party")]: "",
                [t("payments.amount")]: `Total Amount = ${formatAmountCurrency(
                    totalAmount
                )}`,
                [t("payments.payment_status")]: "",
                [t("common.created_by")]: "",
            });

            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.json_to_sheet(newExportData);
            const defaultWidth = 20;
            const range = XLSX.utils.decode_range(ws["!ref"]);
            for (let C = range.s.c; C <= range.e.c; ++C) {
                ws["!cols"] = ws["!cols"] || [];
                ws["!cols"][C] = { width: defaultWidth };
            }
            XLSX.utils.book_append_sheet(wb, ws, "Sheet1");
            XLSX.writeFile(wb, `${FileName}.xlsx`);
            loading.value = false;
        };

        const FilterStaffMember = (xid) => {
            let staff = "staff";
            props.staffMembers.forEach((staffs) => {
                if (staffs.xid === xid) {
                    staff = staffs.name;
                }
            });
            return staff;
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
