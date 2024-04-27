<template>
    <a-button
        type="link"
        @click="showDrawer"
        :style="{ color: `${frontAppSetting.company.primary_color}` }"
        class="front-cart-button"
    >
        <a-badge :count="totalCartItems">
            <shopping-cart-outlined
                :style="{
                    fontSize: '24px',
                    color: `${frontAppSetting.company.primary_color}`,
                    verticalAlign: 'middle',
                }"
            />
        </a-badge>
        &nbsp; &nbsp; &nbsp; Cart
    </a-button>
    <a-drawer
        v-model:visible="visible"
        :width="innerWidth <= 768 ? '80%' : 700"
        placement="right"
        :closable="false"
        :maskClosable="false"
        :headerStyle="{
            backgroundColor: `${frontAppSetting.company.primary_color}`,
        }"
    >
        <template #title>
            <shopping-outlined :style="{ fontSize: '20px', color: '#fff' }" />
            <span style="color: #fff"> Review Your Cart</span>
        </template>
        <template #extra>
            <a-button type="link" @click="closeDrawer">
                <close-outlined :style="{ fontSize: '14px', color: '#fff' }" />
            </a-button>
        </template>

        <a-list
            class="demo-loadmore-list"
            item-layout="horizontal"
            :data-source="products"
        >
            <template #renderItem="{ item }">
                <a-badge-ribbon
                    :text="item.details && item.details.is_stock_message"
                    :color="
                        item.details && item.details.is_stock_message
                            ? 'red'
                            : 'transparent'
                    "
                >
                    <a-list-item class="cart-item">
                        <a-list-item-meta>
                            <template #title>
                                <span
                                    style="cursor: pointer"
                                    @click="() => showProductPage(item.xid)"
                                    >{{ item.name }}</span
                                >
                                <br />
                                <small
                                    :style="{ color: 'rgba(0, 0, 0, 0.45)' }"
                                >
                                    Price:
                                    <span>{{
                                        formatAmountCurrency(setUnitPrice(item))
                                    }}</span>
                                    <template
                                        v-if="
                                            item.details &&
                                            item.details.is_price_updated &&
                                            item.details.is_price_message
                                        "
                                    >
                                        <div
                                            v-if="
                                                item.details.is_price_updated ==
                                                2
                                            "
                                        >
                                            <ArrowDownOutlined
                                                style="
                                                    color: green;
                                                    margin-right: 0.5vw;
                                                "
                                            />
                                            <a-tag color="green">{{
                                                item.details.is_price_message
                                            }}</a-tag>
                                        </div>
                                        <div
                                            v-if="
                                                item.details.is_price_updated ==
                                                1
                                            "
                                        >
                                            <ArrowUpOutlined
                                                style="
                                                    color: red;
                                                    margin-right: 0.5vw;
                                                "
                                            />
                                            <a-tag color="red">{{
                                                item.details.is_price_message
                                            }}</a-tag>
                                        </div>
                                    </template>
                                </small>
                            </template>
                            <template #avatar>
                                <a-avatar
                                    @click="() => showProductPage(item.xid)"
                                    :src="item.image_url"
                                    size="large"
                                />
                            </template>
                            <template #description>
                                {{
                                    formatAmountCurrency(
                                        setUnitPrice(item) * item.cart_quantity
                                    )
                                }}
                            </template>
                        </a-list-item-meta>
                        <!-- <div>
                            <a-input-number
                                v-model:value="item.cart_quantity"
                                :min="1"
                                :style="{ width: '120px' }"
                                :max="item.details.current_stock"
                                @change="
                                    () => {
                                        quantityChanged = true;
                                    }
                                "
                                @blur="
                                    isLowInStock
                                        ? UpdateCartItems(item)
                                "
                            />
                        </div> -->
                        <div class="">
                            <a-button-group>
                                <a-button
                                    @click="
                                        () => {
                                            item.cart_quantity -= 1;
                                            productStatusChange(item);
                                        }
                                    "
                                    size="small"
                                    type="primary"
                                    class="minus-button"
                                    :disabled="item.cart_quantity <= 1"
                                >
                                    <MinusOutlined />
                                </a-button>
                                <a-input-number
                                    v-model:value="item.cart_quantity"
                                    :min="1"
                                    size="small"
                                    class="quantity-input"
                                    :max="item.details.current_stock"
                                    @blur="productStatusChange(item)"
                                />
                                <a-button
                                    type="primary"
                                    @click="
                                        () => {
                                            item.cart_quantity += 1;
                                            productStatusChange(item);
                                        }
                                    "
                                    size="small"
                                    class="pluse-button"
                                    :disabled="
                                        item.cart_quantity >=
                                        item.details.current_stock
                                    "
                                >
                                    <PlusOutlined />
                                </a-button>
                            </a-button-group>
                        </div>
                        <template #actions>
                            <a-button type="link" @click="removeItem(item.xid)">
                                <delete-outlined
                                    :style="{
                                        fontSize: '20px',
                                        color: '#f87171',
                                    }"
                                />
                            </a-button>
                        </template>
                    </a-list-item>
                </a-badge-ribbon>
            </template>
        </a-list>

        <template #footer>
            <a-row
                type="flex"
                justify="start"
                :gutter="16"
                style="align-items: center"
            >
                <a-col :span="12">
                    <div
                        style="
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                        "
                    >
                        <a-typography-text :level="5">
                            Total Shipping:
                        </a-typography-text>
                        <a-typography-text :level="5">
                            {{ formatAmountCurrency(calculateTotalShipping()) }}
                        </a-typography-text>
                    </div>
                    <div
                        style="
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                        "
                    >
                        <a-typography-text :level="5">
                            Subtotal:
                        </a-typography-text>
                        <a-typography-text :level="5">
                            {{ formatAmountCurrency(total) }}
                        </a-typography-text>
                    </div>
                </a-col>
                <a-col :span="12">
                    <a-button
                        type="primary"
                        :disabled="isLowInStock"
                        @click="proceedCheckout"
                        block
                    >
                        Checkout
                        <right-outlined />
                    </a-button>
                </a-col>
            </a-row>
        </template>
    </a-drawer>
