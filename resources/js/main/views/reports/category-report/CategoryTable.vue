<template>
    <div class="table-responsive">
        <a-table
            :columns="columns"
            :row-key="(record) => record.name"
            :data-source="data"
            :pagination="true"
            :loading="loading"
            @change="handleTableChange"
            bordered
        >
            <template #bodyCell="{ column, record }">
                <template v-if="column.dataIndex === 'name'">
                    {{ record.name }}
                </template>
                <template v-if="column.dataIndex === 'total_products'">
                    {{ record.total_products }}
                </template>
                <template v-if="column.dataIndex === 'quantity'">
                    {{ formatAmount(record.quantity) }}
                </template>
                <template v-if="column.dataIndex === 'subtotal'">
                    {{ formatAmountCurrency(record.subtotal) }}
                </template>
                <template v-if="column.dataIndex === 'discount'">
                    {{ formatAmountCurrency(record.discount) }}
                </template>
                <template v-if="column.dataIndex === 'tax_amount'">
                    {{ formatAmountCurrency(record.tax_amount) }}
                </template>
                <template v-if="column.dataIndex === 'total'">
                    {{ formatAmountCurrency(record.total) }}
                </template>
            </template>
            <template #expandedRowRender="record">
                <a-table
                    :row-key="(record) => record.name"
                    :columns="ExtendedColumns"
                    :data-source="record.record.products"
                    :pagination="true"
                >
                    <template #bodyCell="{ column, record }">
                        <template v-if="column.dataIndex === 'name'">
                            {{ record.name }}
                        </template>
                        <template v-if="column.dataIndex === 'hsn_sac_code'">
                            {{ record.hsn_sac_code }}
                        </template>
                        <template v-if="column.dataIndex === 'quantity'">
                            {{ formatAmount(record.quantity) }}
                        </template>
                        <template v-if="column.dataIndex === 'subtotal'">
                            {{ formatAmountCurrency(record.subtotal) }}
                        </template>
                        <template v-if="column.dataIndex === 'discount'">
                            {{ formatAmountCurrency(record.discount) }}
                        </template>
                        <template v-if="column.dataIndex === 'tax_amount'">
                            {{ formatAmountCurrency(record.tax_amount) }}
                        </template>
                        <template v-if="column.dataIndex === 'total'">
                            {{ formatAmountCurrency(record.total) }}
                        </template>
                    </template>
                </a-table>
            </template>
        </a-table>
    </div>
</template>

<script>
import { defineComponent, ref } from "vue";
import fields from "./fields";
import common from "../../../../common/composable/common";

export default defineComponent({
    props: ["data", "loading"],
    setup(props, { emit }) {
        const { formatAmountCurrency, formatAmount } = common();
        const { columns, ExtendedColumns } = fields();
        return {
            formatAmountCurrency,
            columns,
            ExtendedColumns,
            formatAmount
        };
    },
});
</script>
