<template>
	<a-card :bordered="false">
		<CardWidget>
			<a-space>
				<CardWidgetIcon :bgColor="bgColor" class="wedgit_bg">
					<slot name="image"></slot>
				</CardWidgetIcon>
				<figcaption>
					<slot name="description"></slot>
				</figcaption>
			</a-space>
		</CardWidget>
	</a-card>
</template>

<script>
import { CardWidget, CardWidgetIcon } from "./style";

export default {
	props: {
		bgColor: {
			default: "#5F63F2",
		},
	},
	components: {
		CardWidget,
		CardWidgetIcon,
	},
};
</script>

<style>
.wedgit_bg{
	background: var(--primary-color);
}
</style>
