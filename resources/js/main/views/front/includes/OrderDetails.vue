<template>
    <div v-if="order.cancelled">
        <a-alert
            :message="$t('online_orders.order_cancelled')"
            :description="$t('online_orders.order_cancelled_message')"
            type="error"
            show-icon
        >
            <template #icon><stop-outlined /></template>
        </a-alert>
    </div>
    <div v-else>
        <order-status :orderStatus="order.order_status" />
    </div>

    <div class="item-desc mt-40">
        <span>{{ $t("online_orders.order_summary") }}</span>
    </div>
    <div class="mt-20 pl-15">
        <a-descriptions
            :title="null"
            :column="2"
            :labelStyle="{ fontWeight: 'bold' }"
        >
            <a-descriptions-item :label="$t('stock.order_id')">
                {{ order.invoice_number }}
            </a-descriptions-item>
            <a-descriptions-item :label="$t('common.total')">
                {{ formatAmountCurrency(order.total) }}
            </a-descriptions-item>
            <a-descriptions-item :label="$t('payments.payment_status')">
                <PaymentStatus :paymentStatus="order.payment_status" />
            </a-descriptions-item>
            <a-descriptions-item :label="$t('stock.status')">
                <OrderStatusTag :data="order" />
            </a-descriptions-item>
        </a-descriptions>
        <a-row :gutter="16">
            <a-col :span="12">
                <div class="item-desc">
                    <span>Account Details:</span>
                </div>
                <a-descriptions
                    :title="null"
                    :column="1"
                    :labelStyle="{ fontWeight: 'bold' }"
                >
                    <a-descriptions-item :label="$t('user.name')">
                        {{
                            order.user && order.user.name
                                ? order.user.name
                                : "N/A"
                        }}
                    </a-descriptions-item>
                    <a-descriptions-item :label="$t('user.email')">
                        {{
                            order.user && order.user.email
                                ? order.user.email
                                : "N/A"
                        }}
                    </a-descriptions-item>
                    <a-descriptions-item :label="$t('common.phone')">
                        {{
                            order.user && order.user.phone
                                ? order.user.phone
                                : "N/A"
                        }}
                    </a-descriptions-item>
                    <a-descriptions-item :label="$t('user.bussiness_type')">
                        {{
                            order.user && order.user.business_type
                                ? order.user.business_type
                                : "N/A"
                        }}
                    </a-descriptions-item>
                    <a-descriptions-item :label="$t('user.address')">
                        {{
                            `${
                                order.user && order.user.address
                                    ? order.user.address
                                    : ""
                            }${
                                order.user && order.user.location
                                    ? `, ${order.user.location}`
                                    : ""
                            }${
                                order.user && order.user.pincode
                                    ? ` - ${order.user.pincode}`
                                    : ""
                            }`
                        }}
                    </a-descriptions-item>
                </a-descriptions>
            </a-col>
            <a-col :span="12">
                <div class="item-desc">
                    <span>Shipping Details:</span>
                </div>
                <a-descriptions
                    :title="null"
                    :column="1"
                    :labelStyle="{ fontWeight: 'bold' }"
                >
                    <a-descriptions-item
                        :label="$t('user.name')"
                        v-if="order.shipping_type == 0"
                    >
                        {{
                            order.shipping_address &&
                            order.shipping_address.name
                                ? order.shipping_address.name
                                : "N/A"
                        }}
                    </a-descriptions-item>
                    <a-descriptions-item
                        :label="$t('user.email')"
                        v-if="order.shipping_type == 0"
                    >
                        {{
                            order.shipping_address &&
                            order.shipping_address.email
                                ? order.shipping_address.email
                                : "N/A"
                        }}
                    </a-descriptions-item>
                    <a-descriptions-item
                        :label="$t('common.phone')"
                        v-if="order.shipping_type == 0"
                    >
                        {{
                            order.shipping_address &&
                            order.shipping_address.phone
                                ? order.shipping_address.phone
                                : "N/A"
                        }}
                    </a-descriptions-item>
                    <a-descriptions-item
                        :label="$t('stock.shipping_address')"
                        v-if="order.shipping_type == 0"
                    >
                        {{
                            `${
                                order.shipping_address &&
                                order.shipping_address.shipping_address
                                    ? order.shipping_address.shipping_address
                                    : ""
                            }${
                                order.shipping_address &&
                                order.shipping_address.city
                                    ? `, ${order.shipping_address.city}`
                                    : ""
                            }${
                                order.shipping_address &&
                                order.shipping_address.state
                                    ? `, ${order.shipping_address.state}`
                                    : ""
                            }${
                                order.shipping_address &&
                                order.shipping_address.country
                                    ? `, ${order.shipping_address.country}`
                                    : ""
                            }${
                                order.shipping_address &&
                                order.shipping_address.zipcode
                                    ? ` - ${order.shipping_address.zipcode}`
                                    : ""
                            }`
                        }}
                    </a-descriptions-item>
                    <a-descriptions-item
                        label="Shipping Type"
                        v-if="order.shipping_type == 1"
                    >
                        {{ $t("stock.self_pickup") }}
                    </a-descriptions-item>
                </a-descriptions>
            </a-col>
        </a-row>
    </div>

    <a-row class="mt-20">
        <a-col :span="24">
            <a-table
                :columns="columns"
                :row-key="(record) => record.id"
                :data-source="order.items"
                :pagination="false"
            >
                <template #bodyCell="{ index, column, record }">
                    <template v-if="column.dataIndex === 'id'">
                        {{ index + 1 }}
                    </template>
                    <template v-if="column.dataIndex === 'product_id'">
                        <a-list-item>
                            <a-list-item-meta>
                                <template #avatar>
                                    <a-avatar
                                        :src="record.product.image_url"
                                        size="large"
                                        shape="square"
                                    />
                                </template>
                                <template #title>
                                    {{ record.product.name }}
                                </template>
                            </a-list-item-meta>
                        </a-list-item>
                    </template>
                    <template v-if="column.dataIndex === 'unit_price'">
                        {{ formatAmountCurrency(record.unit_price) }}
                    </template>
                    <template v-if="column.dataIndex === 'subtotal'">
                        {{ formatAmountCurrency(record.subtotal) }}
                    </template>
                </template>
            </a-table>
        </a-col>
    </a-row>

    <a-row class="mt-30">
        <a-col :span="18"></a-col>
        <a-col :span="6">
            <div class="pd-10">
                <a-row>
                    <a-col :span="12">{{ $t("product.subtotal") }}</a-col>
                    <a-col :span="12" class="text-right">
                        {{ formatAmountCurrency(order.subtotal) }}
                    </a-col>
                </a-row>
                <a-row class="mt-10">
                    <a-col :span="12">{{ $t("product.discount") }}</a-col>
                    <a-col :span="12" class="text-right">
                        {{ formatAmountCurrency(order.discount) }}
                    </a-col>
                </a-row>
                <a-row class="mt-10">
                    <a-col :span="12">{{ $t("stock.order_tax") }}</a-col>
                    <a-col :span="12" class="text-right">
                        {{ formatAmountCurrency(order.tax_amount) }}
                    </a-col>
                </a-row>
                <a-row class="mt-10">
                    <a-col :span="12">{{ $t("stock.shipping") }}</a-col>
                    <a-col :span="12" class="text-right">
                        {{ formatAmountCurrency(order.shipping) }}
                    </a-col>
                </a-row>
            </div>
            <div class="item-total pd-10">
                <a-row class="mt-10">
                    <a-col :span="12">{{ $t("stock.grand_total") }}</a-col>
                    <a-col :span="12" class="text-right">
                        {{ formatAmountCurrency(order.total) }}
                    </a-col>
                </a-row>
            </div>
        </a-col>
    </a-row>
