<template>
    <div class="product_view-wrp">
        <a-row :gutter="[16, 16]" v-if="!loading">
            <a-col :span="12">
                <div class="product-details-image">
                    <!-- <span> -->
                    <img loading="lazy" :src="currentProduct.image_url" />
                    <!-- </span> -->
                </div>
            </a-col>
            <a-col :span="12">
                <a-typography-title :level="3">{{
                    currentProduct.name
                }}</a-typography-title>
                <div>
                    <a-tag v-if="currentProduct.category_id" color="purple">
                        {{ currentProduct.category.name }}
                    </a-tag>
                    <a-tag v-if="currentProduct.brand_id" color="cyan">
                        {{ currentProduct.brand.name }}
                    </a-tag>
                </div>
                <p class="mt-10" v-if="currentProduct.description">
                    {{ currentProduct.description }}
                </p>
                <a-typography-title class="mt-10 mb-20" :level="3">
                    {{
                        formatAmountCurrency(currentProduct.details.sales_price)
                    }}
                </a-typography-title>

                <div class="mt-10 mb-20 cart-qty-add-wrp">
                    <a-button-group>
                        <a-button
                            @click="addCartQuantity -= 1"
                            size="large"
                            type="primary"
                            class="minus-button"
                            :disabled="addCartQuantity <= 1"
                        >
                            <minus-outlined />
                        </a-button>
                        <a-input-number
                            v-model:value="addCartQuantity"
                            :min="1"
                            size="large"
                            class="quantity-input"
                        />
                        <a-button
                            type="primary"
                            @click="addCartQuantity += 1"
                            size="large"
                            class="pluse-button"
                        >
                            <PlusOutlined />
                        </a-button>
                    </a-button-group>

                    <a-button
                        v-if="
                            currentProduct.cart_quantity <
                            currentProduct.details.current_stock
                        "
                        @click="() => addToCart(currentProduct)"
                        type="primary"
                        size="large"
                    >
                        <ShoppingCartOutlined />
                        {{ $t("front_setting.add_to_cart") }}
                    </a-button>
                    <a-button v-else type="primary" size="large" danger>
                        <CloseCircleOutlined />
                        Out of Stock
                    </a-button>
                </div>
                <div
                    class="mt-10 mb-20 cart-qty-add-wrp"
                    v-if="
                        currentProduct.details &&
                        currentProduct.details.shipping_price
                    "
                >
                    <a-typography-title class="mt-20" :level="5">
                        Shipping Price:
                    </a-typography-title>
                    <a-typography-text class="mt-20" :level="5">
                        Per.Qty x
                        {{
                            formatAmountCurrency(
                                currentProduct.details.shipping_price
                            )
                        }}
                    </a-typography-text>
                </div>
                <div
                    class="product-wholesale-details mt-10 mb-20"
                    v-if="currentProduct.front_wholesale.length > 0"
                >
                    <a-typography-title class="mt-20" :level="5">
                        Price Listing:
                    </a-typography-title>
                    <a-table
                        :columns="pricingColumn"
                        :row-key="(record) => record.xid"
                        :data-source="currentProduct.front_wholesale"
                        :pagination="false"
                    >
                        <template #bodyCell="{ column, record, index }">
                            <template v-if="column.dataIndex === 'no'">
                                {{ index + 1 }}
                            </template>
                            <template v-if="column.dataIndex === 'quantity'">
                                {{
                                    `${record.start_quantity} - ${record.end_quantity}`
                                }}
                            </template>
                            <template v-if="column.dataIndex === 'price'">
                                {{
                                    formatAmountCurrency(record.wholesale_price)
                                }}
                            </template>
                        </template>
                    </a-table>
                </div>
            </a-col>
        </a-row>
    </div>
</template>
<script>
import { defineComponent, onMounted, ref, watch } from "vue";
import { useRoute } from "vue-router";
import {
    ShoppingCartOutlined,
    MinusOutlined,
    PlusOutlined,
    CloseCircleOutlined,
} from "@ant-design/icons-vue";
import common from "../../../common/composable/common";
import { getSalesPriceWithTax } from "../../../common/scripts/functions";
import { useStore } from "vuex";
import { forEach } from "lodash-es";
import { message } from "ant-design-vue";
import cart from "../../../common/composable/cart";

export default defineComponent({
    components: {
        ShoppingCartOutlined,
        MinusOutlined,
        PlusOutlined,
        CloseCircleOutlined,
    },
    setup() {
        const { frontWarehouse, formatAmountCurrency } = common();
        const route = useRoute();
        const currentProduct = ref({});
        const loading = ref(true);
        const store = useStore();
        const { addCartItem } = cart();
        const addCartQuantity = ref(1);

        const addToCart = (product) => {
            currentProduct.value.cart_quantity += addCartQuantity.value;
            addCartItem(product);
            addCartQuantity.value = 1;
        };

        onMounted(() => {
            axiosFront
                .get(
                    `front/homepage/${frontWarehouse.value.slug}/${route.params.id}`
                )
                .then((response) => {
                    let product = response.data[0];
                    const cartItems = store.state.front.cartItems;
                    let productQuantity = 0;

                    forEach(cartItems, (iee) => {
                        if (iee.xid == product.xid) {
                            productQuantity = iee.cart_quantity;
                        }
                    });

                    currentProduct.value = {
                        ...product,
                        cart_quantity: productQuantity,
                    };
                    loading.value = false;
                })
                .catch((error) => {
                    if (axios.isCancel(error)) {
                        console.log("Request canceled:", error.message);
                    } else {
                        console.error("An error occurred:", error);
                    }
                });
        });

        const pricingColumn = [
            {
                title: "#",
                dataIndex: "no",
            },
            {
                title: "Quantity",
                dataIndex: "quantity",
            },
            {
                title: "Price",
                dataIndex: "price",
            },
        ];

        watch(store.state.front, (newVal, oldVal) => {
            let productQuantity = 0;
            forEach(newVal.cartItems, (iee) => {
                if (iee.xid == currentProduct.value.xid) {
                    productQuantity = iee.cart_quantity;
                }
            });

            currentProduct.value = {
                ...currentProduct.value,
                cart_quantity: productQuantity,
            };
        });

        return {
            currentProduct,
            formatAmountCurrency,
            getSalesPriceWithTax,
            loading,
            addCartItem,
            addCartQuantity,
            addToCart,
            pricingColumn,
        };
    },
});
</script>
<style>
.product_view-wrp {
    padding: 2vw 1vw;
    background: white;
    margin: 1vw;
    border-radius: 8px;
    box-shadow: 0px 0px 6px 5px #eaeaea;
}

.cart-qty-add-wrp {
    width: 45%;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.product-wholesale-details {
    width: 45%;
}

.quantity-input {
    border: none;
    text-align: center !important;
}

.quantity-input > .ant-input-number-handler-wrap {
    display: none;
}

.quantity-input input {
    text-align: center;
    font-size: 1vw;
    font-weight: bold;
}
.quantity-input:focus {
    border: none !important;
    box-shadow: none !important;
}

.quantity-input.ant-input-number-focused {
    box-shadow: none !important;
}
</style>
