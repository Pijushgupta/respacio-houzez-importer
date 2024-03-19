<script setup>
import {ref} from 'vue';
import Register from './register/register.vue';
import Login from './login/login.vue';


const currentTabNumber = ref(0)


</script>
<template>
  <div class="rounded-xl bg-white mb-1 shadow p-4">
    <div class="flex flex-col items-center justify-center min-h-[400px] max-w-[400px] mx-auto">
      <ul class="flex flex-row justify-center align-middle p-2 bg-gray-100 rounded-lg  w-full mb-4">
        <li @click="currentTabNumber = 0" v-bind:class="currentTabNumber == 0 ? 'bg-white shadow':''" class="px-4 py-2 mb-0 rounded cursor-pointer w-full text-center">Register</li>
        <li @click="currentTabNumber = 1" v-bind:class="currentTabNumber == 1 ? 'bg-white shadow':''" class="px-4 py-2  mb-0 rounded cursor-pointer w-full text-center">Login</li>
      </ul>
      <div class="flex flex-row items-center justify-center w-full">
        <div v-if="currentTabNumber == 0" class="w-full login-content-area  rounded-lg p-4 shadow-lg">
          
          <Register/>
        </div>
        <div v-if="currentTabNumber == 1" class="w-full register-content-area">
          
          <Login/>
          
        </div>
      </div>

    </div>
  </div>
</template>