</template>
<script>
import { defineComponent, ref } from "vue";
import { StopOutlined } from "@ant-design/icons-vue";
import common from "../../../../common/composable/common";
import OrderStatus from "./OrderStatus.vue";
import PaymentStatus from "../../../../common/components/order/PaymentStatus.vue";
import { buildAddress } from "../../../../common/scripts/functions";
import OrderStatusTag from "../../../../common/components/order/OrderStatus.vue";

export default defineComponent({
    props: ["order"],
    components: {
        OrderStatus,
        PaymentStatus,
        StopOutlined,
        OrderStatusTag,
    },
    setup() {
        const { formatAmountCurrency } = common();
        const columns = [
            {
                title: "#",
                dataIndex: "id",
            },
            {
                title: "Product",
                dataIndex: "product_id",
            },
            {
                title: "Quantity",
                dataIndex: "quantity",
            },
            {
                title: "Price",
                dataIndex: "unit_price",
            },
            {
                title: "Total",
                dataIndex: "subtotal",
            },
        ];

        return {
            columns,
            formatAmountCurrency,
            buildAddress,
        };
    },
});
</script>

<style lang="less">
.item-desc {
    background-color: #f5f5f5;
    padding: 15px;

    span {
        font-weight: 600;
        font-size: 18px;
    }
}

.item-total {
    background-color: #f5f5f5;
    font-weight: bold;
}
</style>
