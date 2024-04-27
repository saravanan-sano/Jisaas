<template>
    <div
        class="product"
        v-if="currentProduct && currentProduct.xid"
        @click="() => showProductPage(currentProduct.xid)"
    >
        <div class="product-top">
            <a href="javascript:void(0)">
                <img
                    loading="lazy"
                    :src="currentProduct.image_url"
                    class="img-fit"
                />
            </a>
        </div>
        <div class="product-bottom">
            <div>
                <span class="product-category">{{
                    currentProduct.category.name
                }}</span>
                <h5 class="product-title">
                    {{ currentProduct.name }}
                </h5>
            </div>
            <div class="product-details">
                <div class="product-price">
                    <span>{{
                        formatAmountCurrency(
                            currentProduct.details.sales_price
                        )
                    }}</span>
                    <br />
                    <del v-if="currentProduct.details.mrp != 0">{{
                        formatAmountCurrency(currentProduct.details.mrp)
                    }}</del>
                </div>
            </div>
        </div>
    </div>

    <a-modal
        v-model:visible="visible"
        centered
        :footer="null"
        :title="null"
        height="auto"
        :width="850"
    >
        <div>Hiii</div>
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
import { useRoute, useRouter } from "vue-router";
import cart from "../../../../common/composable/cart";

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
        const router = useRouter();
        const visible = ref(false);
        const productQuantity = ref(1);
        const currentProduct = ref({});
        const { showProductPage } = cart();

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

            showProductPage,
            getSalesPriceWithTax,
            frontAppSetting,
        };
    },
};
</script>

<style lang="less">
.product {
    background: #fff;
    box-shadow: 1px 1px 5px 2px #dbdbdb;
    border-radius: 4px;
    margin-top: 15px;
    cursor: pointer;
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
                font-weight: 600;
                font-size: 18px;
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
