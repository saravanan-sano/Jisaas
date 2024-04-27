<template>
    <div class="product" v-if="currentProduct && currentProduct.xid">
        <a-row>
            <a-col :sm="8" :md="8" :lg="8" :xl="8">
                <div class="product-top">
                    <a href="javascript:void(0)" @click="showModal">
                        <img
                            loading="lazy"
                            :src="currentProduct.image_url"
                            class="img-fit"
                        />
                    </a>
                </div>
            </a-col>

            <a-col :sm="16" :md="16" :lg="16" :xl="16">
                <div>
                    <div>
                        <span class="product-category">{{
                            currentProduct.category.name
                        }}</span>
                        <h2 class="product-title">
                            {{ currentProduct.name }}
                        </h2>
                        <p class="product-title">
                            {{ currentProduct.description }}
                        </p>
                    </div>
                </div>
                <div class="product-details">
                    <div
                        class="product-price"
                        style="font-size: 22px; margin-bottom: 15px"
                    >
                        <del v-if="currentProduct.details.mrp != 0">{{
                            formatAmountCurrency(currentProduct.details.mrp)
                        }}</del>
                        <span
                            >&nbsp;{{
                                formatAmountCurrency(
                                    currentProduct.details.sales_price
                                )
                            }}</span
                        >
                    </div>
                    <div class="product-card-add-edit">
                        <a-button-group v-if="currentProduct.cart_quantity > 0">
                            <a-button
                                @click="
                                    currentProduct.cart_quantity -= 1;
                                    addItem(currentProduct);
                                "
                                size="small"
                            >
                                <minus-outlined />
                            </a-button>
                            <a-button size="small">
                                {{ currentProduct.cart_quantity }}
                            </a-button>
                            <a-button
                                @click="
                                    currentProduct.cart_quantity += 1;
                                    addItem(currentProduct);
                                "
                                size="small"
                            >
                                <plus-outlined />
                            </a-button>
                        </a-button-group>
                        <a-button
                            v-else
                            @click="
                                currentProduct.cart_quantity++;
                                addItem(currentProduct);
                            "
                            type="primary"
                            :style="{
                                'background-color': `${frontAppSetting.company.primary_color}`,
                                'border-color': `${frontAppSetting.company.primary_color}`,
                            }"
                        >
                            <ShoppingCartOutlined />
                            {{ $t("front_setting.add_to_cart") }}
                        </a-button>
                    </div>
                </div>
            </a-col>
        </a-row>
    </div>

    <a-modal
        v-model:visible="visible"
        centered
        :footer="null"
        :title="null"
        height="auto"
        :width="850"
    >
        <a-row :gutter="[16, 16]">
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
                <a-typography-title class="mt-20" :level="3">
                    {{ formatAmountCurrency(currentProduct.details.sales_price) }}
                </a-typography-title>
                <div class="mt-10 mb-20">
                    <a-button-group v-if="currentProduct.cart_quantity > 0">
                        <a-button
                            @click="
                                currentProduct.cart_quantity -= 1;
                                addItem(currentProduct);
                            "
                            size="large"
                            :style="{ height: '40px' }"
                        >
                            <minus-outlined />
                        </a-button>
                        <a-button size="large">
                            {{ currentProduct.cart_quantity }}
                        </a-button>
                        <a-button
                            @click="
                                currentProduct.cart_quantity += 1;
                                addItem(currentProduct);
                            "
                            size="large"
                        >
                            <plus-outlined />
                        </a-button>
                    </a-button-group>
                    <a-button
                        v-else
                        @click="
                            currentProduct.cart_quantity++;
                            addItem(currentProduct);
                        "
                        type="primary"
                        size="large"
                    >
                        <ShoppingCartOutlined />
                        {{ $t("front_setting.add_to_cart") }}
                    </a-button>
                </div>
            </a-col>
        </a-row>
    </a-modal>
</template>

