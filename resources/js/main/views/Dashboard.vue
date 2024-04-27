<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="$t(`menu.dashboard`)" style="padding: 0px" />
        </template>
    </AdminPageHeader>

    <div
        class="dashboard-page-content-container"
        v-if="permsArray.includes('dashboard') || permsArray.includes('admin')"
    >
        <a-row :gutter="[18, 18]" class="mt-30 mb-20">
            <a-col :xs="24" :sm="24" :md="12" :lg="24" :xl="24">
                <a-row :gutter="[8, 8]" class="mb-10">
                    <a-col :xs="24" :sm="24" :md="12" :lg="14" :xl="14">
                        <a-radio-group
                            v-model:value="selectedDateType"
                            button-style="solid"
                            @change="updatedate"
                        >
                            <a-radio-button value="today">Today</a-radio-button>
                            <a-radio-button value="7days"
                                >7 Days</a-radio-button
                            >
                            <a-radio-button value="1month"
                                >1 Month</a-radio-button
                            >
                            <a-radio-button value="1year"
                                >1 Year</a-radio-button
                            >
                        </a-radio-group>
                    </a-col>
                    <a-col :xs="24" :sm="24" :md="12" :lg="10" :xl="10">
                        <DateRangePicker
                            ref="serachDateRangePicker"
                            @dateTimeChanged="
                                (changedDateTime) => {
                                    filters.dates = [
                                        dayjs(changedDateTime[0])
                                            .startOf('day')
                                            .add(1, 'day')
                                            .format('YYYY-MM-DD HH:mm:ss'),
                                        dayjs(changedDateTime[1])
                                            .endOf('day')
                                            .format('YYYY-MM-DD HH:mm:ss'),
                                    ];
                                }
                            "
                            v-model:value="selectedDate"
                        />
                    </a-col>
                </a-row>
            </a-col>
        </a-row>

        <a-row :gutter="[18, 18]" class="mt-30 mb-20">
            <a-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                <div class="mb-20">
                    <a-row :gutter="[15, 15]">
                        <a-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
                            <StateWidget>
                                <template #image>
                                    <LineChartOutlined
                                        style="color: #fff; font-size: 24px"
                                    />
                                </template>
                                <template #description>
                                    <h2 v-if="responseData.stateData">
                                        {{
                                            formatAmountCurrency(
                                                responseData.stateData
                                                    .totalSales
                                            )
                                        }}
                                        ({{
                                            responseData.stateData.totalNoSales
                                        }})
                                    </h2>
                                    <p>{{ $t("dashboard.total_sales") }}</p>
                                </template>
                            </StateWidget>
                        </a-col>

                        <a-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
                            <StateWidget>
                                <template #image>
                                    <ShoppingOutlined
                                        style="color: #fff; font-size: 24px"
                                    />
                                </template>
                                <template #description>
                                    <h2 v-if="responseData.stateData">
                                        {{
                                            formatAmountCurrency(
                                                responseData.stateData
                                                    .totalExpenses
                                            )
                                        }}
                                    </h2>
                                    <p>{{ $t("dashboard.total_expenses") }}</p>
                                </template>
                            </StateWidget>
                        </a-col>

                        <a-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
                            <StateWidget>
                                <template #image>
                                    <TagOutlined
                                        style="color: #fff; font-size: 24px"
                                    />
                                </template>
                                <template #description>
                                    <h2 v-if="responseData.stateData">
                                        {{
                                            formatAmountCurrency(
                                                responseData.stateData
                                                    .paymentSent
                                            )
                                        }}
                                    </h2>
                                    <p>{{ $t("dashboard.payment_sent") }}</p>
                                </template>
                            </StateWidget>
                        </a-col>

                        <a-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
                            <StateWidget>
                                <template #image>
                                    <BankOutlined
                                        style="color: #fff; font-size: 24px"
                                    />
                                </template>
                                <template #description>
                                    <h2 v-if="responseData.stateData">
                                        {{
                                            formatAmountCurrency(
                                                responseData.stateData
                                                    .paymentReceived
                                            )
                                        }}
                                    </h2>
                                    <p>
                                        {{ $t("dashboard.payment_received") }}
                                    </p>
                                </template>
                            </StateWidget>
                        </a-col>

                        <a-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
                            <StateWidget>
                                <template #image>
                                    <BankOutlined
                                        style="color: #fff; font-size: 24px"
                                    />
                                </template>
                                <template #description>
                                    <h2 v-if="responseData.stateData">
                                        {{
                                            formatAmountCurrency(
                                                responseData
                                                    .pendingPaymentToSend.total
                                            )
                                        }}
                                    </h2>
                                    <p>{{ $t("menu.payable") }}</p>
                                </template>
                            </StateWidget>
                        </a-col>

                        <a-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
                            <StateWidget>
                                <template #image>
                                    <BankOutlined
                                        style="color: #fff; font-size: 24px"
                                    />
                                </template>
                                <template #description>
                                    <h2 v-if="responseData.stateData">
                                        {{
                                            formatAmountCurrency(
                                                responseData
                                                    .pendingPaymentToReceive
                                                    .total
                                            )
                                        }}
                                    </h2>
                                    <p>{{ $t("menu.receivable") }}</p>
                                </template>
                            </StateWidget>
                        </a-col>
                    </a-row>
                </div>
            </a-col>
            <a-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                <a-card
                    :title="$t('dashboard.patment_pending_receive')"
                    :bodyStyle="{ padding: '0px' }"
                    v-if="responseData && responseData.pendingPaymentToSend"
                >
                    <a-tabs style="padding-left: 25px">
                        <a-tab-pane
                            v-if="
                                permsArray.includes('sales_view') ||
                                permsArray.includes('admin')
                            "
                            key="payable"
                            :tab="
                                $t('menu.payable') +
                                ' (' +
                                formatAmountCurrency(
                                    responseData.pendingPaymentToSend.total
                                ) +
                                ')'
                            "
                        >
                            <a-table
                                :columns="topCustomerColumns"
                                :row-key="(record) => record.customer_id"
                                :data-source="
                                    responseData.pendingPaymentToSend.data
                                "
                                :pagination="false"
                            >
                                <template #bodyCell="{ column, record }">
                                    <template
                                        v-if="column.dataIndex == 'customer_id'"
                                    >
                                        <user-info :user="record.customer" />
                                    </template>
                                    <template
                                        v-if="
                                            column.dataIndex == 'total_amount'
                                        "
                                    >
                                        {{
                                            formatAmountCurrency(
                                                record.total_amount
                                            )
                                        }}
                                        ({{ $t("dashboard.total_sales") }} :
                                        {{ record.total_sales }})
                                    </template>
                                </template>
                            </a-table></a-tab-pane
                        >
                        <a-tab-pane
                            v-if="
                                permsArray.includes('purchases_view') ||
                                permsArray.includes('admin')
                            "
                            key="receivable"
                            :tab="
                                $t('menu.receivable') +
                                ' (' +
                                formatAmountCurrency(
                                    responseData.pendingPaymentToReceive.total
                                ) +
                                ')'
                            "
                        >
                            <a-table
                                :columns="topCustomerColumns"
                                :row-key="(record) => record.customer_id"
                                :data-source="
                                    responseData.pendingPaymentToReceive.data
                                "
                                :pagination="false"
                            >
                                <template #bodyCell="{ column, record }">
                                    <template
                                        v-if="column.dataIndex == 'customer_id'"
                                    >
                                        <user-info :user="record.customer" />
                                    </template>
                                    <template
                                        v-if="
                                            column.dataIndex == 'total_amount'
                                        "
                                    >
                                        {{
                                            formatAmountCurrency(
                                                record.total_amount
                                            )
                                        }}
                                        ({{ $t("dashboard.total_sales") }} :
                                        {{ record.total_sales }})
                                    </template>
                                </template>
                            </a-table></a-tab-pane
                        >
                    </a-tabs>

                    <template
                        v-if="
                            permsArray.includes('users_view') ||
                            permsArray.includes('admin')
                        "
                        #extra
                    >
                        <a-button
                            class="mt-10"
                            type="link"
                            @click="
                                $router.push({
                                    name: 'admin.reports.users.index',
                                })
                            "
                        >
                            {{ $t("common.view_all") }}
                            <DoubleRightOutlined />
                        </a-button>
                    </template>
                </a-card>
            </a-col>
        </a-row>
        <a-row :gutter="[18, 18]" class="mt-30 mb-20">
            <a-col :xs="24" :sm="24" :md="12" :lg="6" :xl="6">
                <a-card :title="$t('dashboard.top_selling_product')">
                    <TopProducts :data="responseData" />
                </a-card>
                <a-card :title="$t('dashboard.least_selling_products')">
                    <LeastSellingProducts :data="responseData" />
                </a-card>
            </a-col>
            <a-col :xs="24" :sm="24" :md="12" :lg="18" :xl="18">
                <a-card :title="$t('dashboard.sales_purchases')">
                    <PurchaseSales :data="responseData" />
                    <template
                        v-if="
                            permsArray.includes('sales_view') ||
                            permsArray.includes('admin')
                        "
                        #extra
                    >
                        <a-button
                            class="mt-10"
                            type="link"
                            @click="
                                $router.push({
                                    name: 'admin.stock.sales.index',
                                })
                            "
                        >
                            {{ $t("common.view_all") }}
                            <DoubleRightOutlined />
                        </a-button>
                    </template>
                </a-card>

                <a-card :title="$t('payments.payments')" class="mt-20">
                    <PaymentsChart :data="responseData" />
                    <template
                        v-if="
                            permsArray.includes('order_payments_view') ||
                            permsArray.includes('admin')
                        "
                        #extra
                    >
                        <a-button
                            class="mt-10"
                            type="link"
                            @click="
                                $router.push({
                                    name: 'admin.reports.payments.index',
                                })
                            "
                        >
                            {{ $t("common.view_all") }}
                            <DoubleRightOutlined />
                        </a-button>
                    </template>
                </a-card>
            </a-col>
        </a-row>

        <a-row
            class="mt-30 mb-20 mob"
            v-if="
                (permsArray.includes('purchases_view') ||
                    permsArray.includes('sales_view') ||
                    permsArray.includes('purchase_returns_view') ||
                    permsArray.includes('sales_returns_view') ||
                    permsArray.includes('admin')) &&
                activeOrderType != ''
            "
        >
            <a-col :span="24">
                <a-card
                    :title="$t('dashboard.recent_stock_history')"
                    :bodyStyle="{ paddingTop: '0px' }"
                >
                    <template #extra>
                        <a-tabs v-model:activeKey="activeOrderType">
                            <a-tab-pane
                                v-if="
                                    permsArray.includes('sales_view') ||
                                    permsArray.includes('admin')
                                "
                                key="sales"
                                :tab="$t('menu.sales')"
                            />
                            <a-tab-pane
                                v-if="
                                    permsArray.includes('purchases_view') ||
                                    permsArray.includes('admin')
                                "
                                key="purchases"
                                :tab="$t('menu.purchases')"
                            />
                            <a-tab-pane
                                v-if="
                                    permsArray.includes(
                                        'purchase_returns_view'
                                    ) || permsArray.includes('admin')
                                "
                                key="purchase-returns"
                                :tab="$t('menu.purchase_returns')"
                            />
                            <a-tab-pane
                                v-if="
                                    permsArray.includes('sales_returns_view') ||
                                    permsArray.includes('admin')
                                "
                                key="sales-returns"
                                :tab="$t('menu.sales_returns')"
                            />
                        </a-tabs>
                    </template>
                    <a-row>
                        <a-col
                            :xs="24"
                            :sm="24"
                            :md="12"
                            :lg="6"
                            :xl="6"
                            class="col-border-right pt-30"
                        >
                            <a-row
                                v-if="responseData.stockHistoryStatsData"
                                :style="
                                    appSetting.rtl
                                        ? { marginLeft: '24px' }
                                        : { marginRight: '24px' }
                                "
                                class="stock-history-stats"
                            >
                                <a-col :span="24" class="sales mb-20">
                                    <a-statistic
                                        :title="
                                            $t('dashboard.total_sales_items')
                                        "
                                        :value="
                                            formatAmount(
                                                responseData
                                                    .stockHistoryStatsData
                                                    .totalSales
                                            )
                                        "
                                    />
                                </a-col>
                                <a-col :span="24" class="sales-returns mb-20">
                                    <a-statistic
                                        :title="
                                            $t(
                                                'dashboard.total_sales_returns_items'
                                            )
                                        "
                                        :value="
                                            formatAmount(
                                                responseData
                                                    .stockHistoryStatsData
                                                    .totalSalesReturn
                                            )
                                        "
                                    />
                                </a-col>
                                <a-col :span="24" class="purchases mb-20">
                                    <a-statistic
                                        :title="
                                            $t(
                                                'dashboard.total_purchases_items'
                                            )
                                        "
                                        :value="
                                            formatAmount(
                                                responseData
                                                    .stockHistoryStatsData
                                                    .totalPurchases
                                            )
                                        "
                                    />
                                </a-col>

                                <a-col
                                    :span="24"
                                    class="purchase-returns mb-20"
                                >
                                    <a-statistic
                                        :title="
                                            $t(
                                                'dashboard.total_purchase_returns_items'
                                            )
                                        "
                                        :value="
                                            formatAmount(
                                                responseData
                                                    .stockHistoryStatsData
                                                    .totalPurchaseReturn
                                            )
                                        "
                                    />
                                </a-col>
                            </a-row>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="12" :lg="18" :xl="18">
                            <OrderTable
                                :orderType="activeOrderType"
                                :filters="filters"
                                :perPageItems="5"
                            />
                        </a-col>
                    </a-row>
                </a-card>
            </a-col>
        </a-row>

        <a-row :gutter="[18, 18]" class="mt-30 mb-20">
            <a-col :xs="24" :sm="24" :md="12" :lg="16" :xl="16">
                <a-card
                    :title="$t('menu.stock_alert')"
                    :bodyStyle="{ padding: '0px' }"
                    v-if="responseData && responseData.stockAlerts"
                >
                    <a-table
                        :columns="stockQuantityColumns"
                        :row-key="(record) => record.xid"
                        :data-source="responseData.stockAlerts"
                        :pagination="false"
                    >
                        <template #bodyCell="{ column, record }">
                            <template
                                v-if="column.dataIndex === 'current_stock'"
                            >
                                {{
                                    `${record.current_stock} ${record.short_name}`
                                }}
                            </template>
                            <template
                                v-if="
                                    column.dataIndex === 'stock_quantitiy_alert'
                                "
                            >
                                {{
                                    `${record.stock_quantitiy_alert} ${record.short_name}`
                                }}
                            </template>
                        </template>
                    </a-table>
                    <template
                        v-if="
                            permsArray.includes('warehouses_view') ||
                            permsArray.includes('admin')
                        "
                        #extra
                    >
                        <a-button
                            class="mt-10"
                            type="link"
                            @click="
                                $router.push({
                                    name: 'admin.reports.stock.index',
                                })
                            "
                        >
                            {{ $t("common.view_all") }}
                            <DoubleRightOutlined />
                        </a-button>
                    </template>
                </a-card>
            </a-col>
            <a-col :xs="24" :sm="24" :md="12" :lg="8" :xl="8">
                <a-card
                    :title="$t('dashboard.top_customers')"
                    :bodyStyle="{ padding: '0px' }"
                    v-if="responseData && responseData.topCustomers"
                >
                    <a-table
                        :columns="topCustomerColumns"
                        :row-key="(record) => record.customer_id"
                        :data-source="responseData.topCustomers"
                        :pagination="false"
                    >
                        <template #bodyCell="{ column, record }">
                            <template v-if="column.dataIndex == 'customer_id'">
                                <user-info :user="record.customer" />
                            </template>
                            <template v-if="column.dataIndex == 'total_amount'">
                                {{ formatAmountCurrency(record.total_amount) }}
                                <br />
                                {{ $t("dashboard.total_sales") }} :
                                {{ record.total_sales }}
                            </template>
                        </template>
                    </a-table>
                    <template
                        v-if="
                            permsArray.includes('users_view') ||
                            permsArray.includes('admin')
                        "
                        #extra
                    >
                        <a-button
                            class="mt-10"
                            type="link"
                            @click="
                                $router.push({
                                    name: 'admin.reports.users.index',
                                })
                            "
                        >
                            {{ $t("common.view_all") }}
                            <DoubleRightOutlined />
                        </a-button>
                    </template>
                </a-card>
            </a-col>

            <a-col :xs="24" :sm="24" :md="12" :lg="16" :xl="16">
                <a-card
                    :title="$t('menu.hourly_sale')"
                    :bodyStyle="{ padding: '0px' }"
                    v-if="saleData && saleData"
                >
                    <a-table
                        :columns="daySalesColumns"
                        :row-key="(record) => record.customer_id"
                        :data-source="saleData"
                        :pagination="false"
                    >
                        <template #bodyCell="{ column, record }">
                            <template v-if="column.dataIndex == 'hour'">
                                {{ record.hour }}
                            </template>
                            <template v-if="column.dataIndex == 'order_count'">
                                {{ record.order_count }}
                            </template>
                            <template
                                v-if="column.dataIndex == 'average_order'"
                            >
                                {{ formatAmountCurrency(record.average_order) }}
                            </template>
                            <template v-if="column.dataIndex == 'total_sales'">
                                {{ formatAmountCurrency(record.total_sales) }}
                            </template>
                        </template>
                    </a-table>
                </a-card>
            </a-col>

            <a-col :xs="24" :sm="24" :md="12" :lg="8" :xl="8">
                <a-card
                    :title="$t('report.categorysale')"
                    :bodyStyle="{ padding: '0px' }"
                    v-if="categorySaleData && categorySaleData"
                >
                    <a-table
                        :columns="categoryColumns"
                        :row-key="(record) => record.customer_id"
                        :data-source="categorySaleData"
                        :pagination="false"
                    >
                        <template #bodyCell="{ column, record }">
                            <template
                                v-if="column.dataIndex == 'category_name'"
                            >
                                {{ record.category_name }}
                            </template>
                            <template
                                v-if="column.dataIndex == 'total_quantity'"
                            >
                                {{ record.total_quantity }}
                            </template>
                            <template v-if="column.dataIndex == 'total_value'">
                                {{ formatAmountCurrency(record.total_value) }}
                            </template>
                        </template>
                    </a-table>
                </a-card>
            </a-col>
        </a-row>
    </div>
    <template v-else>
        <a-result
            status="403"
            title="403"
            sub-title="Sorry, you do not possess the necessary permissions to access this page."
        />
    </template>
