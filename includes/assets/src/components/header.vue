<script setup>
import { useBreadcrumbStore } from '../stores/breadcrumb';
const windowStore = useBreadcrumbStore()
</script>

<template>
  <div class="w-full bg-blue-800">
    <div class="  max-w-5xl mx-auto md:py-8 py-5 flex flex-row justify-between items-center">
      <div>
        <h1 class="text-white font-thin  text-xl mb-3">Houzez Import</h1>
        <span class="text-white font-medium bg-blue-900 rounded px-2 py-1">WordPress plugin by Respacio Team.</span>
      </div>

      <a href="#" target="_blank" class="bg-blue-600 rounded text-white px-4 py-1 text-sm font-semibold cursor-pointer hover:text-white hover:bg-blue-500 hover:shadow-sm">
        <svg viewBox="0 0 24 24" aria-hidden="true" class="h-6 w-6 fill-white inline"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.463 2 11.97c0 4.404 2.865 8.14 6.839 9.458.5.092.682-.216.682-.48 0-.236-.008-.864-.013-1.695-2.782.602-3.369-1.337-3.369-1.337-.454-1.151-1.11-1.458-1.11-1.458-.908-.618.069-.606.069-.606 1.003.07 1.531 1.027 1.531 1.027.892 1.524 2.341 1.084 2.91.828.092-.643.35-1.083.636-1.332-2.22-.251-4.555-1.107-4.555-4.927 0-1.088.39-1.979 1.029-2.675-.103-.252-.446-1.266.098-2.638 0 0 .84-.268 2.75 1.022A9.607 9.607 0 0 1 12 6.82c.85.004 1.705.114 2.504.336 1.909-1.29 2.747-1.022 2.747-1.022.546 1.372.202 2.386.1 2.638.64.696 1.028 1.587 1.028 2.675 0 3.83-2.339 4.673-4.566 4.92.359.307.678.915.678 1.846 0 1.332-.012 2.407-.012 2.734 0 .267.18.577.688.48 3.97-1.32 6.833-5.054 6.833-9.458C22 6.463 17.522 2 12 2Z"></path></svg>
        contribute
      </a>
    </div>
  </div>
  <div class="max-w-5xl mx-auto">
    <div class="py-1   mt-2  rounded  text-lg flex flex-row items-center min-h-[50px]">
    <span class="cursor-pointer" v-on:click="windowStore.activeWindow = 0">Houzez Import </span>
    <template v-if="windowStore.activeWindow != 0">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 m-2 inline"> <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /> </svg>
    </template>
    <template v-if="windowStore.activeWindow == 1"><span class="cursor-pointer font-medium">Status</span></template>
    <template v-if="windowStore.activeWindow == 2"><span class="cursor-pointer font-medium">Cron Jobs</span></template>
    <template v-if="windowStore.activeWindow == 3"><span class="cursor-pointer font-medium">Export</span></template>
    <template v-if="windowStore.activeWindow == 4"><span class="cursor-pointer font-medium">Settings</span></template>
    <template v-if="windowStore.activeWindow == 5"><span class="cursor-pointer font-medium">Knowledge Base</span></template>
    
    <template v-if="windowStore.activeWindow == 7"><span class="cursor-pointer font-medium">Connect</span></template>
    </div>
  </div>
</template>
