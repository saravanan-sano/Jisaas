<template>
    <div class="table-responsive">
        <a-table
            :columns="ExpiryColumn"
            :row-key="(record) => record.xid"
            :data-source="data"
            :pagination="false"
            :loading="loading"
            @change="handleTableChange"
            id="product_expiry-reports-table"
        >
            <template #bodyCell="{ column, record }">
                <template v-if="column.dataIndex === 'name'">
                    {{ record.product.name }}
                </template>
                <template v-if="column.dataIndex === 'item_code'">
                    {{ record.product.item_code }}
                </template>
                <template v-if="column.dataIndex === 'sales_price'">
                    <div class="editable-cell">
                        <div
                            v-if="editField == record.xid"
                            class="editable-cell-input-wrapper"
                        >
                            <a-input
                                v-model:value="record.sales_price"
                                @pressEnter="save(record)"
                            />
                            <CheckOutlined
                                class="editable-cell-icon-check"
                                @click="save(record)"
                            />
                        </div>
                        <div v-else class="editable-cell-text-wrapper">
                            {{ formatAmountCurrency(record.sales_price) }}
                            <EditOutlined
                                class="editable-cell-icon"
                                @click="() => (editField = record.xid)"
                            />
                        </div>
                    </div>
                </template>
                <template v-if="column.dataIndex === 'purchase_price'">
                    {{ formatAmountCurrency(record.purchase_price) }}
                </template>
                <template v-if="column.dataIndex === 'current_stock'">
                    {{ record.current_stock }}
                </template>
                <template v-if="column.dataIndex === 'expiry'">
                    {{ formatDate(record.expiry) }}
                </template>
            </template>
        </a-table>
    </div>
</template>

<script>
import { defineComponent, ref } from "vue";
import fields from "./fields";
import common from "../../../../common/composable/common";
import { EditOutlined, CheckOutlined } from "@ant-design/icons-vue";
import { message } from "ant-design-vue";

export default defineComponent({
    props: ["data", "loading"],
    components: { EditOutlined, CheckOutlined },
    setup() {
        const { formatAmountCurrency, formatDate } = common();
        const { ExpiryColumn } = fields();
        const editField = ref("");

        const save = (record) => {
            let data = {
                id: record.xid,
                sales_price: parseInt(record.sales_price),
            };
            axiosAdmin
                .post(`/update-sales-price`, data)
                .then((res) => {
                    message.success(res.message);
                    editField.value = "";
                })
                .catch((err) => {
                    message.error("Something went wrong");
                });
        };
        return {
            ExpiryColumn,
            formatAmountCurrency,
            formatDate,
            editField,
            save,
        };
    },
});
</script>
