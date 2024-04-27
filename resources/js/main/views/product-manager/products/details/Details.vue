<template>
    <a-tabs v-model:activeKey="activeKey">
        <a-tab-pane
            key="product_order_items"
            :tab="$t('product.product_orders')"
        >
            <ProductOrderItems :product="product" />
        </a-tab-pane>
        <a-tab-pane key="stock_history" :tab="$t('product.stock_history')">
            <StockHistory :product="product" />
        </a-tab-pane>
        <a-tab-pane key="price_history" :tab="$t('product.price_history')">
            <PriceHistory :priceHistory="price_history" />
        </a-tab-pane>
        <a-tab-pane key="wholesale" :tab="$t('product.wholesale')">
            <Wholesale :wholesale="wholesale" />
        </a-tab-pane>
    </a-tabs>
</template>

<script>
import { defineComponent, onMounted, ref } from "vue";
import StockHistory from "./StockHistory.vue";
import ProductOrderItems from "./ProductOrderItems.vue";
import PriceHistory from "./PriceHistory.vue";
import Wholesale from "./Wholesale.vue";

export default defineComponent({
    props: ["product"],
    components: {
        StockHistory,
        ProductOrderItems,
        PriceHistory,
        Wholesale,
    },
    setup(props) {
        const price_history = ref(
            props.product.details.price_history
                ? JSON.parse(props.product.details.price_history).reverse()
                : []
        );
        const wholesale = ref(props.product.details.wholesale);
        return {
            activeKey: ref("product_order_items"),
            price_history,
            wholesale,
        };
    },
});
</script>
