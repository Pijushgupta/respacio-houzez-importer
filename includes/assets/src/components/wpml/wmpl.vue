<script setup>
</script>
<template>
    <div class="border-b bg-white ">
      <label for="wpml" class=" flex flex-row justify-between items-center p-4 relative">
    <div class="flex flex-row items-center">
        <div><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"> <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 21l5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 016-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 01-3.827-5.802" /> </svg>
</div>
      <div class="flex flex-col ml-3">
        <div class="text-sm">Wpml</div>
        <div class="text-xs">{{ $t('EnableWordPressmultilingualpluginsupport') }}</div>
      </div>
    </div>
      
    <div class="inline-flex items-center cursor-pointer relative"><input id="wpml" type="checkbox" name="scan-theme" class="sr-only peer"><div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div></div>
      </label>
      
    </div>
</template>