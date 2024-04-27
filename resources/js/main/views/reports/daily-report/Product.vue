<template>
    <div class="table-responsive">
        <a-descriptions
            title="Product Based Sales Report"
            :column="1"
            bordered
        />
        <a-table
            :columns="ProductColumn"
            :row-key="(record) => record.xid"
            :data-source="data"
            :pagination="false"
            :loading="loading"
            @change="handleTableChange"
        >
            <template #bodyCell="{ column, record }">
                <template v-if="column.dataIndex === 'product_name'">
                    {{ record.product_name }}
                </template>
                <template v-if="column.dataIndex === 'total_quantity'">
                    {{ record.total_quantity }}
                </template>
                <template v-if="column.dataIndex === 'total_value'">
                    {{ formatAmountCurrency(record.total_value) }}
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
        const { ProductColumn } = fields();
        const {formatAmountCurrency} =common()
        return {
            ProductColumn,
            formatAmountCurrency
        };
    },
});
</script>
