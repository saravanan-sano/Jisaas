import { computed, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useStore } from "vuex";
import { filter, forEach } from "lodash-es";
import { useI18n } from "vue-i18n";
import { message } from "ant-design-vue";
import axios from "axios";
import common from "../../common/composable/common";
const cart = () => {
    const route = useRoute();
    const store = useStore();
    const router = useRouter();
    const products = computed(() => store.state.front.cartItems);
    const appSetting = store.state.auth.appSetting;
    const user = store.state.auth.user;
    const auth = store.state.front.user;
    const { t } = useI18n();
    const orderType = ref(route.params.type);
    const { frontWarehouse } = common();

    const statusColors = {
        enabled: "success",
        disabled: "error",
    };

    const userStatus = [
        {
            key: "enabled",
            value: t("common.enabled"),
        },
        {
            key: "disabled",
            value: t("common.disabled"),
        },
    ];

    const taxTypes = [
        {
            key: "inclusive",
            value: t("product.inclusive"),
        },
        {
            key: "exclusive",
            value: t("product.exclusive"),
        },
    ];

    const disabledDate = (current) => {
        // Can not select days before today and today
        return current && current > moment().endOf("day");
    };

    const formatAmount = (amount) => {
        return parseFloat(parseFloat(amount).toFixed(2));
    };

    const formatAmountCurrency = (amount) => {
        const newAmount = parseFloat(Math.abs(amount))
            .toFixed(2)
            .toString()
            .replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        const newAmountString = `${appSetting.currency.symbol}${newAmount}`;

        return amount < 0 ? `- ${newAmountString}` : newAmountString;
    };

    const calculateFilterString = (filters) => {
        var filterString = "";

        forEach(filters, (filterValue, filterKey) => {
            if (filterValue != undefined) {
                filterString += `${filterKey} eq "${filterValue}" and `;
            }
        });

        if (filterString.length > 0) {
            filterString = filterString.substring(0, filterString.length - 4);
        }

        return filterString;
    };

    const checkPermission = (permissionName) =>
        checkUserPermission(permissionName, user);

    const updatePageTitle = (pageName) => {
        store.commit("auth/updatePageTitle", t(`menu.${pageName}`));
    };

    const permsArray = computed(() => {
        const permsArrayList = [store.state.auth.user.role.name];

        forEach(store.state.auth.user.role.perms, (permission) => {
            permsArrayList.push(permission.name);
        });

        return permsArrayList;
    });

    const orderPageObject = computed(() => {
        var pageObjectDetails = {};

        if (orderType.value == "purchases") {
            pageObjectDetails = {
                type: "purchases",
                langKey: "purchase",
                menuKey: "purchases",
                userType: "suppliers",
            };
        } else if (orderType.value == "sales") {
            pageObjectDetails = {
                type: "sales",
                langKey: "sales",
                menuKey: "sales",
                userType: "customers",
            };
        } else if (orderType.value == "purchase-returns") {
            pageObjectDetails = {
                type: "purchase-returns",
                langKey: "purchase_returns",
                menuKey: "purchase_returns",
                userType: "suppliers",
            };
        } else if (orderType.value == "sales-returns") {
            pageObjectDetails = {
                type: "sales-returns",
                langKey: "sales_returns",
                menuKey: "sales_returns",
                userType: "customers",
            };
        }

        return pageObjectDetails;
    });

    const getOrderTypeFromstring = (stringVal) => {
        const orderType = stringVal.replace("-", "_");

        return t(`menu.${orderType}`);
    };

    const orderStatus = [
        {
            key: "pending",
            value: t("common.pending"),
        },
        {
            key: "paid",
            value: t("common.paid"),
        },
        {
            key: "cancelled",
            value: t("common.cancelled"),
        },
    ];

    const paymentStatus = [
        {
            key: "pending",
            value: t("common.pending"),
        },
        {
            key: "paid",
            value: t("common.paid"),
        },
        {
            key: "cancelled",
            value: t("common.cancelled"),
        },
    ];

    const purchaseOrderStatus = [
        {
            key: "received",
            value: t("common.received"),
        },
        {
            key: "pending",
            value: t("common.pending"),
        },
        {
            key: "ordered",
            value: t("common.ordered"),
        },
    ];

    const purchaseReturnStatus = [
        {
            key: "completed",
            value: t("common.completed"),
        },
        {
            key: "pending",
            value: t("common.pending"),
        },
    ];

    const salesOrderStatus = [
        {
            key: "ordered",
            value: t("common.ordered"),
        },
        {
            key: "confirmed",
            value: t("common.confirmed"),
        },
        {
            key: "processing",
            value: t("common.processing"),
        },
        {
            key: "shipping",
            value: t("common.shipping"),
        },
        {
            key: "delivered",
            value: t("common.delivered"),
        },
    ];

    const salesReturnStatus = [
        {
            key: "received",
            value: t("common.received"),
        },
        {
            key: "pending",
            value: t("common.pending"),
        },
    ];

    const barcodeSymbology = [
        {
            key: "CODE128",
            value: "CODE128",
        },
        {
            key: "CODE39",
            value: "CODE39",
        },
    ];

    const getRecursiveCategories = (response, excludeId = null) => {
        const allCategoriesArray = [];
        const listArray = [];

        response.data.map((responseArray) => {
            if (
                excludeId == null ||
                (excludeId != null && responseArray.parent_id != excludeId)
            ) {
                listArray.push({
                    id: responseArray.id,
                    parent_id: responseArray.parent_id,
                    title: responseArray.name,
                    value: responseArray.id,
                });
            }
        });

        listArray.forEach((node) => {
            // No parentId means top level
            if (!node.parent_id) return allCategoriesArray.push(node);

            // Insert node as child of parent in listArray array
            const parentIndex = listArray.findIndex(
                (el) => el.id === node.parent_id
            );
            if (!listArray[parentIndex].children) {
                return (listArray[parentIndex].children = [node]);
            }

            listArray[parentIndex].children.push(node);
        });

        return allCategoriesArray;
    };

    const filterTreeNode = (inputValue, treeNode) => {
        const treeString = treeNode.props.title.toLowerCase();

        return treeString.includes(inputValue.toLowerCase());
    };

    const calculateTotalShipping = () => {
        return products.value.reduce((totalShipping, product) => {
            return (
                totalShipping +
                (product.details.shipping_price ?? 0) * product.cart_quantity
            );
        }, 0);
    };

    const total = computed(() => {
        let totalAmount = 0;

        forEach(products.value, (product) => {
            totalAmount += product.cart_quantity * product.details.sales_price;
        });

        calculateTotalShipping();

        return totalAmount + calculateTotalShipping();
    });

    const setUnitPrice = (product) => {
        let updatedPrice = product.details.sales_price;

        // const isWholesaleCustomer =
        //     product.is_wholesale_only == 1 && isCustomerWholesale.value;

        if (product.front_wholesale.length > 0) {
            // Initialize updatedPrice to default_price by default
            // updatedPrice = product.default_price;

            for (const wholesaleObj of product.front_wholesale) {
                const startQuantity = parseFloat(wholesaleObj.start_quantity);
                const endQuantity = parseFloat(wholesaleObj.end_quantity);

                const isInRange =
                    parseFloat(product.cart_quantity) >= startQuantity &&
                    parseFloat(product.cart_quantity) <= endQuantity;

                // if (isWholesaleCustomer || product.is_wholesale_only == 0) {
                // Update price if wholesale customer or non-wholesale customer
                if (isInRange) {
                    updatedPrice =
                        wholesaleObj.wholesale_price == 0
                            ? product.details.sales_price
                            : wholesaleObj.wholesale_price;
                    break;
                    // Don't break to consider the last valid range
                } else {
                    updatedPrice = product.single_unit_price;
                }
                // }
            }
        }
        return updatedPrice;
    };

    const updateCart = () => {
        store.commit("front/addCartItems", products.value);
    };

    const fetchLatestCartItems = () => {
        if (auth && auth.xid) {
            axiosFront
                .get(`front/self/cart-items/${auth.xid}`)
                .then((response) => {
                    let CartItem = response[0].cart_item.map((item) => {
                        return {
                            ...item,
                            details: {
                                ...item.details,
                            },
                        };
                    });
                    store.commit("front/addCartItems", CartItem);
                })
                .catch((error) => {
                    if (axios.isCancel(error)) {
                        console.log("Request canceled:", error.message);
                    } else {
                        console.error("An error occurred:", error);
                    }
                });
        }
    };

    const UpdateCartItems = (product) => {
        const cartItems = store.state.front.cartItems;

        if (auth != null) {
            var xid = auth.xid;
        } else {
            var xid = "";
        }

        const updatedCartItems = cartItems.map((cartItem) => {
            if (cartItem.xid == product.xid) {
                return {
                    ...cartItem,
                    cart_quantity: product.cart_quantity,
                };
            } else {
                return cartItem;
            }
        });

        let latestCartItem = updatedCartItems.map((item) => {
            const { is_updated, ...updatedItems } = item;
            const {
                is_stock_message,
                is_price_updated,
                is_price_message,
                ...updatedDetails
            } = item.details;
            return {
                ...updatedItems,
                details: updatedDetails,
            };
        });

        let data = {
            userid: xid,
            cart_item: JSON.stringify(_.cloneDeep(latestCartItem)),
        };

        axiosFront
            .post(`front/self/cart-items`, data)
            .then((response) => {
                fetchLatestCartItems();
            })
            .catch((error) => {
                if (axios.isCancel(error)) {
                    console.log("Request canceled:", error.message);
                } else {
                    console.error("An error occurred:", error);
                }
            });
    };

    const addCartItem = (product) => {
        const cartItems = store.state.front.cartItems;
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

        if (xid) {
            let latestCartItem = updatedCartItems.map((item) => {
                const { is_updated, ...updatedItems } = item;
                const {
                    is_stock_message,
                    is_price_updated,
                    is_price_message,
                    ...updatedDetails
                } = item.details;
                return {
                    ...updatedItems,
                    details: updatedDetails,
                };
            });

            let data = {
                userid: xid,
                cart_item: JSON.stringify(latestCartItem),
            };

            axiosFront
                .post(`front/self/cart-items`, data)
                .then((response) => {
                    fetchLatestCartItems();
                })
                .catch((error) => {
                    if (axios.isCancel(error)) {
                        console.log("Request canceled:", error.message);
                    } else {
                        console.error("An error occurred:", error);
                    }
                });
        } else {
            store.commit("front/addCartItems", updatedCartItems);
        }
        message.success(`Item updated in cart`);
    };

    const removeItem = (selectedProductId) => {
        const updatedCartItems = filter(
            products.value,
            (cartItem) => cartItem.xid != selectedProductId
        );

        if (auth != null) {
            var xid = auth.xid;
        } else {
            var xid = "";
        }

        if (xid) {
            let latestCartItem = updatedCartItems.map((item) => {
                const { is_updated, ...updatedItems } = item;
                const {
                    is_stock_message,
                    is_price_updated,
                    is_price_message,
                    ...updatedDetails
                } = item.details;
                return {
                    ...updatedItems,
                    details: updatedDetails,
                };
            });

            let data = {
                userid: xid,
                cart_item: JSON.stringify(latestCartItem),
            };

            axiosFront
                .post(`front/self/cart-items`, data)
                .then((response) => {
                    fetchLatestCartItems();
                })
                .catch((error) => {
                    if (axios.isCancel(error)) {
                        console.log("Request canceled:", error.message);
                    } else {
                        console.error("An error occurred:", error);
                    }
                });
        } else {
            store.commit("front/addCartItems", updatedCartItems);
        }
    };

    const showProductPage = (xid) => {
        router.push({
            name: "front.product",
            params: {
                warehouse: frontWarehouse.value.slug,
                id: xid,
            },
        });
    };

    return {
        appSetting,
        user,
        checkPermission,
        permsArray,
        statusColors,
        userStatus,
        taxTypes,
        barcodeSymbology,

        disabledDate,
        formatAmount,
        formatAmountCurrency,

        calculateFilterString,
        updatePageTitle,

        // For Stock routes
        orderType,
        orderPageObject,
        orderStatus,
        paymentStatus,
        purchaseOrderStatus,
        salesOrderStatus,
        purchaseReturnStatus,
        salesReturnStatus,

        getRecursiveCategories,
        filterTreeNode,
        getOrderTypeFromstring,

        products,
        total,
        updateCart,
        removeItem,
        addCartItem,
        UpdateCartItems,
        fetchLatestCartItems,
        showProductPage,
        calculateTotalShipping,
        setUnitPrice,
    };
};

export default cart;
