<script setup>
import {ref} from 'vue';

/** This pinia state contain activation status for the whole app*/
import {useGlobalstateStore} from "../../stores/globalstate";
const license =  useGlobalstateStore();
/** Pinia state code ends **/
/** Importing vue toast notification lib */
import {useToast} from "vue-toastification";
/* ends */

/** This to hold key when user add that to the input area **/
const key = ref('');

/** This to verify and activate Submitted API key*/
const verifyKey = () =>{
  const data = new FormData();
  data.append('respacio_houzez_nonce',respacio_houzez_nonce);
  data.append('action','checkApiKey');
  data.append('key',key.value.trim());
  fetch(respacio_houzez_ajax_path,{
    method:'POST',
    body:data
  })
      .then(res => res.json())
      .then(res => {
        if(res == true || res == 'true'){
          license.getActivatedStatus();
          const notification = useToast()
          notification('API key has been verified and stored!',{timeout: 2000});
          getApiKeyMasked();
        }
        if(res == false || res == 'false'){
          license.getActivatedStatus();
          const notification = useToast()
          notification.error('API key is not valid',{timeout: 2000});

        }

      })
      .catch(err => console.log(err))
}
/*ends*/

const removeKey = () =>{
  const data = new FormData();
  data.append('respacio_houzez_nonce',respacio_houzez_nonce);
  data.append('action','removeKey');

  fetch(respacio_houzez_ajax_path,{
    method:'POST',
    body:data
  })
      .then(res => res.json())
      .then(res => {
        if(res == true || res == 'true'){
          key.value = '';
          license.getActivatedStatus();
          const notification = useToast()
          notification('API key has been removed!',{timeout: 2000});
        }
      })
      .catch(err => console.log(err))
}

/** get masked key */
const getApiKeyMasked = () => {
  const data = new FormData();
  data.append('respacio_houzez_nonce',respacio_houzez_nonce);
  data.append('action','getApiKeyMasked');
  fetch(respacio_houzez_ajax_path,{
    method:'POST',
    body:data
  })
      .then(res => res.json())
      .then(res => {
        if(res == false || res == 'false'){
          key.value = '';
          return;
        }
        key.value = res;
      })
      .catch(err => console.log(err));

}
if(license.isActivated == true){
  getApiKeyMasked();
}

</script>
<template>
    <div class="rounded-xl bg-white mb-1 shadow p-4">
      <div class="flex flex-row items-center justify-between">
        <div class="flex flex-col  w-1/2">
          <div class="text-sm">Change API key </div>
          <div class="text-xs">Enter your website API key from Respacio CRM</div>
        </div>
        <div class="flex flex-row w-1/2">
          <input class="w-full" type="text" v-model="key" />
          <button v-if="license.isActivated == false" class="bg-blue-700 text-white px-4 py1 rounded-full ml-2" @click="verifyKey">Activate</button>
          <button v-if="license.isActivated == true"  class="bg-blue-700 text-white px-4 py1 rounded-full ml-2" @click="removeKey" >Remove</button>
        </div>
      </div>
    </div>
</template>