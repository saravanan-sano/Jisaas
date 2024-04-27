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
    props: ["reportData", "date", "staffUserId", "staffMembers"],
    components: {
        FileExcelOutlined,
    },
    setup(props, { emit }) {
        const { formatAmountCurrency } = common();
        const loading = ref(false);
        const { t } = useI18n();
        const handleDownload = async () => {
            loading.value = true;
            let FileName = "overall payment-in";
            let staffName = FilterStaffMember(props.staffUserId);
            if (props.date.length > 0 && props.staffUserId != undefined) {
                FileName = `${FilterStaffMember(props.staffUserId)} ${SplitDate(
                    props.date
                )} payment-in`;
            } else if (props.staffUserId != undefined) {
                FileName = `${FilterStaffMember(props.staffUserId)} payment-in`;
            } else if (props.date.length > 0) {
                FileName = `${SplitDate(props.date)} payment-in`;
            }

            let newExportData = [];
            try {
                newExportData = await Promise.all(
                    props.reportData.paymentList.map(async (item) => {
                        return {
                            [t("payments.date")]: item.date,
                            [t("payments.transaction_number")]:
                                item.payment_number,
                            [t("payments.user")]:
                                item.user && item.user.name
                                    ? item.user.name
                                    : "",
                            [t("payments.amount")]: formatAmountCurrency(
                                item.amount
                            ),
                            [t("staff_member.staff")]: staffName,
                            [t("payments.payment_mode")]:
                                item.payment_mode && item.payment_mode
                                    ? item.payment_mode.name.toUpperCase()
                                    : "",
                        };
                    })
                );
            } catch (err) {
                console.log(err);
            }
            const totalData = props.reportData.total;
            newExportData.push({
                [t("payments.date")]: `OverAll Collection = (${Object.keys(
                    totalData
                )
                    .map(
                        (key) =>
                            `${key.toUpperCase()}:${formatAmountCurrency(
                                totalData[key]
                            )}`
                    )
                    .join(", ")})`,
                [t("payments.transaction_number")]: "",
                [t("payments.user")]: "",
                [t("payments.amount")]: "",
                [t("staff_member.staff")]: "",
                [t("payments.payment_mode")]: "",
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
