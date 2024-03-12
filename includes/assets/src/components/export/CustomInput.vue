<!-- CustomInput.vue -->
<template>
    <input type="text" v-model="inputValue" :placeholder="placeholder" />
  </template>
  
  <script setup>
  import { ref, defineProps } from 'vue';
  
  const props = defineProps(['placeholder']);
  const inputValue = ref('');
  </script>