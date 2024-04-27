import { useI18n } from "vue-i18n";

const fields = () => {
    const { t } = useI18n();
    const ExpiryColumn = [
        {
            title: t("product.product"),
            dataIndex: "name",
        },
        {
            title: t("product.item_code"),
            dataIndex: "item_code",
        },
        {
            title: t("product.sales_price"),
            dataIndex: "sales_price",
        },
        {
            title: t("product.purchase_price"),
            dataIndex: "purchase_price",
        },
        {
            title: t("product.current_stock"),
            dataIndex: "current_stock",
        },
        {
            title: t("product.expiry"),
            dataIndex: "expiry",
        },
    ];
    return {
        ExpiryColumn
    }
}
export default fields
