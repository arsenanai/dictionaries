 <template>
  <div class=container>
    <div class="row">
      <div class="col-lg-8 offset-lg-2"> 
        <h2>
            {{$t('Users edit')}}
            <!--<router-link class="btn btn-outline-success float-right" :to="{ name: 'codes.create' }">{{$t('Add New')}}</router-link>-->
        </h2>
        <hr>
        <div v-if="message" :class="message.type">{{ message.text }}</div>
        <div v-if="! loaded">{{$t('Loading')}}...</div>
        <form @submit.prevent="onSubmit($event)" v-else>
            <div class="form-group">
                <label for="user_name">{{$t('Username')}}</label>
                <input class="form-control" id="user_name" v-model="user.name" />
                <span v-if="validation.name!==''">{{validation.name}}</span>
            </div>
            <div class="form-group">
                <label for="user_email">{{$t('Email')}}</label>
                <input class="form-control" id="user_email" type="email" v-model="user.email" />
                <span v-if="validation.email!==''">{{validation.email}}</span>
            </div>
            <div class="form-group">
              <label for="user_password">{{$t('Password')}} {{$t('type to change password')}}</label>
              <input class="form-control" id="user_password" type="password" v-model="user.password" />
              <span v-if="validation.password!==''">{{validation.password}}</span>
          </div>
            <div class="form-group">
                <button class="btn btn-outline-primary" type="submit" :disabled="saving">{{$t('Update')}}</button>
                <button class="btn btn-outline-danger" :disabled="saving" @click.prevent="onDelete($event)">{{$t('Delete')}}</button>
                <router-link class="btn btn-outline-secondary" :to="{ name: 'users.index' }">{{$t('Cancel')}}</router-link>
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
      	name:"",
      	email:"",
        password:""
      },
      user: {
        id: null,
        name: "",
        email: ""
      }
    };
  },
  methods: {
    onSubmit(event) {
    	if(this.validated()===true){
    		this.saving = true;
        this.message = null
	        api.update('user',this.user.id, this.user).then((response) => {
	            this.message={}
              this.message.type='alert alert-success'
              this.message.text = this.$i18n.t('User updated');

	            this.user = response.data;
	            setTimeout(() => {
	            	this.message = null
	            	this.$router.push({name:"users.index"});
	            }, 500);
	        }).catch(e => {
            this.basicErrorHandling(e)
            if(e.response.status==422){
                      for(var key in e.response.data.errors)
                          this.validation[key] = this.$i18n.t(e.response.data.errors[key][0])
                    }
	        }).then(_ => this.saving = false);
    	}
    },
    validated(){
	  	this.validation.name=""
	  	this.validation.email=""
      this.validation.password=""
	  	var result = true;
	  	if(this.user.name==="" 
	  		|| this.user.name===null){
	  		this.validation.name = this.$i18n.t('Name can not be empty')
	  		result = false;
	  	}
  		if(this.validateEmail(this.user.email)===false){
  			this.validation.email = this.$i18n.t('Specify valid email')
  			result = false
  	  }
      if(this.stringIsSet(this.user.password)){
        var res = this.validatePassword(this.user.password)
        if(res!='valid'){
          this.validation.password = res
          result = false
        }
      }
  	  return result
	},
  onDelete() {
    this.saving = true;

    api.delete('user',this.user.id)
       .then((response) => {
          this.message = this.$i18n.t('User Deleted');
        setTimeout(() => this.$router.push({ name: 'users.index' }), 1000);
       }).catch((e) => {
        this.basicErrorHandling(e)
      });
  },
	validateEmail(email) {
	    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	    return re.test(String(email).toLowerCase());
	},
  validatePassword(password){
            if(password.length < 6) 
              return "Длина пароля должна быть минимум 6 символов!";
            
            if(password == this.user.name) 
              return "Пароль не может быть равным имени пользователя!";
            var re = /[0-9]/;
            if(!re.test(password)) 
              return "Пароль должен содержать как минимум одну цифру (0-9)!"
            re = /[a-z]/;
            if(!re.test(password)) {
              return "Пароль должен содержать как минимум одну прописную букву (a-z)!"
            }
            re = /[A-Z]/;
            if(!re.test(password)) {
              return "Пароль должен содержать как минимум одну заглавную букву (A-Z)!"
            }
            return "valid"
          },
  },
  created() {
      api.find('user',this.$route.params.id)
      .then((response) => {
          //setTimeout(() => {
            this.loaded = true;
            this.user = response.data.data;
          //}, 1000);
      })
      .catch((e) => {
        this.basicErrorHandling(e)
      });
  }
};
</script>