</template>

<script>
import { ref, onMounted, reactive, toRef, watch } from "vue";
import {
    EyeOutlined,
    ArrowUpOutlined,
    ArrowDownOutlined,
    LineChartOutlined,
    BankOutlined,
    ShoppingOutlined,
    TagOutlined,
    DoubleRightOutlined,
} from "@ant-design/icons-vue";
import { notification } from "ant-design-vue";
import { useI18n } from "vue-i18n";
import { useRoute } from "vue-router";
import common from "../../common/composable/common";
import TopProducts from "../components/charts/dashboard/TopProducts.vue";
import LeastSellingProducts from "../components/charts/dashboard/LeastSellingProducts.vue";
import PurchaseSales from "../components/charts/dashboard/PurchaseSales.vue";
import PaymentsChart from "../components/charts/dashboard/PaymentsChart.vue";
import StateWidget from "../../common/components/common/card/StateWidget.vue";
import Tiimeline from "../components/stock-history/Tiimeline.vue";
import OrderTable from "../components/order/OrderTable.vue";
import UserInfo from "../../common/components/user/UserInfo.vue";
import DateRangePicker from "../../common/components/common/calendar/DateRangePicker.vue";
import AdminPageHeader from "../../common/layouts/AdminPageHeader.vue";
import dayjs from "dayjs";

