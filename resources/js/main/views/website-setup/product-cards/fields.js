import { useI18n } from "vue-i18n";

const fields = () => {
    const url =
        "product-cards?fields=xid,title,image,image_url,subtitle,products,x_products,products_details";
    const { t } = useI18n();
    const hashableColumns = ["products"];

    const initData = {
        title: "",
        subtitle: "",
        image: undefined,
        image_url: undefined,
        products: [],
    };

    const columns = [
        {
            title: t("product_card.title"),
            dataIndex: "title",
            width: "12%",
        },
        {
            title: t("product_card.image"),
            dataIndex: "image",
            width: "25%",
        },
        {
            title: t("product_card.products"),
            dataIndex: "products",
        },
        {
            title: t("common.action"),
            dataIndex: "action",
            width: "15%",
        },
    ];

    const filterableColumns = [
        {
            key: "title",
            value: t("product_card.title"),
        },
    ];

    return {
        url,
        initData,
        columns,
        filterableColumns,
        hashableColumns,
    };
};

export default fields;
