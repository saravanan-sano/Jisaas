<template>
    <a-row :gutter="16">
        <a-button
            v-if="!(checkInDetails && checkInDetails.hours)"
            type="primary"
            :loading="Loading"
            @click="handleCheckIn"
        >
            <template #icon>
                <LogoutOutlined v-if="checkInStatus" />
                <LoginOutlined v-else />
            </template>
            {{ checkInStatus ? "Check Out" : "Check In" }}
        </a-button>
        <a-button type="primary" v-if="checkInDetails && checkInDetails.hours">
            <template #icon>
                <ClockCircleOutlined />
            </template>
            {{ checkInDetails.hours }}
        </a-button>
    </a-row>
</template>
<script>
import { Modal, notification } from "ant-design-vue";
import dayjs from "dayjs";
import { ref, onMounted, createVNode } from "vue";
import {
    ExclamationCircleOutlined,
    LoginOutlined,
    LogoutOutlined,
    ClockCircleOutlined
} from "@ant-design/icons-vue";
import { useI18n } from "vue-i18n";

export default {
    components: {
        ExclamationCircleOutlined,
        LoginOutlined,
        LogoutOutlined,
        ClockCircleOutlined
    },
    setup(props) {
        const checkInStatus = ref(true);
        const Loading = ref(false);
        const checkInDetails = ref(null);
        const { t } = useI18n();

        const handleCheckIn = () => {
            Loading.value = true;
            if (checkInStatus.value) {
                Modal.confirm({
                    title: "Check Out",
                    icon: createVNode(ExclamationCircleOutlined),
                    content: "Are you sure you want to Check Out?",
                    centered: true,
                    okText: t("common.yes"),
                    okType: "primary",
                    cancelText: t("common.no"),
                    onOk() {
                        return axiosAdmin
                            .post("staff-check-in", {
                                date: dayjs().format("YYYY-MM-DD"),
                                time: dayjs().format("HH:mm:ss"),
                                type: "web",
                            })
                            .then((res) => {
                                notification.success({
                                    message: t("common.success"),
                                    description: "Successfully Checked Out!",
                                    placement: "bottomRight",
                                });
                                Loading.value = false;
                                getData();
                            })
                            .catch((err) => {
                                console.log(err);
                                Loading.value = false;
                                getData();
                            });
                    },
                    onCancel() {
                        Loading.value = false;
                    },
                });
            } else {
                axiosAdmin
                    .post("staff-check-in", {
                        date: dayjs().format("YYYY-MM-DD"),
                        time: dayjs().format("HH:mm:ss"),
                        type: "web",
                    })
                    .then((res) => {
                        notification.success({
                            message: t("common.success"),
                            description: "Successfully Checked In!",
                            placement: "bottomRight",
                        });
                        Loading.value = false;
                        getData();
                    })
                    .catch((err) => {
                        console.log(err);
                        Loading.value = false;
                        getData();
                    });
            }
        };

        const getData = () => {
            axiosAdmin
                .get("staff-check-in")
                .then((res) => {
                    checkInStatus.value = res.status;
                    if (res.status) checkInDetails.value = res.data;
                })
                .catch((err) => {
                    console.log(err);
                });
        };

        onMounted(() => {
            getData();
        });

        return {
            checkInStatus,
            handleCheckIn,
            Loading,
            checkInDetails,
        };
    },
};
</script>

<style></style>
