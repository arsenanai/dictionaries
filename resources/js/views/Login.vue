<template>
    <div class="container">
        <div class="row">
            <div class="col-md-4 offset-md-4">
                <h2>{{$t('login.page_title')}}</h2>
                <hr>
                <div class="alert alert-warning" v-if="message">
                        {{message}}
                    </div>
                <form>
                  <div class="form-group">
                    <label for="exampleInputEmail1">{{$t('Email')}}</label>
                    <input type="email" class="form-control" id="exampleInputEmail1" v-model="login" :placeholder="$i18n.t('Email')">
                  </div>
                  <div class="form-group">
                    <label for="exampleInputPassword1">{{$t('Password')}}</label>
                    <input type="password" class="form-control" id="exampleInputPassword1" :placeholder="$i18n.t('Password')" v-model="pass">
                  </div>
                  <button type="submit" class="btn btn-outline-primary"
                    @click.prevent="submit" :disabled="!(login.length>0 && pass.length>0)">
                        {{$t('Login')}}
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
<script>
    import api from '../api/routes';
    import axios from 'axios';
    import {common} from '../common.js'
    //const api = () => import('../api/auth');
    export default {
        mixins: [common],
        data() {
            return {
              token: null,
              login: '',
              pass: '',
              loading: false,
              message: null,
            };
        },
        methods: {
            submit() {
                //validation
                this.message = null;
                if(this.login.length>0 && this.pass.length>0){
                    this.loading = true
                    /*
                    'form_params' => [
                        'grant_type' => 'password',
                        'client_id' => 'client-id',
                        'client_secret' => 'client-secret',
                        'username' => 'taylor@laravel.com',
                        'password' => 'my-password',
                        'scope' => '',
                    ],
                    */
                    const formData = new FormData();
                    formData.append('grant_type', 'password')
                    formData.append('client_id', passportClientId)
                    formData.append('client_secret', passportClientSecret)
                    formData.append('username',this.login)
                    formData.append('password', this.pass)
                    formData.append('scope','*')
                    api.login(formData)
                        .then(response=>{
                            //console.log(response)
                            this.token = response.data.access_token
                            localStorage.setItem(this.tokenVariable, this.token);
                            localStorage.setItem('refresh_token', response.data.refresh_token)
                            localStorage.setItem('expires_in', response.data.expires_in)
                            localStorage.setItem('token_type', response.data.token_type)
                            localStorage.setItem('dictionary_user_email', this.login)
                            api.logged()
                            //setTimeout(() => {
                                this.$router.push({name:"codes.index"});
                            //}, 1000);
                        }).catch(error=>{
                            console.log(error.response)
                            if(error.response.status==401){
                                this.message = this.$i18n.t('Bad credentials')
                            }
                        }).then(_=> this.loading = false)
                    /*api.login({
                        email: this.login,
                        password: this.pass,
                    }).then((response) => {
                        this.token = response.data.token;
                        localStorage.setItem(this.tokenVariable, this.token);
                        setTimeout(() => {
                            this.$router.push({name:"codes.index"});
                        }, 1000);
                    }).catch(error => {
                        console.log(error.response)
                        if(error.response.status==422){
                            this.message = this.$i18n.t('Bad credentials')
                        }
                    }).then(_ => this.loading = false);*/
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