<script setup>
import Status from './status/status.vue'
import Import from './import/import.vue'
import Wpml from './wpml/wmpl.vue'
import Setting from './setting/setting.vue'
import Guide from './guide/guide.vue'
import Cron from './cron/cron.vue'
import {ref} from 'vue';
const activeWindow = ref(0);
</script>
<template>
  <div class="max-w-5xl mx-auto pt-2">
    <template v-if="activeWindow == 0">
      <Status v-on:click="activeWindow =  1"/>
      <Cron v-on:click="activeWindow = 2"/>
      <Import v-on:click="activeWindow = 3"/>
      <Wpml/>
      <Setting v-on:click="activeWindow = 4"/>
      <Guide v-on:click="activeWindow = 5"/>
    </template>
  </div>
</template>
