<script setup>
import {ref, watch, computed} from 'vue';


const logs = ref(0);
const pages = ref(0);
const perpage = ref(20);
const offset = ref(0);
const posts = ref(0);
const siteurl = siteUrl;

const numberOfLogs = () =>{
    const data = new FormData();
    data.append('respacio_houzez_nonce',respacio_houzez_nonce);
    data.append('action','ajaxGetTotalNumberOfPropertyLog');

    fetch(respacio_houzez_ajax_path,{
        method:'POST',
        body:data
    })
    .then(res => res.json())
    .then(res => {
        if(res != '0' || res != 0){
            console.log(res);
            logs.value = res; 
            calculatePages()
        }
    })
    .catch(err => console.log(err));


}
const getPerPage = () =>{
    const data = new FormData();
    data.append('respacio_houzez_nonce',respacio_houzez_nonce);
    data.append('action','ajaxGetLogPerPageOption');

    fetch(respacio_houzez_ajax_path,{
        method:'POST',
        body:data
    })
    .then(res => res.json())
    .then(res => {
        if(res){
            perpage.value = res;
        }
        console.log(res)
    })
    .catch(err => console.log(err));
}
const setPerPage = () =>{
    const data = new FormData();
    data.append('respacio_houzez_nonce',respacio_houzez_nonce);
    data.append('action','ajaxSetLogPerPageOption');
    data.append('perpage',perpage.value);

    fetch(respacio_houzez_ajax_path,{
        method:'POST',
        body:data
    })
    .then(res => res.json())
    .then(res => {
        
        //console.log(res)
    })
    .catch(err => console.log(err));
}

watch(perpage,(newPerpage,oldPerpage) => {
    if(newPerpage == oldPerpage) return false;
    setPerPage();
});

//init
numberOfLogs();
getPerPage();

//2nd call
const calculatePages = () => {
    if(logs.value == 0) return false;
    if(perpage.value >= logs.value) {
        pages.value = 1;
    }else{
        let tempPages = logs.value / perpage.value;
        //changing the value of pages, which will trigger watch method
        pages.value = incrementAndRemoveDecimal(tempPages);
    }
    //console.log(pages.value);
    
} 
// 3rd call
const incrementAndRemoveDecimal = (inputNumber) => {
    let inputString = inputNumber.toString();

    // Check if the inputNumber has a decimal point
    if (inputString.includes('.')) {
        // Split the number into integer and decimal parts
        let parts = inputString.split('.');
        // Check if the decimal part is not zero
        if (parseInt(parts[1], 10) !== 0) {
            // If it's not zero, parse the integer part and increment it by 1
            let incrementedInteger = parseInt(parts[0], 10) + 1;
            // Return the incremented integer part as a string
            return incrementedInteger.toString();
        }
    }
    // If it doesn't meet the conditions, return the number as it is
    return inputString;
}

//watch: its machanism to watch any changes for specific ref based variable
watch([perpage,offset],([newPerpage,newOffset], [oldPerpage,oldOffset]) => {
    calculatePages()
    getLogs(newPerpage,newOffset)

});
const getLogs = (numposts,offset) =>{
    const data = new FormData();
    data.append('respacio_houzez_nonce',respacio_houzez_nonce);
    data.append('action','ajaxGetPropertyLogs');
    data.append('numposts',numposts);
    data.append('offset',offset);

    fetch(respacio_houzez_ajax_path,{
        method:'POST',
        body:data
    })
    .then(res => res.json())
    .then(res => {
        posts.value = res;
    })
    .catch(err => console.log(err));
}
//just v-for we are making number of pages to array element 
const countRange = computed(() => {
      return Array.from({ length: pages.value }, (_, index) => index + 1);
});

const changePage = (ofs) =>{
    console.log(ofs);
    //for first page where we want offset to be 0
    if(ofs == 0){
        getLogs(perpage.value,ofs)
        offset.value = 0
        return false;
    }
    //for last page where we want post perpage to be the remaining posts 
    if(ofs == (pages.value - 1)){
        
        let numberofposts = logs.value - (perpage.value * ofs)
        let offset = perpage.value * ofs
        getLogs(numberofposts,offset)
        return false;
    }
    
    getLogs(perpage.value,perpage.value * ofs)
    
    return false;
}

</script>
<template>
    <div class="relative">
        <div class="absolute top-[-50px] right-0 rounded-xl bg-white shadow px-4 py-1 text-sm">Items per page: <select v-model="perpage" class="border-0 ">
            <option value="10">10</option>
            <option value="20">20</option>
            <option value="30">30</option>
            <option value="40">40</option>
            <option value="50">50</option>
            <option value="60">60</option>
            <option value="70">70</option>
            <option value="80">80</option>
            <option value="90">90</option>
            <option value="100">100</option>
        </select></div>
        <!-- property row -->
        <div class="mb-2">
            
            <div class="flex flex-row rounded-xl bg-white shadow">
                <ul class="w-full flex flex-col ">
                    <template v-if="logs != 0">
                        <li v-for="post in posts" :key="post.id"  class="flex flex-row items-center justify-between last:border-b-0 mb-0 border-b p-4">
                            <span class="text-sm flex flex-col justify-start"><span>{{ post.title }}</span><span>{{ post.time }}</span></span>
                            
                            <span class="flex flex-row justify-end items-center">
                                
                                <a v-bind:href="siteurl+'/wp-admin/post.php?post='+ post.property_id + '&action=edit'" target="_blank" class="mr-4"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"> <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /> </svg> </a>

                                <a v-bind:href="post.guid" target="_blank"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"> <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /> <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /> </svg> </a>
                            </span>
                        </li>
                    </template>
                </ul>
            </div>

        </div>
        <!-- end of property row -->
        <!-- for pagination -->
        <div class="w-full" v-if="pages != 1">
            <ul class="flex flex-row justify-end items-center ">
                <li v-for="(n,index) in countRange" :key="index" v-on:click="changePage(index)" class="mr-2 last:mr-0 rounded-lg bg-white shadow px-3 py-1  cursor-pointer">{{ n }}</li>
               
            </ul>
        </div>
        <!-- end of pagination -->
    </div>
</template>