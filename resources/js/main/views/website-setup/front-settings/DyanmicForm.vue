<template>
    <div>
        <div v-for="(dataArray, index) in dynamicValidateForm" :key="index">
            <a-row :gutter="[24, 24]">
                <a-col :xs="10" :sm="10" :md="6" :lg="6">
                    <a-form-item :name="['dataArray', index, 'title']">
                        <a-input
                            v-model:value="dataArray.title"
                            :placeholder="$t('common.title')"
                            :rules="{
                                required: true,
                                message: $t('front_setting.required_text', [
                                    $t('common.title'),
                                ]),
                            }"
                        />
                    </a-form-item>
                </a-col>
                <a-col :xs="10" :sm="10" :md="6" :lg="6">
                    <a-form-item :name="['dataArray', index, 'value']">
                        <a-input
                            v-model:value="dataArray.value"
                            :placeholder="$t('common.value')"
                            :rules="{
                                required: true,
                                message: $t('front_setting.required_text', [
                                    $t('common.value'),
                                ]),
                            }"
                        />
                    </a-form-item>
                </a-col>
                <a-col :xs="4" :sm="4" :md="2" :lg="2">
                    <a-form-item :name="['dataArray', index, 'isMenu']">
                        <a-checkbox-group
                            v-model:value="dataArray.isMenu"
                            style="width: 100%"
                        >
                            <a-checkbox value="1"> Yes </a-checkbox>
                        </a-checkbox-group>
                        <!-- <a-input
							v-model:value="dataArray.isMenu"
							:placeholder="$t('common.value')"
							:type="checkbox"
						/> -->
                    </a-form-item>
                </a-col>
                <a-col :xs="4" :sm="4" :md="2" :lg="2">
                    <MinusCircleOutlined @click="removeItem(dataArray)" />
                </a-col>
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item :name="['dataArray', index, 'content']">
                        <!-- <a-input
							v-model:value="dataArray.content"
							:placeholder="$t('common.value')"
							:rules="{
								required: true,
								message: $t('front_setting.required_text', [
									$t('common.value'),
								]),
							}"
						/> -->
                        <QuillEditor
                            contentType="html"
                            v-model:content="dataArray.content"
                            toolbar="full"
                            @update:content="updateContent"
                        />
                    </a-form-item>
                </a-col>
            </a-row>
        </div>

        <a-row :gutter="16">
            <a-col :xs="24" :sm="24" :md="24" :lg="24">
                <a-form-item>
                    <a-button type="dashed" block @click="addItem">
                        <PlusOutlined />
                        {{ addText }}
                    </a-button>
                </a-form-item>
            </a-col>
        </a-row>
    </div>
</template>

<script>
import { defineComponent, ref, onMounted } from "vue";
import { PlusOutlined, MinusCircleOutlined } from "@ant-design/icons-vue";
import "quill/dist/quill.core.css";
import "quill/dist/quill.snow.css";
import "quill/dist/quill.bubble.css";
import { QuillEditor } from "@vueup/vue-quill";
import "@vueup/vue-quill/dist/vue-quill.snow.css";

export default defineComponent({
    props: ["data", "addText"],
    emits: ["onEntry"],
    components: {
        PlusOutlined,
        MinusCircleOutlined,
        QuillEditor,
    },
    setup(props, { emit }) {
        const dynamicValidateForm = ref([]);

        onMounted(() => {
            dynamicValidateForm.value = props.data;
        });

        const removeItem = (item) => {
            let index = dynamicValidateForm.value.indexOf(item);

            if (index !== -1) {
                dynamicValidateForm.value.splice(index, 1);
            }
        };

        const addItem = () => {
            dynamicValidateForm.value.push({
                title: "",
                value: "",
                content: "",
                isMenu: "",
            });
        };

        const updateContent = (value) => {
        };

        const onSubmit = () => {
            emit("onEntry", dynamicValidateForm.value);
        };

        return {
            dynamicValidateForm,
            onSubmit,
            updateContent,
            removeItem,
            addItem,
        };
    },
});
</script>
<style></style>
