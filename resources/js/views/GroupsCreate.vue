<template>
    <div class=container>
        <div class="row">
            <div class="col-lg-8 offset-lg-2"> 
        <h2>
            {{$t('Groups create')}}
            <!--<router-link class="btn btn-outline-success float-right" :to="{ name: 'codes.create' }">{{$t('Add New')}}</router-link>-->
        </h2>
        <hr>
        <div v-if="message" class="alert">{{ message }}</div>
        <form @submit.prevent="onSubmit($event)">
          <div class="form-group">
              <label for="group_name">Name KK</label>
              <input class="form-control" id="group_name" v-model="group.name_kk" />
          </div>
          <div class="form-group">
              <label for="group_name">Name RU</label>
              <input class="form-control" id="group_name" v-model="group.name_ru" />
          </div>
          <div class="form-check">
             <input class="form-check-input" type="checkbox" v-model="group.isZKS" /> {{$t('isZKS')}}
          </div>
          <br>
          <div class="form-group">
              <button type="submit" class="btn btn-outline-primary" :disabled="saving">
                  {{ saving ? 'Creating...' : 'Create' }}
              </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
<script>
    import api from '../api/groups';
    import {common} from '../common'
    export default {
      mixins: [common],
        data() {
            return {
                saving: false,
                message: false,
                group: {
                    name_kk: '',
                    name_ru: '',
                    isZKS: false,
                }
            }
        },
        methods: {
            onSubmit($event) {
                this.saving = true
                this.message = false
                api.create(this.group)
                  .then((data) => {
                      this.$router.push({ name: 'groups.index' });
                  })
                  .catch((e) => {
                    if(e.response.status==401)
                    this.redirectToLogin()
                      this.message = e.response.data.message || 'There was an issue creating the group.';
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