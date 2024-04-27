<template>
    <div class="table-responsive">
        <a-table
            :columns="AssignedColumn"
            :row-key="(record) => record.staff_member"
            :data-source="data"
            :pagination="true"
            :loading="loading"
            @change="handleTableChange"
        >
            <template #bodyCell="{ column, record, index }">
                <template v-if="column.dataIndex === 'index'">
                    {{ index + 1 }}
                </template>
                <template v-if="column.dataIndex === 'name'">
                    {{ record.staff_member }}
                </template>
                <template v-if="column.dataIndex === 'customer_count'">
                    {{ record.customer_count }}
                </template>
            </template>
            <template #expandedRowRender="record">
                <a-table
                    v-if="record && record.record && record.record.customers"
                    :row-key="(record) => record.xid"
                    :columns="ExtendedCustomerColumn"
                    :data-source="record.record.customers"
                    :pagination="true"
                >
                    <template #bodyCell="{ column, record }">
                        <template v-if="column.dataIndex === 'name'">
                            {{ record.name }}
                        </template>
                        <template v-if="column.dataIndex === 'status'">
                            <a-tag :color="statusColors[record.status]">
                                {{ $t(`common.${record.status}`) }}
                            </a-tag>
                        </template>
                    </template>
                </a-table>
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
        const { AssignedColumn, ExtendedCustomerColumn } = fields();
        const { formatAmountCurrency, statusColors } = common();
        return {
            AssignedColumn,
            formatAmountCurrency,
            ExtendedCustomerColumn,
            statusColors,
        };
    },
});
</script>
