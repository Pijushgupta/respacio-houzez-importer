<script setup>
import {ref,watch} from 'vue';
import {useToast} from "vue-toastification";
import Drawer from "./drawer/drawer.vue";
const toast = useToast();


const formEntries = ref(null);
const forms = ref(false);
const childItems = ref(0);
const isOpen = ref(false);
const formType = ref(false);
const formList = ref(false);
const selectedForm = ref(false);
const formTitle = ref(false);
const formTypeName = ref(false);
const toggleDrawer = ref(false);


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
        if(res !== false){
          forms.value = res;
          countChilds();
          formTypeAutoSelect();
          //console.log(res);
        }

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
        //console.log(res);
      })
      .catch(err => console.log(err));
}
getFormEntries();

/**
 * this to count number of actual forms
 */
const countChilds = () =>{

  let totalDataItems = 0;
  for(const item of forms.value){
    totalDataItems += item.data.length;
  }
  childItems.value = totalDataItems;
  //console.log(totalDataItems);
  return false;
}

/**
 * if form type having child forms then the type will be selected
 */
const formTypeAutoSelect = () =>{
  let totalDataItems = 0;
  for(const item of forms.value){
    if(item.data.length !== 0){
      formType.value = item.type;
      break;
    }
  }
  return false;
}
watch(forms, (newForms, oldForms) => {
  if (newForms!== oldForms) {
    updateFormList();
  }
});


const updateFormList = () => {
  formList.value = [];
  formTypeName.value = '';
  selectedForm.value = null;
  for (const item of forms.value) {
    if (item.type === formType.value) {
      formList.value = item.data;
      formTypeName.value = item.name;
      if (item.data.length > 0) {
        selectedForm.value = item.data[0].id;
      }
      break;
    }
  }
};


watch(formType,(newFormType, oldFormType)=>{
    if(newFormType === oldFormType) return false
    updateFormList();
})

const updateFormTitle = () => {
  for(const item of formList.value){
    if(item.id === selectedForm.value ){
      formTitle.value = item.title;
    }
  }
}

//saving entry
const save = () =>{
  updateFormTitle();

  if(!formType.value && formType.value.trim() === '') return false;
  if(!selectedForm.value && selectedForm.value.trim() === '') return false;
  if(!formTitle.value && formTitle.value.trim() === '') return false
  const data = new FormData();
  data.append('respacio_houzez_nonce',respacio_houzez_nonce);
  data.append('action','ajaxSaveEntry');
  data.append('form_type',formType.value);
  data.append('form_id',selectedForm.value);
  data.append('form_title',formTitle.value);
  data.append('form_type_name',formTypeName.value);
  fetch(respacio_houzez_ajax_path,{
    method:'POST',
    body:data
  })
  .then(res => res.json())
  .then(res => {
    console.log(res)
    if(res === true){
      //closing the modal
      isOpen.value = !isOpen.value
      //showing toast of entry addition
      toast("Form entry added");
      //getting all forms again
      getForms();
      //getting all entries again.
      getFormEntries();
      //formTypeAutoSelect();
     // updateFormList();
    }
  })
  .catch(err => console.log(err));
}

//returning the image path for the logo
const imagePath = (imageName = false) =>{
  if(imageName === false) return false;
  return siteUrl + "/wp-content/plugins/houzez-respacio-import/includes/dist/" + imageName + ".png";
}

//deletes the entry
const deleteEntry = (id = false) =>{
  let confirmation = confirm("Do you want to disconnect the form from CRM ?");
  if(confirmation && id !== false){
    const data = new FormData();
    data.append('respacio_houzez_nonce',respacio_houzez_nonce);
    data.append('action','ajaxDeleteEntry');

    data.append('id',id);
    fetch(respacio_houzez_ajax_path,{
      method:'POST',
      body:data
    })
        .then(res => res.json())
        .then(res => {
          if(res === true){
            toast("Entry Deleted")
            getForms();
            getFormEntries();

          }
        })
        .catch(err => console.log(err));
  }
}

//it's to toggle an entry to active and to inactive
const toggleActive = (id = false) =>{
  if(id === false) return false;
  const data = new FormData();
  data.append('respacio_houzez_nonce',respacio_houzez_nonce);
  data.append('action','ajaxToggleEntryStatus');

  data.append('id',id);
  fetch(respacio_houzez_ajax_path,{
    method:'POST',
    body:data
  })
      .then(res => res.json())
      .then(res => {
        if(res === true){
          toast("Setting Updated!");
        }
      })
      .catch(err => console.log(err));
}


