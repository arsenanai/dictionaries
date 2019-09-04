<template>
    <div>
        <p>
            <span v-if=loading>{{$t('logout.message')}}</span>
        </p>
    </div>
</template>
<script>
    import api from '../api/auth';
    import {common} from '../common.js'
    //const api = () => import('../api/auth');
    export default {
        mixins: [common],
        data() {
            return {
                token: null,
                loading: false,
            };
        },
        methods: {
            sendLogoutRequest(){
                if(this.token!=null){
                    this.loading = true
                    api.logout({
                        headers:{
                            Authorization: 'Bearer '+this.token
                        }
                    }).then((response) => {
                        if(response.status == 200 && response.data == 'You have been succesfully logged out!')
                            this.eraseToken()
                    }).catch(error => {
                        console.log(error)
                        this.eraseToken()
                    }).then(_ => this.loading = false);
                }else
                    this.$router.push({name:"auth.login"});
            },
            eraseToken(){
                console.log(this.tokenVariable)
                localStorage.removeItem(this.tokenVariable)
                this.$router.push({name:"auth.login"});
            }
        },
        created() {
            this.token = localStorage.getItem(this.tokenVariable)
            this.sendLogoutRequest()
        }
    }
</script>