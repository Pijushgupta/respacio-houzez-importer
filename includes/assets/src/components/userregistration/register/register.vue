<script setup>
import {ref} from 'vue';
import { useBreadcrumbStore } from '../../../stores/breadcrumb';
const windowStore = useBreadcrumbStore()
import { useToast } from 'vue-toastification';
const toast = useToast();

//form fields
const name = ref('');
const surname = ref('');

const email = ref('');
const website = ref('');

const phone = ref('');
const numberOfUsers = ref('');

const password = ref(generateKey(16));
const code = ref('');
//ends

const page = ref(0)

const emailVerificationStatus = ref(false);

const endVideo = siteUrl + "/wp-content/plugins/houzez-respacio-import/includes/dist/video.webm";

const next = () =>{
  //page < 3 ? page++ : page
  //validation 

  //step - 1
  if(page.value == 0){
    if(name.value.trim() == '' || surname.value.trim() == '') return false;
    page.value++; 
    return false
  }

  //step - 2 
  if(page.value == 1){
    if(email.value.trim() == '' || website.value.trim() == '') return false;
    if(!isValidEmail(email.value.trim())) {
      //TODO:show toast notification
      toast('Email address is not valid');
      return false;
    }
    if(!isValidURL(website.value.trim())){
      //TODO: show toast notification
      toast('Web address is not valid');
      return false;
    }
    page.value++;
    //sending email id for email verification
    getEmailVerificationCode();

    return false;
  }

  //step - 3 
  if(page.value == 2){
    if(phone.value.trim() == '' || numberOfUsers.value.trim() == '') return false;
    //show toast to check email for verification Code 
    if(emailVerificationStatus.value == true){
      toast('Please check your inbox for verification code!')
    }
    page.value++;
    return false;
  }

  //step - 4
  if(page.value == 3){
    if(password.value.trim() == '' || code.value.trim() == '') return false;
    page.value++
    submitAllData();
    return;
  }

}

