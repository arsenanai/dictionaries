<template>
    <div class="container">
        <div class="row">
            <div class="col-md-4 offset-md-4">
                <h2>{{ $t('login.page_title') }}</h2>
                <hr>
                <form>
                  <div class="form-group">
                    <label for="exampleInputEmail1">{{$t('Login')}}</label>
                    <input type="email" class="form-control" id="exampleInputEmail1" v-model="login" :placeholder="$i18n.t('Email')">
                  </div>
                  <div class="form-group">
                    <label for="exampleInputPassword1">Password</label>
                    <input type="password" class="form-control" id="exampleInputPassword1" placeholder="Password" v-model="pass">
                  </div>
                  <button type="submit" class="btn btn-primary"
                    @click.prevent="submit" :disabled="!(login.length>0 && pass.length>0)">
                        {{$t('Login')}}
                    </button>
                </form>
            </div>
        </div>
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
              login: 'ensuser@skc.kz',
              pass: '6O0#2k9`5}-.^>T',
              loading: false,
              message: null,
            };
        },
        methods: {
            submit() {
                //validation
                this.message = null;
                if(this.login.length>0 && this.pass.length>0){
                    this.loading = true;
                    api.login({
                        email: this.login,
                        password: this.pass,
                    }).then((response) => {
                        this.token = response.data.token;
                        localStorage.setItem(this.tokenVariable, this.token);
                        setTimeout(() => {
                            this.$router.push({name:"codes.index"});
                        }, 1000);
                    }).catch(error => {
                        console.log(error.response.status)
                        if(error.response.status==422){
                            this.message = ''
                        }
                    }).then(_ => this.loading = false);
                }
            },
        },
        created() {
            var token = localStorage.getItem(this.tokenVariable)
            if(token !== null){
                console.log('token is set');
                this.$router.push({name:"codes.index"});
            }
        }
    }
</script>