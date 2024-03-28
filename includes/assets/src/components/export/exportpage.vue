<script setup>
import { useToast } from 'vue-toastification';
import {ref} from 'vue';
const isDownloadActive = ref(false);

const selectedFileType = ref('File Type');
const xmllink = ref('');
const open = ref(false)
/**
 * showing the notification
 * and starting download process
 */
function showDownloadNotification(){
	if(selectedFileType.value === 'File Type')  return;
	if(isDownloadActive.value !== false) return;
	isDownloadActive.value = true;

	// checking if the user wants to download the xml 
	if(selectedFileType.value == "1"){
		const notificaiotn =  useToast();
		notificaiotn("Creating the file please wait", {
			timeout: 4000
		});
		
	}

	// checking if the user wants to download the excel
	if(selectedFileType.value == "2"){
		const notificaiotn =  useToast();
		notificaiotn("Creating the file please wait, download will start automatically!", {
			timeout: 4000
		});
		
	}
	prepareDownload();
}



/**
 * calling the server to create the file
 */
function prepareDownload(){
	const data = new FormData();
	data.append('respacio_houzez_nonce',respacio_houzez_nonce );
	data.append('action','exportAndDownload');
	data.append('fileType',selectedFileType.value);

	fetch(respacio_houzez_ajax_path,{
		method:'POST',
		mode: 'no-cors',
		body:data
	})
			.then(res =>  res.json())
			.then(res => {
				
				if(res == null){
					const notificaiotn =  useToast();
					notificaiotn.error('no data',{
						timeout: 4000
					});
					isDownloadActive.value = false;
					return;
				} 

				if(selectedFileType.value == "2"){
					const link = document.createElement('a');
					link.href = res;
					link.download = 'export.xls';
					link.click();

				}else{
					
					xmllink.value = res;
					open.value = true;
					console.log(xmllink.value);
				}
				
				/**
				 * removing ui selection lock
				 * @type {boolean}
				 */
				isDownloadActive.value = false;
			})
			.catch(err => console.log(err));
}


function copyToClipBoard(){
	navigator.clipboard.writeText(xmllink.value)
        .then(() => {
          alert("Text copied to clipboard!");
        })
        .catch((error) => {
          console.error("Failed to copy text: ", error);
        });
	
}
</script>

<template>
	<div class="rounded-xl bg-white mb-1 shadow p-4">
		<div class="flex flex-row items-center justify-between">
			<div class="flex flex-col ml-3 w-1/2">
				<div class="text-sm">{{$t('export')}}</div>
				<div class="text-xs">{{ $t('Choosethefileformat') }}</div>
			</div>
			<div class="flex flex-row w-1/2 justify-end">
				<select class="w-full" v-on:change="showDownloadNotification" v-model="selectedFileType">
					<option value="1">Xml</option>
					<option value="2">Excel</option>
				</select>
			</div>
		</div>
	</div>
	
	<!-- modal - Teleport to appent the modal before body ends -->
	<Teleport to="body">
		<div v-if="open" class="w-full h-screen absolute top-0 backdrop-blur" >
			<div   class=" fixed z-50 top-1/3 left-1/2 w-[350px] bg-white rounded p-2 -ml-[150px] border">
				<svg @click="open = false" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 absolute -right-2 -top-2 bg-white rounded-full cursor-pointer shadow-lg"> <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /> </svg>

				<div class="px-2 py-2 flex flex-row justify-center items-center">
					<p class="p-2 bg-gray-200 rounded  overflow-x-auto whitespace-nowrap mr-1 " >{{ xmllink }}</p>
					<div class="border p-[8px] rounded cursor-pointer" @click="copyToClipBoard">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 "> <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" /> </svg>

					</div>

				</div>
				
				
			</div>
		</div>
	</Teleport>
	<!-- modal ends  -->
	
</template>