function generateKey(keyLength = 22) {
  const characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  const charactersLength = characters.length;
  let key = '';
  for (let i = 0; i < keyLength; i++) {
      key += characters[Math.floor(Math.random() * charactersLength)];
  }
  return key;
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

const getEmailVerificationCode = () => {
  
  if(email.value === '') return false;

  const data = new FormData();
  data.append('respacio_houzez_nonce',respacio_houzez_nonce);
  data.append('action','ajaxVerifyEmail');
  data.append('email',email.value.trim());

  fetch(respacio_houzez_ajax_path,{
    method:'POST',
    body:data
  })
  .then(res => res.json())
  .then(res => {
    emailVerificationStatus.value = true;
  })
  .catch(err => console.log(err));
}

//submitting all the data
const submitAllData = () => {
  const data = new FormData();
  data.append('respacio_houzez_nonce',respacio_houzez_nonce);
  data.append('action','ajaxCreateAccount');

  //form fields
  data.append('name',name.value.trim());
  data.append('surname',surname.value.trim());

  data.append('email_address',email.value.trim());
  data.append('website',website.value.trim());

  data.append('mobile',phone.value.trim());
  data.append('no_of_user',numberOfUsers.value.trim());

  data.append('password',password.value.trim());
  data.append('code',code.value.trim());
  //ends
  
  fetch(respacio_houzez_ajax_path,{
    method:'POST',
    body:data
  })
  .then(res => res.json())
  .then(res => {
    if(res == 1){
      toast('Incorrect email verification code');
      page.value--;
    }
    if(res.status && res.status == 'success'){
      
    }
  })
  .catch(err => console.log(err));


}
</script>
<template>
  <div class="flex flex-col  w-full ">
    <div class="mb-4">
      <h2 class="font-bold text-lg mb-1">{{ $t('Register') }}</h2>
      <p class="text-gray-400 ">{{ $t('GetStartedTodayCreateYourAccount') }}</p>
    </div>
    <!-- indicator -->
    <ul class="flex flex-row justify-between items-center">
      <li class="flex flex-row justify-center  w-1/4 relative after:content-[''] after:w-full after:z-0 after:absolute after:h-[2px]  after:inset-y-1/2 after:inset-x-1/2" v-bind:class="page >= 1 ? 'after:transition after:ease-in-out after:delay-300 after:bg-blue-800':'after:bg-gray-100'">
        <span  class=" rounded-full p-2 z-10" v-bind:class="page >= 0 ? 'transition ease-in-out delay-300 bg-blue-800 ':'bg-gray-100'"></span>
      </li>

      <li class="flex flex-row justify-center w-1/4 relative after:content-[''] after:w-full after:z-0 after:absolute after:h-[2px]  after:inset-y-1/2 after:inset-x-1/2" v-bind:class="page >= 2 ? 'after:transition after:ease-in-out after:delay-300 after:bg-blue-800':'after:bg-gray-100'">
        <span class="rounded-full p-2 z-10" v-bind:class="page >= 1 ? 'transition ease-in-out delay-300 bg-blue-800':'bg-gray-100'" ></span>
      </li>

      <li class=" flex flex-row justify-center w-1/4 relative  after:content-[''] after:w-full after:z-0 after:absolute after:h-[2px]  after:inset-y-1/2 after:inset-x-1/2" v-bind:class="page >= 3 ? 'after:transition after:ease-in-out after:delay-300 after:bg-blue-800':'after:bg-gray-100'">
        <span class="rounded-full p-2 z-10" v-bind:class="page >= 2 ? 'transition ease-in-out delay-300 bg-blue-800':'bg-gray-100'" ></span>
      </li>

      <li class=" flex flex-row justify-center w-1/4 relative  " >
        <span  class="rounded-full p-2 z-10" v-bind:class="page >= 3 ? 'btransition ease-in-out delay-300 bg-blue-800':'bg-gray-100'"></span>
      </li>
    </ul>
    <!-- step 1 -->
    <div v-show="page == 0">
    <div class="mb-2 flex flex-col w-full">
      <label for="registername" class="font-semibold  mb-1">{{ $t('Name') }}</label>
      <input id="registername" type="text" class="py-2" v-model="name"/>
    </div>
    <div class="mb-4 flex flex-col w-full">
      <label for="registersurname" class="font-semibold mb-1">{{ $t('Surname') }}</label>
      <input id="registersurname" type="text" class="py-2" v-model="surname"/>
    </div>
    </div>
    <!-- step 2 -->
    <div v-show="page == 1">
    <div class="mb-2 flex flex-col w-full">
      <label for="registeremail" class="font-semibold mb-1">{{ $t('Email') }}</label>
      <input id="registeremail" type="email" class="py-2" v-model="email"/>
    </div>
    <div class="mb-4 flex flex-col w-full">
      <label for="registersurwebsite" class="font-semibold mb-1">{{ $t('Website') }}</label>
      <input id="registersurwebsite" type="url" class="py-2" v-model="website"/>
    </div>
    </div>
    <!-- step 3 -->
    <div v-show="page == 2">
    <div class="mb-2 flex flex-col w-full">
      <label for="registertel" class="font-semibold mb-1">{{ $t('Phone')}}</label>
      <input id="registertel" type="tel" class="py-2" v-model="phone"/>
    </div>
    <div class="mb-4 flex flex-col w-full">
      <label for="registeruser" class="font-semibold mb-1">{{ $t('Numberofusers') }}</label>
      <input id="registeruser" type="url" class="py-2" v-model="numberOfUsers"/>
    </div>
    </div>
    <!-- step 4 -->
    <div v-show="page == 3">
    <div class="mb-2 flex flex-col w-full">
      <label for="registerpass" class="font-semibold mb-1">{{ $t('Password') }}</label>
      <div class="flex flex-row relative">
      <input id="registerpass" type="text" class="py-2 w-full" v-model="password"/>
      <button class="absolute p-2 border-l top-0 right-0" @click="password = generateKey(16)"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"> <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12c0-1.232-.046-2.453-.138-3.662a4.006 4.006 0 0 0-3.7-3.7 48.678 48.678 0 0 0-7.324 0 4.006 4.006 0 0 0-3.7 3.7c-.017.22-.032.441-.046.662M19.5 12l3-3m-3 3-3-3m-12 3c0 1.232.046 2.453.138 3.662a4.006 4.006 0 0 0 3.7 3.7 48.656 48.656 0 0 0 7.324 0 4.006 4.006 0 0 0 3.7-3.7c.017-.22.032-.441.046-.662M4.5 12l3 3m-3-3-3 3" /> </svg> </button>
      </div>
    </div>
    <div class="mb-4 flex flex-col w-full">
      <label for="registerpass" class="font-semibold mb-1">{{ $t('Codesentviaemail') }}</label>
      <input id="registerotp" type="text" class="py-2" v-model="code"/>
    </div>
    </div>
    <!-- final step/msg -->
    <div v-if="page == 4">
    <div class="mb-2 flex flex-col w-full justify-center items-center">
      <video autoplay width="150" height="150">
        <source v-bind:src="endVideo" type="video/webm">
        Your browser does not support the video tag.
      </video>
      <span class="text-xl mb-3">Finished!</span>
      <span class="mb-3">Your account creation process should be completed within the next 15-30 minutes. We appreciate your understanding.</span>
    </div>
    </div>

    <div class="flex" v-if="page != 4">
      <button @click="page > 0 ? page-- : page" class="mr-2 bg-blue-300 rounded-lg py-3 px-4"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 stroke-white"> <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 15.75 3 12m0 0 3.75-3.75M3 12h18" /> </svg> </button>
      <button @click="next()" class="bg-blue-800 text-white py-3 rounded-lg w-full flex flex-row justify-center align-middle">{{ $t('Next') }}<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 stroke-white"> <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25 21 12m0 0-3.75 3.75M21 12H3" /> </svg></button>
    </div>

    <div class="flex" v-if="page == 4">
      <button v-on:click="windowStore.activeWindow = 0" class="bg-blue-800 text-white py-3 rounded-lg w-full flex flex-row justify-center align-middle">{{ $t('Done') }}</button>
    </div>
            
            
  </div>
</template>