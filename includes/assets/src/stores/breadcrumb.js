import { defineStore } from "pinia";
export const useBreadcrumbStore = defineStore("breadcrumbState",{
    state: ()=>({ activeWindow : 0,breadcrumbId:null}),
    getters: {},
    actions : {
        changeActiveWindow(val){
            this.activeWindow = val;
        },

    }
});

