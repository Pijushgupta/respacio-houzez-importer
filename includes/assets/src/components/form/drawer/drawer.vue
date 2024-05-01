<script setup>
import {ref,onMounted} from 'vue';
import draggable from 'vuedraggable'
import { useToast } from 'vue-toastification';

/**
 * props are to receive arguments from another components specifically from parent components
 * @type {Prettify<Readonly<ExtractPropTypes<{postType: {type: StringConstructor | *, required: boolean}, postId: {type: NumberConstructor, required: boolean}}>>>}
 */
const props = defineProps({
  entry:{
    type:Object,
    required:true
  }
});


//fresh crm form fields - for search right
const freshCrmFormField = ref(false);
//getting crm form field - for search right
const crmFromFields = () =>{
  
  const data = new FormData();
  data.append('action','ajaxGetCrmFormFields');
  data.append('respacio_houzez_nonce',respacio_houzez_nonce);

  fetch(respacio_houzez_ajax_path,{
    method:'POST',
    body:data
  })
      .then(res => res.json())
      .then(res => {
        if(res !== false){
          freshCrmFormField.value = res;
          console.log(freshCrmFormField.value);
        }
      })
      .catch(err => console.log(err))
}

// fresh form fields - for search left
const freshFormField = ref(false);
//getting form fields - for search right
const siteFromFields = () =>{
  const data = new FormData();
  data.append('action','ajaxGetFormFields');
  data.append('respacio_houzez_nonce',respacio_houzez_nonce);
  //this is entry id not actual form id
  data.append('postId',props.entry.id);
  fetch(respacio_houzez_ajax_path,{
    method:'POST',
    body:data
  })
      .then(res => res.json())
      .then(res => {
        if(res !== false){
          freshFormField.value = res;
          console.log(freshFormField.value);
        }
      })
      .catch(err => console.log(err))
}

const mappedFormFields = ref([]);
const mappedCrmFields = ref([]);


const getMappedFormFields = () =>{
    const data = new FormData();
    data.append('action','ajaxGetEntryFormFieldMap');
    data.append('respacio_houzez_nonce',respacio_houzez_nonce);
    data.append('postId',props.entry.id);

    fetch(respacio_houzez_ajax_path,{
      method:'POST',
      body:data
    })
    .then(res => res.json())
    .then(res => {
      if(res !== false){
        //mappedFormFields.value = res;
        
        if(res.crm_fields.length > 0){
          mappedCrmFields.value = res.crm_fields;
        }

        if(res.form_fields.length > 0){
          mappedFormFields.value = res.form_fields;
        }

      }
      
    })
    .catch(err => console.log(err));
}

const removeFormFieldLeft = (index = false) =>{
  if(index === false) return;
  mappedFormFields.value.splice(index,1);
}
const removeFormFieldRight = (index = false) =>{
  if(index === false) return;
  mappedCrmFields.value.splice(index,1);
}
/**
 * saving the map
 */
const saveFormFieldMap = () =>{
  if( !Array.isArray(mappedFormFields.value) && mappedFormFields.value.length <= 0) return false
  if( !Array.isArray(mappedCrmFields.value) && mappedCrmFields.value.length <= 0) return false
  
  const toast = useToast();
  
  const data = new FormData();
  data.append('action','ajaxSetFormMapFields');
  data.append('respacio_houzez_nonce',respacio_houzez_nonce);
  data.append('postId',props.entry.id);

  data.append('form_fields', JSON.stringify(mappedFormFields.value));
  data.append('crm_fields', JSON.stringify(mappedCrmFields.value));
 
  
  fetch(respacio_houzez_ajax_path,{
      method:'POST',
      body:data
    })
  .then(res => res.json())
  .then(res =>{
    if(res !== false){
      toast('Setting updated');
      //console.log(res);
    }
  })
  .catch(err => console.log(err));
}

const truncateString = (str) => {
    if (str.length <= 45) {
        return str;
    } else {
        return str.slice(0, 42) + '...';
    }
}

//run anything on startup/mounting of this componenet
onMounted(()=>{
  crmFromFields();
  siteFromFields();
  getMappedFormFields();
  
});

</script>

