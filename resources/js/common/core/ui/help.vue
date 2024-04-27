<template>
	<div>
		<a-button type="primary" @click="showModal">
			<QuestionOutlined />
			{{ pageTitle }}
		</a-button>
		<a-modal v-model:visible="visible" :title="pageTitle">
			<a-row :gutter="16" class="mb-10">
				<a-col :xs="24" :sm="24" :md="24" :lg="24">
				
                    <iframe width="100%" height="376" :src="FileUrl" title="BRAND" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
				</a-col>
			</a-row>

		</a-modal>
	</div>
</template>
<script>
import { defineComponent, ref } from "vue";
import { CloudDownloadOutlined, UploadOutlined,QuestionOutlined } from "@ant-design/icons-vue";
import { useI18n } from "vue-i18n";
import apiAdmin from "../../composable/apiAdmin";

export default defineComponent({
	props: ["pageTitle", "FileUrl", "importUrl"],
	emits: ["onUploadSuccess"],
	components: {
		CloudDownloadOutlined,
		UploadOutlined,
        QuestionOutlined,
	},
	setup(props, { emit }) {
		const { addEditFileRequestAdmin, loading, rules } = apiAdmin();
		const { t } = useI18n();
		const fileList = ref([]);
		const visible = ref(false);
    

		const beforeUpload = (file) => {
			fileList.value = [...fileList.value, file];
			return false;
		};

		const importItems = () => {
			const formData = {};
			if (fileList && fileList.value && fileList.value[0] != undefined) {
				formData.file = fileList.value[0];
			}

			loading.value = true;

			addEditFileRequestAdmin({
				url: props.importUrl,
				data: formData,
				successMessage: t("messages.imported_successfully"),
				success: (res) => {
					handleCancel();

					emit("onUploadSuccess");
				},
			});
		};

		const showModal = () => {
			visible.value = true;
		};

		const handleCancel = (e) => {
			fileList.value = [];
			visible.value = false;
		};

		return {
			fileList,
			rules,
			loading,

			visible,
			showModal,
			handleCancel,
			importItems,

			beforeUpload,
		};
	},
});
</script>
