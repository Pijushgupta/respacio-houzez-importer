<script setup>
import Status from './status/status.vue'
import Statuspage from './status/statuspage.vue'
import Export from './export/export.vue'
import Exportpage from './export/exportpage.vue'
import Image from './image/image.vue'
import Imagepage from './image/imagepage.vue'
import Wpml from './wpml/wmpl.vue'
import Setting from './setting/setting.vue'
import Settingpage from './setting/settingpage.vue'
import Guide from './guide/guide.vue'
import Cron from './cron/cron.vue'
import {ref} from 'vue';
import Psync from './propertysync/psync.vue'
import Userregistration from './userregistration/userregistration.vue'
import Userregistrationpage from './userregistration/userregistrationpage.vue'
import Form from './form/form.vue'
import Formpage from "./form/formpage.vue";


import {useBreadcrumbStore} from '../stores/breadcrumb'
import {useGlobalstateStore} from "../stores/globalstate";

const windowStore = useBreadcrumbStore();
/**
 * this to keep activation status throughout the app
 * @type {Store<"useGlobalstateStore", {isActivated: boolean}, {}, {getActivatedStatus(): void}>}
 */
const license = useGlobalstateStore();
license.getActivatedStatus();
/**
 * ends
 */
</script>
<template>
	<div class="  max-w-5xl mx-auto pt-2 ">
	<!--active window switcher-->
	<template v-if="windowStore.activeWindow == 0 ">
		<Status v-on:click="windowStore.changeActiveWindow(1)"/>
		<Export v-on:click="windowStore.changeActiveWindow(3)"/>
		<Psync/>
		
		<Wpml/>

		<Setting v-on:click="windowStore.changeActiveWindow(4)"/>
		<Userregistration v-on:click="windowStore.changeActiveWindow(7)"/>
    <Form v-on:click="windowStore.changeActiveWindow(8)"/>
		<Guide v-on:click="windowStore.changeActiveWindow(5)"/>
	</template>
	<!--ends-->

	<!--components to load based on active window value-->
		<Statuspage v-if="windowStore.activeWindow == 1"/>
		<Exportpage v-if="windowStore.activeWindow == 3"/>
		<Settingpage v-if="windowStore.activeWindow == 4"/>
		<Userregistrationpage v-if="windowStore.activeWindow == 7" />
    <Formpage v-if="windowStore.activeWindow == 8" />
	<!--end-->
	</div>
</template>
