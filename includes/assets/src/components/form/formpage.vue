<script setup>
import {ref} from 'vue';

const formEntries = ref(null);
const isOpen = ref(false);
const getForms = () =>{
  const data = new FormData();
  data.append('respacio_houzez_nonce',respacio_houzez_nonce);
  data.append('action','ajaxGetForms');
  fetch(respacio_houzez_ajax_path,{
    method:'POST',
    body:data
  })
      .then(res => res.json())
      .then(res => {
        console.log(res);
      })
      .catch(err => console.log(err));
}
getForms();


const getFormEntries = () =>{
  const data = new FormData();
  data.append('respacio_houzez_nonce',respacio_houzez_nonce);
  data.append('action','ajaxGetFormEntries');
  fetch(respacio_houzez_ajax_path,{
    method:'POST',
    body:data
  })
      .then(res => res.json())
      .then(res => {
        formEntries.value = res;
      })
      .catch(err => console.log(err));
}
getFormEntries();

const showModal = () =>{

}

</script>

<template>
<div class="relative">
<!--  small button-->
  <button @click="isOpen = true" v-if="formEntries != false" class="absolute right-0 top-0 p-2 rounded-xl bg-white shadow -top-12 ">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 stroke-gray-400">
      <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
    </svg>
  </button>
<!--  Big button-->
  <div @click="isOpen = true" v-if="formEntries == false" class="rounded-xl bg-white p-8 shadow">
    <button class="w-full border border-2 border-dashed rounded-xl min-h-[250px] flex justify-center items-center bg-gray-100">
      <svg class="stroke-gray-200 w-16 h-16 stroke-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" >
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
      </svg>

    </button>
  </div>
<!--  form selector modal-->
  <Teleport to="#respacio_houzez_root" >
    <div v-if="isOpen == true" class="w-full absolute top-0 bottom-0 left-0 right-0 flex justify-center items-center backdrop-blur">
      <div class="w-[350px] bg-white rounded-lg  shadow relative">

        <div @click="isOpen = !isOpen" class="cursor-pointer flex justify-end absolute right-1.5 top-1.5 ">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 stroke-gray-400">
            <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
          </svg>
        </div>

        <form class="p-8">

          <div class="mb-2 flex flex-col">
            <label class="font-semibold mb-1">Select Form Type</label>
            <select class="input-padding input-border">
              <option>Gravity</option>
              <option>Contact 7</option>
              <option>Forminator</option>
              <option>WPforms</option>
            </select>
          </div>
          <div class="mb-4 flex flex-col">
            <label class="font-semibold mb-1">Select Form</label>
            <select class="input-padding input-border">
              <option>Form 1</option>
              <option>Form 2</option>
              <option>Contact form - form 5</option>
              <option>Form 7</option>
            </select>
          </div>
          <div class="flex flex-col">
            <button class="bg-blue-800 text-white py-3 rounded-lg w-full flex flex-row justify-center align-middle" type="submit" @click.prevent="console.log('Hello')">Select</button>
          </div>
        </form>
      </div>
    </div>

  </Teleport>
</div>
</template>