</script>

<template>
<div class="relative">
<!--  small button-->
  <button @click="isOpen = true" v-if="formEntries !== false" class="absolute right-0 p-2 rounded-xl bg-white shadow -top-12 ">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 stroke-gray-400">
      <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
    </svg>
  </button>
<!--  Big button-->
  <div @click="isOpen = true" v-if="formEntries === false" class="rounded-xl bg-white p-8 shadow">
    <button class="w-full border border-2 border-dashed rounded-xl min-h-[250px] flex justify-center items-center bg-gray-100">
      <svg class="stroke-gray-200 w-16 h-16 stroke-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" >
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
      </svg>
    </button>
  </div>
<!--  form selector modal-->
  <Teleport to="#respacio_houzez_root" >
    <div v-if="isOpen === true" class="w-full absolute top-0 bottom-0 left-0 right-0 flex justify-center items-center backdrop-blur">
      <div class="w-[350px] bg-white rounded-lg  shadow relative">

        <div @click="isOpen = !isOpen" class="cursor-pointer flex justify-end absolute right-1.5 top-1.5 ">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 stroke-gray-400">
            <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
          </svg>
        </div>

        <form class="p-8">
          <template v-if="forms !== false && childItems !== 0">
            <!-- form type selection -->
            <div class="mb-2 flex flex-col">
              <label class="font-semibold mb-1">{{ $t('SelectFormType') }}</label>
              <select class="input-padding input-border" v-model="formType">
                <option  v-for="(form,index) in forms" :key="index" :value="form.type" >{{form.name}}</option>
              </select>
            </div>
            <!-- form selection -->
            <div class="mb-4 flex flex-col">
              <label class="font-semibold mb-1">{{ $t('SelectForm') }}</label>
              <select class="input-padding input-border" v-model="selectedForm">
                <option v-for="(e,i) in formList" :key="i" :value="e.id">{{e.title}}</option>

              </select>
            </div>
            <!-- Submit Button -->
            <div class="flex flex-col">
              <button class="bg-blue-800 text-white py-3 rounded-lg w-full flex flex-row justify-center align-middle" type="submit" @click.prevent="save">{{ $t('Select') }}</button>
            </div>
          </template>
          <template v-if="forms === false || childItems === 0">
            <span>{{$t('Noformwasfound')}}.</span>
          </template>
        </form>
      </div>
    </div>

  </Teleport>

  <!--  show entries-->
  <ul>
    <li v-for="(entry,index) in formEntries" :key="index" class="group last:rounded-b-xl first:rounded-t-xl bg-white mb-0 shadow p-4 border-b last:border-b-0">
      <div class="flex flex-row items-center justify-between">
        <!-- form lodo -->
        <div class="flex flex-row">
          <div class="w-12  rounded-full">
            <img :src="imagePath(entry.form_type)" class="w-8 grayscale group-hover:grayscale-0 "/>
          </div>
        </div>
        <!--plugin name and form title-->
        <div class="flex flex-col  w-full">
          <div class="text-sm">{{entry.form_type_name}}</div>
          <div class="text-xs">{{entry.form_title}}</div>
        </div>
        <!-- delete entry   -->
        <div class="flex flex-row mr-4">
          <button @click="deleteEntry(entry.id)">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 stroke-gray-200 hover:stroke-gray-400 ">
              <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
            </svg>
          </button>
        </div>
        <!--toggle switch-->
        <div class="flex flex-row ">
          <div class="inline-flex items-center cursor-pointer relative">
            <label :for="entry.id" class="cursor-pointer">
            <input v-on:change="toggleActive(entry.id)" v-bind:checked="entry.form_active" :id="entry.id" type="checkbox" name="scan-theme" class="sr-only peer">
            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
            </label>
          </div>
        </div>
        <!--drawer open/close button-->
        <div class="flex flex-row ml-4 cursor-pointer" @click="()=>{
          if(toggleDrawer == entry.id){
            toggleDrawer = false;
          }else{
            toggleDrawer = entry.id
          }
        }">
          <svg id="setting" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 stroke-gray-200 hover:stroke-gray-400  " v-bind:class="toggleDrawer == entry.id ? 'rotate-90 ':''">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
          </svg>
        </div>
      </div>
      <!--Drawer-->
      <Drawer
          v-if="toggleDrawer == entry.id"
          v-bind:entry="entry"/>
    </li>
  </ul>


</div>
</template>
