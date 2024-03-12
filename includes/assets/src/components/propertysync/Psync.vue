<script setup>
import { useToast } from 'vue-toastification';
const propertySync = () =>{

  const data = new FormData();
  data.append('respacio_houzez_nonce',respacio_houzez_nonce);
  data.append('action','ajaxSyncProperties');
  const toast = useToast();
  toast("Syncing Stareted");
  fetch(respacio_houzez_ajax_path,{
    method:'POST',
    body:data
  })
      .then(res => res.json())
      .then(res => {
        if(res == true || res == "true"){
        toast("First Phase of syncing completed.");
        console.log(res)
        }
      })
      .catch(err => console.log(err));

}
</script>
<template>
    <div class=" bg-white border-b ">
      <label for="setting" class=" flex flex-row justify-between items-center p-4">
      <div class="flex flex-row items-center">
          <div> 
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"> <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12c0-1.232-.046-2.453-.138-3.662a4.006 4.006 0 0 0-3.7-3.7 48.678 48.678 0 0 0-7.324 0 4.006 4.006 0 0 0-3.7 3.7c-.017.22-.032.441-.046.662M19.5 12l3-3m-3 3-3-3m-12 3c0 1.232.046 2.453.138 3.662a4.006 4.006 0 0 0 3.7 3.7 48.656 48.656 0 0 0 7.324 0 4.006 4.006 0 0 0 3.7-3.7c.017-.22.032-.441.046-.662M4.5 12l3 3m-3-3-3 3" /> </svg>

          </div>
        <div class="flex flex-col ml-3">
          <div class="text-sm">Sync Properties</div>
          <div class="text-xs">Manually sync properties with crm.(not required) </div>
        </div>
      </div>
      
      
      
      <div>
        <button class="bg-blue-800 px-4 py-1 rounded-full text-white" @click="propertySync">Sync</button>

      </div>
    </label>
    </div>
</template>