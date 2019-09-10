<template>
    <div class=container>
        <div class="row">
            <div class="col-lg-8 offset-lg-2"> 
              <h2>
            {{$t('Subgroups create')}}
            <!--<router-link class="btn btn-outline-success float-right" :to="{ name: 'codes.create' }">{{$t('Add New')}}</router-link>-->
        </h2>
        <hr>
      <div v-if="message" class="alert alert-warning">{{ message }}</div>
      <form @submit.prevent="onSubmit($event)" v-else>
          <div class="form-group">
              <label for="subgroup_name_kk">{{$t('Name KK')}}</label>
              <input class="form-control" id="subgroup_name_kk" v-model="subgroup.name_kk" />
              <span v-if="validation.name_kk!==''">{{validation.name_kk}}</span>
          </div>
          <div class="form-group">
              <label for="subgroup_name_ru">{{$t('Name RU')}}</label>
              <input class="form-control" id="subgroup_name_ru" v-model="subgroup.name_ru" />
              <span v-if="validation.name_ru!==''">{{validation.name_ru}}</span>
          </div>
          <div class="form-group">
              <label for="group_name">{{$t('Group')}}</label>
              
              <input class="form-control" id="group_name" list="groups" @keyup="typeahead($event.target.value, 'groups',$event)">
            <datalist id="groups">
              <option v-for="group in groups" :value="display('name',group)"></option>
            </datalist>
              <span v-if="validation.group!==''">{{validation.group}}</span>
          </div>
          <div class="form-group">
              <button type="submit" :disabled="saving"
                class="btn btn-outline-primary">
                  {{ saving ? $t('Creating')+'...' : $t('Create') }}
              </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
<script>
  import api from '../api/subgroups';
  import {common} from '../common.js'
  export default {
    mixins: [common],
    data() {
      return {
        message: null,
        loaded: false,
        saving: false,
        validation:{
          name_kk:null,
          name_ru:null,
          group: null
        },
        subgroup: {
          id: null,
          group: null,
          name_kk: null,
          name_ru: null,
        },
        groups:null,
      };
    },
    methods: {
      onSubmit(event) {
        if(this.validated()===true){
          this.saving = true;
            api.create(this.subgroup).then((response) => {
                this.message = this.$i18n.t('Subgroup created');
                this.subgroup = response.data.data;
                setTimeout(() => {
                  this.message = null
                  this.$router.push({name:"subgroups.index"});
                }, 1000);
            }).catch(e => {
                if(e.response.status==401)
                      this.redirectToLogin()
                    if(e.response.status==422){
                      var message = this.$i18n.t(e.response.data.message) + '\n'
                      for(var key in e.response.data.errors){
                          e.response.data.errors[key].forEach((value)=>{
                              message += this.$i18n.t(value) +'\n'
                          })
                      }
                      alert(message)
                      console.log(message)
                    }
            }).then(_ => this.saving = false);
        }
      },
      validated(){
        this.validation.name_kk=""
        this.validation.name_ru=""
        this.validation.group = ""
        var result = true;
        /*if(this.subgroup.name_kk==="" 
          || this.subgroup.name_kk===null){
          this.validation.name_kk = "Name in kazakh can not be empty"
          result = false;
        }*/
      if(this.subgroup.name_ru==="" 
          || this.subgroup.name_ru===null){
          this.validation.name_ru = this.$i18n.t('Name in kazakh can not be empty')
          result = false;
        }
        if(this.subgroup.group == null){
          this.validation.group = this.$i18n.t('Group have to be choosen')
          result = false;
        }
      return result
    },
    typeahead(input, type, event=null) {
      if (event instanceof KeyboardEvent || event === null){
          if(type==='groups'){
            var params = {} 
                  params.input = input
                  params.lang = this.$i18n.locale
            api.search('group',params).then((response) => {
              this.groups = response.data.data
            }).catch(e => {
              if(e.response.status==401)
                this.redirectToLogin()
            });
          }
      }else{
        this.subgroup.group = this.groups.find(group => group['name_'+this.$i18n.locale] === input);
      }
    },
    created() {
        api.find(this.$route.params.id)
        .then((response) => {
            //setTimeout(() => {
              this.loaded = true;
              this.subgroup = response.data.data;
            //}, 1000);
        })
        .catch((e) => {
          if(e.response.status==401)
            this.redirectToLogin()
          this.$router.push({ name: '404' });
        });
    }
  }
};
</script>