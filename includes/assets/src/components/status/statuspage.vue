<script setup>
import {ref, watch} from 'vue';


const logs = ref(0);
const pages = ref(0);
const perpage = ref(20);
const offset = ref(0);
const posts = ref(0);

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
//init
numberOfLogs();

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
    console.log(pages.value);
    
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
    
    const data = new FormData();
    data.append('respacio_houzez_nonce',respacio_houzez_nonce);
    data.append('action','ajaxGetPropertyLogs');
    data.append('numposts',newPerpage);
    data.append('offset',newOffset);

    fetch(respacio_houzez_ajax_path,{
        method:'POST',
        body:data
    })
    .then(res => res.json())
    .then(res => {
        posts.value = res;
    })
    .catch(err => console.log(err));

});


</script>
<template>
    <div>
        <div class="mb-1">
            <div class="flex flex-row rounded-xl bg-white shadow">
                <ul class="w-full flex flex-col ">
                    <template v-if="logs != 0">
                        <li v-for="post in posts" :key="post.id" class="last:border-b-0 mb-0 border-b p-4"><span class="text-sm">{{ post.title }}</span></li>
                    </template>
                </ul>
            </div>
        </div>
        <div>

        </div>
    </div>
</template>