<script setup>
import {ref} from 'vue';

const logs = ref(0);

const numberOfLogs = () =>{
    const data = new FormData();
    data.append('respacio_houzez_nonce',respacio_houzez_nonce);
    data.append('action','ajaxGetTotalNumberOfPropertyLog');

    fetch(respacio_houzez_ajax_path,{
        method:'POST',
        body:data
    })
    .then(res => res.json())
    .then(res => {
        if(res != '0' || res != 0){
           
            logs.value = res; 
            
        }
    })
    .catch(err => console.log(err));


}
numberOfLogs();
</script>
<template>
    <div class="border-b bg-white rounded-t-xl">
      <label for="openstatus" class=" flex flex-row justify-between items-center p-4">
    <div class="flex flex-row items-center">
        <div><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"> <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205l3 1m1.5.5l-1.5-.5M6.75 7.364V3h-3v18m3-13.636l10.5-3.819" /> </svg></div>
      <div class="flex flex-col ml-3">
        <div class="text-sm">{{$t('status')}} <span class="bg-blue-800 rounded-full text-white px-1 text-xs shadow">{{ logs }}</span></div>
        <div class="text-xs">{{$t('ListofSyncedproperties')}}</div>
      </div>
    </div>
      
      <div>
        <svg id="openstatus" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 stroke-gray-500">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
        </svg>

      </div>
      </label>
      
    </div>
</template>