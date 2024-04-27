<template>
    <a-modal
        :visible="visible"
        :closable="false"
        :centered="true"
        :title="$t(`payment_gateway.${formData.paymentKey}`)"
        @ok="onSubmit"
    >
        <a-form layout="horizontal">
            <a-row :gutter="16" v-if="formData.paymentKey == 'upi_gateway'">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item :label="$t(`payment_gateway.key`)">
                        <a-input
                            v-model:value="formData.credentials.key"
                            :placeholder="`Please enter the UPI key`"
                            style="width: 100%"
                        />
                    </a-form-item>
                </a-col>
            </a-row>
            <a-row :gutter="16" v-if="formData.paymentKey == 'paytm'">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item
                        :label="$t(`payment_gateway.mid`)"
                        name="credentials.mid"
                    >
                        <a-input
                            v-model:value="formData.credentials.MID"
                            :placeholder="`Please enter the MID key`"
                            style="width: 100%"
                        />
                    </a-form-item>
                </a-col>
                <a-col
                    :xs="24"
                    :sm="24"
                    :md="24"
                    :lg="24"
                    v-for="(tid, index) in formData.credentials.TID"
                    :key="index"
                >
                    <a-form-item
                        :label="`${$t('payment_gateway.tid')} ${index + 1}`"
                        name="credentials.tid"
                    >
                        <a-input
                            v-model:value="formData.credentials.TID[index]"
                            @change="(e) => handleTid(e, index)"
                            :placeholder="`Please enter the TID`"
                            style="width: 100%"
                        />
                    </a-form-item>
                </a-col>
                <a-col
                    :xs="24"
                    :sm="24"
                    :md="24"
                    :lg="24"
                    style="margin-bottom: 1rem"
                >
                    <a-row :gutter="16">
                        <a-col :xs="20" :sm="20" :md="20" :lg="20">
                            <a-button type="dashed" @click="handleAddTid"
                                ><PlusOutlined /> Add TID</a-button
                            ></a-col
                        >
                        <a-col :xs="4" :sm="4" :md="4" :lg="4">
                            <a-button
                                type="primary"
                                danger
                                @click="handleDeleteTid"
                                ><DeleteOutlined /></a-button
                        ></a-col>
                    </a-row>
                </a-col>

                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item
                        :label="$t(`payment_gateway.paytm_mid_key`)"
                        name="credentials.paytm_mid_key"
                    >
                        <a-input
                            v-model:value="formData.credentials.paytm_mid_key"
                            :placeholder="`Please enter the Paytm mid key`"
                            style="width: 100%"
                        />
                    </a-form-item>
                </a-col>
            </a-row>
        </a-form>
        <template #footer>
            <a-button
                key="submit"
                type="primary"
                :loading="loading"
                @click="onSubmit"
            >
                <template #icon>
                    <SaveOutlined />
                </template>
                {{
                    addEditType == "add"
                        ? $t("common.create")
                        : $t("common.update")
                }}
            </a-button>
            <a-button key="back" @click="onClose">
                {{ $t("common.cancel") }}
            </a-button>
        </template>
    </a-modal>
</template>

<script>
import { defineComponent } from "vue";
import {
    PlusOutlined,
    LoadingOutlined,
    SaveOutlined,
    DeleteOutlined,
} from "@ant-design/icons-vue";
import { message } from "ant-design-vue";

export default defineComponent({
    props: ["formData", "visible", "addEditType"],
    components: {
        PlusOutlined,
        LoadingOutlined,
        SaveOutlined,
        DeleteOutlined,
    },
    setup(props, { emit }) {
        const onSubmit = () => {
            axiosAdmin
                .post("payment-key", props.formData)
                .then((result) => {
                    message.success("Updated Successfully");
                    emit("addEditSuccess");
                })
                .catch((err) => {
                    console.log(err);
                });
        };

        const handleTid = (e, index) => {
            const { value } = e.target;
            props.formData.credentials.TID[index] = value;
        };

        const handleAddTid = () => {
            props.formData.credentials.TID.push("");
        };
        const handleDeleteTid = () => {
            if (props.formData.credentials.TID.length > 1) {
                props.formData.credentials.TID.pop();
            }
        };

        const onClose = () => {
            emit("closed");
        };

        return {
            onClose,
            onSubmit,
            handleTid,
            handleAddTid,
            handleDeleteTid,
            drawerWidth: window.innerWidth <= 991 ? "90%" : "45%",
        };
    },
});
</script>
