<script setup>
import {ref} from 'vue';
import { useToast } from 'vue-toastification';
const toast = useToast();

const email_address = ref('');
const password = ref('');
const website = ref('');

//submitting all the data
const submitAllData = () => {

  if(!isValidEmail(email_address.value.trim())) {
    toast('Email address is not valid');
    return false;
  }

  if(!isValidURL(website.value.trim())){
    toast('Web address is not valid');
    return false;
  }

  if(password.value.trim == ''){
    toast('Please is required');
  }

  const data = new FormData();
  data.append('respacio_houzez_nonce',respacio_houzez_nonce);
  data.append('action','ajaxAccountLogin');

  //form fields
  data.append('email_address',email_address.value.trim());
  data.append('password',password.value.trim());
  data.append('website',website.value.trim());
  //ends
  
  fetch(respacio_houzez_ajax_path,{
    method:'POST',
    body:data
  })
  .then(res => res.json())
  .then(res => {
      console.log(res);
      if(res == true || res == 'true'){
        toast('Login successful');
      }
      if(res == false || res == 'false'){
        toast('Login Failed');
      }
  })
  .catch(err => console.log(err));

}

//as its name says, its to validate email
function isValidEmail(email) {
  // Regular expression for basic email format validation
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
}

function isValidURL(url) {
  // Regular expression for URL validation
  const urlRegex = /^(http(s)?:\/\/)?[^ "]+$/;
  return urlRegex.test(url);
}

</script>
<template>
    <form class="flex flex-col  w-full " @submit.prevent="submitAllData">
      <div class="mb-4">
        <h2 class="font-bold text-lg mb-1">{{ $t('Login') }}</h2>
        <p class="text-gray-400 ">{{ $t('Member Login: Enter Your Credentials') }}</p>
      </div>
      <div class="mb-2 flex flex-col w-full">
        <label for="loginemail" class="font-semibold  mb-1">{{ $t('Email') }}</label>
        <input id="loginemail" type="email" class="py-2" v-model="email_address"/>
      </div>

      <div class="mb-2 flex flex-col w-full">
        <label for="loginpassword" class="font-semibold  mb-1">{{ $t('Password') }}</label>
        <input id="loginpassword" type="password" class="py-2" v-model="password"/>
      </div>

      <div class="mb-2 flex flex-col w-full">
        <label for="loginwebsite" class="font-semibold  mb-1">{{ $t('Website') }}</label>
        <input id="loginwebsite" type="text" class="py-2 mb-3" v-model="website"/>
      </div>
      <div class="flex" >
        
        <button type="submit" class="bg-blue-800 text-white py-3 rounded-lg w-full flex flex-row justify-center align-middle">{{ $t('Login') }}<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 stroke-white"> <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25 21 12m0 0-3.75 3.75M21 12H3" /> </svg></button>
      </div>


    </form>
</template>