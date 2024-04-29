import { computed, ref, onMounted } from "vue";
import moment from "moment";
import { useStore } from "vuex";
import { useI18n } from "vue-i18n";
import { forEach, find, includes } from "lodash-es";
import { useRoute } from "vue-router";
import { checkUserPermission } from "../scripts/functions";
import dayjs from "dayjs";

moment.suppressDeprecationWarnings = true;
import utc from "dayjs/plugin/utc";
import timezone from "dayjs/plugin/timezone";
dayjs.extend(utc);
dayjs.extend(timezone);

const common = () => {
    const route = useRoute();
    const store = useStore();
    const { t } = useI18n();
    const baseUrl = window.config.path;
    const orderType = ref(route.meta.orderType);
    const invoiceBaseUrl = window.config.invoice_url;
    const downloadLangCsvUrl = window.config.download_lang_csv_url;
    const appType = window.config.app_type;
    const viewinvoiceBaseUrl = window.config.view_invoice_url;
    const pageTitle = store.state.auth.pageTitle;
    const countryCode = store.state.auth.country_code;
    const menuCollapsed = computed(() => store.state.auth.menuCollapsed);
    const cssSettings = computed(() => store.state.auth.cssSettings);
    const appModules = computed(() => store.state.auth.activeModules);
    const visibleSubscriptionModules = computed(
        () => store.state.auth.visibleSubscriptionModules
    );
    const globalSetting = computed(() => store.state.auth.globalSetting);
    const appSetting = computed(() => store.state.auth.appSetting);
    const addMenus = computed(() => store.state.auth.addMenus);
    const selectedLang = computed(() => store.state.auth.lang);
    const user = computed(() => store.state.auth.user);
    const selectedWarehouse = computed(() => store.state.auth.warehouse);
    const allWarehouses = computed(() => store.state.auth.all_warehouses);
    const frontAppSetting = computed(() => store.state.front.appSetting);
    const frontUserToken = computed(() => store.state.front.token);
    const frontWarehouse = computed(() => {
        return find(allWarehouses.value, ["slug", route.params.warehouse]);
    });

    onMounted(() => {
        if (route.meta && route.meta.orderType) {
            orderType.value = route.meta.orderType;
        } else {
            orderType.value = "online-orders";
        }
    });

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

    const dayjsObject = (date) => {
        if (date == undefined) {
            return dayjs().tz(appSetting.value.timezone);
        } else {
            return dayjs(date).tz(appSetting.value.timezone);
        }
    };

    const formatDate = (date) => {
        if (date == undefined) {
            return dayjs()
                .tz(appSetting.value.timezone)
                .format(`${appSetting.value.date_format}`);
        } else {
            return dayjs(date)
                .tz(appSetting.value.timezone)
                .format(`${appSetting.value.date_format}`);
        }
    };

    const formatDate1 = (date) => {
        if (date == undefined) {
            return dayjs().format(`${appSetting.value.date_format}`);
        } else {
            return dayjs(date).format(`${appSetting.value.date_format}`);
        }
    };

    const formatDateTime = (dateTime) => {
        if (dateTime == undefined) {
            return dayjs()
                .tz(appSetting.value.timezone)
                .format(
                    `${appSetting.value.date_format} ${appSetting.value.time_format}`
                );
        } else {
            return dayjs(dateTime)
                .tz(appSetting.value.timezone)
                .format(
                    `${appSetting.value.date_format} ${appSetting.value.time_format}`
                );
        }
    };

    const formatAmount = (amount) => {
        return parseFloat(parseFloat(amount).toFixed(2));
    };

    const formatAmountCurrency = (amount) => {
        const newAmount = parseFloat(Math.abs(amount))
            .toFixed(2)
            .toString()
            .replace(/\B(?=(\d{3})+(?!\d))/g, ",");

        let hasKey = appSetting.value.hasOwnProperty("currency");
        // let hasKey1 = frontAppSetting.value.hasOwnProperty('currency');
        if (hasKey) {
            if (appSetting.value.currency.position == "front") {
                var newAmountString = `${appSetting.value.currency.symbol}${newAmount}`;
            } else {
                var newAmountString = `${newAmount}${appSetting.value.currency.symbol}`;
            }
        } else {
            if (frontAppSetting.value != "") {
                if (frontAppSetting.value.currency.position == "front") {
                    var newAmountString = `${frontAppSetting.value.currency.symbol}${newAmount}`;
                } else {
                    var newAmountString = `${newAmount}${frontAppSetting.value.currency.symbol}`;
                }
            }
        }

        // let hasKey1 = frontAppSetting.value.hasOwnProperty('currency');

        // if (frontAppSetting.value.currency.position == "front") {
        //     var newAmountString = `${frontAppSetting.value.currency.symbol}${newAmount}`;
        // } else {
        //     var newAmountString = `${newAmount}${frontAppSetting.value.currency.symbol}`;
        // }

        return amount < 0 ? `- ${newAmountString}` : newAmountString;
    };

    const formatAmountCurrency1 = (amount) => {
        const newAmount = parseFloat(Math.abs(amount))
            .toFixed(2)
            .toString()
            .replace(/\B(?=(\d{3})+(?!\d))/g, ",");

        let hasKey = appSetting.value.hasOwnProperty("currency");
        // let hasKey1 = frontAppSetting.value.hasOwnProperty('currency');
        if (hasKey) {
            if (appSetting.value.currency.position == "front") {
                var newAmountString = `${newAmount}`;
            } else {
                var newAmountString = `${newAmount}`;
            }
        } else {
            if (frontAppSetting.value != "") {
                if (frontAppSetting.value.currency.position == "front") {
                    var newAmountString = `${frontAppSetting.value.currency.symbol}${newAmount}`;
                } else {
                    var newAmountString = `${newAmount}${frontAppSetting.value.currency.symbol}`;
                }
            }
        }

        return amount < 0 ? `- ${newAmountString}` : newAmountString;
    };

    const formatAmountUsingCurrencyObject = (amount, currency) => {
        const newAmount = parseFloat(Math.abs(amount))
            .toFixed(2)
            .toString()
            .replace(/\B(?=(\d{3})+(?!\d))/g, ",");

        if (currency.position == "front") {
            var newAmountString = `${currency.symbol}${newAmount}`;
        } else {
            var newAmountString = `${newAmount}${currency.symbol}`;
        }

        return amount < 0 ? `- ${newAmountString}` : newAmountString;
    };

    const calculateOrderFilterString = (filters) => {
        var filterString = "";

        if (
            filters.payment_status != undefined &&
            filters.payment_status != "all"
        ) {
            if (filters.payment_status == "pending") {
                filterString += `(payment_status eq "${filters.payment_status}" or payment_status eq "partially_paid" or payment_status eq "unpaid")`;
            } else {
                filterString += `payment_status eq "${filters.payment_status}"`;
            }
        }

        // Order Status
        if (
            filters.order_status != undefined &&
            filters.order_status != "all"
        ) {
            if (orderType.value == "online-orders") {
                if (filters.order_status == "cancelled") {
                    filterString += `cancelled eq 1`;
                } else if (filters.order_status == "pending") {
                    filterString += `((order_status eq "ordered" or order_status eq "confirmed" or order_status eq "processing" or order_status eq "shipping") and cancelled ne 1)`;
                } else {
                    filterString += `(order_status eq "${filters.order_status}" and cancelled ne 1)`;
                }
            } else {
                filterString += `order_status eq "${filters.order_status}"`;
            }
        }

        return filterString;
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
        checkUserPermission(permissionName, user.value);

    const updatePageTitle = (pageName) => {
        store.commit("auth/updatePageTitle", t(`menu.${pageName}`));
    };

    const permsArray = computed(() => {
        const permsArrayList =
            user && user.value && user.value.role ? [user.value.role.name] : [];

        if (user && user.value && user.value.role) {
            forEach(user.value.role.perms, (permission) => {
                permsArrayList.push(permission.name);
            });
        }

        return permsArrayList;
    });

    const QrCodeValue = (upi, amount) => {
        return `upi://pay?pa=${upi}&am=${amount}`;
    };

    const orderPageObject = computed(() => {
        var pageObjectDetails = {};

        if (orderType.value == "purchases") {
            pageObjectDetails = {
                type: "purchases",
                langKey: "purchase",
                menuKey: "purchases",
                userType: "suppliers",
                permission: "purchases",
            };
        } else if (orderType.value == "sales") {
            pageObjectDetails = {
                type: "sales",
                langKey: "sales",
                menuKey: "sales",
                userType: "customers",
                permission: "sales",
            };
        } else if (orderType.value == "purchase-returns") {
            pageObjectDetails = {
                type: "purchase-returns",
                langKey: "purchase_returns",
                menuKey: "purchase_returns",
                userType: "suppliers",
                permission: "purchase_returns",
            };
        } else if (orderType.value == "sales-returns") {
            pageObjectDetails = {
                type: "sales-returns",
                langKey: "sales_returns",
                menuKey: "sales_returns",
                userType: "customers",
                permission: "sales_returns",
            };
        } else if (orderType.value == "online-orders") {
            pageObjectDetails = {
                type: "online-orders",
                langKey: "online_orders",
                menuKey: "online_orders",
                userType: "customers",
                permission: "online_orders",
            };
        } else if (orderType.value == "quotations") {
            pageObjectDetails = {
                type: "quotations",
                langKey: "quotation",
                menuKey: "quotation",
                userType: "customers",
                permission: "quotations",
            };
        } else if (orderType.value == "stock-transfers") {
            pageObjectDetails = {
                type: "stock-transfers",
                langKey: "stock_transfer",
                menuKey: "stock_transfer",
                userType: "customers",
                permission: "stock_transfers",
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
            value: t("common.unpaid"),
        },
        {
            key: "paid",
            value: t("common.paid"),
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

    const orderStatusColors = {
        received: "green",
        pending: "orange",
        ordered: "blue",

        completed: "green",
        pending: "orange",

        delivered: "green",
        shipping: "purple",
        processing: "pink",
        confirmed: "cyan",
        ordered: "blue",

        received: "green",
        pending: "orange",
    };

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

    const onlineOrderChangeStatus = [
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
    ];

    const salesOrderStatus = [
        {
            key: "ordered",
            value: t("common.ordered"),
        },
        ...onlineOrderChangeStatus,
        {
            key: "delivered",
            value: t("common.delivered"),
        },
    ];

    const invoiceSymbol = [
        {
            key: "-",
            value: "-",
        },
        {
            key: "/",
            value: "/",
        },
    ];

    const resetInvoice = [
        {
            key: "January",
            value: "January",
        },
        {
            key: "February",
            value: "February",
        },
        {
            key: "March",
            value: "March",
        },
        {
            key: "April",
            value: "April",
        },
        {
            key: "May",
            value: "May",
        },
        {
            key: "June",
            value: "June",
        },
        {
            key: "July",
            value: "July",
        },
        {
            key: "August",
            value: "August",
        },
        {
            key: "September",
            value: "September",
        },
        {
            key: "October",
            value: "October",
        },
        {
            key: "November",
            value: "November",
        },
        {
            key: "December",
            value: "December",
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
                (excludeId != null && responseArray.x_parent_id != excludeId)
            ) {
                listArray.push({
                    xid: responseArray.xid,
                    x_parent_id: responseArray.x_parent_id,
                    title: responseArray.name,
                    value: responseArray.xid,
                });
            }
        });

        listArray.forEach((node) => {
            // No parentId means top level
            if (!node.x_parent_id) return allCategoriesArray.push(node);

            // Insert node as child of parent in listArray array
            const parentIndex = listArray.findIndex(
                (el) => el.xid === node.x_parent_id
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

    const slugify = (text) => {
        return text
            .toString()
            .toLowerCase()
            .replace(/\s+/g, "-") // Replace spaces with -
            .replace(/[^\w\-]+/g, "") // Remove all non-word chars
            .replace(/\-\-+/g, "-") // Replace multiple - with single -
            .replace(/^-+/, "") // Trim - from start of text
            .replace(/-+$/, ""); // Trim - from end of text
    };

    const convertToPositive = (amount) => {
        return amount < 0 ? amount * -1 : amount;
    };

    const willSubscriptionModuleVisible = (moduleName) => {
        if (appType == "single") {
            return true;
        } else {
            if (
                appSetting.value.subscription_plan &&
                appSetting.value.subscription_plan.modules
            ) {
                return includes(
                    appSetting.value.subscription_plan.modules,
                    moduleName
                );
            } else {
                return false;
            }
        }
    };

    const validateImageSize = (data) => {
        return new Promise((resolve, reject) => {
            const file = data.file;

            // Check if the file is an image
            if (file.type.startsWith("image/")) {
                const reader = new FileReader();

                reader.onload = function (e) {
                    // Create an Image element
                    const img = new Image();

                    img.onload = function () {
                        const width = img.width;
                        const height = img.height;

                        if (width <= 400 && height <= 180) {
                            resolve(true);
                        } else {
                            resolve(false);
                        }
                    };

                    img.src = e.target.result;
                };

                reader.onerror = function (error) {
                    reject(error);
                };
                reader.readAsDataURL(file);
            } else {
                resolve(false);
            }
        });
    };

    const validateImageWithTrnBg = (data) => {
        return new Promise((resolve, reject) => {
            const file = data.file;

            // Check if the file is an image
            if (file.type.startsWith("image/png")) {
                const reader = new FileReader();

                reader.onload = function (e) {
                    const img = new Image();

                    img.onload = function () {
                        // Create a canvas element
                        const canvas = document.createElement("canvas");
                        const context = canvas.getContext("2d");

                        canvas.width = img.width;
                        canvas.height = img.height;

                        // Draw the image onto the canvas
                        context.drawImage(img, 0, 0, img.width, img.height);

                        // Get the image data
                        const imageData = context.getImageData(
                            0,
                            0,
                            canvas.width,
                            canvas.height
                        );

                        // Check if any pixel has a non-opaque alpha value (indicating transparency)
                        const hasTransparentPixels = Array.from(
                            imageData.data
                        ).some((value, index) => {
                            // The alpha channel is the fourth value in each group of four values (RGBA)
                            if ((index + 1) % 4 === 0 && value !== 255) {
                                return true;
                            }
                            return false;
                        });

                        resolve(hasTransparentPixels);
                    };

                    img.src = e.target.result;
                };

                reader.onerror = function (error) {
                    reject(error);
                };

                reader.readAsDataURL(file);
            } else {
                // If the file is not a PNG image, resolve with false
                resolve(false);
            }
        });
    };

    const numberToWords = (num) => {
        let newNum = num | 0;

        const ones = [
            "Zero",
            "One",
            "Two",
            "Three",
            "Four",
            "Five",
            "Six",
            "Seven",
            "Eight",
            "Nine",
            "Ten",
            "Eleven",
            "Twelve",
            "Thirteen",
            "Fourteen",
            "Fifteen",
            "Sixteen",
            "Seventeen",
            "Eighteen",
            "Nineteen",
        ];
        const tens = [
            "",
            "",
            "Twenty",
            "Thirty",
            "Forty",
            "Fifty",
            "Sixty",
            "Seventy",
            "Eighty",
            "Ninety",
        ];
        const scales = ["", "Thousand", "Million", "Billion"];

        if (newNum === 0) return ones[0];

        const toWords = (newNum) => {
            if (newNum === 0) return "";
            if (newNum < 20) return ones[newNum];
            if (newNum < 100)
                return (
                    tens[Math.floor(newNum / 10)] +
                    (newNum % 10 !== 0 ? " " + ones[newNum % 10] : "")
                );
            if (newNum < 1000)
                return (
                    ones[Math.floor(newNum / 100)] +
                    " Hundred" +
                    (newNum % 100 !== 0 ? " " + toWords(newNum % 100) : "")
                );
            for (let i = 0; i < scales.length; i++) {
                const scale = Math.pow(1000, i);
                if (newNum < scale * 1000) {
                    return (
                        toWords(Math.floor(newNum / scale)) +
                        " " +
                        scales[i] +
                        (newNum % scale !== 0
                            ? " " + toWords(newNum % scale)
                            : "")
                    );
                }
            }
        };

        return toWords(newNum);
    };

    const filterCustomers =
        selectedWarehouse?.value?.is_staff_base == 1 &&
        !permsArray.value.includes("admin") &&
        !permsArray.value.includes("view_all_customer")
            ? `&filters=(assign_to lk "${user.value.xid}" or is_walkin_customer lk 1)`
            : "";

    async function convertImageToBase64(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = () => {
                resolve(reader.result);
            };
            reader.onerror = reject;
        });
    }
    return {
        menuCollapsed,
        appSetting,
        appType,
        addMenus,
        selectedLang,
        user,
        selectedWarehouse,
        allWarehouses,
        checkPermission,
        permsArray,
        statusColors,
        orderStatusColors,
        userStatus,
        taxTypes,
        barcodeSymbology,
        frontAppSetting,
        pageTitle,
        countryCode,

        disabledDate,
        formatAmount,
        formatAmountCurrency,
        formatAmountCurrency1,
        formatAmountUsingCurrencyObject,
        convertToPositive,

        calculateOrderFilterString,
        calculateFilterString,
        updatePageTitle,

        // For Stock routes
        orderType,
        orderPageObject,
        orderStatus,
        paymentStatus,
        purchaseOrderStatus,
        onlineOrderChangeStatus,
        salesOrderStatus,
        invoiceSymbol,
        resetInvoice,
        purchaseReturnStatus,
        salesReturnStatus,

        getRecursiveCategories,
        filterTreeNode,
        getOrderTypeFromstring,

        invoiceBaseUrl,
        baseUrl,
        downloadLangCsvUrl,
        appModules,
        dayjs,
        formatDate,
        formatDate1,
        formatDateTime,
        dayjsObject,
        slugify,

        cssSettings,
        frontWarehouse,
        globalSetting,

        willSubscriptionModuleVisible,
        visibleSubscriptionModules,
        viewinvoiceBaseUrl,
        numberToWords,
        filterCustomers,
        QrCodeValue,
        validateImageSize,
        frontUserToken,
        convertImageToBase64,
        validateImageWithTrnBg
    };
};

export default common;
