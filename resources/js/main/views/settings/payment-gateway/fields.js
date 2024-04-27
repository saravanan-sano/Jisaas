import { useI18n } from "vue-i18n";

const fields = () => {
    const url = "get-payment-key";
    const addEditUrl = "payment-modes";
    const { t } = useI18n();
    const dummyData = {
        upi_gateway: {
            paymentKey: "upi_gateway",
            credentials: {
                key: "",
            },
        },
        paytm: {
            paymentKey: "paytm",
            credentials: {
                MID: "",
                TID: [''],
                paytm_mid_key: "",
            },
        },
    };

    const columns = [
        {
            title: t("payment_gateway.paymentKey"),
            dataIndex: "paymentKey",
        },
        {
            title: t("common.action"),
            dataIndex: "action",
        },
    ];

    return {
        url,
        addEditUrl,
        columns,
        dummyData,
    };
};

export default fields;
