<template>
    <div class="table-responsive">
        <a-table
            :columns="Columns"
            :row-key="(record) => record.name"
            :data-source="data"
            :pagination="true"
            :loading="loading"
            :scroll="{ x: 1500, y: 300 }"
            @change="handleTableChange"
            bordered
        >
            <template #bodyCell="{ column, record }">
                <template v-if="column.dataIndex === 'name'">
                    {{ record.name }}
                </template>
                <template v-if="column.dataIndex === 'total_hours'">
                    {{ record.total_hours }} Hours
                </template>
                <template
                    v-if="
                        column.dataIndex === column.dataIndex &&
                        column.dataIndex !== 'name' &&
                        column.dataIndex !== 'total_hours'
                    "
                >
                    <div
                        v-if="record[column.dataIndex]"
                        style="
                            display: flex;
                            flex-direction: column;
                            align-items: flex-start;
                            gap: 2px;
                        "
                        @click="
                            () =>
                                viewDetails({
                                    name: `${record.name} ${column.dataIndex}`,
                                    details: record[column.dataIndex],
                                })
                        "
                    >
                        <a-tag style="width: 100%" color="#87d068">
                            {{
                                record[column.dataIndex].check_in_time
                                    ? record[column.dataIndex].check_in_time
                                    : "--:--:--"
                            }}
                        </a-tag>
                        <a-tag style="width: 100%" color="#2db7f5">
                            {{
                                record[column.dataIndex].check_out_time
                                    ? record[column.dataIndex].check_out_time
                                    : "--:--:--"
                            }}</a-tag
                        >
                        <a-tag style="width: 100%" color="#108ee9">
                            {{
                                record[column.dataIndex].hours
                                    ? record[column.dataIndex].hours
                                    : "--"
                            }}</a-tag
                        >
                    </div>
                </template>
            </template>
        </a-table>
        <a-modal
            :title="viewData.name"
            v-model:visible="isOpen"
            width="70%"
            :footer="false"
        >
            <a-descriptions
                :title="null"
                :column="2"
                :labelStyle="{ fontWeight: 'bold' }"
            >
                <a-descriptions-item label="Check In Time">
                    {{
                        viewData.details.check_in_time
                            ? viewData.details.check_in_time
                            : "--:--:--"
                    }}
                </a-descriptions-item>
                <a-descriptions-item label="Check Out Time">
                    {{
                        viewData.details.check_out_time
                            ? viewData.details.check_out_time
                            : "--:--:--"
                    }}
                </a-descriptions-item>
                <a-descriptions-item label="Check In Location">
                    {{
                        viewData.details.check_in_location_details
                            ? viewData.details.check_in_location_details
                            : "--:--:--"
                    }}
                </a-descriptions-item>
                <a-descriptions-item label="Check Out Location">
                    {{
                        viewData.details.check_out_location_details
                            ? viewData.details.check_out_location_details
                            : "--:--:--"
                    }}
                </a-descriptions-item>
                <a-descriptions-item label="Check In IP">
                    {{
                        viewData.details.check_in_ip
                            ? viewData.details.check_in_ip
                            : "--:--:--"
                    }}
                </a-descriptions-item>
                <a-descriptions-item label="Check Out IP">
                    {{
                        viewData.details.check_out_ip
                            ? viewData.details.check_out_ip
                            : "--:--:--"
                    }}
                </a-descriptions-item>
                <a-descriptions-item label="Hours">
                    {{
                        viewData.details.hours
                            ? viewData.details.hours
                            : "--:--:--"
                    }}
                </a-descriptions-item>
            </a-descriptions>
        </a-modal>
    </div>
</template>

<script>
import dayjs from "dayjs";
import { defineComponent, ref } from "vue";
import common from "../../../../common/composable/common";
import moment from "moment";

export default defineComponent({
    props: ["data", "loading", "Columns"],
    setup(props, { emit }) {
        const { formatAmountCurrency, statusColors } = common();
        const viewData = ref("");
        const isOpen = ref(false);
        const viewDetails = (data) => {
            viewData.value = data;
            isOpen.value = !isOpen.value;
        };

        const formatTime = (time) => {
            // let newTime
            return moment(time).format('HH:mm A');
        };
        return {
            formatAmountCurrency,
            statusColors,
            viewData,
            isOpen,
            viewDetails,
            formatTime,
        };
    },
});
</script>
