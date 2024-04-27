<template>
    <a-row>
        <a-col :span="24">
            <div class="table-responsive">
                <a-table
                    :columns="wholesaleColumns"
                    :row-key="(record) => record.xid"
                    :data-source="wholesale"
                    :pagination="{ defaultPages: 10 }"
                    @change="handleTableChange"
                >
                    <template #bodyCell="{ column, record }">
                        <template v-if="column.dataIndex === 'start_quantity'">
                            {{ record.start_quantity }}
                        </template>
                        <template v-if="column.dataIndex === 'end_quantity'">
                            {{ record.end_quantity }}
                        </template>
                        <template v-if="column.dataIndex === 'wholesale_price'">
                            {{ formatAmountCurrency(record.wholesale_price) }}
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
    props: ["wholesale"],
    setup(props) {
        const { wholesaleColumns } = fields();
        const {
            formatDate,
            formatDateTime,
            formatAmountCurrency,
            getOrderTypeFromstring,
        } = common();


        return {
            wholesaleColumns,
            formatDate,
            formatDateTime,
            formatAmountCurrency,
            getOrderTypeFromstring,
        };
    },
});
</script>
