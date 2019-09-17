 <template>
  <div class="container ">
    <div class="row">
      <div class="col-lg-8 offset-lg-2">
      <h2>
            {{$t('Codes edit')}}
            <!--<router-link class="btn btn-outline-success float-right" :to="{ name: 'codes.create' }">{{$t('Add New')}}</router-link>-->
        </h2>
        <hr>
      <div v-if="message" :class="message.type">{{ message.text }}</div>
      <div v-if="! loaded">{{$t('Loading')}}...</div>
      <form @submit.prevent="onSubmit($event)" v-else>
        <div class="form-group">
            <label for="code_name">{{$t('Code')}}</label>
            <input readonly class="form-control" id="code_name" v-model="code.code" />
            <span v-if="validation.code!==''">{{validation.code}}</span>
        </div>
        <div class="form-group">
            <label for="code_name_kk">{{$t('Name kk')}}</label>
            <input readonly class="form-control" id="code_name_kk" v-model="code.name_kk" />
            <span v-if="validation.name_kk!==''">{{validation.name_kk}}</span>
        </div>
        <div class="form-group">
            <label for="code_name_ru">{{$t('Name ru')}}</label>
            <input readonly class="form-control" id="code_name_ru" v-model="code.name_ru" />
            <span v-if="validation.name_ru!==''">{{validation.name_ru}}</span>
        </div>
        <div class="form-group">
            <label for="code_description_kk">{{$t('Description kk')}}</label>
            <input readonly class="form-control" id="code_description_kk" v-model="code.description_kk" />
            <span v-if="validation.description_kk!==''">{{validation.description_kk}}</span>
        </div>
        <div class="form-group">
            <label for="code_description_ru">{{$t('Description ru')}}</label>
            <input readonly class="form-control" id="code_description_ru" v-model="code.description_ru" />
            <span v-if="validation.description_ru!==''">{{validation.description_ru}}</span>
        </div>
        <div class=form-group>
          <label for="type">{{$t('Type')}}</label>
          <select readonly class="form-control" v-model="code.type">
            <option value=GOODS selected>{{$t('GOODS')}}</option>
            <option value=WORK>{{$t('WORK')}}</option>
            <option value=SERVICE>{{$t('SERVICE')}}</option>
          </select>
        </div>
        <div class="form-group">
            <label for="subgroup_name">{{$t('Subgroup')}}</label>
            <input  class="form-control" id="subgroup_name" list="subgroups" @keyup="typeahead($event.target.value, 'subgroups')" v-model="type_subgroup">
            <datalist id="subgroups">
              <option v-for="subgroup in subgroups" :value="display('name',subgroup)"></option>
            </datalist>
        </div>
        <div class="form-group">
            <button class="btn btn-outline-primary" type="submit" :disabled="saving">{{$t('Update')}}</button>
            <!--<button :disabled="saving" @click.prevent="onDelete($event)">{{$t('Delete')}}</button>-->
            <router-link class="btn btn-outline-secondary" :to="{ name: 'codes.index' }">{{$t('Cancel')}}</router-link>
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
      message: null,
      loaded: false,
      saving: false,
      validation:{
      	code: "",
      	group: "",
      	subgroup: "",
      	name_kk: "",
      	name_ru: "",
      	description_kk: "",
      	description_ru: ""
      },
      code: {
        id: null,
        code: "",
      	group: null,
      	subgroup: null,
      	name_kk: "",
      	name_ru: "",
      	description_kk: "",
      	description_ru: "",
      },
      type_group: null,
      type_subgroup: null,
      groups:null,
      subgroups:null,
    };
  },
  methods: {
    onSubmit(event) {
      if(this.validated()===true){
        this.message = null
        this.saving = true;
          api.update('code',this.code.id, this.code).then((response) => {
            this.message={}
            this.message.type='alert alert-success'
            this.message.text=this.$i18n.t('Code')+' '+this.$i18n.t('updated successfully') ;
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
	  	this.validation.group=""
	  	this.validation.subgroup=""
	  	var result = true;
	  	if(this.code.code==="" 
	  		|| this.code.code.length!==17){
	  		this.validation.code= this.$i18n.t('Code must be with length 17')
	  		result = false;
	  	}
		if(this.code.name_kk===""){
	  		this.validation.name_kk= this.$i18n.t('Name in kazakh can not be empty')
	  		result = false;
	  	}
	  	if(this.code.name_ru===""){
	  		this.validation.name_ru= this.$i18n.t('Name in russian can not be empty')
	  		result = false;
	  	}
	  	if(this.code.description_kk===""){
	  		this.validation.description_kk= this.$i18n.t('Specify description in kazakh')
	  		result = false;
	  	}
	  	if(this.code.description_ru===""){
	  		this.validation.description_ru= this.$i18n.t('Specify description in russian')
	  		result = false;
	  	}
	  	if(this.code.group===null){
	  		this.validation.group = this.$i18n.t('Choose code group')
	  		result = false
	  	}
	  return result
	},
  onDelete() {
    this.saving = true;
    api.delete('code',this.code.id)
       .then((response) => {
          this.message = 'Code Deleted';
        setTimeout(() => this.$router.push({ name: 'codes.index' }), 1000);
       }).catch(e=>{
        this.basicErrorHandling(e)
       });
  },
    typeahead(input, type) {
      var matched = false;
      if(this.groups && type==='groups'){
        var group;
        if(this.$i18n.locale==='ru')
          group = this.groups.find(group => group.name_ru === input);
        else
          group = this.groups.find(group => group.name_kk === input);
        if(group){
          this.code.group = group
          matched = true
        }
      }else if(this.subgroups && type==='subgroups'){
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
        
        if(type==='groups'){
          var params = {} 
          params.input = input
          params.lang = this.$i18n.locale
          api.search('group', params).then((response) => {
            this.groups = response.data
          }).catch(e => {
            this.basicErrorHandling(e)
          });
        }else if(type==='subgroups'){
          var params = {} 
          params.input = input
          params.lang = this.$i18n.locale
          params.parent = this.display('name',this.code.group)
          api.search('subgroup', {params}).then((response) => {
            this.subgroups = response.data
          }).catch(e => {
            this.basicErrorHandling(e)
          });
        }
      }else if(input.length == 0){
        if(type==='groups'){
          this.code.group=null;
          this.groups=null;
          this.code.subgroup=null;
          this.subgroups=null;
          this.type_subgroup=null
        }
        else if(type==='subgroups'){
          this.code.subgroup=null;
          this.subgroups=null;
        }
      }
    },
  },
  created() {
      api.find(this.$route.params.id)
      .then((response) => {
          setTimeout(() => {
            this.loaded = true;
            this.code = response.data.data;
            if(this.code.group)
              this.type_group = this.display('name',this.code.group)
            if(this.code.subgroup)
              this.type_subgroup = this.display('name',this.code.subgroup)
          }, 1000);
      })
      .catch((e) => {
        this.basicErrorHandling(e)
      });
  }
};
</script>