<template>
<div class="flex flex-col">
<!--  search area -->
  <div class="flex flex-row w-full py-3">
    <!--form field search-->
    <div class="w-1/2 pr-1.5 ">
      <span class="mb-1 block">{{$t('FormFields')}}</span>
      <div class="min-w-full h-[100px] border !rounded p-1 !border-gray-200">
        <draggable 
        v-model="freshFormField"
        :group="{ name: 'forms', pull: 'clone', put: false }"
        drag-class="drag-class"
        tag="div"
        >
          <template #item="{element}">
           
            <button 
            v-if=" element.basetype !== 'submit'"
             class="px-4 py-3 m-1 border rounded capitalize shadow">
              <div class="flex justify-center items-center">
                  <span v-if="props.entry.form_type == 'gravity'">{{ element.label }}</span>
                  <span v-if="props.entry.form_type == 'cf7'">{{ element.name }}</span>
                  <span v-if="props.entry.form_type == 'forminator'">{{ element.slug }}</span>
                  <span v-if="props.entry.form_type == 'wpforms'">{{ element.label }}</span>
                  
              </div>
            </button>
          </template>        
        </draggable>
        
          
      </div>
    
    </div>
    <!--crm field search-->
    <div class="w-1/2 pl-1.5 ">
      <span class="mb-1 block">{{$t('CrmFields')}}</span>
      <div class="min-w-full h-[100px] border !rounded p-1 !border-gray-200 overflow-y-auto ">
        <draggable 
        v-model="freshCrmFormField"
        :group="{ name: 'crm', pull: 'clone', put: false }"
        item-key="parameter_key"
        drag-class="drag-class"
        tag="div"
        >
        <template #item="{element}">
          <button class="px-4 py-3 m-1 border rounded capitalize shadow">
            <div class="flex justify-center items-center">
              <span>{{ element.name }}</span>
              
            </div>
          </button>
        </template>

        </draggable>
          
      </div>
    </div>
  </div>
<!--  mapping area-->
  <div class="flex flex-row w-full  min-h-[200px]">
    <div class="rounded border border-2 border-dashed bg-gray-100 w-1/2">
      <draggable
      v-model="mappedFormFields"
      group="forms"
      item-key="parameter_key"
      tag="div"
      class="w-full flex flex-col"
      
      ghost-class="ghost-class"
      :emptyInsertThreshold="500"
    >
      <template #item="{element,index}">
        <button class="px-2 py-2 m-1 border rounded capitalize shadow bg-white" >
          <div class="flex flex-row">
            <span class="flex justify-center items-center w-8 h-8 rounded shadow ">{{ index+1 }}</span>
            <div class="w-full flex justify-center items-center">
              <span v-if="props.entry.form_type == 'gravity'">
                {{ truncateString(element.label) }}
              </span>
              <span v-if="props.entry.form_type == 'cf7'">
                {{ truncateString(element.name) }}
              </span>
              <span v-if="props.entry.form_type == 'forminator'">
                {{ truncateString(element.slug) }}
              </span>
              <span v-if="props.entry.form_type == 'wpforms'">
                {{ truncateString(element.label) }}
              </span>
            </div>
            <div class="flex justify-center items-center" @click="removeFormFieldLeft(index)">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 stroke-gray-200"> <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /> </svg>
            </div>
          </div>
        </button>
      </template>

      </draggable>
    </div>
    <div class="rounded border border-2 border-dashed bg-gray-100 w-1/2 ml-1.5">
      <draggable
      v-model="mappedCrmFields"
      group="crm"
      item-key="parameter_key"
      tag="div"
      class="w-full flex flex-col"
      ghost-class="ghost-class"
      :emptyInsertThreshold="500"
    >
      <template #item="{element,index}">
        <button class="px-2 py-2 m-1 border rounded capitalize shadow bg-white" >
          <div class="flex flex-row">
            <span class="flex justify-center items-center w-8 h-8 rounded shadow ">{{ index+1 }}</span>
            <div class="w-full flex justify-center items-center">
              <span>
                {{ truncateString(element.name) }}
              </span>
            </div>
            <div class="flex justify-center items-center" @click="removeFormFieldRight(index)">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 stroke-gray-200"> <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /> </svg>
            </div>
          </div>
        </button>
      </template>

    </draggable>
    </div>
    

  </div>
<!-- footer button area -->
  <div class="flex flex-row justify-end mt-3">
      <button class="bg-blue-800 px-6 py-2 rounded text-white" @click="saveFormFieldMap">{{$t('Save')}}</button>
  </div>
</div>
</template>

