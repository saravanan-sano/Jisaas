import { ref, reactive, computed, watch, nextTick } from "vue";
import { useRoute } from "vue-router";
import { debounce, includes } from "lodash-es";
import common from "../../../../common/composable/common";
import dayjs from "dayjs";
import * as XLSX from "xlsx";
import { message } from "ant-design-vue";

const stockManagement = () => {
    const { formatAmount, orderType, orderPageObject, selectedWarehouse } =
        common();
    const route = useRoute();
    const selectedProducts = ref([]);
    const selectedProductIds = ref([]);
    const removedOrderItemsIds = ref([]);
    const isCustomerWholesale = ref(false);
    const state = reactive({
        orderSearchTerm: [],
        productFetching: false,
        products: [],
    });

    const formData = ref({
        order_type: route.params.type,
        invoice_number: "",
        order_date: dayjs(),
        warehouse_id:
            orderType.value == "stock-transfers"
                ? undefined
                : selectedWarehouse.value.xid,
        user_id: undefined,
        terms_condition: selectedWarehouse.value.terms_condition,
        notes: "",
        order_status: undefined,
        tax_id: undefined,
        tax_rate: 0,
        tax_amount: 0,
        discount: 0,
        shipping: 0,
        subtotal: 0,
        discount_type: "percentage",
        discount_value: 0,
        attachments: undefined,
        attachments_url: undefined,
    });
    const taxes = ref([]);
    const productsAmount = ref({
        subtotal: 0,
        tax: 0,
    });
    const searchBarcodeInput = ref(false);
    const barcodeSearchTerm = ref("");

    // AddEdit
    const addEditVisible = ref(false);
    const addEditFormSubmitting = ref(false);
    const addEditFormData = ref({});
    const addEditRules = ref([]);
    const addEditPageTitle = ref("");

    const fetchProducts = debounce((value) => {
        state.products = [];

        if (value != "") {
            state.productFetching = true;
            let url = `search-product`;

            axiosAdmin
                .post(url, {
                    order_type: orderPageObject.value.type,
                    search_term: value,
                    warehouse_id: formData.value.warehouse_id,
                    products: selectedProductIds.value,
                    userid: formData.value.user_id,
                })
                .then((response) => {
                    state.products = response.data;
                    state.productFetching = false;
                });
        }
    }, 300);

    const barcodeFetch = (value) => {
        nextTick(() => {
            if (value != "") {
                state.productFetching = true;
                let url = `search-barcode-product`;

                axiosAdmin
                    .post(url, {
                        order_type: orderPageObject.value.type,
                        search_term: value.target.value,
                        warehouse_id: formData.value.warehouse_id,
                        products: selectedProductIds.value,
                    })
                    .then((response) => {
                        if (
                            response.data &&
                            response.data.product &&
                            response.data.product.xid
                        ) {
                            var product = response.data.product;
                            searchValueSelected("", { product: product });
                        }

                        barcodeSearchTerm.value = "";
                        state.productFetching = false;
                    });
            }
        });
    };

    const searchValueSelected = (value, option) => {
        const newProduct = option.product;
        if (!includes(selectedProductIds.value, newProduct.xid)) {
            selectedProductIds.value.push(newProduct.xid);
            newProduct.default_price = formatAmount(newProduct.unit_price);
            selectedProducts.value.push({
                ...newProduct,
                sn: selectedProducts.value.length + 1,
                unit_price: formatAmount(
                    orderPageObject.value.type == "sales"
                        ? setUnitPrice(newProduct)
                        : newProduct.unit_price
                ),
                discount_type: newProduct.discount_type
                    ? newProduct.discount_type
                    : "fixed",
                discount: newProduct.discount ? newProduct.discount : 0,
                single_unit_price: formatAmount(newProduct.single_unit_price),
                tax_amount: formatAmount(newProduct.tax_amount),
                subtotal: formatAmount(newProduct.subtotal),
                default_sales_price: newProduct.sales_price,
                is_sales_price_changed: false,
            });
            state.orderSearchTerm = [];
            state.products = [];
            selectedProducts.value.map((product) => {
                quantityChanged(product);
            });
            recalculateFinalTotal();
        } else {
            const newResults = [];
            var foundRecord = {};

            selectedProducts.value.map((selectedProduct) => {
                var newQuantity = selectedProduct.quantity;

                if (selectedProduct.xid == newProduct.xid) {
                    newQuantity += 1;
                    selectedProduct.quantity = newQuantity;
                    foundRecord = selectedProduct;
                }

                newResults.push(selectedProduct);
            });
            selectedProducts.value = newResults;
            state.orderSearchTerm = [];
            state.products = [];

            quantityChanged(foundRecord);
        }
    };

    const recalculateValues = (product, name) => {
        var quantityValue = parseFloat(product.quantity ? product.quantity : 0);
        var maxQuantity = parseFloat(product.stock_quantity);
        const unitPrice = parseFloat(
            orderPageObject.value.type == "sales"
                ? setUnitPrice(product)
                : product.unit_price
        );

        // Check if entered quantity value is greater
        if (
            orderPageObject.value.type == "sales" ||
            orderPageObject.value.type == "purchase-returns"
        ) {
            quantityValue =
                quantityValue > maxQuantity &&
                selectedWarehouse.value.is_negative_stock == 0
                    ? maxQuantity
                    : quantityValue;
        } else {
            quantityValue = quantityValue;
        }

        // Discount Amount
        let discountRate = 0;
        let totalDiscount = 0;
        let fixedDiscountAmount = 0;
        let beforeTaxValue = unitPrice;
        let totalPriceAfterDiscount = beforeTaxValue * quantityValue;

        // While Changing the Total Discount of the product
        if (product.discount_type == "fixed") {
            totalDiscount =
                product.discount <= totalPriceAfterDiscount
                    ? product.discount
                    : totalPriceAfterDiscount;

            totalPriceAfterDiscount = totalPriceAfterDiscount - totalDiscount;
            fixedDiscountAmount = totalDiscount;
            discountRate =
                totalDiscount > 0
                    ? (totalDiscount / totalPriceAfterDiscount) * 100
                    : 0;
        }

        if (product.discount_type == "percentage") {
            totalDiscount = product.discount <= 100 ? product.discount : 100;
            discountRate = totalDiscount;
            fixedDiscountAmount =
                (totalDiscount / 100) * totalPriceAfterDiscount;
            totalPriceAfterDiscount =
                totalDiscount > 0
                    ? totalPriceAfterDiscount - fixedDiscountAmount
                    : totalPriceAfterDiscount;
        }

        var taxAmount = 0;
        var subtotal = totalPriceAfterDiscount;

        var singleUnitPrice = unitPrice;

        // Tax Amount
        if (product.tax_rate > 0) {
            if (product.tax_type == "inclusive") {
                taxAmount =
                    totalPriceAfterDiscount -
                    totalPriceAfterDiscount / (1 + product.tax_rate / 100);
            } else {
                taxAmount = totalPriceAfterDiscount * (product.tax_rate / 100);
                subtotal = totalPriceAfterDiscount + taxAmount;
            }
        }

        const newObject = {
            ...product,
            discount_rate: formatAmount(discountRate),
            total_discount: fixedDiscountAmount,
            subtotal: subtotal,
            quantity: quantityValue,
            total_tax: taxAmount,
            discount: totalDiscount,
            max_quantity: maxQuantity,
            unit_price: singleUnitPrice,
            return_quantity:
                orderPageObject.value.type == "sales-returns" ||
                orderPageObject.value.type == "purchase-returns"
                    ? product.return_quantity
                    : quantityValue,
        };
        return newObject;
    };

    const quantityChanged = (record, name) => {
        const newResults = [];

        selectedProducts.value.map((selectedProduct) => {
            if (selectedProduct.xid == record.xid) {
                const newValueCalculated = recalculateValues(record, name);
                newResults.push(newValueCalculated);
            } else {
                newResults.push(selectedProduct);
            }
        });
        selectedProducts.value = newResults;

        recalculateFinalTotal();
    };

    const recalculateFinalTotal = () => {
        let total = 0;
        let taxAmount = 0;
        var discountAmount = 0;
        selectedProducts.value.map((selectedProduct) => {
            total += selectedProduct.subtotal;
        });
        if (formData.value.discount_type == "percentage") {
            discountAmount =
                formData.value.discount_value != "" ||
                formData.value.discount_value != null
                    ? (parseFloat(formData.value.discount_value) * total) / 100
                    : 0;
        } else if (formData.value.discount_type == "fixed") {
            discountAmount =
                formData.value.discount_value != "" ||
                formData.value.discount_value != null
                    ? parseFloat(formData.value.discount_value)
                    : 0;
        } else {
            discountAmount =
                formData.value.discount != "" || formData.value.discount != null
                    ? parseFloat(formData.value.discount)
                    : 0;
        }

        const taxRate =
            formData.value.tax_rate != "" || formData.value.tax_rate != null
                ? parseFloat(formData.value.tax_rate)
                : 0;

        selectedProducts.value.map((selectedProduct) => {
            taxAmount += selectedProduct.total_tax;
        });
        productsAmount.value.subtotal = total;
        productsAmount.value.tax = taxAmount;

        total = total - discountAmount;

        const tax = total * (taxRate / 100);

        total = total + parseFloat(formData.value.shipping);

        formData.value.subtotal = formatAmount(total + tax);
        formData.value.tax_amount = formatAmount(tax);
        formData.value.discount = discountAmount;
    };

    const calculateProductAmount = () => {
        let total = 0;
        let taxAmount = 0;
        selectedProducts.value.map((selectedProduct) => {
            total += selectedProduct.subtotal;
        });

        selectedProducts.value.map((selectedProduct) => {
            taxAmount += selectedProduct.total_tax;
        });
        productsAmount.value.subtotal = total;
        productsAmount.value.tax = taxAmount;
    };

    const showDeleteConfirm = (product) => {
        // Delete selected product and rearrange SN
        const newResults = [];
        let counter = 1;
        if (product.item_id != null || product.item_id != "") {
            removedOrderItemsIds.value = [
                ...removedOrderItemsIds.value,
                product.item_id,
            ];
        }

        selectedProducts.value.map((selectedProduct) => {
            if (selectedProduct.xid != product.xid) {
                newResults.push({
                    ...selectedProduct,
                    sn: counter,
                    single_unit_price: formatAmount(
                        selectedProduct.single_unit_price
                    ),
                    tax_amount: formatAmount(selectedProduct.tax_amount),
                    subtotal: formatAmount(selectedProduct.subtotal),
                });

                counter++;
            }
        });
        selectedProducts.value = newResults;

        // Remove deleted product id from lists
        const filterProductIdArray = selectedProductIds.value.filter(
            (newId) => {
                return newId != product.xid;
            }
        );
        selectedProductIds.value = filterProductIdArray;
        recalculateFinalTotal();
    };

    const taxChanged = (value, option) => {
        formData.value.tax_rate = value == undefined ? 0 : option.tax.rate;
        recalculateFinalTotal();
    };

    const editItem = (product) => {
        addEditFormData.value = {
            id: product.xid,
            discount_rate: product.discount_rate,
            unit_price: product.unit_price,
            tax_id: product.x_tax_id,
            tax_type: product.tax_type == null ? undefined : product.tax_type,
        };
        addEditVisible.value = true;
        addEditPageTitle.value = product.name;
    };

    // For Add Edit
    const onAddEditSubmit = () => {
        const record = selectedProducts.value.filter(
            (selectedProduct) => selectedProduct.xid == addEditFormData.value.id
        );

        const selecteTax = taxes.value.filter(
            (tax) => tax.xid == addEditFormData.value.tax_id
        );

        const taxType =
            addEditFormData.value.tax_type != undefined
                ? addEditFormData.value.tax_type
                : "exclusive";

        const newData = {
            ...record[0],
            discount_rate: parseFloat(addEditFormData.value.discount_rate),
            unit_price: parseFloat(addEditFormData.value.unit_price),
            tax_id: addEditFormData.value.tax_id,
            tax_rate: selecteTax[0] ? selecteTax[0].rate : 0,
            tax_type: taxType,
            is_edited: true,
        };
        quantityChanged(newData);
        onAddEditClose();
    };

    // Added by saravanan for Changing the Unit price, Discount Rate & Total Discount.
    const changeRate = (record, name) => {
        const product = selectedProducts.value.filter(
            (selectedProduct) => selectedProduct.xid == record.xid
        );
        const selecteTax = taxes.value.filter(
            (tax) => tax.xid == record.x_tax_id
        );
        const taxType =
            record.tax_type != undefined ? record.tax_type : "exclusive";

        const newData = {
            ...product[0],
            discount_rate: parseFloat(
                record.discount_rate ? record.discount_rate : 0
            ),
            unit_price: parseFloat(record.unit_price ? record.unit_price : 0),
            tax_id: product[0].tax_id,
            tax_rate: selecteTax[0] ? selecteTax[0].rate : 0,
            tax_type: taxType,
            is_edited: true,
        };
        quantityChanged(newData, name);
    };

    const calculateShippingPrice = () => {
        return selectedProducts.value.reduce((totalShipping, product) => {
            if (product.is_shipping === 1) {
                return (
                    totalShipping + product.shipping_price * product.quantity
                );
            }
            return totalShipping;
        }, 0);
    };

    const setUnitPrice = (product) => {
        let updatedPrice = product.unit_price;

        const isWholesaleCustomer =
            product.is_wholesale_only == 1 && !isCustomerWholesale.value;

        const edited = product && product.is_edited;

        if (!edited && product.wholesale.length > 0) {
            // Initialize updatedPrice to default_price by default
            updatedPrice = product.default_price;
            for (const wholesaleObj of product.wholesale) {
                const startQuantity = parseFloat(wholesaleObj.start_quantity);
                const endQuantity = parseFloat(wholesaleObj.end_quantity);

                const isInRange =
                    product.quantity >= startQuantity &&
                    product.quantity <= endQuantity;

                if (!isWholesaleCustomer) {
                    // Update price if wholesale customer or non-wholesale customer
                    if (isInRange) {
                        updatedPrice =
                            wholesaleObj.wholesale_price == 0
                                ? product.unit_price
                                : wholesaleObj.wholesale_price;
                        // Don't break to consider the last valid range
                    }
                }
            }
        }
        return updatedPrice;
    };

    const onAddEditClose = () => {
        addEditFormData.value = {};
        addEditVisible.value = false;
    };

    watch(route, (newVal, oldVal) => {
        orderType.value = newVal.params.type;
        formData.value = {
            ...formData.value,
            order_type: newVal.params.type,
        };
    });

    const readFile = (file) => {
        if (file instanceof Blob) {
            const fileReader = new FileReader();

            fileReader.onload = (e) => {
                const bufferArray = e.target.result;
                const wb = XLSX.read(bufferArray, { type: "array" });
                const wsname = wb.SheetNames[0];
                const ws = wb.Sheets[wsname];
                const data = XLSX.utils.sheet_to_json(ws);
                const today = new Date();
                if (data.length > 50) {
                    return message.error(
                        "For Smooth Upload Please import max 50 products."
                    );
                }
                const validate = {
                    product: "Product",
                    unit: "Unit",
                    category: "Category",
                    brand: "brand",
                    purchase_price: "Purchase Price",
                    sales_price: "Sales Price",
                    mrp: "MRP",
                    quantity: "Quantity",
                    subtotal: "Subtotal",
                };
                const dataValidated = data.map((item, index) => {
                    let isValid = true;
                    const missingFields = [];

                    Object.keys(validate).forEach(function (key) {
                        if (!item.hasOwnProperty(key)) {
                            isValid = false;
                            missingFields.push(validate[key]);
                        }
                    });

                    if (!isValid) {
                        const errorMessage = `Row ${index + 2} (${
                            item.product
                        }) - ${missingFields.join(
                            ", "
                        )} Need to be Filled or In Invalid Format`;
                        message.error(errorMessage);
                    }

                    return isValid;
                });

                if (!dataValidated.includes(false)) {
                    data.map((item) => {
                        let newData = {
                            Last_selling: 0,
                            custom_fields: [],
                            discount_rate: 0,
                            image: null,
                            image_url: null,
                            is_wholesale_only: 0,
                            item_code: item.item_code
                                ? item.item_code
                                : parseInt(Math.random() * 10000000000),
                            item_id: "",
                            name: item.product,
                            price_history: null,
                            quantity: item.quantity,
                            single_unit_price: item.sales_price,
                            stock_quantity: item.quantity,
                            subtotal: item.subtotal,
                            tax_rate: item.tax,
                            tax_type: item.tax_type,
                            total_discount: 0,
                            total_tax: 0,
                            unit: item.unit,
                            unit_price: item.purchase_price,
                            unit_short_name: item.unit,
                            wholesale_price: null,
                            wholesale_quantity: null,
                            x_tax_id: null,
                            x_unit_id: "",
                            xid: item.__rowNum__,
                            category: item.category,
                            brand: item.brand,
                            purchase_price: item.purchase_price,
                            sales_price: item.sales_price,
                            mrp: item.mrp,
                        };
                        searchValueSelected("", { product: newData });
                    });
                }
            };

            fileReader.onerror = (error) => {
                console.error("File reading error:", error);
            };

            fileReader.readAsArrayBuffer(file);
        }
    };

    return {
        state,
        route,
        orderType,
        orderPageObject,
        selectedProducts,
        selectedProductIds,
        formData,
        productsAmount,
        taxes,

        fetchProducts,
        searchValueSelected,
        recalculateValues,
        quantityChanged,
        recalculateFinalTotal,
        showDeleteConfirm,
        taxChanged,
        editItem,

        // Add Edit
        addEditVisible,
        addEditFormData,
        addEditFormSubmitting,
        addEditRules,
        addEditPageTitle,
        onAddEditSubmit,
        onAddEditClose,
        removedOrderItemsIds,
        isCustomerWholesale,

        calculateProductAmount,

        barcodeSearchTerm,
        searchBarcodeInput,
        barcodeFetch,

        changeRate,
        readFile,
        calculateShippingPrice,
    };
};

export default stockManagement;
