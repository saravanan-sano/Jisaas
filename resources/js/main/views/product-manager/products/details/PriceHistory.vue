<template>
    <a-row>
        <a-col :span="24">
            <div class="table-responsive">
                <a-table
                    :columns="priceHistoryColumns"
                    :row-key="(record) => record.xid"
                    :data-source="priceHistory"
                    :pagination="{defaultPages: 10}"
                    @change="handleTableChange"
                >
                    <template #bodyCell="{ column, record }">
                        <template v-if="column.dataIndex === 'sale_price'">
                            {{ formatAmountCurrency(record.sale_price) }}
                        </template>
                        <template v-if="column.dataIndex === 'purchase_price'">
                            {{ formatAmountCurrency(record.purchase_price) }}
                        </template>
                        <template v-if="column.dataIndex === 'mrp'">
                            {{ formatAmountCurrency(record.mrp) }}
                        </template>
                        <template v-if="column.dataIndex === 'date'">
                            {{ formatDateTime(record.date) }}
                        </template>
                    </template>
                </a-table>
            </div>
        </a-col>
    </a-row>
</template>

<script>
import { defineComponent, ref, onMounted } from "vue";
import common from "../../../../../common/composable/common";
import fields from "./fields";

export default defineComponent({
    props: ["priceHistory"],
    setup(props) {
        const { priceHistoryColumns } = fields();
        const {
            formatDate,
            formatDateTime,
            formatAmountCurrency,
            getOrderTypeFromstring,
        } = common();

        return {
            priceHistoryColumns,
            formatDate,
            formatDateTime,
            formatAmountCurrency,
            getOrderTypeFromstring,
        };
    },
});
</script>
