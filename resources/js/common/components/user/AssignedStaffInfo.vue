<template>
    <div v-if="staff">
        <a-badge>
            <a-avatar :size="30" :src="staff.profile_image_url" />
            {{ staff.name }}
        </a-badge>
    </div>
    <div v-else>
        <span></span>
    </div>
</template>

<script>
import { onMounted, ref } from "vue";

export default {
    props: ["assign_to", "size", "staffMember"],
    setup(props) {
        const staff = ref({});
        onMounted(() => {
            staff.value = props.staffMember.find((item) => {
                return item.xid == props.assign_to;
            });
        });
        return {
            staff,
        };
    },
};
</script>
