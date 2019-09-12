 <template>
  <div class="container ">
        
        <div class="row">
            <div class="col-lg-8 offset-lg-2"> 
              <h2>
            {{$t('Codes create')}}
            <!--<router-link class="btn btn-outline-success float-right" :to="{ name: 'codes.create' }">{{$t('Add New')}}</router-link>-->
        </h2>
        <hr>
      <div v-if="message" :class="message.type">{{ message.text }}</div>
      <form @submit.prevent="onSubmit($event)">
        <div class="form-group">
            <label for="code_name">{{$t('Code')}}</label>
            <input class="form-control" id="code_name" v-model="code.code" />
            <span v-if="validation.code!==''">{{validation.code}}</span>
        </div>
        <div class="form-group">
            <label for="code_name_kk">{{$t('Name kk')}}</label>
            <input class="form-control" id="code_name_kk" v-model="code.name_kk" />
            <span v-if="validation.name_kk!==''">{{validation.name_kk}}</span>
        </div>
        <div class="form-group">
            <label for="code_name_ru">{{$t('Name ru')}}</label>
            <input class="form-control" id="code_name_ru" v-model="code.name_ru" />
            <span v-if="validation.name_ru!==''">{{validation.name_ru}}</span>
        </div>
        <div class="form-group">
            <label for="code_description_kk">{{$t('Description kk')}}</label>
            <input  class="form-control" id="code_description_kk" v-model="code.description_kk" />
            <span v-if="validation.description_kk!==''">{{validation.description_kk}}</span>
        </div>
        <div class="form-group">
            <label for="code_description_ru">{{$t('Description ru')}}</label>
            <input  class="form-control" id="code_description_ru" v-model="code.description_ru" />
            <span v-if="validation.description_ru!==''">{{validation.description_ru}}</span>
        </div>
        <div class=form-group>
          <label for="type">{{$t('Type')}}</label>
          <select class="form-control" v-model="code.type">
            <option value=GOODS selected>{{$t('GOODS')}}</option>
            <option value=WORK>{{$t('WORK')}}</option>
            <option value=SERVICE>{{$t('SERVICE')}}</option>
          </select>
        </div>
        <div class="form-group">
            <label for="subgroup_name">{{$t('Subgroup')}}</label>
            <input  class="form-control" id="subgroup_name" list="subgroups" @keyup="typeahead($event.target.value, 'subgroups')"
            :readonly="this.code.group===null" v-model="type_subgroup">
            <datalist id="subgroups">
              <option v-for="subgroup in subgroups" :value="display('name',subgroup)"></option>
            </datalist>
            
        </div>
        <div class="form-group">
            <button class="btn btn-outline-primary" type="submit" :disabled="saving">{{$t('Create')}}</button>
            <router-link class="btn btn-outline-secondary" :to="{ name: 'codes.index' }">{{$t('Cancel')}}</router-link>
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
  mixins:[common],
  data() {
    return {
      message: null,
      saving: false,
      validation:{
        code: "",
        subgroup: "",
        name_kk: "",
        name_ru: "",
        description_kk: "",
        description_ru: ""
      },
      code: {
        id: null,
        code: null,
        name_kk: null,
        name_ru: null,
        description_kk: null,
        description_ru: null,
        type: 'GOODS',
        subgroup: null,
      },
      subgroups: null,
      type_subgroup: null,
    };
  },
  methods: {
    onSubmit(event) {
      if(this.validated()===true){
        this.message = null
        this.saving = true;
          api.create('code',this.code).then((response) => {
            this.message={}
            this.message.type='alert alert-success'
            this.message.text=this.$i18n.t('Code created successfully');
              this.code = response.data.data;
              setTimeout(() => {
                this.saving = false;
                this.message = null
                this.$router.push({name:"codes.index"});
              }, 500);
          }).catch(e => {
              this.basicErrorHandling(e)
              if(e.response.status==422){
                this.message = {}
                this.message.type = 'alert alert-danger'
                var message = this.$i18n.t(e.response.data.message)
                for(var key in e.response.data.errors){
                    e.response.data.errors[key].forEach((value)=>{
                        this.validation[key] = this.$i18n.t(value)
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
      this.validation.description_kk=""
      this.validation.description_ru=""
      this.validation.code=""
      this.validation.subgroup=""
      var result = true;
      if(this.code.code==="" 
        || this.code.code.length!==17){
        this.validation.code= this.$i18n.t('Code must be with length 17')
        result = false;
      }
      if(this.code.name_kk===""){
        this.validation.name_kk= this.$i18n.t('Specify code name in kazakh')
        result = false;
      }
      if(this.code.name_ru===""){
        this.validation.name_ru= this.$i18n.t('Specify code name in russian')
        result = false;
      }
      if(this.code.description_kk===""){
        this.validation.description_kk= this.$i18n.t('Specify code description in kazakh')
        result = false;
      }
      if(this.code.description_ru===""){
        this.validation.description_ru= this.$i18n.t('Specify code description in russian')
        result = false;
      }
      if(this.code.subgroup===null){
        this.validation.subgroup = this.$i18n.t('Choose code subgroup')
        result = false
      }
    return result
  },
    typeahead(input, type) {
      var matched = false;
      if(this.subgroups && type==='subgroups'){
        var subgroup;
        if(this.$i18n.locale==='ru')
          subgroup = this.subgroups.find(subgroup => subgroup.name_ru === input);
        else
          subgroup = this.subgroups.find(subgroup => subgroup.name_kk === input);
        if(subgroup){
          this.code.subgroup = subgroup
          matched = true
        }
      }
      if (input.length > 1 && matched===false) {
        
        if(type==='subgroups'){
          var params = {} 
          params.input = input
          params.lang = this.$i18n.locale
          params.except = -1
          params.parent = this.code.group.id
          api.search('subgroup', params).then((response) => {
            this.subgroups = response.data.data
          }).catch(e => {
            this.basicErrorHandling(e)
          });
        }
      }else if(input.length == 0){
        if(type==='subgroups'){
          this.code.subgroup=null;
          this.subgroups=null;
        }
      }
    },
  }
};
</script>