</template>
<script>
import {
    defineComponent,
    computed,
    ref,
    onMounted,
    watch,
    createVNode,
} from "vue";
import {
    ShoppingCartOutlined,
    ShoppingOutlined,
    CloseOutlined,
    DeleteOutlined,
    RightOutlined,
    ArrowUpOutlined,
    ArrowDownOutlined,
    MinusOutlined,
    PlusOutlined,
} from "@ant-design/icons-vue";
import { useStore } from "vuex";
import { useRouter } from "vue-router";
import common from "../../../../common/composable/common";
import cart from "../../../..//common/composable/cart";
import { getSalesPriceWithTax } from "../../../../common/scripts/functions";
import { Modal } from "ant-design-vue";

export default defineComponent({
    emits: ["openLoginModal"],
    components: {
        ShoppingCartOutlined,
        ShoppingOutlined,
        CloseOutlined,
        DeleteOutlined,
        RightOutlined,
        ArrowUpOutlined,
        ArrowDownOutlined,
        MinusOutlined,
        PlusOutlined,
    },
    setup(props, { emit }) {
        const { formatAmountCurrency, frontWarehouse, frontAppSetting } =
            common();
        const store = useStore();
        const visible = ref(false);
        const router = useRouter();
        const {
            products,
            addCartItem,
            UpdateCartItems,
            removeItem,
            total,
            fetchLatestCartItems,
            showProductPage,
            calculateTotalShipping,
            setUnitPrice,
        } = cart();

        const user = store.state.front.user;
        const quantityChanged = ref(false);
        const showDrawer = () => {
            if (user && user.xid) {
                fetchLatestCartItems();
            }
            visible.value = true;
        };

        onMounted(() => {
            if (user && user.xid) {
                fetchLatestCartItems();
            }
        });

        const isLowInStock = computed(() => {
            if (products.value) {
                let itemsHasUpdated = _.some(products.value, (product) => {
                    return "is_stock_message" in product.details;
                });
                return itemsHasUpdated;
            }
        });
        const productStatusChange = (product) => {
            quantityChanged.value = true;
            if (isLowInStock.value) UpdateCartItems(product);
        };

        const closeDrawer = () => {
            let itemsHasUpdated = _.some(products.value, (product) => {
                const isPriceUpdated = product.details.is_price_updated;
                return isPriceUpdated === 1 || isPriceUpdated === 2;
            });

            if (itemsHasUpdated || quantityChanged.value) {
                Modal.confirm({
                    title: "Are you sure to update the cart?",
                    icon: createVNode(ShoppingCartOutlined),
                    // content: "Some descriptions",
                    okText: "Yes",
                    okType: "danger",
                    cancelText: "No",
                    onOk() {
                        products.value.map((product) => {
                            UpdateCartItems(product);
                        });
                        quantityChanged.value = false;
                        visible.value = false;
                    },
                    onCancel() {},
                });
            } else {
                products.value.map((product) => {
                    UpdateCartItems(product);
                });
                visible.value = false;
            }
        };

        const isLoggedIn = computed(() => {
            return store.getters["front/isLoggedIn"];
        });

        const proceedCheckout = () => {
            visible.value = false;

            if (isLoggedIn.value) {
                router.push({
                    name: "front.checkout",
                    params: { warehouse: frontWarehouse.value.slug },
                });
                products.value.map((product) => {
                    UpdateCartItems(product);
                });
                quantityChanged.value = false;
            } else {
                router.push({
                    name: "front.login",
                });
            }
        };

        return {
            visible,
            showDrawer,
            closeDrawer,
            totalCartItems: computed(
                () => store.getters["front/totalCartItems"]
            ),

            products,
            removeItem,
            addCartItem,
            UpdateCartItems,
            formatAmountCurrency,
            total,
            proceedCheckout,
            getSalesPriceWithTax,
            frontWarehouse,
            frontAppSetting,
            showProductPage,
            calculateTotalShipping,
            innerWidth: window.innerWidth,
            quantityChanged,
            isLowInStock,
            productStatusChange,
            setUnitPrice,
        };
    },
});
</script>
<style lang="less">
#title {
    color: #fff;
}
.front-cart-button > span:nth-child(2) {
    margin-left: 10px;
}
.ant-ribbon.ant-ribbon-placement-end .ant-ribbon-corner {
    right: 0;
    border-color: transparent !important;
}
.cart-item {
    padding: 1vw 0.5vw;
    border-radius: 8px;
    background: #fff;
    margin: 5px 0;
    transition: 0.25s all ease-in-out;
}
.cart-item:hover {
    box-shadow: 0 0 6px 4px #eee;
}
</style>