export default {
    components: {
        EyeOutlined,
        ArrowUpOutlined,
        ArrowDownOutlined,
        DoubleRightOutlined,
        StateWidget,
        TopProducts,
        LeastSellingProducts,
        PurchaseSales,
        PaymentsChart,
        OrderTable,
        UserInfo,
        Tiimeline,
        LineChartOutlined,
        BankOutlined,
        ShoppingOutlined,
        TagOutlined,
        DateRangePicker,
        AdminPageHeader,
    },
    setup() {
        const { t } = useI18n();
        const {
            formatAmountCurrency,
            appSetting,
            user,
            permsArray,
            selectedWarehouse,
            formatAmount,
        } = common();
        const activeOrderType = ref("");
        const filters = reactive({
            dates: [
                dayjs().startOf("day").format("YYYY-MM-DD HH:mm:ss"),
                dayjs().endOf("day").format("YYYY-MM-DD HH:mm:ss"),
            ],
        });
        const responseData = ref([]);
        const saleData = ref([]);
        const categorySaleData = ref([]);
        const route = useRoute();

        const stockQuantityColumns = [
            {
                title: t("product.product"),
                dataIndex: "product_name",
            },
            {
                title: t("product.quantity"),
                dataIndex: "current_stock",
            },
            {
                title: t("product.quantitiy_alert"),
                dataIndex: "stock_quantitiy_alert",
            },
        ];

        const topCustomerColumns = [
            {
                title: t("stock.customer"),
                dataIndex: "customer_id",
            },
            {
                title: t("payments.total_amount"),
                dataIndex: "total_amount",
            },
        ];

        const daySalesColumns = [
            {
                title: t("report.hour"),
                dataIndex: "hour",
            },
            {
                title: t("report.order_count"),
                dataIndex: "order_count",
            },
            {
                title: t("report.average_order"),
                dataIndex: "average_order",
            },
            {
                title: t("report.total_sales"),
                dataIndex: "total_sales",
            },
        ];

        const categoryColumns = [
            {
                title: t("report.category_name"),
                dataIndex: "category_name",
            },
            {
                title: t("report.total_quantity"),
                dataIndex: "total_quantity",
            },
            {
                title: t("report.total_value"),
                dataIndex: "total_value",
            },
        ];

        const selectedDateType = ref("today");
        const selectedDate = ref([
            dayjs().startOf("day"),
            dayjs().endOf("day"),
        ]);

        const updatedate = () => {
            const formatDate = (date) => {
                return date.format("YYYY-MM-DD HH:mm:ss");
            };

            if (selectedDateType.value === "today") {
                const startOfDay = dayjs().startOf("day");
                const endOfDay = dayjs().endOf("day");
                selectedDate.value = [startOfDay, endOfDay];
                filters.dates = [formatDate(startOfDay), formatDate(endOfDay)];
            } else if (selectedDateType.value === "7days") {
                const today = dayjs().endOf("day");
                const sevenDaysAgo = today.subtract(7, "day").startOf("day");
                selectedDate.value = [sevenDaysAgo, today];
                filters.dates = [formatDate(sevenDaysAgo), formatDate(today)];
            } else if (selectedDateType.value === "1month") {
                const today = dayjs().endOf("day");
                const oneMonthAgo = today.subtract(1, "month").startOf("day");
                selectedDate.value = [oneMonthAgo, today];
                filters.dates = [formatDate(oneMonthAgo), formatDate(today)];
            } else if (selectedDateType.value === "1year") {
                const today = dayjs().endOf("day");
                const oneYearAgo = today.subtract(1, "year").startOf("day");
                selectedDate.value = [oneYearAgo, today];
                filters.dates = [formatDate(oneYearAgo), formatDate(today)];
            }
        };

        onMounted(() => {
            const dashboardPromise = axiosAdmin.post("dashboard", filters);
            const dashboardPromise1 = axiosAdmin.post("dailyReport", filters);
            if (permsArray.value.includes("purchases_view")) {
                activeOrderType.value = "purchases";
            } else if (permsArray.value.includes("purchase_returns_view")) {
                activeOrderType.value = "purchase-returns";
            } else if (permsArray.value.includes("sales_returns_view")) {
                activeOrderType.value = "sales-returns";
            } else {
                activeOrderType.value = "sales";
            }

            // Message showing when comes from login page
            if (route.params && route.params.success) {
                notification.success({
                    message: t("common.welcome_back", [user.value.name]),
                    description: t("messages.login_success_dashboard"),
                    placement: "topRight",
                });
            }

            Promise.all([dashboardPromise]).then(([dashboardResponse]) => {
                responseData.value = dashboardResponse.data;
                saleData.value = responseData.value.saleData.totalSalesData;
                categorySaleData.value =
                    responseData.value.saleData.category_sale;
            });
            Promise.all([dashboardPromise1]).then(([dashboardResponse1]) => {
                //    console.log(dashboardResponse1.data);
            });
        });

        watch([filters, selectedWarehouse], (newVal, oldVal) => {
            axiosAdmin.post("dashboard", filters).then((response) => {
                responseData.value = response.data;
                saleData.value = responseData.value.saleData.totalSalesData;
                categorySaleData.value =
                    responseData.value.saleData.category_sale;
            });
        });

        return {
            filters,
            activeOrderType,
            responseData,
            formatAmount,
            stockQuantityColumns,
            topCustomerColumns,
            formatAmountCurrency,
            permsArray,
            appSetting,
            selectedDateType,
            selectedDate,
            updatedate,
            saleData,
            daySalesColumns,
            categorySaleData,
            categoryColumns,
            dayjs,
        };
    },
};
</script>

<style lang="less">
.ant-card-extra,
.ant-card-head-title {
    padding: 0px;
}

.ant-card-head-title {
    margin-top: 10px;
}

.stock-history-stats {
    margin-right: 20px;

    .sales {
        background: #e6f2ed;
        padding: 15px;
        border-radius: 10px;
    }

    .sales-returns {
        background: #ffefed;
        padding: 15px;
        border-radius: 10px;
    }

    .purchases {
        background: #eff3fe;
        padding: 15px;
        border-radius: 10px;
    }

    .purchase-returns {
        background: #f5f0df;
        padding: 15px;
        border-radius: 10px;
    }
}
</style>
