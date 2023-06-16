<script setup>
import Status from './status/status.vue'
import Statuspage from './status/statuspage.vue'
import Export from './export/export.vue'
import Exportpage from './export/exportpage.vue'
import Wpml from './wpml/wmpl.vue'
import Setting from './setting/setting.vue'
import Settingpage from './setting/settingpage.vue'
import Guide from './guide/guide.vue'
import Cron from './cron/cron.vue'
import {ref} from 'vue';

import {useBreadcrumbStore} from '../stores/breadcrumb'

const windowStore = useBreadcrumbStore();


</script>
<template>
  <div class="max-w-5xl mx-auto pt-2">
    <template v-if="windowStore.activeWindow == 0 ">
      <Status v-on:click="windowStore.changeActiveWindow(1)"/>
      
      <Cron v-on:click="windowStore.changeActiveWindow(2)"/>
      <Export v-on:click="windowStore.changeActiveWindow(3)"/>
      <Wpml/>
      <Setting v-on:click="windowStore.changeActiveWindow(4)"/>
      <Guide v-on:click="windowStore.changeActiveWindow(5)"/>
    </template>
    <Statuspage v-if="windowStore.activeWindow == 1"/>
    <Exportpage v-if="windowStore.activeWindow == 3"/>
    <Settingpage v-if="windowStore.activeWindow == 4"/>
  </div>
</template>
