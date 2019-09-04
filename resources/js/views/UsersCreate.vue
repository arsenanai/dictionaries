<template>
    <div class=container>
        <div class="row">
            <div class="col-lg-8 offset-lg-2"> 
        <h2>
            {{$t('Users create')}}
            <!--<router-link class="btn btn-outline-success float-right" :to="{ name: 'codes.create' }">{{$t('Add New')}}</router-link>-->
        </h2>
        <hr>
        <div v-if="message" class="alert">{{ message }}</div>
        <form @submit.prevent="onSubmit($event)">
          <div class="form-group">
              <label for="user_name">Name</label>
              <input class="form-control" id="user_name" v-model="user.name" />
          </div>
          <div class="form-group">
              <label for="user_email">Email</label>
              <input class="form-control" id="user_email" type="email" v-model="user.email" />
          </div>
          <div class="form-group">
              <label for="user_password">Password</label>
              <input class="form-control" id="user_password" type="password" v-model="user.password" />
          </div>
          <div class="form-group">
              <button class="btn btn-outline-primary" type="submit" :disabled="saving">
                  {{ saving ? 'Creating...' : 'Create' }}
              </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
<script>
    import api from '../api/users';
    import {common} from '../common'
    export default {
      mixins: [common],
        data() {
            return {
                saving: false,
                message: false,
                user: {
                    name: '',
                    email: '',
                    password: '',
                }
            }
        },
        methods: {
            onSubmit($event) {
                this.saving = true
                this.message = false
                api.create(this.user)
                  .then((data) => {
                      this.$router.push({ name: 'users.index' });
                  })
                  .catch((e) => {
                      this.message = e.response.data.message || 'There was an issue creating the user.';
                      if(e.response.status=='401')
                        this.redirectToLogin()
                  })
                  .then(() => this.saving = false)
            }
        }
    }
</script>
<style lang="scss" scoped>
$red: lighten(red, 30%);
$darkRed: darken($red, 50%);

.form-group {
    margin-bottom: 1em;
    label {
        display: block;
    }
}
.alert {
    background: $red;
    color: $darkRed;
    padding: 1rem;
    margin-bottom: 1rem;
    width: 50%;
    border: 1px solid $darkRed;
    border-radius: 5px;
}
</style>