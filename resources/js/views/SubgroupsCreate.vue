<template>
    <div class=container>
        <div class="row">
            <div class="col-lg-8 offset-lg-2"> 
              <h2>
            {{$t('Subgroups create')}}
            <!--<router-link class="btn btn-outline-success float-right" :to="{ name: 'codes.create' }">{{$t('Add New')}}</router-link>-->
        </h2>
        <hr>
      <div v-if="message" :class="message.type">{{ message.text }}</div>
      <form @submit.prevent="onSubmit($event)">
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
          {{subgroup.group_id}}
          <div class="form-group">
              <label for="group_name">{{$t('Group')}}</label>
              <select class="form-control" v-model="subgroup.group_id" id="group_name">
                <option v-for="group in groups" :value="group.id">{{display('name',group)}}</option>
              </select>
              <span v-if="validation.group_id!==''">{{validation.group_id}}</span>
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
  import api from '../api/routes';
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
          group_id: null
        },
        subgroup: {
          id: null,
          group_id: null,
          name_kk: null,
          name_ru: null,
        },
        groups:null,
      };
    },
    mounted() {
      this.typeahead('','group')
    },
    methods: {
      onSubmit(event) {
        if(this.validated()===true){
          this.saving = true;
          this.message = null
            api.create('subgroup',this.subgroup).then((response) => {
                this.message={}
                this.message.type='alert alert-success'
                this.message.text=this.$i18n.t('Subgroup created');
                this.subgroup = response.data.data;
                setTimeout(() => {
                  //this.message = null
                  this.$router.push({name:"subgroups.index"});
                }, 500);
            }).catch(e => {
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
            }).then(_ => this.saving = false);
        }
      },
      validated(){
        this.validation.name_kk=""
        this.validation.name_ru=""
        this.validation.group_id = ""
        var result = true;
        if(this.subgroup.name_kk==="" 
          || this.subgroup.name_kk===null){
          this.validation.name_kk = this.$i18n.t('Name in kazakh can not be empty')
          result = false;
        }
      if(this.subgroup.name_ru==="" 
          || this.subgroup.name_ru===null){
          this.validation.name_ru = this.$i18n.t('Name in russian can not be empty')
          result = false;
        }
        if(this.subgroup.group_id == null){
          this.validation.group_id = this.$i18n.t('Group have to be choosen')
          result = false;
        }
      return result
    },
    typeahead(input, type, event=null) {
      if (event instanceof KeyboardEvent || event === null){
        var params = {} 
          params.input = input
          params.lang = this.$i18n.locale
        api.search('group',params).then((response) => {
          console.log(response)
          this.groups = response.data.data
        }).catch(e => {
          basicErrorHandling(e)
        });
      }
    },
  },
};
</script>