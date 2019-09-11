<template>
    <div class=container>
        <div class="row">
            <div class="col-lg-8 offset-lg-2"> 
        <h2>
            {{$t('Groups create')}}
            <!--<router-link class="btn btn-outline-success float-right" :to="{ name: 'codes.create' }">{{$t('Add New')}}</router-link>-->
        </h2>
        <hr>
        <div v-if="message" :class="message.type">{{ message.text }}</div>
        <form @submit.prevent="onSubmit($event)">
          <div class="form-group">
              <label for="group_name">{{$t('Name KK')}}</label>
              <input class="form-control" id="group_name" v-model="group.name_kk" />
              <span v-if="validation.name_kk!==''">{{validation.name_kk}}</span>
          </div>
          <div class="form-group">
              <label for="group_name">{{$t('Name RU')}}</label>
              <input class="form-control" id="group_name" v-model="group.name_ru" />
              <span v-if="validation.name_ru!==''">{{validation.name_ru}}</span>
          </div>
          <div class="form-check">
             <input class="form-check-input" type="checkbox" v-model="group.isZKS" /> {{$t('isZKS')}}
          </div>
          <br>
          <div class="form-group">
              <button type="submit" class="btn btn-outline-primary" :disabled="saving">
                  {{ saving ? $t('Creating')+'...' : $t('Create') }}
              </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
<script>
    import api from '../api/routes';
    import {common} from '../common'
    export default {
      mixins: [common],
        data() {
            return {
                saving: false,
                message: null,
                group: {
                    name_kk: '',
                    name_ru: '',
                    isZKS: false,
                },
                validation:{
                  name_kk:"",
                  name_ru:""
                },
            }
        },
        methods: {
          validated(){
              this.validation.name_kk=""
              this.validation.name_ru=""
              var result = true;
              if(this.group.name_kk==="" 
                || this.group.name_kk===null){
                this.validation.name_kk = this.$i18n.t('Name in kazakh can not be empty')
                result = false;
              }
            if(this.group.name_ru==="" 
                || this.group.name_ru===null){
                this.validation.name_ru = this.$i18n.t('Name in russian can not be empty')
                result = false;
              }
            return result
          },
          onSubmit($event) {
            if(this.validated()===true){
              this.saving = true
              this.message = null
              api.create('group',this.group)
                .then(response => {
                  this.message={}
                  this.message.type='alert alert-success'
                  this.message.text=this.$i18n.t('Group created');
                  this.subgroup = response.data.data;
                  setTimeout(() => {
                    //this.message = null
                    this.$router.push({name:"groups.index"});
                  }, 500);
                })
                .catch(e => {
                  basicErrorHandling(e)
                  if(e.response.status==422){
                    this.message = {}
                    this.message.type = 'alert alert-danger'
                    var message = this.$i18n.t(e.response.data.message)
                    for(var key in e.response.data.errors){
                        e.response.data.errors[key].forEach((value)=>{
                            this.validation[key] = this.$i18n.t(value)
                            //message += this.$i18n.t(value) +' \n'
                        })
                    }
                    this.message.text = message;
                  }
                })
                .then(() => this.saving = false)
            }
          }
        }
    }
</script>