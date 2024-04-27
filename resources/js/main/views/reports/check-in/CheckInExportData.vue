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

export default defineComponent({
    props: ["data", "dates", "staffMembers", "staffUserId"],
    components: {
        FileExcelOutlined,
    },
    setup(props, { emit }) {
        const loading = ref(false);

        const handleDownload = () => {
            loading.value = true;
            let FileName = "check_in_report";
            if (props.dates.length > 0 && props.staffUserId != undefined) {
                FileName = `${FilterStaffMember(props.staffUserId)} ${SplitDate(
                    props.dates
                )} check_in_report`;
            } else if (props.staffUserId != undefined) {
                FileName = `${FilterStaffMember(
                    props.staffUserId
                )} check_in_report`;
            } else if (props.dates.length > 0) {
                FileName = `${SplitDate(props.dates)} check_in_report`;
            }
            const columns = Object.keys(props.data[0]).map((key) => {
                return { key: key };
            });

            const newExportData = props.data.map((list) => {
                const newData = {};
                columns.forEach((column) => {
                    const { key } = column;
                    const originalKey = Object.keys(list).find(
                        (k) => columns.find((c) => c.key === k).key === key
                    );
                    if (key == "name") newData["Name"] = list[originalKey];
                    else if (key == "total_hours")
                        newData["Total Hours"] = `${list[originalKey]} Hours`;
                    else {
                        newData[key] = generateCheckinString(list[originalKey]);
                    }
                });
                return newData;
            });

            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.json_to_sheet(newExportData);
            XLSX.utils.book_append_sheet(wb, ws, "Sheet1");
            XLSX.writeFile(wb, `${FileName}.xlsx`);
            loading.value = false;
        };

        const generateCheckinString = (data) => {
            let string = "";
            if (data) {
                if (data.check_in_time && data.check_out_time) {
                    string = `In - ${data.check_in_time} Out - ${data.check_out_time} Working Hours - ${data.hours}`;
                } else if (data.check_in_time) {
                    string = `In - ${data.check_in_time} Out - N/A Working Hours - N/A`;
                }
            }
            return string;
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
