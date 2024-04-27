<template>
    <div class="table-responsive">
        <a-descriptions
            title="Sales Based Payment Report"
            :column="1"
            bordered
        />
        <a-table
            :columns="PaymentsColumn"
            :row-key="(record) => record.xid"
            :data-source="data"
            :pagination="false"
            :loading="loading"
            @change="handleTableChange"
        >
            <template #bodyCell="{ column, record }">
                <template v-if="column.dataIndex === 'payment'">
                    {{ record.payment_mode }}
                </template>
                <template v-if="column.dataIndex === 'orders'">
                    {{ record.total_orders }}
                </template>
                <template v-if="column.dataIndex === 'amount'">
                    {{ formatAmountCurrency(record.total_amount) }}
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
        const { formatAmountCurrency } = common();
        const { PaymentsColumn } = fields();
        return {
            PaymentsColumn,
            formatAmountCurrency,
        };
    },
});
</script>
