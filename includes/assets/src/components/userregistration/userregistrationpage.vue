<script setup>
import {ref} from 'vue';
import Register from './register/register.vue';
import Login from './login/login.vue';


const currentTabNumber = ref(0)
const apiKey = ref(null);

/**
 * checking if api key present or not
 */
const isApiKeyPresent = () =>{
  const data = new FormData();
  data.append('respacio_houzez_nonce',respacio_houzez_nonce);
  data.append('action','ajaxIsApiKeyPresent')

  fetch(respacio_houzez_ajax_path,{
    method:'POST',
    body:data
  })
  .then(res => res.json())
  .then(res => {
    //console.log(res)
    apiKey.value = res;
  })
  .catch(err => console.log(err));
}
isApiKeyPresent();

const removeAPIKey = () => {
  const data = new FormData();
  data.append('respacio_houzez_nonce',respacio_houzez_nonce);
  data.append('action','removeKey');

  fetch(respacio_houzez_ajax_path,{
    method:'POST',
    body:data
  })
      .then(res => res.json())
      .then(res => {
        //console.log(res)
        if(res == true){
          window.location.reload();
        }
      })
      .catch(err => console.log(err));

}
</script>
<template>
  <div  v-if="apiKey === false" class="rounded-xl bg-white mb-1 shadow p-4">
    <div  class="flex flex-col items-center justify-center min-h-[400px] max-w-[400px] mx-auto">
        <ul class="flex flex-row justify-center align-middle p-2 bg-gray-100 rounded-lg  w-full mb-4">
          <li @click="currentTabNumber = 0" v-bind:class="currentTabNumber == 0 ? 'bg-white shadow':''" class="px-4 py-2 mb-0 rounded cursor-pointer w-full text-center">{{ $t('Register') }}</li>
          <li @click="currentTabNumber = 1" v-bind:class="currentTabNumber == 1 ? 'bg-white shadow':''" class="px-4 py-2  mb-0 rounded cursor-pointer w-full text-center">{{ $t('Login') }}</li>
        </ul>
        <div class="flex flex-row items-center justify-center w-full">
          <div v-if="currentTabNumber == 0" class="w-full   rounded-lg p-8 shadow-lg">

            <Register/>
          </div>
          <div v-if="currentTabNumber == 1" class="w-full rounded-lg p-8 shadow-lg">

            <Login/>

          </div>
        </div>
    </div>
  </div>
  <div v-if="apiKey === true" class="bg-white rounded-lg">
    <label for="openstatus" class=" flex flex-row justify-between items-center p-4">
      <div class="flex flex-row items-center">
        <div>
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
               stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M13.5 16.875h3.375m0 0h3.375m-3.375 0V13.5m0 3.375v3.375M6 10.5h2.25a2.25 2.25 0 0 0 2.25-2.25V6a2.25 2.25 0 0 0-2.25-2.25H6A2.25 2.25 0 0 0 3.75 6v2.25A2.25 2.25 0 0 0 6 10.5Zm0 9.75h2.25A2.25 2.25 0 0 0 10.5 18v-2.25a2.25 2.25 0 0 0-2.25-2.25H6a2.25 2.25 0 0 0-2.25 2.25V18A2.25 2.25 0 0 0 6 20.25Zm9.75-9.75H18a2.25 2.25 0 0 0 2.25-2.25V6A2.25 2.25 0 0 0 18 3.75h-2.25A2.25 2.25 0 0 0 13.5 6v2.25a2.25 2.25 0 0 0 2.25 2.25Z"/>
          </svg>
        </div>
        <div class="flex flex-col ml-3">
          <div class="text-sm">{{ $t('Disconenct') }}</div>
          <div class="text-xs">{{ $t('Site is already connected to crm') }}</div>
        </div>
      </div>

      <div>
        <button class="bg-blue-800 px-4 py-1 rounded-full text-white" @click="removeAPIKey">Disconnect</button>

      </div>
    </label>

  </div>
</template>