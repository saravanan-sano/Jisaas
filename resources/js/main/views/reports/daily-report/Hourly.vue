<template>
    <div class="table-responsive">
        <a-descriptions
            title="Hourly Based Sales Report"
            :column="1"
            bordered
        />
        <a-table
            :columns="HourlyColumn"
            :row-key="(record) => record.xid"
            :data-source="data"
            :pagination="false"
            :loading="loading"
            @change="handleTableChange"
        >
        <template #bodyCell="{ column, record }">
            <template v-if="column.dataIndex === 'hour'">
                {{ record.hour }}
            </template>
            <template v-if="column.dataIndex === 'order_count'">
                {{ record.order_count }}
            </template>
            <template v-if="column.dataIndex === 'average_order'">
                {{ formatAmountCurrency(record.average_order) }}
            </template>
            <template v-if="column.dataIndex === 'total_sales'">
                {{ formatAmountCurrency(record.total_sales) }}
            </template>
        </template>
    </a-table>
    </div>
</template>

<script>
import { defineComponent } from "vue";
import fields from "./fields";
import common from "../../../../common/composable/common";

export default defineComponent({
    props: ["data", "loading"],
    setup(props, { emit }) {
        const {formatAmountCurrency} = common()
        const { HourlyColumn } = fields();
        return {
            HourlyColumn,
            formatAmountCurrency
        };
    },
});
</script>
