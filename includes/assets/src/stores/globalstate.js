import { defineStore } from "pinia";
export const useGlobalstateStore = defineStore("useGlobalstateStore",{
    state: ()=>({ isActivated:false}),
    getters: {},
    actions : {
        getActivatedStatus(){

            const data = new FormData();
            data.append('respacio_houzez_nonce',respacio_houzez_nonce);
            data.append('action','isActivated');

            fetch(respacio_houzez_ajax_path,{
                method:'POST',
                body:data
            })
                .then(res => res.json())
                .then(res => {
                    this.isActivated = res;
                })
                .catch(err => console.log(err))
        },

    }
});

