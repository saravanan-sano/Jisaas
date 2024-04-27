<template>
    <a-row>
        <a-col :span="24">
            <div v-if="!loading" class="table-responsive">
                <a-table
                    :columns="salesMarginColumn"
                    :data-source="salesMarginData"
                    :row-key="(record) => record.xid"
                    :pagination="pagination"
                    :loading="loading"
                    @change="handleTableChange"
                    id="sales-margin-reports-table"
                >
                    <template #bodyCell="{ column, record }">
                        <template v-if="column.dataIndex === 'order_date'">
                            {{ formatDate(record.order_date) }}
                        </template>

                        <template v-if="column.dataIndex === 'purchase_price'">
                            {{ formatAmountCurrency(record.purchase_price) }}
                        </template>

                        <template v-if="column.dataIndex === 'margin'">
                            {{ formatAmountCurrency(record.margin) }}
                        </template>
                        <template v-if="column.dataIndex === 'sales_price'">
                            {{ formatAmountCurrency(record.sales_price) }}
                        </template>
                    </template>
                </a-table>
            </div>
        </a-col>
    </a-row>
</template>

<script>
import { defineComponent, ref, onMounted, watch } from "vue";
import common from "../../../../common/composable/common";
import UserInfo from "../../../../common/components/user/UserInfo.vue";
import fields from "./fields";
import PaymentStatus from "../../../../common/components/order/PaymentStatus.vue";
import * as _ from "lodash-es";
export default defineComponent({
    props: {
        paymentMode: null,
        dates: {
            default: [],
            type: null,
        },
    },
    emits: ["overallSummary"],
    components: {
        UserInfo,
        PaymentStatus,
    },
    setup(props, { emit }) {
        const { salesMarginColumn } = fields();
        const { formatDate, formatAmountCurrency, selectedWarehouse } =
            common();

        const salesMarginData = ref([]);

        const loading = ref(true);
        const pagination = ref({
            current: 1,
            pageSize: 10,
            showSizeChanger: true,
        });

        onMounted(() => {
            const propsData = props;
            getData(propsData);
        });

        const handleTableChange = (event) => {
            pagination.value = event;
        };

        const getData = (propsData) => {
            loading.value = true;
            const filterDate = { dates: propsData.dates };
            axiosAdmin
                .post("reports/profit-margin", filterDate)
                .then((response) => {
                    let res = response.data.sales;
                    const newGroup = Object.keys(res).map(function (key) {
                        return {
                            order_date: key,
                            purchase_price: _.sumBy(res[key], (item) =>
                                parseFloat(item.purchase_price)
                            ),
                            sales_price: _.sumBy(res[key], (item) =>
                                parseFloat(item.unit_price)
                            ),
                            margin:
                                _.sumBy(res[key], (item) =>
                                    parseFloat(item.unit_price)
                                ) -
                                _.sumBy(res[key], (item) =>
                                    parseFloat(item.purchase_price)
                                ),
                        };
                    });
                    salesMarginData.value = newGroup.reverse();

                    let overAllSummary = {
                        start_date: "",
                        end_date: "",
                        sales_price: 0,
                        purchase_price: 0,
                        margin: 0,
                    };

                    overAllSummary.start_date =
                        salesMarginData.value[0].order_date;
                    overAllSummary.end_date =
                        salesMarginData.value[
                            salesMarginData.value.length - 1
                        ].order_date;
                    overAllSummary.sales_price = _.sumBy(
                        salesMarginData.value,
                        (item) => parseFloat(item.sales_price)
                    );
                    overAllSummary.purchase_price = _.sumBy(
                        salesMarginData.value,
                        (item) => parseFloat(item.purchase_price)
                    );
                    overAllSummary.margin = _.sumBy(
                        salesMarginData.value,
                        (item) => parseFloat(item.margin)
                    );

                    emit("overallSummary", overAllSummary);
                    loading.value = false;
                });
        };

        watch(props, (newVal, oldVal) => {
            getData(newVal);
        });

        watch(selectedWarehouse, (newVal, oldVal) => {
            getData(props);
        });

        return {
            salesMarginColumn,
            salesMarginData,
            loading,
            pagination,
            formatDate,
            formatAmountCurrency,
            handleTableChange,
        };
    },
});
</script>