<script>
import { ref, onMounted, watch } from "vue";
import { useStore } from "vuex";
import {
    ShoppingCartOutlined,
    MinusOutlined,
    PlusOutlined,
} from "@ant-design/icons-vue";
import { message } from "ant-design-vue";
import { filter, forEach } from "lodash-es";
import common from "../../../../common/composable/common";
import { getSalesPriceWithTax } from "../../../../common/scripts/functions";

export default {
    props: ["product"],
    components: {
        ShoppingCartOutlined,
        MinusOutlined,
        PlusOutlined,
    },
    setup(props) {
        const { formatAmountCurrency, frontAppSetting, frontWarehouse } =
            common();
        const store = useStore();
        const visible = ref(false);
        const productQuantity = ref(1);
        const currentProduct = ref({});

        onMounted(() => {
            const cartItems = store.state.front.cartItems;
            let productQuantity = 0;

            forEach(cartItems, (iee) => {
                if (iee.xid == props.product.xid) {
                    productQuantity = iee.cart_quantity;
                }
            });

            currentProduct.value = {
                ...props.product,
                cart_quantity: productQuantity,
            };
        });

        const showModal = () => {
            visible.value = true;
        };

        const addItem = (product) => {
            const cartItems = store.state.front.cartItems;
            const auth = store.state.front.user;
            const updatedCartItems = filter(
                cartItems,
                (cartItem) => cartItem.xid != product.xid
            );

            if (product.cart_quantity > 0) {
                updatedCartItems.push({
                    ...product,
                    cart_quantity: product.cart_quantity,
                });
            }

            if (auth != null) {
                var xid = auth.xid;
            } else {
                var xid = "";
            }
            let data = {
                userid: xid,
                cart_item: JSON.stringify(updatedCartItems),
            };

            axiosFront
                .post(`front/self/cart-items`, data)
                .then((response) => {
                    //    console.log(response)
                })
                .catch((error) => {
                    if (axios.isCancel(error)) {
                        console.log("Request canceled:", error.message);
                    } else {
                        console.error("An error occurred:", error);
                    }
                });

            store.commit("front/addCartItems", updatedCartItems);
            message.success(`Item updated in cart`);
        };

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

            visible,
            productQuantity,

            showModal,
            addItem,
            getSalesPriceWithTax,
            frontAppSetting,
        };
    },
};
</script>

<style lang="less">
.product {
    background: #fff;
    // border: 1px solid #eee;
    border-radius: 4px;
    margin-top: 15px;
}

.product-top {
    display: flex;
    justify-content: center;
    width: 100%;

    a {
        text-align: "center";
    }

    img {
        height: 180px;
        max-width: 100%;
        width: 100%;
        -o-object-fit: cover;
        object-fit: cover;
    }
}

.product-bottom {
    padding-left: 15px;
    padding-right: 15px;
    padding-bottom: 15px;

    .product-category {
        font-weight: 500;
        font-size: 12px;
        color: #9ca3af;
    }

    .product-title {
        font-weight: 400;
        font-size: 14px;
        height: 40px;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .product-details {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-top: 15px;

        .product-price {
            span {
                font-weight: bold !important;
                font-size: 22px !important;
            }

            del {
                font-weight: 400;
                color: #9ca3af;
                margin-left: 5px;
            }
        }
    }
}

.product-details-image {
    display: flex;
    justify-content: center;
    height: 300px;

    span {
        box-sizing: border-box;
        display: inline-block;
        overflow: hidden;
        width: initial;
        height: initial;
        background: none;
        opacity: 1;
        border: 0px;
        margin: 0px;
        padding: 0px;
        position: relative;
        max-width: 100%;
    }

    img {
        display: block;
        max-width: 100%;
        width: initial;
        height: initial;
        background: none;
        opacity: 1;
        border: 0px;
        margin: 0px;
        padding: 0px;
    }
}
</style>
